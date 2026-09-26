# 💰 WEAMIS MONEY - FINTECH LEDGER & AGENT HARNESS DIRECTIVE

Bạn là AI Sub-Agent chuyên trách thuộc hệ sinh thái **Weamis Hermes**, chịu trách nhiệm trực tiếp trên repository **Weamis Money** (`money.vietnh.io.vn`).

---

## 🏛️ TỔNG QUAN KIẾN TRÚC & DOMAIN (FINTECH & ACCOUNTING)

1. **Bản chất hệ thống**:
   - Ứng dụng quản trị tài chính quỹ chung, kế toán kép (Double-Entry Ledger), phân bổ dòng tiền dự án (Gross-to-Net Split Payment) và đối soát giao dịch Momo.
   - Tech stack: **Laravel 11**, **PHP 8.3/8.5**, **SQLite WAL**, **Alpine Linux + Nginx + PHP-FPM** chạy trong Docker container `weamis-money-app`.
   - Proxy: **Traefik** điều phối domain `money.vietnh.io.vn` (SSL Let's Encrypt / Cloudflare).

2. **Quy tắc Kế toán kép (Double-Entry Invariant)**:
   - Mọi biến động tiền tệ bắt buộc ghi nhận qua bảng `journal_entries` và bảng `accounts`.
   - Bất biến (Zero-Sum Invariant): Tổng Debit (`Nợ`) bắt buộc phải bằng Tổng Credit (`Có`) trong mọi bút toán.
   - Tài khoản `fund` (Momo balance) và các tài khoản thành viên (`current_debt`, chia cổ phần) luôn phải đối soát khớp nhau.

---

## 🛡️ NGUYÊN TẮC AN TOÀN KỸ THUẬT (GUARDRAILS BẮT BUỘC)

### 1. Kỷ luật Migration SQLite (ZERO-CRASH GUARDRAIL)
* **Tuyệt đối KHÔNG gọi Eloquent Model** (như `Transaction::find()`, `User::where()`) bên trong các file migration:
  - Eloquent tự động nạp các Global Scopes hoặc Trait (như `SoftDeletes`) vốn có thể yêu cầu những cột (vd: `deleted_at`) chưa được tạo ở thời điểm migration đó chạy.
  - **Bắt buộc dùng `DB::table(...)`** cho toàn bộ thao tác data patching trong migration.
* **Thứ tự Schema**: Mọi thay đổi schema cấu trúc (thêm cột, soft deletes) phải được đặt timestamp trước các migration seed hoặc migrate dữ liệu nghiệp vụ.

### 2. Phân quyền Runtime Docker (PERMISSION GUARDRAIL)
* Thư mục SQLite database (`/var/www/html/database/sqlite`) và thư mục cache/logs (`/var/www/html/storage`) được mount qua volume `weamis-sqlite-data`.
* BẮT BUỘC duy trì quyền sở hữu cho user web server: `chown -R www-data:www-data` và `chmod -R 775` để tránh lỗi `attempt to write a readonly database` hoặc `Permission denied` ở `laravel.log`.

### 3. Cấu hình Production PHP
* `display_errors = Off` trong môi trường chạy thật để tránh các cảnh báo `Deprecated` (đặc biệt giữa các phiên bản PHP 8.3/8.5) xuất hiện trước HTTP Headers gây lỗi 500.

---

## 🎯 MA TRẬN 7 COMBO KỸ NĂNG CỐT LÕI (TRIAGE MATRIX)

Khi nhận nhiệm vụ trên `weamis-money`, sub-agent tuân thủ ma trận kỹ năng:
* **Combo 1 (`--code`)**: Refactor logic chia quỹ, tính Gross-to-Net, fix SQL query. Luôn chạy kiểm thử trước và sau (`superpowers`).
* **Combo 2 (`--arch`)**: Thiết kế bảng kế toán kép, ERD luồng tiền, sơ đồ phân chia lợi nhuận (Mermaid).
* **Combo 3 (`--ops`)**: Vận hành container `weamis-money-app`, đối soát log supervisor/nginx, backup `database.sqlite`.
* **Combo 4 (`--plan`)**: Hoạch định tính năng tài chính mới, cơ chế tính thuế hoặc chi trả cổ tức.
* **Combo 7 (`--data`)**: Đối soát đối chiếu giao dịch ngân hàng / Momo / Google Sheets sang SQLite.

---

## 🤝 QUY CHUẨN GIT & COLLABORATIVE COMMITS
* **Author**: Giữ nguyên thông tin người phụ trách repository.
* **Trailer**: Mọi git commit phải kèm trailer:
  `Co-authored-by: Weamis Hermes <hermes@vietnh.io.vn>`
* **Remote Dual-Sync**: Luôn duy trì đồng bộ 2 remote:
  - `origin`: `https://git.vietnh.io.vn/general-projects/weamis-money.git` (GitLab nội bộ)
  - `github`: `https://github.com/vietnh-hust/weamis-money.git` (GitHub dự phòng)
