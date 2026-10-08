# 🚀 HƯỚNG DẪN ĐẨY WEBSITE "THANGCYRUS GAMER" LÊN VERCEL

Thư mục **`SHOPGAME_DEPLOY`** này đã được tối ưu hóa 100% để sẵn sàng đưa lên Vercel:
- Đã cấu hình `vercel.json` định tuyến chuẩn Serverless.
- Đã tạo `api/index.php` làm cổng vào cho Vercel Lambda.
- Đã cấu hình bộ nhớ đệm `/tmp` tự động để không bị lỗi `Read-only filesystem`.
- Đã bao gồm logo, views, router, database và script tự động ngân hàng.

---

## 📌 CÁCH ĐẨY LÊN VERCEL (CHUẨN & DỄ NHẤT QUA GITHUB)

### Bước 1: Tạo Repository trên GitHub
1. Truy cập [github.com](https://github.com) và đăng nhập.
2. Bấm dấu **+** ở góc trên bên phải ➜ Chọn **New repository**.
3. Đặt tên (ví dụ: `thangcyrus-shopgame`) ➜ Chọn **Private** hoặc **Public** ➜ Bấm **Create repository**.

### Bước 2: Đẩy thư mục này lên GitHub
Mở PowerShell hoặc Command Prompt tại thư mục `SHOPGAME_DEPLOY` này và gõ lần lượt các lệnh:

```bash
git init
git add .
git commit -m "Deploy THANGCYRUS GAMER"
git branch -M main
git remote add origin <DÁN_LINK_GITHUB_REPO_CỦA_BRO_VÀO_ĐÂY>
git push -u origin main
```

*(Lưu ý: Nếu chưa cài Git trên máy, bro có thể dùng phần mềm **GitHub Desktop** kéo thả thư mục này vào là đẩy lên được ngay!)*

### Bước 3: Đăng nhập Vercel và Bấm Deploy
1. Truy cập [vercel.com](https://vercel.com) ➜ Đăng nhập bằng tài khoản **GitHub**.
2. Bấm nút **Add New...** ➜ Chọn **Project**.
3. Tìm đến repository `thangcyrus-shopgame` vừa đẩy lên ➜ Bấm **Import**.
4. **Không cần chỉnh sửa gì thêm** (vì file `vercel.json` đã cấu hình sẵn hết).
5. Bấm nút **Deploy**!
6. Đợi khoảng 1 - 2 phút, Vercel sẽ cấp cho bro 1 đường link dạng:
   👉 `https://thangcyrus-shopgame.vercel.app`

---

## ⚠️ LƯU Ý SỐNG CÒN VỀ DATABASE TRÊN VERCEL
* Nền tảng Vercel là **Serverless (Hàm không trạng thái)**, nghĩa là ổ cứng của Vercel là Read-Only.
* Nếu dùng database SQLite cục bộ, mỗi khi Vercel tắt máy chủ tạm thời, dữ liệu đăng ký mới của khách có thể bị reset về ban đầu.
* 👉 **Giải pháp tốt nhất để lưu dữ liệu vĩnh viễn:**
  1. Tạo 1 cơ sở dữ liệu MySQL miễn phí 100% trên [Aiven.io](https://aiven.io) hoặc [TiDB Cloud](https://tidbcloud.com) (không cần thẻ Visa).
  2. Import file `shopgame.sql` vào đó.
  3. Vào trang quản trị dự án trên Vercel ➜ Mục **Settings** ➜ **Environment Variables** ➜ Thêm các biến:
     - `DB_CONNECTION`: `mysql`
     - `DB_HOST`: *<host của Aiven/TiDB>*
     - `DB_PORT`: `3306`
     - `DB_DATABASE`: *<tên db>*
     - `DB_USERNAME`: *<username>*
     - `DB_PASSWORD`: *<password>*
  4. Redploy là shop hoạt động vĩnh viễn, khách mua hàng, nạp tiền lưu trữ an toàn 100%!
