/**
 * ==============================================================================
 * GOOGLE APPS SCRIPT: TỰ ĐỘNG QUÉT EMAIL NGÂN HÀNG & CỘNG TIỀN CHO SHOP
 * Hệ thống: THANGCYRUS GAMER
 * Bản quyền: 100% Miễn phí - Chạy trên Google Cloud 24/7
 * ==============================================================================
 */

// 1. CẤU HÌNH HỆ THỐNG CỦA BRO
const CONFIG = {
  // Điền link website của bro (khi up lên hosting/domain thì thay đổi link này)
  WEBHOOK_URL: "https://ten-mien-cua-bro.com/api/webhook/bank-email",
  
  // Mã bí mật bảo mật (phải trùng với mã trong file .env trên web của bro)
  SECRET_KEY: "ThangCyrusBankSecure2026",
  
  // Tiền tố nạp tiền (khách chuyển khoản ghi: naptien 15)
  PREFIX: "naptien",

  // Bộ lọc tìm email ngân hàng chưa đọc trong hộp thư Gmail của bro
  GMAIL_SEARCH_QUERY: 'is:unread (from:(mbbank.com.vn OR vietcombank.com.vn OR techcombank.com.vn OR acb.com.vn OR tpb.com.vn OR vpbank.com.vn) OR subject:("biến động số dư" OR "thông báo số dư" OR "giao dịch"))'
};

/**
 * Hàm chính: Tự động chạy mỗi 1 phút để quét email ngân hàng mới
 */
function autoScanBankEmails() {
  Logger.log("=== Bắt đầu quét email ngân hàng ===");
  
  const threads = GmailApp.search(CONFIG.GMAIL_SEARCH_QUERY, 0, 10);
  if (threads.length === 0) {
    Logger.log("Không có email ngân hàng mới nào.");
    return;
  }

  for (let i = 0; i < threads.length; i++) {
    const messages = threads[i].getMessages();
    for (let j = 0; j < messages.length; j++) {
      const msg = messages[j];
      
      // Chỉ xử lý tin nhắn chưa đọc
      if (msg.isUnread()) {
        const subject = msg.getSubject();
        const from = msg.getFrom();
        const body = msg.getPlainBody() || msg.getBody();

        Logger.log(`Đang đọc mail từ: ${from} | Tiêu đề: ${subject}`);
        
        // Trích xuất dữ liệu giao dịch từ email
        const txData = parseBankEmail(from, subject, body);
        
        if (txData && txData.amount > 0 && txData.content) {
          Logger.log(`Tìm thấy giao dịch: +${txData.amount}đ | Nội dung: ${txData.content} | Mã GD: ${txData.transaction_id}`);
          
          // Bắn dữ liệu về website của bro
          const result = sendWebhookToShop(txData);
          
          if (result && (result.status === 'success' || result.status === 'already_processed' || result.status === 'ignored')) {
            // Đánh dấu email đã đọc để lần sau không quét lại
            msg.markRead();
            Logger.log(`Đã xử lý xong email: ${txData.transaction_id}`);
          }
        } else {
          Logger.log("Không trích xuất được giao dịch cộng tiền hợp lệ hoặc không có tiền vào.");
        }
      }
    }
  }
}

/**
 * Hàm phân tích nội dung email các ngân hàng tại Việt Nam (MB, VCB, Tech, ACB, TPB...)
 */
function parseBankEmail(from, subject, body) {
  let bankName = "BANK";
  let amount = 0;
  let content = "";
  let transactionId = "";

  const fromLower = from.toLowerCase();
  const text = body.replace(/&nbsp;/g, ' ');

  // 1. Nhận diện ngân hàng
  if (fromLower.includes("mbbank") || text.includes("MB Bank") || text.includes("MBBank")) {
    bankName = "MBBank";
  } else if (fromLower.includes("vietcombank") || text.includes("Vietcombank") || text.includes("VCB")) {
    bankName = "Vietcombank";
  } else if (fromLower.includes("techcombank") || text.includes("Techcombank")) {
    bankName = "Techcombank";
  } else if (fromLower.includes("acb") || text.includes("ACB")) {
    bankName = "ACB";
  } else if (fromLower.includes("tpb") || text.includes("TPBank")) {
    bankName = "TPBank";
  } else if (fromLower.includes("vpbank") || text.includes("VPBank")) {
    bankName = "VPBank";
  }

  // 2. Trích xuất số tiền nhận (+)
  // Thường có dạng: +50,000 VND hoặc Số tiền: +50.000 đ hoặc Số tiền giao dịch: 50,000 VND
  const amountMatch = text.match(/(?:\+|\b(?:Số tiền|Số tiền GD|Phát sinh tăng|Giao dịch|Ghi có):?\s*\+?)\s*([\d,.]+)\s*(?:VND|VNĐ|đ)/i)
                   || text.match(/\+\s*([\d,.]+)\s*(?:VND|VNĐ|đ)/i);
  
  if (amountMatch) {
    amount = parseFloat(amountMatch[1].replace(/[,.]/g, ''));
  }

  // 3. Trích xuất nội dung chuyển khoản
  // Thường có dạng: Nội dung: naptien 15 hoặc Lý do: naptien 15
  const contentMatch = text.match(/(?:Nội dung|Nội dung GD|Lý do|Diễn giải|Chi tiết):?\s*([^\n\r<]+)/i)
                    || text.match(/(?:ND|Description):?\s*([^\n\r<]+)/i);
  if (contentMatch) {
    content = contentMatch[1].trim();
  }

  // 4. Trích xuất mã giao dịch / Số tham chiếu
  const refMatch = text.match(/(?:Mã giao dịch|Mã GD|Số tham chiếu|Số GD|Ref no):?\s*([A-Za-z0-9._-]+)/i)
                || text.match(/\b(FT[0-9A-Z]+)\b/i);
  if (refMatch) {
    transactionId = refMatch[1].trim();
  } else {
    // Nếu ngân hàng không in mã rõ, dùng ID duy nhất tạo theo ngày giờ
    transactionId = "EMAIL_" + new Date().getTime();
  }

  return {
    bank: bankName,
    amount: amount,
    content: content,
    transaction_id: transactionId
  };
}

/**
 * Gửi HTTP POST Webhook về website shop của bro
 */
function sendWebhookToShop(txData) {
  const payload = {
    secret: CONFIG.SECRET_KEY,
    bank: txData.bank,
    amount: txData.amount,
    content: txData.content,
    transaction_id: txData.transaction_id
  };

  const options = {
    method: "post",
    contentType: "application/json",
    headers: {
      "Accept": "application/json",
      "X-Webhook-Secret": CONFIG.SECRET_KEY
    },
    payload: JSON.stringify(payload),
    muteHttpExceptions: true
  };

  try {
    const response = UrlFetchApp.fetch(CONFIG.WEBHOOK_URL, options);
    const responseText = response.getContentText();
    Logger.log("Website phản hồi: " + responseText);
    return JSON.parse(responseText);
  } catch (err) {
    Logger.log("Lỗi gửi webhook: " + err.toString());
    return null;
  }
}

/**
 * Hàm kiểm tra thử nghiệm (chạy thủ công trong Apps Script để test trước)
 */
function testWebhookManual() {
  const sampleData = {
    bank: "MBBank",
    amount: 50000,
    content: "naptien 1 (Test thủ công)",
    transaction_id: "TEST_MANUAL_" + new Date().getTime()
  };
  Logger.log("Bắn thử dữ liệu mẫu về web...");
  const res = sendWebhookToShop(sampleData);
  Logger.log(JSON.stringify(res));
}
