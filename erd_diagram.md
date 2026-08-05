# Weamis Money — Entity Relationship Diagram (ERD)

> Schema sau khi cập nhật: **Temporal Shares** + **Revenue Type** + **Cleanup total_profit**

```mermaid
erDiagram
    users {
        bigint id PK
        string name
        string username UK
        string email UK
        string password
        enum role "member | lead | admin"
        string avatar "nullable"
        decimal share_percentage "% chia quỹ toàn cục"
        decimal current_debt "computed - nợ hiện tại"
        timestamps created_at
        timestamps updated_at
    }

    funds {
        bigint id PK
        string name
        decimal balance "Số dư quỹ hiện tại"
        timestamps created_at
        timestamps updated_at
    }

    transactions {
        bigint id PK
        bigint fund_id FK
        bigint user_id FK
        bigint project_id FK "nullable"
        bigint responsible_user_id FK "nullable"
        bigint claimant_user_id FK "nullable"
        enum type "contribution | expense | loan | repayment | withdrawal"
        decimal amount
        string description
        string billing_cycle "nullable - VD: 2026-08"
        enum revenue_type "development | subscription | NULL"
        enum evidence_type "file | link | text | none"
        text evidence_value "nullable"
        enum status "pending | approved | rejected"
        bigint approved_by FK "nullable"
        softDeletes deleted_at
        timestamps created_at
        timestamps updated_at
    }

    projects {
        bigint id PK
        string name
        string code UK
        text description "nullable"
        enum status "active | completed | cancelled"
        date release_date "nullable"
        decimal weamis_fund_percentage "% trích về quỹ chung"
        decimal fund_credited_amount "Số tiền đã trích khi hoàn thành"
        bigint lead_user_id FK "nullable"
        bigint created_by_user_id FK "nullable"
        softDeletes deleted_at
        timestamps created_at
        timestamps updated_at
    }

    project_members {
        bigint id PK
        bigint project_id FK
        bigint user_id FK
        decimal share_percentage "% cổ phần giai đoạn"
        date effective_from "Ngày hiệu lực giai đoạn"
        timestamps created_at
        timestamps updated_at
    }

    distributions {
        bigint id PK
        bigint fund_id FK
        decimal total_amount
        string note "nullable"
        json payout_details
        bigint created_by FK
        timestamps created_at
        timestamps updated_at
    }

    sessions {
        string id PK
        bigint user_id FK "nullable"
        string ip_address "nullable"
        text user_agent "nullable"
        longText payload
        integer last_activity
    }

    funds ||--o{ transactions : "has many"
    users ||--o{ transactions : "creates"
    projects ||--o{ transactions : "linked to"
    users ||--o{ transactions : "approved_by"
    users ||--o{ transactions : "responsible_user"
    users ||--o{ transactions : "claimant_user"

    projects ||--o{ project_members : "has share periods"
    users ||--o{ project_members : "member of"

    users ||--o{ projects : "lead_user"
    users ||--o{ projects : "created_by"

    funds ||--o{ distributions : "has many"
    users ||--o{ distributions : "created_by"

    users ||--o{ sessions : "has sessions"
```

---

## Tóm Tắt Thay Đổi Schema

| Thay đổi | Bảng | Chi tiết |
|---|---|---|
| ✅ **Thêm** | `project_members.effective_from` | Ngày hiệu lực cổ phần → hỗ trợ cổ phần thay đổi theo thời gian |
| ✅ **Thêm** | `transactions.revenue_type` | `development` / `subscription` → tách tiền phát triển & thuê bao |
| ✅ **Xóa** | `funds.total_profit` | Không còn ý nghĩa sau khi bỏ Túi Thần Tài |
| ✅ **Thay đổi** | `project_members` UNIQUE | Từ `(project_id, user_id)` → `(project_id, user_id, effective_from)` |

## Đánh Giá 3NF

| Tiêu chí | Kết quả |
|---|---|
| 1NF - Mọi giá trị là atomic | ✅ Đạt (trừ `distributions.payout_details` là JSON, chấp nhận được) |
| 2NF - Không phụ thuộc một phần | ✅ Đạt |
| 3NF - Không phụ thuộc bắc cầu | ⚠️ `funds.balance` và `users.current_debt` là computed → **denormalization có chủ đích** (performance trade-off) |

> [!NOTE]
> Schema hiện tại đạt chuẩn **3NF với denormalization hợp lý** — `funds.balance` được giữ lại để tránh tính lại từ hàng trăm transactions mỗi request.
