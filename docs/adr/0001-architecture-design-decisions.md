# ADR 0001: Kiến trúc & Quyết định Thiết kế cho Hệ thống Quản lý Quỹ Nhóm `weamis-money`

* **Trạng thái**: Accepted (Đã chấp nhận)
* **Ngày quyết định**: 28-07-2026
* **Người thực hiện**: Antigravity & Team Weamis
* **Ngôn ngữ**: Việt Nam / Markdown

---

## 1. Bối cảnh & Đặt vấn đề (Context & Problem Statement)

Dự án `weamis-money` được phát triển nhằm giải quyết nhu cầu quản lý thu chi, góp quỹ, vay/trả nợ nội bộ và phân chia lợi nhuận/quỹ cho một team (đặc biệt là các dự án nhóm, startup nhỏ hoặc quỹ sinh hoạt).

Dựa trên phân tích từ hình ảnh thực tế dịch vụ Quỹ Nhóm MoMo ("Trả nợ thuê Ltd"), hệ thống cần giải quyết các bài toán nghiệp vụ sau:
1. Ghi nhận các nguồn tiền vào quỹ (Góp quỹ định kỳ, góp % thu nhập/doanh thu cá nhân).
2. Theo dõi các khoản chi tiêu chung của team (Networking, tri ân, họp hành...).
3. Quản lý việc thành viên vay vốn tạm thời từ quỹ và lịch sử trả nợ cá nhân.
4. Phân chia quỹ dư / lợi nhuận cho các thành viên dựa trên Tỷ lệ % đóng góp/cổ phần được quy định.
5. Kiểm soát an toàn dòng tiền bằng quy trình phê duyệt yêu cầu rút/vay tiền bởi Chủ quỹ (Admin).

---

## 2. Các Động lực Quyết định (Decision Drivers)

* **Tương thích môi trường**: Chạy trực tiếp và mượt mà trên môi trường XAMPP (PHP 8.2 + MySQL) sẵn có của người dùng.
* **Minh bạch tài chính**: Phân biệt rõ ràng giữa "Tiền góp quỹ ròng" (tăng số dư quỹ) và "Tiền vay cá nhân" (tạo dư nợ cá nhân), tránh nhầm lẫn về mặt kế toán.
* **Đơn giản & Chính xác**: Tính toán số tiền phân chia theo % cổ phần cố định nhanh chóng, không gây sai lệch số dư.
* **Trải nghiệm người dùng (UX/UI)**: Giao diện hiện đại, trực quan, mang phong cách MoMo Pink Theme với màu sắc sinh động, responsive trên cả mobile và desktop.

---

## 3. Các Quyết định Kiến trúc & Thiết kế (Architecture & Design Decisions)

### 3.1. Mô hình Phân loại Giao dịch (Transaction Domain Model)
**Quyết định**: Phân tách rõ ràng 5 loại giao dịch trong bảng `transactions`:
1. `contribution` (Góp quỹ): Tiền vào quỹ -> Tăng `funds.balance`.
2. `expense` (Chi tiêu chung): Tiền ra khỏi quỹ phục vụ mục đích chung -> Giảm `funds.balance`.
3. `loan` (Vay quỹ cá nhân): Tiền rút từ quỹ cho cá nhân vay -> Giảm `funds.balance`, Tăng `users.current_debt`.
4. `repayment` (Trả nợ vay): Tiền cá nhân trả lại quỹ -> Tăng `funds.balance`, Giảm `users.current_debt`.
5. `distribution` (Chia lợi nhuận/quỹ): Xuất tiền từ quỹ chia cho mng -> Giảm `funds.balance`, ghi vết sổ chia tiền.

*Lý do*: Đáp ứng chính xác các usecase trong thực tế (VD: Việt "vay 3 củ đóng học phí" rồi "CTO trả nợ tiền học", Đức "giai cuu Chi Pheo mua World cup", Kiên "rút quỹ networking").

---

### 3.2. Công thức & Cơ chế Phân chia Tiền theo % (Percentage & Profit Distribution Model)
**Quyết định**: Sử dụng mô hình **Cổ phần/Tỷ lệ % gán cố định cho thành viên** (`users.share_percentage`).
* Mỗi thành viên được thiết lập tỷ lệ % cổ phần (Ví dụ: Thành viên A 40%, Thành viên B 30%, Thành viên C 30% -> Tổng 100%).
* Khi thực hiện đợt Chia tiền/Chia lợi nhuận với tổng số tiền $S$:
  $$\text{Số tiền nhận của thành viên } i = S \times \frac{\text{share\_percentage}_i}{100}$$
* Tiền góp quỹ hàng tháng của các thành viên đóng vai trò theo dõi chỉ tiêu/nghĩa vụ đóng góp, không làm biến động liên tục tỷ lệ % cổ phần ròng để tránh phức tạp không cần thiết.

---

### 3.3. Quy trình Phê duyệt Giao dịch (Approval Workflow)
**Quyết định**: Áp dụng quy trình kiểm duyệt bất đồng bộ (Asynchronous Approval State):
* **Góp quỹ (`contribution`) & Trả nợ (`repayment`)**: Trạng thái mặc định là `approved` (hoặc ghi nhận tức thì), số dư quỹ và nợ cá nhân được cập nhật ngay lập tức.
* **Chi tiêu chung (`expense`) & Vay quỹ (`loan`)**: Trạng thái khởi tạo là `pending`. Hệ thống chỉ cập nhật số dư quỹ và dư nợ cá nhân khi Chủ quỹ (Admin) bấm **Approve**. Nếu bấm **Reject**, yêu cầu bị hủy mà không tác động tới tài chính.

---

### 3.4. Công nghệ & Stack Kỹ thuật (Tech Stack Selection)
**Quyết định**:
* **Backend Framework**: Laravel 11 / PHP 8.2 (Sử dụng MVC pattern chuẩn, Eloquent ORM, DB Migrations & Seeders).
* **Database**: MySQL trên XAMPP (`localhost:3306`, database: `weamis_money`).
* **Frontend**: Laravel Blade templates + Alpine.js (cho tương tác modal, popup, dynamic calculation) + Custom CSS/Tailwind CSS với hệ màu MoMo (Deep Pink `#d82d8b`, Vibrant Accents, Soft Dark/Light Mode).

---

## 4. Hệ quả của Quyết định (Consequences)

### 4.1. Tích cực (Positive)
* **Toàn vẹn dữ liệu**: Quy trình Duyệt (Pending -> Approved) bảo vệ quỹ nhóm khỏi việc thành viên tự ý rút tiền mà không có sự đồng ý của Chủ quỹ.
* **Minh bạch dư nợ**: Tách biệt rõ ràng khoản vay cá nhân với chi tiêu tập thể, giúp team theo dõi ai đang nợ quỹ bao nhiêu tiền (như khoản nợ 3 triệu của Việt hay 1 triệu của Đức).
* **Dễ dàng chia tiền**: Nhập số tiền muốn chia là hệ thống tự động tính ra bảng phân bổ tiền cho từng người theo đúng % hợp đồng/cổ phần.
* **Dễ bảo trì & cài đặt**: Chạy trực tiếp trên XAMPP mà không cần cấu hình phức tạp.

### 4.2. Thách thức & Hạn chế (Negative & Trade-offs)
* Admin/Chủ quỹ cần thực hiện phê duyệt thủ công cho các lệnh rút tiền.
* Tỷ lệ % cổ phần là cố định cho đến khi Admin chủ động điều chỉnh lại trong cài đặt thành viên.

---

## 5. Dữ liệu Mẫu Ban đầu (Initial Seed Data)

Dựa trên ảnh chụp thực tế màn hình MoMo "Trả nợ thuê Ltd":
* **Số dư quỹ ban đầu**: `7.028.106 VNĐ`
* **Tổng lợi nhuận tích lũy**: `126.160 VNĐ`
* **Thành viên khởi tạo**:
  1. Nguyễn Hoàng Việt (Chủ quỹ / CTO - Tỷ lệ 40%)
  2. Nguyễn Trung Kiên (Thành viên - Tỷ lệ 30%)
  3. Nguyễn Quý Đức (Thành viên - Tỷ lệ 30%)
* **Lịch sử giao dịch ban đầu**:
  * `+900.000đ` - Nguyễn Hoàng Việt (Góp quỹ - CTO góp cns tháng 7)
  * `-535.000đ` - Nguyễn Trung Kiên (Chi tiêu - Quỹ networking với anh 3T)
  * `+700.000đ` - Nguyễn Hoàng Việt (Góp quỹ - Góp 10% cns)
  * `+3.000.000đ` - Nguyễn Hoàng Việt (Trả nợ - CTO trả nợ tiền học)
  * `-3.000.000đ` - Nguyễn Hoàng Việt (Vay quỹ - Vay 3 củ đóng học phí)
  * `-1.000.000đ` - Nguyễn Quý Đức (Vay quỹ - Giai cuu Chi Pheo mua World cup)

---

## 6. Kế hoạch Kiểm thử & Xác minh (Verification)

1. **Khởi tạo & Migration**: Chạy `php artisan migrate:fresh --seed` thành công không có lỗi syntax SQL.
2. **Kiểm thử Luồng Vay & Trả**: Thành viên A vay 1.000.000đ -> Admin duyệt -> Số dư quỹ giảm 1.000.000đ, Dư nợ của A tăng 1.000.000đ. Thành viên A trả 1.000.000đ -> Số dư quỹ khôi phục, Dư nợ của A về 0.
3. **Kiểm thử Bộ tính Chia tiền**: Nhập tổng số tiền chia `10.000.000đ` -> Kết quả nhận của Việt (40%) = `4.000.000đ`, Kiên (30%) = `3.000.000đ`, Đức (30%) = `3.000.000đ`.
