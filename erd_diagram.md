# Weamis Money Database ERD

Sơ đồ Thực thể Liên kết (ERD) mô tả kiến trúc Kế toán kép (Double-Entry Bookkeeping) mới của hệ thống.

```mermaid
erDiagram
    %% Core Entities (Factors)
    USER {
        int id PK
        string name
        string email
        string role
    }
    
    PROJECT {
        int id PK
        string name
        string code
        string status
        decimal weamis_fund_percentage
    }
    
    FUND {
        int id PK
        string name
    }

    PROJECT_MEMBER {
        int id PK
        int project_id FK
        int user_id FK
        decimal share_percentage
        date effective_from
    }

    %% Double-Entry Accounting Core
    ACCOUNT {
        int id PK
        string type "enum: user, project, fund, external"
        string owner_type "Polymorphic (App\\Models\\User, etc.)"
        int owner_id "Polymorphic ID"
        string name "Ví Nguyễn Hoàng Việt, Dự án BMG..."
        decimal balance "Số dư hiện tại"
    }

    TRANSACTION {
        int id PK
        string type "Giao dịch gốc (contribution, withdrawal...)"
        decimal amount "Tổng số tiền cục bill"
        string description
        string status
        date created_at
    }

    JOURNAL_ENTRY {
        int id PK
        int transaction_id FK
        int from_account_id FK "Ví Nguồn"
        int to_account_id FK "Ví Đích"
        decimal amount "Số tiền tách"
        string memo "Diễn giải chi tiết dòng"
    }

    %% Relationships
    USER ||--o{ PROJECT_MEMBER : "has"
    PROJECT ||--o{ PROJECT_MEMBER : "has"
    
    USER ||--o| ACCOUNT : "owns (Polymorphic)"
    PROJECT ||--o| ACCOUNT : "owns (Polymorphic)"
    FUND ||--o| ACCOUNT : "owns (Polymorphic)"

    TRANSACTION ||--o{ JOURNAL_ENTRY : "splits into"
    ACCOUNT ||--o{ JOURNAL_ENTRY : "sends money (from_account)"
    ACCOUNT ||--o{ JOURNAL_ENTRY : "receives money (to_account)"
```

### Chú thích thiết kế mới:
1. **Polymorphic Accounts (Ví Đa hình):** Thay vì bảng giao dịch gán trực tiếp cho User hay Project, giờ đây `User`, `Project`, và `Fund` đều sở hữu một `Account` (Ví) của riêng mình.
2. **Tách Giao dịch (Split Transactions):** Một `Transaction` (ví dụ bill chuyển khoản ngân hàng 3 triệu) sẽ sinh ra nhiều dòng `Journal Entry` (Bút toán kép), giúp điều hướng số tiền chính xác từ Ví Ngân Hàng sang Ví của nhiều Dự án khác nhau.
3. **Tính toán Số dư (Balance):** Mọi công thức tính cổ phần, tài sản ròng giờ đây chỉ cần Query tổng thu chi trên bảng `Journal Entry` thuộc về `Account` tương ứng, tuân thủ đúng chuẩn OOP Factor-based.
