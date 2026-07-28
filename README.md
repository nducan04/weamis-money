# 💲 Weamis Money - Quản Lý Thu Chi & Quỹ Team

Hệ thống quản lý tài chính, quỹ nhóm và lịch sử thu chi chuyên nghiệp xây dựng trên nền tảng Laravel 11, Tailwind CSS và Alpine.js.

## 🚀 Tính Năng Nổi Bật
- **Thống Kê Cá Nhân & Cổ Phần**: Tự động tính toán cổ phần %, tổng tiền đã góp, đã vay, đã trả nợ và số tiền dự kiến nhận khi chia quỹ.
- **Quản Lý Giao Dịch**: Hỗ trợ 5 loại giao dịch (Góp quỹ, Chi tiêu, Vay cá nhân, Trả nợ, Chia %).
- **Duyệt Yêu Cầu**: Hệ thống chờ duyệt đối với các yêu cầu Chi/Vay.
- **Biểu Đồ Thống Kê ApexCharts**: Biểu đồ Donut tỷ lệ dòng tiền và Biểu đồ Cột cổ phần thành viên (tự động thích ứng giao diện Sáng/Tối).
- **Lọc Tức Thì & Phân Trang Client-Side**: Lọc tìm kiếm và phân trang mượt mà 0ms không cần reload lại trang.
- **Avatar Đám Mây**: Hỗ trợ lưu trữ ảnh đại diện trực tiếp trên đám mây CDN (Catbox / ImgBB) giúp tiết kiệm dung lượng ổ đĩa.

## 🛠️ Yêu Cầu Hệ Thống
- PHP >= 8.2
- Composer
- MySQL / MariaDB (XAMPP / Laragon)

## 💻 Hướng Dẫn Cài Đặt Local

1. **Clone project:**
   ```bash
   git clone https://github.com/nducan04/weamis-money.git
   cd weamis-money
   ```

2. **Cài đặt dependencies:**
   ```bash
   composer install
   ```

3. **Cấu hình môi trường (.env):**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Khởi tạo cơ sở dữ liệu:**
   ```bash
   php artisan migrate --seed
   ```

5. **Khởi chạy ứng dụng:**
   ```bash
   php artisan serve
   ```

---
*Phát triển bởi Team Weamis.*
