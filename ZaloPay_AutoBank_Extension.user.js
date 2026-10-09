// ==UserScript==
// @name         AutoBank ZaloPay to ShopGame
// @namespace    https://shop-thangcyrus.vercel.app/
// @version      1.0
// @description  Tự động đọc tin nhắn biến động nhận tiền ZaloPay trên Zalo Web và cộng tiền cho shop game
// @author       ThangCyrus
// @match        https://chat.zalo.me/*
// @grant        GM_xmlhttpRequest
// @connect      shop-thangcyrus.vercel.app
// @run-at       document-idle
// ==/UserScript==

(function() {
    'use strict';

    // 1. CẤU HÌNH HỆ THỐNG
    const CONFIG = {
        WEBHOOK_URL: "https://shop-thangcyrus.vercel.app/api/webhook/bank-email",
        SECRET_KEY: "ThangCyrusBankSecure2026",
        PREFIX: "naptien",
        CHECK_INTERVAL_MS: 3000 // Quét tin nhắn mỗi 3 giây
    };

    console.log("%c[AutoBank ZaloPay] Script đã khởi động trên Zalo Web!", "color: #10b981; font-weight: bold; font-size: 14px;");

    // Tạo thanh widget trạng thái hiển thị trên màn hình Zalo Web
    function createStatusWidget() {
        if (document.getElementById('zalopay-autobank-widget')) return;

        const widget = document.createElement('div');
        widget.id = 'zalopay-autobank-widget';
        widget.innerHTML = `
            <div style="position: fixed; bottom: 20px; right: 20px; z-index: 999999; background: #1e293b; color: #fff; padding: 12px 16px; border-radius: 12px; font-family: sans-serif; box-shadow: 0 10px 25px rgba(0,0,0,0.3); border: 1px solid #10b981; font-size: 13px; display: flex; align-items: center; gap: 10px;">
                <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #10b981; animation: pulse 1.5s infinite;"></span>
                <div>
                    <div style="font-weight: bold; color: #10b981;">AutoBank ZaloPay: Đang chạy 🟢</div>
                    <div id="zalopay-last-status" style="font-size: 11px; color: #94a3b8; margin-top: 2px;">Chờ giao dịch mới...</div>
                </div>
            </div>
            <style>
                @keyframes pulse { 0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); } 70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); } 100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); } }
            </style>
        `;
        document.body.appendChild(widget);
    }

    function updateWidgetStatus(text, isSuccess = false) {
        const el = document.getElementById('zalopay-last-status');
        if (el) {
            el.textContent = text;
            el.style.color = isSuccess ? '#34d399' : '#94a3b8';
        }
    }

    // Danh sách các giao dịch đã xử lý để tránh cộng tiền trùng
    function getProcessedTransactions() {
        try {
            return JSON.parse(localStorage.getItem('ZALOPAY_PROCESSED_TXS') || '[]');
        } catch (e) {
            return [];
        }
    }

    function saveProcessedTransaction(txId) {
        try {
            const list = getProcessedTransactions();
            if (!list.includes(txId)) {
                list.push(txId);
                // Giữ lại 200 mã gần nhất
                if (list.length > 200) list.shift();
                localStorage.setItem('ZALOPAY_PROCESSED_TXS', JSON.stringify(list));
            }
        } catch (e) {}
    }

    // Bóc tách thông tin giao dịch nhận tiền từ tin nhắn Zalo
    function parseZaloPayMessage(rawText) {
        const text = rawText.replace(/[\r\n]+/g, ' ');

        // Kiểm tra xem tin nhắn có chứa dấu hiệu nhận tiền không
        const isReceiveMoney = /nhận\s+(?:được|tiền|thành\s*công)|chuyển\s+tiền\s+đến|\+\s*[\d,.]+\s*(?:đ|VND)/i.test(text);
        if (!isReceiveMoney) return null;

        // 1. Trích xuất số tiền nhận
        let amount = 0;
        const amountMatch = text.match(/(?:nhận\s*(?:được)?\s*|\+\s*|số\s*tiền:?\s*)([\d,.]+)\s*(?:đ|vnd|vnđ)/i);
        if (amountMatch) {
            amount = parseFloat(amountMatch[1].replace(/[,.]/g, ''));
        }

        if (amount < 1000) return null;

        // 2. Trích xuất nội dung chuyển khoản (lời nhắn)
        let content = "";
        const contentMatch = text.match(/(?:lời\s*nhắn|nội\s*dung|ghi\s*chú|nd):?\s*([^\n\r<.,]+)/i)
                          || text.match(/(naptien\s*\d+)/i);
        if (contentMatch) {
            content = contentMatch[1].trim();
        }

        // 3. Trích xuất mã giao dịch (hoặc tạo mã duy nhất từ chuỗi và số tiền)
        let txId = "";
        const idMatch = text.match(/(?:mã\s*(?:giao\s*dịch|gd)|trans(?:action)?\s*id):?\s*([A-Za-z0-9._-]+)/i);
        if (idMatch) {
            txId = idMatch[1].trim();
        } else {
            // Tạo hash ngắn từ nội dung và số tiền
            const cleanHash = btoa(encodeURIComponent(text.substring(0, 100))).replace(/[^a-zA-Z0-9]/g, '').substring(0, 16);
            txId = "ZP_" + cleanHash;
        }

        return {
            amount: amount,
            content: content,
            transaction_id: txId,
            raw: text
        };
    }

    // Gửi webhook về website shop
    function sendWebhook(txData) {
        const processed = getProcessedTransactions();
        if (processed.includes(txData.transaction_id)) {
            return; // Đã xử lý rồi
        }

        console.log("[AutoBank ZaloPay] Phát hiện giao dịch mới:", txData);
        updateWidgetStatus(`Đang cộng +${txData.amount.toLocaleString()}đ...`);

        const payload = {
            secret: CONFIG.SECRET_KEY,
            bank: "ZaloPay",
            amount: txData.amount,
            content: txData.content,
            transaction_id: txData.transaction_id
        };

        const postOptions = {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(payload)
        };

        fetch(CONFIG.WEBHOOK_URL, postOptions)
            .then(res => res.json())
            .then(data => {
                console.log("[AutoBank ZaloPay] Phản hồi từ web:", data);
                if (data.status === 'success' || data.status === 'already_processed' || data.status === 'ignored') {
                    saveProcessedTransaction(txData.transaction_id);
                    updateWidgetStatus(`Cộng thành công +${txData.amount.toLocaleString()}đ!`, true);
                } else {
                    updateWidgetStatus(`Lỗi: ${data.message || 'Không thành công'}`);
                }
            })
            .catch(err => {
                console.error("[AutoBank ZaloPay] Lỗi kết nối Webhook:", err);
                updateWidgetStatus("Lỗi kết nối Webhook website!");
            });
    }

    // Quét các tin nhắn trên màn hình chat Zalo
    function scanZaloMessages() {
        createStatusWidget();

        // Tìm tất cả các phần tử tin nhắn trong khung chat
        const msgElements = document.querySelectorAll('.card-content, .message-view, .chat-message, [data-id]');
        msgElements.forEach(el => {
            const text = el.innerText || el.textContent;
            if (text && (text.includes('naptien') || text.includes('ZaloPay') || text.includes('nhận được'))) {
                const txData = parseZaloPayMessage(text);
                if (txData && txData.amount > 0 && txData.content) {
                    sendWebhook(txData);
                }
            }
        });
    }

    // Khởi động chu kỳ quét tự động
    setInterval(scanZaloMessages, CONFIG.CHECK_INTERVAL_MS);
    setTimeout(scanZaloMessages, 2000);

})();
