*User prompt: Bạn nói với công thức paid in capital bằng tổng tiền các khoản tiền ròng, cụ thể là 90% từ dự án và tiền dự án quỹ. Thế nhưng tuy nhiên, không phải dự án nào cũng có cái mức giá như nhau. Cụ thể, với dự án CNF thì tôi góp vào 10% của tôi là 900.000 chẳng hạn, thế nhưng người khác góp vào 10% của họ chỉ là 10.000 chẳng hạn. Vậy điều này mình sẽ tính cái vốn capital nó sẽ không chuẩn xác, mà cái vốn capital nên tính toàn bộ cả 10% thu nhập dự án tech, tech lead vào quỹ unit nữa để mình tính được capital chuẩn xác nhất. Thì điều này mới gây khó dễ, thì chúng ta cần tìm ra một giải pháp để tính được công bằng đầy đủ nhất về lượng tiền mình đã trả lại cho công ty. Cụ thể ví dụ như ban đầu là dự án sẽ có tỷ lệ là 0,45, 0,45, 0,1 thì cái capital tôi nghĩ góp thì nó sẽ là 0,5 và 0,5. Bạn thấy sao?*

Response: Phân tích của bạn rất chính xác và chạm đúng **bản chất của bài toán Định giá Cống hiến (Sweat Equity & Revenue Share Attribution)** trong quản trị tài chính FinTech\!

Sở dĩ có sự lấn cấn này là vì hệ thống hiện tại đang bị **lẫn lộn giữa 2 dòng tiền**:

> 1. **Dòng tiền Thu nhập cá nhân thực nhận (Net Cash Payout):** Số tiền thực tế chuyển vào ví cá nhân sau khi đã trừ 10% phí công ty (chiếm 90%).  
> 2. **Giá trị Cống hiến / Quy mô Vốn góp (Capital Credit / Valuation Contribution):** Tổng giá trị công sức/dự án mà cá nhân đó mang về cho công ty (chiếm 100% giá trị Gross ban đầu).

### **1\. Tại sao cách tính cũ (chỉ cộng 90% Net) lại gây mất công bằng?**

Giống như ví dụ bạn đưa ra:

> * **Bạn (Project Lead/Tech Lead):** Mang về Dự án CNS 9.000.000 VNĐ. Bạn trích 10% đóng góp cho Quỹ Weamis là **900.000 VNĐ**.  
> * **Thành viên B:** Làm dự án nhỏ 100.000 VNĐ. Trích 10% đóng góp cho Quỹ Weamis chỉ là **10.000 VNĐ**.

Nếu chỉ dùng số tiền 90% Net để tính "Vốn góp", ta đã vô tình **bỏ qua giá trị 900.000 VNĐ mà bạn đã tạo ra cho Quỹ Weamis**. Bạn là người nộp cho công ty 900.000 VNĐ nhưng lại không được ghi nhận khoản đó vào điểm Vốn góp (Capital) của mình\!

### **2\. Phân tích đề xuất của bạn (Tỷ lệ 0,5 \- 0,5 thay vì 0,45 \- 0,45 \- 0,10)**

Đề xuất của bạn về việc quy đổi tỷ lệ (0.45−0.45−0.10) thành (0.50−0.50) để tính Vốn góp là **hoàn toàn chính xác về mặt bản chất tài chính**.

> * **Về dòng tiền mặt (Cashflow):** Mỗi người nhận 45% (0.45), Quỹ nhận 10% (0.10).  
> * **Về giá trị Vốn góp (Capital Credit):** Vì khoản 10% Quỹ nhận được **chính là nhờ công sức 50−50 của 2 người tạo ra**, nên khoản 10% đó thực chất là **Vốn do 2 bạn cống hiến/nộp vào Quỹ**.  
> * Do đó, đóng góp Vốn (Capital Contribution) của mỗi người trong dự án này phải được ghi nhận đúng bằng **50% Gross** (0.50).

### **3\. Giải pháp FinTech chuẩn xác: Mô hình Dual-Ledger (Sổ cái Kép)**

Để giải quyết bài toán này một cách triệt để trên hệ thống phần mềm, bạn cần tách bạch thành **2 loại Sổ cái / 2 Chỉ số độc lập**:

                               `┌──► 1. CASH LEDGER (Sổ tiền mặt / Net Payout)`  
                               `│    • Ghi nhận: 90% Gross (Thu nhập thực nhận)`  
`[ MỘT DỰ ÁN GROSS (100%) ] ────┼──► • Dùng để: Rút tiền mặt, vay nợ, chi tiêu cá nhân`  
                               `│`  
                               `└──► 2. CAPITAL LEDGER (Sổ Cổ phần / Capital Credit)`  
                                    `• Ghi nhận: 100% Gross chia theo tỷ lệ làm dự án (0.5 - 0.5)`  
                                    `• Dùng để: Tính tỷ lệ Cổ phần (% Equity) & Quyền lợi công ty`

#### **Chi tiết 2 thước đo trong hệ thống:**

| Tên chỉ số trong hệ thống | Công thức tính cho mỗi cá nhân | Ý nghĩa & Mục đích sử dụng |
| :---- | :---- | :---- |
| **1\. Net Cash Balance** *(Số dư Tiền mặt)* | \= 90% Thu nhập dự án \+ Nạp quỹ \- Chi tiêu cá nhân | **Tiền thật trong túi.** Là số tiền thành viên có quyền rút ra tiêu xài hoặc dùng để thanh toán các khoản nợ cá nhân. |
| **2\. Contributed Capital / Equity Points** *(Vốn Cống hiến Tích lũy)* | \= 100% Gross Dự án (chia theo % làm) \+ Tiền túi tự nạp | **Điểm Vốn góp / Giá trị cống hiến.** Là thước đo công bằng nhất phản ánh tổng giá trị tài chính mà cá nhân đó đã tạo ra cho toàn bộ hệ thống Weamis. |

### **4\. Minh họa bằng con số cụ thể**

Giả sử trong tháng có 2 dự án phát sinh:

> * **Dự án A (CNS):** Gross \= 9.000.000 VNĐ (Việt làm 100%).  
> * **Dự án B:** Gross \= 100.000 VNĐ (Thành viên B làm 100%).

#### **Cách hạch toán trên phần mềm:**

> 1. **Về mặt Tiền mặt (Cash Balance \- Chia 90%):**  
   * **Việt:** Được rút \+8.100.000 VNĐ (90%).  
   * **Thành viên B:** Được rút \+90.000 VNĐ (90%).  
   * **Quỹ Weamis:** Có \+910.000 VNĐ tiền mặt để mua vật tư, trả tiền server.  
> 2. **Về mặt Vốn ròng & Cổ phần (Cap Table \- Tính theo 100% Gross):**  
   * **Vốn góp của Việt:** Được ghi nhận \+9.000.000 VNĐ (100% Gross dự án A).  
   * **Vốn góp của B:** Được ghi nhận \+100.000 VNĐ (100% Gross dự án B).  
   * **Tổng Vốn cống hiến toàn công ty:** 9.100.000 VNĐ.  
   * **Tỷ lệ cổ phần (Capital Share %):**  
     * Tỷ lệ của Việt \= 9.100.0009.000.000​≈98,9%  
     * Tỷ lệ của B \= 9.100.000100.000​≈1,1%

### **5\. Tóm lại thuật ngữ & logic cần đưa vào Code**

> * **Gross Project Contribution (GPC):** Giá trị Gross của dự án mà nhân sự tham gia đóng góp.  
> * **Member Capital Score / Earned Capital:** \= Gross Project Contribution \+ Direct Cash Capital Injected. Chỉ số này **chỉ có tăng, không giảm** khi mua thiết bị chung, và dùng để tính **% Ownership (Cổ phần)**.  
> * **Member Wallet / Cash Balance:** \= (Gross Project Contribution \* (1 \- Take\_Rate)) \- Withdrawals \- Personal Expenses. Dùng để quản lý **Tiền mặt thực nhận**.

Với cách thiết kế này, bạn vừa ghi nhận được chính xác khoản 10% mà Tech Lead/Project Lead nộp về cho Quỹ công ty, vừa ghi nhận chuẩn xác tỷ lệ cống hiến vốn, đồng thời vẫn giữ nguyên được tiền mặt 90% chia cho anh em rút về.

---

*User prompt: Chốt cái công nghệ net balance, cash balance trên và contributed capital trên và viết thành một cái ADR cho tôi để tôi viết thành một tài liệu chi tiết đầy đủ toàn diện nhất, cụ thể nhất, nêu ra các ví dụ, định nghĩa khái niệm, các cái từ khóa liên quan đến các cái ý tưởng bạn nhắc ngay bên trên để tôi có thể đưa cho agent Antigravity có thể code ra và thiết kế dự án cho nghiệp vụ này, cho tính năng này.*

Response:  
---

*User prompt: Đối với phần contributed capital, bạn sẽ xử trí thế nào với những cái khoản tiêu chung cho các cái thiết bị trang vật tư, như kiểu là mua wifi hay mua kìm wifi nọ kia? Liệu những khoản đấy có nên được tính vào trừ khấu hao capital cho tất cả mọi người hay là trừ cho quỹ riêng và nó không liên quan ảnh hưởng đến capital?*

Response: Đây là vấn đề cốt lõi trong **Kế toán Quản trị Vốn (Capital Accounting)**.

Để trả lời ngắn gọn: **Các khoản mua trang thiết bị, vật tư chung (như mua kìm, mua bộ phát WiFi, mua RAM...) TUYỆT ĐỐI KHÔNG NÊN bị trừ/khấu hao vào Contributed Capital (Vốn cống hiến) của các thành viên, mà phải thuộc về Quỹ Weamis riêng.**

Dưới đây là phân tích chi tiết nghiệp vụ và cách xử lý triệt để trên hệ thống:

### **1\. Tại sao KHÔNG NÊN trừ vào Contributed Capital (Vốn cống hiến)?**

> 1. **Bản chất của Contributed Capital là "Lịch sử cống hiến" (Historical Contribution):**  
   * *Contributed Capital* đo lường: **"Cá nhân đó đã tạo ra / mang về cho công ty bao nhiêu giá trị trong suốt quá trình hoạt động?"**  
   * Khi bạn mang về một dự án 10 triệu hay nạp tiền túi 1 triệu, giá trị cống hiến đó là một **sự thật lịch sử đã diễn ra**. Việc công ty mang tiền đi mua cái kìm hay bộ WiFi không làm giảm đi sự thật là bạn đã cống hiến 10 triệu đó.  
> 2. **Tài sản cố định vẫn tồn tại trong Công ty (CapEx \- Capital Expenditure):**  
   * Mua kìm hay mua bộ phát WiFi không phải là tiền "mất đi" (như ăn chè hay đi du lịch), mà tiền mặt biến thành **Tài sản cố định / Công cụ dụng cụ** của tổ chức. Công ty không bị giảm tổng giá trị, chỉ là chuyển từ dạng *Cash (Tiền mặt)* sang *Asset (Tài sản)*.  
> 3. **Tránh gây bất mãn & sai lệch tỷ lệ cổ phần (% Equity):**  
   * Nếu trừ tiền mua vật tư vào Contributed Capital của mọi người, tỷ lệ cổ phần của những người làm nhiều (góp nhiều) sẽ bị sụt giảm bất hợp lý vì những chi phí vận hành chung.

### **2\. Vậy xử lý các khoản chi thiết bị/vật tư chung như thế nào trên Hệ thống?**

Bạn nên phân tách rõ **3 Khái niệm Vốn & Tài sản** sau đây để thiết kế logic phần mềm:

`[ 1. CONTRIBUTED CAPITAL ]  ──► Tổng cống hiến lịch sử của cá nhân (CHỈ TĂNG, KHÔNG GIẢM vì chi phí chung).`  
                                `──► Dùng để tính: % Cổ phần / % Quyền lợi dài hạn (% Equity).`

`[ 2. TREASURY CASH ]       ──► Tiền mặt còn lại trong Quỹ Weamis (Bị GIẢM khi mua kìm, WiFi).`  
                                `──► Dùng để: Chi trả trang thiết bị, server, hạ tầng.`

`[ 3. COMPANY ASSETS (NAV) ] ──► Giá trị Tài sản ròng công ty = Tiền mặt Quỹ + Giá trị các Vật tư/Thiết bị đã mua.`

#### **Quy tắc hạch toán bút toán (Transaction Rule):**

Khi phát sinh giao dịch **"Mua kìm, bộ phát WiFi"**:

| Chỉ số | Biến động | Lý giải nghiệp vụ |
| :---- | :---- | :---- |
| **Contributed Capital (Thành viên)** | **Không đổi (0)** | Lịch sử vốn cống hiến của cá nhân được giữ nguyên tuyệt đối. |
| **Member Cash Wallet (Ví tiền cá nhân)** | **Không đổi (0)** | Cá nhân không bị trừ tiền rút mặt. |
| **Weamis Treasury Cash (Quỹ tiền mặt)** | **Giảm (−*X*)** | Quỹ Weamis chi tiền ra mua. |
| **Weamis Fixed Assets (Tài sản vật tư)** | **Tăng (+*X*)** | Ghi nhận công ty sở hữu thêm 1 chiếc kìm / 1 bộ WiFi. |

### **3\. Trường hợp duy nhất có thể Khấu hao (Depreciation) là gì?**

Nếu công ty hoạt động đến giai đoạn định giá sâu hơn hoặc các thiết bị bị hỏng/cũ hỏng theo thời gian (ví dụ: máy tính, server dùng 2 năm bị hỏng/mất giá trị), FinTech xử lý khấu hao như sau:

> * **Không khấu hao vào Contributed Capital** của từng người.  
> * **Khấu hao vào Giá trị sổ sách của Công ty (Book Value / NAV \- Net Asset Value):**  
>   NAV Coˆng ty=Tổng tieˆˋn mặt trong Quy˜​\+Giaˊ trị coˋn lại của Thieˆˊt bị/Vật tư (sau khaˆˊu hao)

Nếu một thành viên muốn rút vốn hoặc công ty chia cổ tức/giải thể, số tiền họ nhận được sẽ tính theo:

Giaˊ trị thực nhận=NAV Coˆng ty×%Equity (dựa treˆn Contributed Capital)

### **4\. Tóm lược giải pháp cho Phần mềm Sổ cái & Capital Check**

> * **Contributed Capital:** **KHÔNG trừ** các khoản mua thiết bị, kìm, WiFi. Chỉ tính dựa trên 100% Gross dự án mà họ cống hiến \+ tiền túi nạp quỹ.  
> * **Quỹ Weamis (Treasury):** Chịu trách nhiệm chi trả và âm/dương tiền mặt cho các khoản vật tư chung này.  
> * **Form nhập liệu:**  
  * Nếu chọn Expense Type \= Equipment / Asset (Thiết bị/Vật tư) → Trừ tiền **Quỹ Weamis**, ghi nhận vào **Danh mục Tài sản chung**, **không chạm vào Capital hay Ví của bất kỳ ai**.

---

*User prompt: Viết thêm ADR hoàn chỉnh về thiết kế hệ thống chi tiết toàn diện cho website software as a service về tính năng dịch vụ này, thiết kế database ERD chi tiết và đưa ra các ví dụ, các cái từ khóa, miền kiến thức về cái thông tin này và đưa ra toàn bộ các cây có thể bao phủ được các cái trường hợp này để làm ADR cho anti-APT có thể code ra dự án này tiếp tục.*

Response: Dưới đây là tài liệu **Architecture Decision Record (ADR)** chi tiết và toàn diện nhất, được thiết kế theo chuẩn doanh nghiệp để lập trình viên (Developer/AI Code Agent) có thể dựa vào đó thực thi và triển khai trực tiếp cho phân hệ **Core Accounting & Cap Table System** của ứng dụng SaaS/Web App này.

# **Architecture Decision Record (ADR-001)**

## **Architecture for Multi-Entity General Ledger, Split Payment & Sweat Equity Cap Table System**

> * **Status:** Approved  
> * **Date:** 11/08/2026  
> * **Deciders:** Nguyễn Hoàng Việt (Project Owner / Lead Architect), System Engineering Team  
> * **Target Audience:** Full-stack Developers, Database Engineers, Code Agents (Anti-APT / AI Coding Assistants)

## **1\. Context & Problem Statement (Bối cảnh & Bài toán)**

Hệ thống quản lý tài chính cho tổ chức **WeMeet (Weamis)** và các dự án vệ tinh cần giải quyết bài toán biến động dòng tiền đa thành viên với các đặc thù nghiệp vụ phức tạp:

> 1. **Phân tách dòng tiền thô (Gross-to-Net Split Payment):** Một dự án thu tiền thô (100% Gross) phải tự động trích lập 10% Platform Take Rate về Quỹ ngân khố (Weamis Treasury), 90% còn lại (Net Payout) phân bổ vào Ví khả dụng (Cash Wallet) của các thành viên tham gia.  
> 2. **Ghi nhận Vốn Cống hiến (Sweat Equity Attribution):** Việc trích 10% về Quỹ làm cho chỉ số Vốn góp của người cống hiến bị lệch nếu chỉ cộng 90% Net. Hệ thống bắt buộc phải ghi nhận 100% giá trị Gross cống hiến vào **Vốn cống hiến tích lũy (Contributed Capital)** để tính đúng tỷ lệ sở hữu/cổ phần (% Equity).  
> 3. **Quản lý Chi phí hỗn hợp (Mixed Expenses):**  
   * *Chi riêng/Vay nợ:* Trừ/Cộng trực tiếp Ví cá nhân.  
   * *Chi tiêu dùng chung (Ăn uống, du lịch):* Bổ đầu người (Equal Split) trừ vào Ví các thành viên tham gia.  
   * *Chi vật tư/Trang thiết bị (CapEx \- Kìm, Wifi, Server):* Trừ 100% vào Quỹ Weamis, không trừ vào Ví cá nhân hay Vốn cống hiến của bất kỳ ai.  
> 4. **Yêu cầu kỹ thuật:** Bất biến dữ liệu (Immutability), hỗ trợ Audit Log, tính toán số dư tức thì, tách biệt giữa **Dòng tiền mặt (Cash Flow)** và **Giá trị Vốn (Capital Value)**.

## **2\. Decision Drivers (Nguyên tắc Kế toán & Kiến trúc)**

> * **Dual-Ledger Architecture (Mô hình Sổ cái kép):** Tách biệt hoàn toàn giữa Cash Ledger (Tiền thật có thể rút/chi) và Capital Ledger (Điểm cống hiến / Giá trị cổ phần).  
> * **Double-Entry Bookkeeping Principles:** Mọi biến động tài chính đều là giao dịch cân bằng (Balance Equation). Dữ liệu sổ cái chỉ được chèn (INSERT-only), cấm sửa/xóa (NO UPDATE / DELETE). Điều chỉnh dùng bút toán đảo (Reversal Entry).  
> * **CapEx vs. OpEx Isolation:** Chi phí mua sắm thiết bị công ty (CapEx) chỉ làm giảm Treasury Cash Balance và tăng Company Fixed Asset Balance, tuyệt đối không làm suy giảm Contributed Capital của cá nhân.

## **3\. Detailed Architectural Blueprint (Thiết kế chi tiết)**

### **3.1 Domain Knowledge & Core Glossary (Từ khóa & Miền kiến thức)**

Để Dev/Code Agent dễ dàng triển khai code, các thuật ngữ được quy chuẩn như sau:

> * **GTV (Gross Transaction Volume):** Tổng giá trị giao dịch thô ban đầu.  
> * **Take Rate / Platform Cut:** Tỷ lệ % trích lập về Quỹ trung tâm (Mặc định: 10%).  
> * **Member Cash Wallet / Liquid Balance (*Wcash*​):** Số dư tiền mặt thực tế cá nhân có quyền rút hoặc tiêu dùng.  
> * **Contributed Capital / Earned Equity (*Ccap*​):** Tổng giá trị vốn cống hiến tích lũy lịch sử của cá nhân (Dùng tính % Equity). Chỉ tăng khi cống hiến/nạp tiền, không giảm do chi phí vận hành chung.  
> * **Treasury Cash Pool (*Tcash*​):** Quỹ tiền mặt của tổ chức/công ty (Weamis).  
> * **Company Fixed Asset Pool (*Aasset*​):** Tổng giá trị tài sản/thiết bị cố định tổ chức đang sở hữu.  
> * **NAV (Net Asset Value):** Giá trị tài sản ròng toàn công ty \= *Tcash*​\+*Aasset*​.

### **3.2 Database ERD (Entity Relationship Diagram)**

`+-------------------+       +-----------------------+       +-------------------------+`  
`|      users        |       |     project_splits    |       |      transactions       |`  
`+-------------------+       +-----------------------+       +-------------------------+`  
`| PK user_id        |<---|  | PK split_id           |  |--->| PK transaction_id       |`  
`|    full_name      |    |  | FK project_id         |  |    |    transaction_code     |`  
`|    email          |    |--| FK user_id            |  |    |    created_at           |`  
`|    created_at     |       |    share_percentage   |  |    |    description          |`  
`+-------------------+       +-----------------------+  |    |    gross_amount         |`  
          `|                                            |    |    category             |`  
          `|                                            |    |    status               |`  
          `|                 +-----------------------+  |    +-------------------------+`  
          `|                 |   ledger_entries      |  |                 |`  
          `|                 +-----------------------+  |                 |`  
          `|                 | PK entry_id           |  |                 |`  
          `+---------------->| FK user_id (nullable) |  |                 |`  
                            `| FK treasury_id (null) |  |                 |`  
                            `| FK transaction_id     |--+                 |`  
                            `|    account_type       |                    |`  
                            `|    cash_impact        |                    |`  
                            `|    capital_impact     |                    |`  
                            `|    asset_impact       |                    |`  
                            `+-----------------------+                    |`  
                                        `|                                |`  
                                        `v                                v`  
                            `+-----------------------+       +-------------------------+`  
                            `|   company_assets      |       |      audit_logs         |`  
                            `+-----------------------+       +-------------------------+`  
                            `| PK asset_id           |       | PK log_id               |`  
                            `| FK transaction_id     |       | FK transaction_id       |`  
                            `|    asset_name         |       |    action_type          |`  
                            `|    purchase_cost      |       |    payload_json         |`  
                            `|    current_value      |       |    created_at           |`  
                            `+-----------------------+       +-------------------------+`

### **3.3 SQL Schema Definition (PostgreSQL DDL)**

`-- 1. Enum types for Transaction Classification`  
`CREATE TYPE transaction_category AS ENUM (`  
    `'PROJECT_REVENUE',      -- Thu nhập từ dự án (10% Cut, 90% Member)`  
    `'DIRECT_CAPITAL_IN',    -- Thành viên tự nạp tiền túi vào Quỹ`  
    `'PERSONAL_WITHDRAWAL',  -- Thành viên rút tiền lương/ví`  
    `'SHARED_CONSUMPTION',   -- Chi ăn uống, du lịch (Chia đều các thành viên)`  
    `'ASSET_PURCHASE',       -- Mua vật tư, thiết bị (Kìm, Wifi, RAM...) - CapEx`  
    `'INTERNAL_LOAN'         -- Vay nợ nội bộ`  
`);`

`CREATE TYPE ledger_account_type AS ENUM (`  
    `'MEMBER_WALLET',        -- Ví tiền mặt cá nhân`  
    `'MEMBER_CAPITAL',       -- Vốn cống hiến cá nhân`  
    `'COMPANY_TREASURY',     -- Quỹ tiền mặt công ty (Weamis)`  
    `'COMPANY_ASSET'         -- Tài sản cố định công ty`  
`);`

`-- 2. Users Table`  
`CREATE TABLE users (`  
    `user_id UUID PRIMARY KEY DEFAULT gen_random_uuid(),`  
    `full_name VARCHAR(100) NOT NULL,`  
    `email VARCHAR(255) UNIQUE NOT NULL,`  
    `is_active BOOLEAN DEFAULT TRUE,`  
    `created_at TIMESTAMPTZ DEFAULT NOW()`  
`);`

`-- 3. Transactions Table (Header)`  
`CREATE TABLE transactions (`  
    `transaction_id UUID PRIMARY KEY DEFAULT gen_random_uuid(),`  
    `transaction_code VARCHAR(50) UNIQUE NOT NULL,`  
    `description TEXT NOT NULL,`  
    `gross_amount NUMERIC(15, 2) NOT NULL,`  
    `category transaction_category NOT NULL,`  
    `created_at TIMESTAMPTZ DEFAULT NOW(),`  
    `metadata JSONB DEFAULT '{}'::jsonb`  
`);`

`-- 4. Ledger Entries Table (Line Items - Immutable)`  
`CREATE TABLE ledger_entries (`  
    `entry_id UUID PRIMARY KEY DEFAULT gen_random_uuid(),`  
    `transaction_id UUID NOT NULL REFERENCES transactions(transaction_id) ON DELETE RESTRICT,`  
    `user_id UUID REFERENCES users(user_id) ON DELETE RESTRICT, -- NULL nếu là tài khoản Company`  
    `account_type ledger_account_type NOT NULL,`  
    `cash_impact NUMERIC(15, 2) DEFAULT 0.00,    -- Biến động ví tiền mặt (+/-)`  
    `capital_impact NUMERIC(15, 2) DEFAULT 0.00, -- Biến động vốn cống hiến (+/-)`  
    `asset_impact NUMERIC(15, 2) DEFAULT 0.00,   -- Biến động tài sản cố định (+/-)`  
    `created_at TIMESTAMPTZ DEFAULT NOW()`  
`);`

`-- Indexing for Lightning-Fast Real-time Calculations`  
`CREATE INDEX idx_ledger_user_account ON ledger_entries(user_id, account_type);`  
`CREATE INDEX idx_ledger_transaction ON ledger_entries(transaction_id);`

`-- 5. Company Assets Tracking Table`  
`CREATE TABLE company_assets (`  
    `asset_id UUID PRIMARY KEY DEFAULT gen_random_uuid(),`  
    `transaction_id UUID REFERENCES transactions(transaction_id),`  
    `asset_name VARCHAR(255) NOT NULL,`  
    `purchase_cost NUMERIC(15, 2) NOT NULL,`  
    `current_value NUMERIC(15, 2) NOT NULL,`  
    `created_at TIMESTAMPTZ DEFAULT NOW()`  
`);`

## **4\. State Machine & Execution Matrix (Các kịch bản nghiệp vụ)**

Dưới đây là bảng Ma trận tính toán toán học cho tất cả các kịch bản có thể phát sinh trong hệ thống. Lập trình viên phải tuân thủ đúng logic phân bổ này:

| \# | Kịch bản / Nghiệp vụ | Tác động cash\_impact | Tác động capital\_impact | Tác động asset\_impact |
| :---- | :---- | :---- | :---- | :---- |
| **1** | **Dự án Gross 10.000.000 (A làm 60%, B làm 40%)** | \- Member A: \+5.400.000 \- Member B: \+3.600.000 \- Treasury: \+1.000.000 | \- Member A: \+6.000.000 \- Member B: \+4.000.000 \- Treasury: 0 | \- Treasury Asset: 0 |
| **2** | **Thành viên A nạp 2.000.000 tiền túi vào Quỹ** | \- Member A: 0 \- Treasury: \+2.000.000 | \- Member A: \+2.000.000 \- Treasury: 0 | \- Treasury Asset: 0 |
| **3** | **Chi 1.000.000 ăn uống/Cát Bà (Cho A, B, C)** | \- Member A: −333.333 \- Member B: −333.333 \- Member C: −333.334 | \- Member A: 0 \- Member B: 0 \- Member C: 0 | \- Asset: 0 |
| **4** | **Chi 500.000 mua kìm \+ WiFi (CapEx)** | \- Member A, B, C: 0 \- Treasury Cash: −500.000 | \- Member Capital: 0 \- Treasury Capital: 0 | \- Company Asset: \+500.000 |
| **5** | **Thành viên A vay 1.000.000 từ Quỹ** | \- Member A: \+1.000.000 \- Treasury Cash: −1.000.000 | \- All Member Capitals: 0 | \- Asset: 0 |
| **6** | **Thành viên A trả nợ 1.000.000 cho Quỹ** | \- Member A: −1.000.000 \- Treasury Cash: \+1.000.000 | \- All Member Capitals: 0 | \- Asset: 0 |
| **7** | **Thành viên A rút 2.000.000 tiền lương/ví** | \- Member A: −2.000.000 \- Treasury Cash: −2.000.000 | \- Member A Capital: 0 | \- Asset: 0 |

## **5\. Comprehensive Edge Cases & Test Coverage Matrix**

Được thiết kế để bao phủ toàn bộ trường hợp lỗi (Corner/Edge Cases) cho QA/Automation Test/Anti-APT Code Agent:

`+---------------------------------------------------------------------------------------+`  
`|                                    EDGE CASES MATRIX                                  |`  
`+-----------------------------------+---------------------------------------------------+`  
`| Case Description                  | Expected System Behavior / Assertion              |`  
`+-----------------------------------+---------------------------------------------------+`  
`| 1. Project Split Sum != 100%      | Reject Transaction. Throw UnprocessableEntity.    |`  
`| 2. Member Cash Wallet < 0         | Allow (Neg balance represents internal debt).     |`  
`| 3. Treasury Cash Pool < 0         | Allow & Flag "Liquidity Warning" on Dashboard.     |`  
`| 4. Asset Purchase with 0 Treasury | Allow (Treasury Cash becomes negative),           |`  
`|                                   | Asset Value recorded. Asset != Capital.           |`  
`| 5. Decimal Rounding Loss (.0001)  | Direct remainder amount to Treasury Cash Impact.  |`  
`| 6. User deactivation with Capital | Capital history preserved for historical CapTable |`  
`| 7. Reversal / Refund Transaction  | Create inverse transaction entry. NEVER UPDATE.   |`  
`+-----------------------------------+---------------------------------------------------+`

### **Chi tiết giải trình Edge Cases:**

> 1. **Dư sai số làm tròn thập phân (Rounding Remainder):** Khi chia tiền 1.000.000 cho 3 người (333.333,333...), tổng bị hụt 1 đồng. **Xử lý:** Phần tiền lẻ lẻ dư/thiếu tự động dồn vào bút toán của COMPANY\_TREASURY.  
> 2. **Quỹ Weamis bị âm tiền mặt (Negative Treasury):** Khi công ty mua thiết bị nhưng Quỹ không đủ tiền. **Xử lý:** Cho phép âm cash\_impact ở Quỹ COMPANY\_TREASURY để phản ánh thực tế thiếu hụt thanh khoản (Liquidity Shortage), nhưng **tuyệt đối không can thiệp** làm giảm capital\_impact của các thành viên.  
> 3. **Thành viên rút vốn / Rời tổ chức (Offboarding):** Số dư Vốn cống hiến capital\_impact được giữ nguyên làm lịch sử. Tỷ lệ % Equity sẽ tự động tái tính toán (Re-indexed) dựa trên tổng số vốn của các thành viên active còn lại hoặc thỏa thuận mua lại (Buyback).

## **6\. Core Business Logic Implementation (TypeScript Reference)**

Đoạn code TypeScript mẫu chuẩn hóa để Dev/Anti-APT tích hợp trực tiếp vào Service Layer:

`interface ProjectSplit {`  
  `userId: string;`  
  `sharePercentage: number; // e.g., 0.50 for 50%`  
`}`

`export class AccountingService {`  
  `private readonly TAKE_RATE = 0.10; // 10% Platform Cut`

  `/**`  
   `* Processing Project Revenue with Dual-Ledger System`  
   `*/`  
  `async processProjectRevenue(`  
    `transactionId: string,`  
    `grossAmount: number,`  
    `splits: ProjectSplit[]`  
  `): Promise<void> {`  
    `// 1. Validate total split percentage`  
    `const totalSplit = splits.reduce((sum, s) => sum + s.sharePercentage, 0);`  
    `if (Math.abs(totalSplit - 1.0) > 0.0001) {`  
      `throw new Error("Invalid Project Splits: Sum must equal 100%");`  
    `}`

    `const treasuryCutCash = grossAmount * this.TAKE_RATE;`  
    `const remainingCashPool = grossAmount - treasuryCutCash;`

    `const ledgerEntries = [];`

    `// 2. Treasury Record (Cash only, Capital impact is 0)`  
    `ledgerEntries.push({`  
      `transactionId,`  
      `userId: null,`  
      `accountType: 'COMPANY_TREASURY',`  
      `cashImpact: treasuryCutCash,`  
      `capitalImpact: 0,`  
      `assetImpact: 0`  
    `});`

    `// 3. Member Records (Cash = 90% * Share, Capital = 100% * Share)`  
    `for (const split of splits) {`  
      `const memberCash = remainingCashPool * split.sharePercentage;`  
      `const memberCapital = grossAmount * split.sharePercentage; // Full Gross Credit!`

      `ledgerEntries.push({`  
        `transactionId,`  
        `userId: split.userId,`  
        `accountType: 'MEMBER_WALLET',`  
        `cashImpact: memberCash,`  
        `capitalImpact: 0,`  
        `assetImpact: 0`  
      `});`

      `ledgerEntries.push({`  
        `transactionId,`  
        `userId: split.userId,`  
        `accountType: 'MEMBER_CAPITAL',`  
        `cashImpact: 0,`  
        `capitalImpact: memberCapital,`  
        `assetImpact: 0`  
      `});`  
    `}`

    `// 4. Execute Atomic DB Transaction`  
    `await this.saveLedgerEntries(ledgerEntries);`  
  `}`

  `/**`  
   `* Real-time Cap Table Calculator (% Equity)`  
   `*/`  
  `async calculateCapTable(): Promise<Record<string, { capital: number; equityShare: number }>> {`  
    `const rawCapitals = await this.getAggregatedMemberCapitals();`  
    `const totalCompanyCapital = Object.values(rawCapitals).reduce((a, b) => a + b, 0);`

    `if (totalCompanyCapital === 0) return {};`

    `const capTable: Record<string, { capital: number; equityShare: number }> = {};`  
    `for (const [userId, capital] of Object.entries(rawCapitals)) {`  
      `capTable[userId] = {`  
        `capital,`  
        `equityShare: (capital / totalCompanyCapital) * 100`  
      `};`  
    `}`  
    `return capTable;`  
  `}`  
`}`

## **7\. Operational Dashboard SQL Queries (Sổ cái & Cap Table)**

Dev/Code Agent sử dụng trực tiếp các câu truy vấn SQL đã tối ưu hóa này để làm API Dashboard:

### **7.1 Truy vấn Bảng Vốn Cống Hiến & Tỷ lệ Cổ phần (% Cap Table)**

`SELECT`   
    `u.user_id,`  
    `u.full_name,`  
    `COALESCE(SUM(le.capital_impact), 0) AS total_contributed_capital,`  
    `ROUND(`  
        `(COALESCE(SUM(le.capital_impact), 0) / NULLIF(SUM(SUM(le.capital_impact)) OVER (), 0)) * 100,`   
        `2`  
    `) AS equity_ownership_percentage`  
`FROM users u`  
`LEFT JOIN ledger_entries le ON u.user_id = le.user_id AND le.account_type = 'MEMBER_CAPITAL'`  
`WHERE u.is_active = TRUE`  
`GROUP BY u.user_id, u.full_name`  
`ORDER BY total_contributed_capital DESC;`

### **7.2 Truy vấn Ví tiền mặt & Công nợ cá nhân (Cash Wallet)**

`SELECT`   
    `u.user_id,`  
    `u.full_name,`  
    `COALESCE(SUM(le.cash_impact), 0) AS liquid_cash_balance`  
`FROM users u`  
`LEFT JOIN ledger_entries le ON u.user_id = le.user_id AND le.account_type = 'MEMBER_WALLET'`  
`GROUP BY u.user_id, u.full_name;`

### **7.3 Truy vấn Sức khỏe Ngân khố & Tài sản Công ty (Treasury & NAV)**

`SELECT`   
    `(SELECT COALESCE(SUM(cash_impact), 0) FROM ledger_entries WHERE account_type = 'COMPANY_TREASURY') AS treasury_cash_balance,`  
    `(SELECT COALESCE(SUM(current_value), 0) FROM company_assets) AS total_fixed_assets_value,`  
    `(`  
        `(SELECT COALESCE(SUM(cash_impact), 0) FROM ledger_entries WHERE account_type = 'COMPANY_TREASURY') +`  
        `(SELECT COALESCE(SUM(current_value), 0) FROM company_assets)`  
    `) AS net_asset_value_nav;`

## **8\. Summary & Next Steps (Hướng dẫn triển khai tiếp theo)**

> 1. **Database Migration:** Chạy script DDL ở **Mục 3.3** trên cơ sở dữ liệu PostgreSQL.  
> 2. **Service Layer Integration:** Triển khai AccountingService theo spec ở **Mục 6**.  
> 3. **Data Backfill Script:** Viết script đọc lại 51 dòng giao dịch lịch sử hiện tại, biến đổi thành các ledger\_entries tương ứng theo đúng **State Machine (Mục 4\)** để đưa dữ liệu quá khứ về trạng thái chuẩn hóa.

---

*User prompt: Bạn hãy thiết kế UI/UX cho trang CRUD cho nghiệp vụ là single page, list và bao phủ là cả hai cái loại hình sổ cái này.*

Response: Dưới đây là thiết kế chi tiết **UI/UX Single-Page Application (SPA)** cho hệ thống **Sổ Cái Kép (Dual-Ledger: Cash & Capital)**. Thiết kế này được tối ưu cho Desktop, giúp quản trị viên và thành viên vừa thực hiện các thao tác CRUD (Tạo, Đọc, Sửa, Hủy) dễ dàng, vừa theo dõi được toàn bộ chỉ số tài chính theo thời gian thực mà không bị chuyển trang.

## **1\. TỔNG QUAN HỆ THỐNG UX/UI (LAYOUT & WORKFLOW)**

Giao diện được chia thành **3 vùng không gian chính** theo chuẩn Dashboard FinTech chuyên nghiệp:

> 1. **Header & Top KPI Metrics (Vùng chỉ số tài chính tức thì):** Hiển thị tổng quan NAV, Quỹ Weamis, Tổng Vốn cống hiến và số dư ví cá nhân.  
> 2. **Main Workspace (Vùng thao tác chính \- Dual View):** Bộ Tab chuyển đổi góc nhìn giữa **Sổ cái tiền mặt (Cash Ledger)** và **Sổ cái vốn (Capital Ledger / Cap Table)** kèm thanh công cụ lọc/tìm kiếm.  
> 3. **Action Drawer / Slide-over Modal (Vùng thao tác CRUD):** Bảng trượt từ phải sang (Slide-over) giúp Tạo giao dịch mới hoặc Đọc/Huỷ bút toán mà không che mất dữ liệu sổ cái bên dưới.

## **2\. CHI TIẾT THIẾT KẾ CÁC THÀNH PHẦN GIAO DIỆN**

### **2.1 Top KPI Cards (Bảng Chỉ Số Nhanh)**

Nằm ở ngay đầu trang, giúp người dùng định hình ngay trạng thái hệ thống:

`┌─────────────────────────┐ ┌─────────────────────────┐ ┌─────────────────────────┐ ┌─────────────────────────┐`  
`│ 🏦 QUỸ WEAMIS (CASH)    │ │ 📦 TÀI SẢN CỐ ĐỊNH      │ │ 💎 TỔNG VỐN CỐNG HIẾN   │ │ 👤 VÍ TIỀN CÁ NHÂN      │`  
`│  -538,520 VNĐ           │ │  1,200,000 VNĐ          │ │  15,200,000 VNĐ         │ │  930,000 VNĐ            │`  
`│ [Cảnh báo thiếu hụt]    │ │ (RAM, Kìm, Wifi...)     │ │ (Tổng Gross tích lũy)   │ │ (Số dư khả dụng của bạn)│`  
`└─────────────────────────┘ └─────────────────────────┘ └─────────────────────────┘ └─────────────────────────┘`

### **2.2 Main Workspace: Sổ Cái Kép & CRUD Table**

Hệ thống sử dụng **Tabs** để người dùng chuyển đổi tức thì giữa 2 góc nhìn Sổ cái mà không cần reload.

#### **Tab 1: CASH LEDGER VIEW (Sổ Cái Tiền Mặt \- Luồng Tiền Rút/Chi)**

Tập trung vào dòng tiền khả dụng thực tế của từng cá nhân và quỹ công ty.

> * **Thanh công cụ (Toolbar):**  
  * \[+ Tạo Giao Dịch Mới\] *(Button Nổi bật \- Primary Color)*  
  * Bộ lọc Loại Chi Tiêu: \[Tất cả | Thu dự án | Chi chung | Chi vật tư/Tài sản | Vay nợ\]  
  * Lọc Thành Viên: \[Tất cả thành viên | Việt | Sơn | Kiên...\]  
  * Search Bar: Tìm kiếm theo nội dung/Mã giao dịch.  
> * **Bảng Dữ Liệu (DataTable):**

| Mã GD | Ngày/Giờ | Nội dung Giao dịch | Phân loại (Category) | Tổng Gross | Quỹ Weamis | Việt | Sơn | Kiên | Thao tác |
| :---- | :---- | :---- | :---- | :---- | :---- | :---- | :---- | :---- | :---- |
| \#TX-001 | 12/02 15:10 | Việt: Tiền Video AI | PROJECT\_REVENUE | 1,200,000 | \+120,000 | 0 | 0 | 0 | \[👁️\] \[🗑️\] |
| \#TX-024 | 29/04 00:18 | Bú tiền ứng Cát Bà | SHARED\_CONSUMPTION | \-5,477,300 | 0 | \-60,000 | \-710,000 | \-560,000 | \[👁️\] \[🗑️\] |
| \#TX-046 | 20/07 12:00 | Mua kìm đấu wifi | ASSET\_PURCHASE | \-150,000 | \-150,000 | 0 | 0 | 0 | \[👁️\] \[🗑️\] |

#### **Tab 2: CAPITAL LEDGER & CAP TABLE VIEW (Sổ Cái Vốn & Cổ Phần)**

Tập trung vào điểm cống hiến lịch sử (100% Gross) để tính chính xác tỷ lệ sở hữu.

> * **Cấu trúc 2 phân vùng (Split Screen):**  
  * **Nửa trên (Cap Table Chart & Summary):** Tiến trình phân bổ cổ phần hiện tại (% Equity Ownership) dạng Donut Chart và Progress Bar.  
  * **Nửa dưới (Capital Ledger Log):** Nhật ký tích lũy điểm cống hiến.  
> * **Bảng Phân Bổ Cổ Phần (Cap Table Summary):**

| Thành viên | Vốn cống hiến tích lũy (Gross) | Tỷ lệ Cổ phần (% Equity) | Trạng thái Vốn |
| :---- | :---- | :---- | :---- |
| **Nguyễn Hoàng Việt** | 9,000,000 VNĐ | **59.21%** | \[Active\] |
| **Hồ Trùng Sơn** | 5,200,000 VNĐ | **34.21%** | \[Active\] |
| **Nguyễn Quý Đức** | 1,000,000 VNĐ | **6.58%** | \[Active\] |

> * **Bảng Nhật Ký Ghi Nhận Vốn (Capital Ledger Log):**

| Mã GD | Ngày/Giờ | Tên Dự án / Nguồn Vốn | Tác động Vốn Việt | Tác động Vốn Sơn | Tác động Vốn Quý Đức |
| :---- | :---- | :---- | :---- | :---- | :---- |
| \#TX-001 | 12/02 15:10 | Video AI (Việt \+ Đức làm) | 0 | 0 | \+1,200,000 |
| \#TX-002 | 15/02 17:37 | Tiền Everbloom (Việt \+ Sơn) | \+2,500,000 | \+2,500,000 | 0 |
| \#TX-046 | 20/07 12:00 | Mua kìm đấu wifi (CapEx) | *\[Bỏ qua \- Không làm giảm Vốn\]* | *\[Bỏ qua\]* | *\[Bỏ qua\]* |

## **3\. THIẾT KẾ CÁC THAO TÁC CRUD (SINGLE PAGE)**

Vì hệ thống kế toán yêu cầu **Bất biến dữ liệu (Immutability)**, các thao tác CRUD được quy chuẩn UX như sau:

  `┌──────────────────────────────────────────────────────────────┐`  
  `│                   LUỒNG THAO TÁC CRUD SPA                    │`  
  `│                                                              │`  
  `│  [C] CREATE  ──► Slide-Over Form (Phân loại & Auto Split)    │`  
  `│  [R] READ    ──► Drawer Detail View + Traceability           │`  
  `│  [U] UPDATE  ──► Bị CẤM (Phải dùng Reversal Transaction)     │`  
  `│  [D] DELETE  ──► Soft Void (Tạo Bút toán Đảo Hủy)            │`  
  `└──────────────────────────────────────────────────────────────┘`

### **3.1 \[C\] CREATE \- Tạo Giao Dịch Mới (Dynamic Slide-Over Form)**

Khi nhấn nút \[+ Tạo Giao Dịch Mới\], một bảng Form trượt ra từ bên phải màn hình. Form sẽ động đổi trường nhập dữ liệu dựa trên **Loại giao dịch (Category)** được chọn:

> 1. **Nhập thông tin cơ bản:**  
   * Nội dung giao dịch (text)  
   * Tổng tiền thô (number)  
   * Ngày giờ (datetime-picker)  
   * Loại giao dịch (dropdown):  
     * *Option A: Thu nhập Dự án (PROJECT\_REVENUE)*  
     * *Option B: Chi tiêu dùng chung (SHARED\_CONSUMPTION)*  
     * *Option C: Mua Trang thiết bị/Vật tư (ASSET\_PURCHASE)*  
     * *Option D: Vay nợ / Trả nợ nội bộ (INTERNAL\_LOAN)*  
> 2. **Khung xử lý Phân bổ Tự động (Smart Allocation Section):**  
   * **Nếu chọn Option A (Thu dự án):**  
     * Hệ thống tự động fix **10%** về Quỹ Weamis.  
     * Cho chọn các thành viên tham gia làm dự án \+ % đóng góp. Hệ thống tự tính ra 90% chia vào Ví Cash và 100% ghi vào Sổ Vốn Capital.  
   * **Nếu chọn Option B (Chi ăn uống/Cát Bà):**  
     * Cho tích chọn danh sách thành viên tham gia → Có checkbox \[x\] Chia đều (Split Equally).  
   * **Nếu chọn Option C (Mua kìm/WiFi):**  
     * Tự động khóa mục tiêu trừ tiền vào **Quỹ Weamis (Treasury)**. Hiện thêm ô nhập: Tên Tài Sản Cố Định (để lưu vào danh mục Asset).

### **3.2 \[R\] READ \- Xem Chi Tiết Giao Dịch & Bút Toán Đối Soát**

Khi click vào một dòng bất kỳ trên bảng dữ liệu, Drawer chi tiết mở ra hiển thị **Bản ghi Kép**:

> * **Thông tin thô (Transaction Header):** Mã GD, Người tạo, Ngày giờ, Chứng từ đính kèm (Hình ảnh hóa đơn nếu có).  
> * **Bút toán Sổ cái Tiền mặt (Cash Impact):** Hiển thị rõ số tiền biến động trên Ví của từng người.  
> * **Bút toán Sổ cái Vốn (Capital Impact):** Hiển thị số điểm cống hiến được cộng vào Cap Table.  
> * **Nút Thao Tác:** \[In Chứng Từ\] | \[Hủy Giao Dịch (Void)\].

### **3.3 \[U\] UPDATE & \[D\] DELETE \- Logic Hủy/Sửa Chuẩn FinTech**

Trong kế toán, **Update/Delete trực tiếp bản ghi cũ là sai quy tắc audit**. Do đó UX/UI xử lý kịch bản này như sau:

> * **Sửa (UPDATE):** Nút "Sửa" bị ẩn/vô hiệu hóa. Nếu chọn sửa, UI sẽ gợi ý người dùng bấm \[Hủy Giao Dịch\] và tạo giao dịch mới thay thế.  
> * **Xóa (DELETE / VOID):**  
  * Khi bấm nút \[🗑️ Hủy Giao Dịch\] → Hiển thị Modal xác nhận kèm ô bắt buộc nhập Lý do hủy.  
  * **Trạng thái UI:** Dòng giao dịch bị hủy sẽ **không mất đi** mà chuyển sang trạng thái mờ (Opacitly 50%), chữ gạch ngang (strikethrough), dán nhãn Badge màu đỏ \[VOIDED\].  
  * **Hệ thống ngầm:** Tự động sinh ra một **Bút toán Đảo (Reversal Entry)** với số tiền âm/dương ngược lại 100% để triệt tiêu số dư của giao dịch bị hủy.

## **4\. BẢNG PHÂN BỔ MICRO-INTERACTIONS (UX/UI DETAILS)**

Toàn bộ trang được tối ưu hóa phản hồi người dùng giúp việc thao tác diễn ra mượt mà:

| Thành phần UI | Trạng thái UX / Micro-interaction |
| :---- | :---- |
| **Trạng thái Quỹ âm** | Nếu Quỹ Weamis \<0, Badge chỉ số ở Top KPI đổi sang màu cam/đỏ kèm cảnh báo *“Cần trích nạp thêm vốn vận hành”*. |
| **Phân biệt Âm/Dương** | Dòng tiền vào/Tăng vốn hiển thị chữ màu **Xanh Lá (+)**. Dòng tiền ra/Chi tiêu hiển thị chữ màu **Đỏ/Cam (-)**. |
| **Form Auto-Calculation** | Ngay khi gõ Tổng tiền ở Form Tạo mới, hệ thống tính toán sẵn kết quả preview 10% Quỹ và 90% Ví cá nhân theo thời gian thực (Debounce 300ms) trước khi người dùng bấm Save. |
| **Lọc tức thì (Client-side Filter)** | Chuyển Tab hoặc gõ từ khóa tìm kiếm sẽ lọc dữ liệu ngay lập tức mà không làm nảy giật trang (sử dụng Skeleton loader khi fetch dữ liệu mới). |

---

*User prompt: Đối với contributor, capital nếu họ góp vào nhưng họ rút ra theo kiểu cá nhân, tức là rút lương thì phần capital sẽ tính toán lại như thế nào?*

Response: Khi một thành viên (contributor) rút tiền cá nhân ra khỏi hệ thống (ví dụ: rút lương, rút thu nhập dự án về tài khoản ngân hàng), việc tính toán lại **Contributed Capital (Vốn cống hiến)** sẽ phụ thuộc vào **Bản chất của khoản tiền họ rút**.

Trong Kế toán Quản trị Vốn (Capital Accounting), hệ thống sẽ xử lý theo **2 kịch bản** rõ ràng sau:

### **Kịch bản 1: Rút Thu nhập / Rút Lương (Payout of Earnings)**

**Trường hợp:** Bạn có số dư trong Ví tiền mặt (Member Cash Wallet) nhờ việc thực hiện các dự án (90% Net) hoặc tích lũy lương, và bạn thực hiện rút số tiền đó về tài khoản cá nhân để tiêu dùng.

> * **Tác động lên Contributed Capital:** **BẰNG ZERO (0). KHÔNG BỊ TRỪ.**  
> * **Giải thích bản chất:**  
  * Khoản tiền bạn rút là **Phần thu nhập cá nhân mà bạn xứng đáng nhận được** từ lao động/dự án đã hoàn thành (Cash Realization).  
  * Giá trị dự án bạn cống hiến cho công ty (100% Gross) là **sự thật lịch sử đã diễn ra**. Việc bạn rút 90% tiền lương/thu nhập cá nhân không làm giảm đi tổng giá trị cống hiến hay tỷ lệ cổ phần (100% Gross) bạn đã đem về cho tổ chức.  
> * **Tác động trên Hệ thống Sổ cái (Dual-Ledger):**  
  * Member Cash Wallet (Ví tiền mặt cá nhân): **Giảm (−*X*)**  
  * Treasury Cash Pool (Quỹ tiền mặt Weamis): **Giảm (−*X*)** (khi chi trả tiền mặt cho bạn)  
  * Contributed Capital (Vốn cống hiến / Equity Points): **Không đổi (0)**  
  * Equity Ownership % (Tỷ lệ cổ phần): **Không đổi (0%)**

### **Kịch bản 2: Rút vốn cổ phần / Giảm vốn điều lệ (Capital Reduction / Buyback)**

**Trường hợp:** Thành viên muốn rút bớt một phần **Vốn góp điều lệ gốc/Cổ phần** của mình ra khỏi công ty (hoặc công ty mua lại cổ phần/vốn góp của thành viên đó để giảm quy mô vốn).

> * **Tác động lên Contributed Capital:** **BỊ TRỪ GIẢM DƯƠNG (−*X*).**  
> * **Giải thích bản chất:**  
  * Đây là hành động giảm quy mô đầu tư/cổ phần của cá nhân đó trong công ty, không phải là rút lương hay rút thu nhập lao động.  
  * Khi Contributed Capital bị trừ, **Tỷ lệ cổ phần (% Equity) của thành viên này sẽ ngay lập tức bị giảm xuống**, và tỷ lệ cổ phần của các thành viên còn lại sẽ tự động tăng lên (Re-indexed).  
> * **Tác động trên Hệ thống Sổ cái (Dual-Ledger):**  
  * Contributed Capital (Vốn cống hiến cá nhân): **Giảm (−*X*)**  
  * Treasury Cash Pool (Quỹ tiền mặt Weamis): **Giảm (−*X*)**  
  * Equity Ownership % (Tỷ lệ cổ phần): **Tự động tính toán giảm tương ứng.**

### **Bảng So Sánh Chi Tiết Cho Lập Trình Viên (Dev/System Specs)**

Khi xây dựng tính năng **Rút tiền**, Form thao tác trên giao diện UI cần cho chọn rõ **Mục đích rút tiền (Withdrawal Intent)**:

| Chỉ số / Tài khoản | Rút Lương / Rút Thu nhập (PERSONAL\_PAYOUT) | Rút Vốn Cổ phần (CAPITAL\_REDUCTION) |
| :---- | :---- | :---- |
| **Mục đích** | Rút tiền túi/lương ra tiêu xài cá nhân | Giảm quyền sở hữu/Cổ phần trong công ty |
| **Ví tiền mặt cá nhân (cash\_impact)** | **Giảm (−*X*)** | Không ảnh hưởng (0) |
| **Quỹ tiền mặt công ty (treasury\_cash)** | **Giảm (−*X*)** | **Giảm (−*X*)** |
| **Vốn cống hiến cá nhân (capital\_impact)** | **KHÔNG BỊ TRỪ (0)** | **GIẢM (−*X*)** |
| **Tỷ lệ cổ phần (% Equity Ownership)** | **GIỮ NGUYÊN (Unchanged)** | **GIẢM XUỐNG (Decreased)** |

### **Tóm lại quy tắc ứng xử trên Hệ thống**

**Rút lương/thu nhập** → Chỉ là sự dịch chuyển **Dòng tiền mặt (Cashflow)**. Vốn cống hiến & Tỷ lệ cổ phần **giữ nguyên 100%**.

**Rút vốn cổ phần** → Là sự dịch chuyển **Cấu trúc sở hữu (Cap Table/Capital)**. Vốn cống hiến & Tỷ lệ cổ phần **bị trừ giảm tương ứng**.

---

Đối với contributor, capital nếu họ góp vào nhưng họ rút ra theo kiểu cá nhân, tức là rút lương thì phần capital sẽ tính toán lại như thế nào?

Khi một thành viên (contributor) rút tiền cá nhân ra khỏi hệ thống (ví dụ: rút lương, rút thu nhập dự án về tài khoản ngân hàng), việc tính toán lại Contributed Capital (Vốn cống hiến) sẽ phụ thuộc vào Bản chất của khoản tiền họ rút.

Trong Kế toán Quản trị Vốn (Capital Accounting), hệ thống sẽ xử lý theo 2 kịch bản rõ ràng sau:

Kịch bản 1: Rút Thu nhập / Rút Lương (Payout of Earnings)  
Trường hợp: Bạn có số dư trong Ví tiền mặt (Member Cash Wallet) nhờ việc thực hiện các dự án (90% Net) hoặc tích lũy lương, và bạn thực hiện rút số tiền đó về tài khoản cá nhân để tiêu dùng.

Tác động lên Contributed Capital: BẰNG ZERO (0). KHÔNG BỊ TRỪ.

Giải thích bản chất:

Khoản tiền bạn rút là Phần thu nhập cá nhân mà bạn xứng đáng nhận được từ lao động/dự án đã hoàn thành (Cash Realization).

Giá trị dự án bạn cống hiến cho công ty (100% Gross) là sự thật lịch sử đã diễn ra. Việc bạn rút 90% tiền lương/thu nhập cá nhân không làm giảm đi tổng giá trị cống hiến hay tỷ lệ cổ phần (100% Gross) bạn đã đem về cho tổ chức.

Tác động trên Hệ thống Sổ cái (Dual-Ledger):

Member Cash Wallet (Ví tiền mặt cá nhân): Giảm (−X)

Treasury Cash Pool (Quỹ tiền mặt Weamis): Giảm (−X) (khi chi trả tiền mặt cho bạn)

Contributed Capital (Vốn cống hiến / Equity Points): Không đổi (0)

Equity Ownership % (Tỷ lệ cổ phần): Không đổi (0%)

Kịch bản 2: Rút vốn cổ phần / Giảm vốn điều lệ (Capital Reduction / Buyback)  
Trường hợp: Thành viên muốn rút bớt một phần Vốn góp điều lệ gốc/Cổ phần của mình ra khỏi công ty (hoặc công ty mua lại cổ phần/vốn góp của thành viên đó để giảm quy mô vốn).

Tác động lên Contributed Capital: BỊ TRỪ GIẢM DƯƠNG (−X).

Giải thích bản chất:

Đây là hành động giảm quy mô đầu tư/cổ phần của cá nhân đó trong công ty, không phải là rút lương hay rút thu nhập lao động.

Khi Contributed Capital bị trừ, Tỷ lệ cổ phần (% Equity) của thành viên này sẽ ngay lập tức bị giảm xuống, và tỷ lệ cổ phần của các thành viên còn lại sẽ tự động tăng lên (Re-indexed).

Tác động trên Hệ thống Sổ cái (Dual-Ledger):

Contributed Capital (Vốn cống hiến cá nhân): Giảm (−X)

Treasury Cash Pool (Quỹ tiền mặt Weamis): Giảm (−X)

Equity Ownership % (Tỷ lệ cổ phần): Tự động tính toán giảm tương ứng.

Bảng So Sánh Chi Tiết Cho Lập Trình Viên (Dev/System Specs)  
Khi xây dựng tính năng Rút tiền, Form thao tác trên giao diện UI cần cho chọn rõ Mục đích rút tiền (Withdrawal Intent):

Chỉ số / Tài khoản	Rút Lương / Rút Thu nhập (PERSONAL\_PAYOUT)	Rút Vốn Cổ phần (CAPITAL\_REDUCTION)  
Mục đích	Rút tiền túi/lương ra tiêu xài cá nhân	Giảm quyền sở hữu/Cổ phần trong công ty  
Ví tiền mặt cá nhân (cash\_impact)	Giảm (−X)	Không ảnh hưởng (0)  
Quỹ tiền mặt công ty (treasury\_cash)	Giảm (−X)	Giảm (−X)  
Vốn cống hiến cá nhân (capital\_impact)	KHÔNG BỊ TRỪ (0)	GIẢM (−X)  
Tỷ lệ cổ phần (% Equity Ownership)	GIỮ NGUYÊN (Unchanged)	GIẢM XUỐNG (Decreased)  
Tóm lại quy tắc ứng xử trên Hệ thống  
Rút lương/thu nhập → Chỉ là sự dịch chuyển Dòng tiền mặt (Cashflow). Vốn cống hiến & Tỷ lệ cổ phần giữ nguyên 100%.

Rút vốn cổ phần → Là sự dịch chuyển Cấu trúc sở hữu (Cap Table/Capital). Vốn cống hiến & Tỷ lệ cổ phần bị trừ giảm tương ứng.

dòng này 24 29/04/2026 00:18 Bú tiền ứng cát bà \-5477300 \-60000 \-710000 \-350000 \-560000 \-710000 \-650000 \-560000 \-710000 \-710000 \-510000 , bây giờ tôi muốn tính % cổ phần capital cuối, thì tôi nên tính dòng này thế nào

Với dòng giao dịch số 24 (Bú tiền ứng Cát Bà: \-5.477.300 VNĐ), đây là một khoản Chi tiêu dùng chung cho hoạt động vui chơi/tiêu dùng nhóm (Shared Consumption Expense).

Dựa trên nguyên tắc thiết kế Dual-Ledger đã thống nhất, cách tính toán cho dòng này đối với % Cổ phần Capital cuối được xử lý cụ thể như sau:

1\. Nguyên tắc xử lý dòng này đối với Capital (% Cổ phần)  
TẮC NGHIỆP VỤ: Dòng này hoàn toàn KHÔNG NÊN ghi nhận tác động vào Contributed Capital (Vốn cống hiến) của bất kỳ ai. Tác động capital\_impact của dòng này đối với tất cả các thành viên là bằng 0\.

Tại sao lại bằng 0 đối với Capital?  
Không phải rút vốn cổ phần: Đây là tiền ứng chi cho chuyến đi Cát Bà (tiêu dùng xong là hết, không tạo ra hay làm mất đi tài sản tích lũy của công ty).

Capital phản ánh Lịch sử Cống hiến: Vốn cống hiến (100% Gross từ các dự án \+ tiền nạp quỹ) đo lường giá trị dài hạn mà từng người mang về cho tổ chức. Các chi phí ăn chơi, du lịch ngắn hạn không được làm sụt giảm điểm cống hiến/tỷ lệ cổ phần của họ.

2\. Dòng này sẽ được ghi nhận vào đâu trong hệ thống?  
Khoản tiền −5.477.300 VNĐ phân bổ âm cho các cá nhân (−60k,−710k,−350k...) chỉ tác động duy nhất vào Ví tiền mặt / Số dư khả dụng (Member Cash Wallet) để quản lý công nợ/rút tiền:

Cash Ledger (cash\_impact): Trừ trực tiếp vào số dư tiền mặt có thể rút của từng người tương ứng với số tiền họ đã tiêu dùng trong chuyến đi.

Capital Ledger (capital\_impact): Bằng 0 cho tất cả thành viên.

3\. Công thức tính % Cổ phần Capital cuối cùng (Cap Table Final)  
Để tính % Cổ phần Capital cuối một cách chuẩn xác nhất mà không bị dòng Cát Bà (và các khoản ăn uống/tiêu dùng chung khác) làm méo mó, bạn áp dụng công thức sau:

Bước 1: Tính Vốn cống hiến tích lũy của từng người (C   
i  
​  
 )  
C   
i  
​  
 \=∑(100% Gross c   
a  
ˊ  
 c dự    
a  
ˊ  
 n người đ   
o  
ˊ  
  tham gia)+∑(Ti   
e  
ˆ  
   
ˋ  
 n t   
u  
ˊ  
 i người đ   
o  
ˊ  
  tự nạp trực ti   
e  
ˆ  
   
ˊ  
 p v   
a  
ˋ  
 o Qu   
y  
˜  
​  
 )  
(Lưu ý: Bỏ qua hoàn toàn các dòng chi ăn uống Cát Bà, mua kìm, mua WiFi, chi ăn chè...)

Bước 2: Tính Tổng Vốn cống hiến toàn hệ thống (C   
total  
​  
 )  
C   
total  
​  
 \=∑C   
i  
​  
 (Tổng C   
i  
​  
  của t   
a  
ˆ  
   
ˊ  
 t cả th   
a  
ˋ  
 nh vi   
e  
ˆ  
 n)  
Bước 3: Tính % Cổ phần Capital cuối (E   
i  
​  
 )  
Equity % của th   
a  
ˋ  
 nh vi   
e  
ˆ  
 n i=(   
C   
total  
​  
   
C   
i  
​  
   
​  
 )×100%  
4\. Bảng minh họa hạch toán riêng cho dòng \#24  
Tài khoản / Thành viên	Tác động Ví tiền mặt (cash\_impact)	Tác động Vốn cổ phần (capital\_impact)  
Nguyễn Đăng Phúc Hưng	−60.000 VNĐ	0 VNĐ  
Hồ Trùng Sơn	−710.000 VNĐ	0 VNĐ  
Lê Văn Thành An	−350.000 VNĐ	0 VNĐ  
Nguyễn Trung Kiên	−560.000 VNĐ	0 VNĐ  
... (các thành viên khác)	... (trừ đúng số âm tương ứng)	0 VNĐ  
Quỹ Weamis (Công ty)	0 VNĐ	0 VNĐ  
Tóm lại: Khi tính % Cổ phần Capital cuối, bạn chỉ cần LỌC BỎ (Filter out) dòng \#24 này ra khỏi công thức tính Capital. Dòng này chỉ dùng để cấn trừ vào số tiền mặt mà từng người được phép rút về túi cá nhân\!

2896666 630000 6457766 1850000 \-1623334 3450000 2636666 \-750000 200000 0 0 0 0

Nguyễn Hoàng Việt Nguyễn Đăng Phúc Hưng Hồ Trùng Sơn Lê Văn Thành An Nguyễn Trung Kiên Nguyễn Quý Đức Trịnh Quang Minh Vũ Đức Hoàng Anh Phúc Đăng Trần Đăng Sinh Dương Nguyễn An Quốc Minh vậy đây là capital cuối, ta cần tính % cổ phần thế nào: "STT Ngày, Giờ ND giao dịch Tổng tiền Nguyễn Hoàng Việt Nguyễn Đăng Phúc Hưng Hồ Trùng Sơn Lê Văn Thành An Nguyễn Trung Kiên Nguyễn Quý Đức Trịnh Quang Minh Vũ Đức Hoàng Anh Phúc Đăng Trần Đăng Sinh Dương Nguyễn An Quốc Minh

1 12/02/2026 15:10 Việt: Tiền Video AI 1200000 1200000

2 15/02/2026 17:37 Việt: Tiền Everbloom 5000000 2500000 2500000

3 16/02/2026 23:16 Hồ Sơn: đóng bát năm mới 2000000 2000000

4 17/02/2026 12:11 Việt: CTO vắt cổ chày ra nước ủng hộ kèo 3 500000 500000

5 17/02/2026 12:11 Quý Đức: Góp vào quỹ chung 200000 200000

6 23/02/2026 11:27 Hồ Sơn: thèm bánh mì que cay 1000000 1000000

7 01/03/2026 18:28 Việt: Tiền đớp ăn vặt hp \-920000

8 01/03/2026 22:33 Việt: Chi tiền mặt \+ nước: Minh Hưng Việt \-390000 \-130000 \-130000 \-130000

9 08/03/2026 12:33 Việt: Kin vay \-1000000 \-1000000

10 09/03/2026 11:36 Kiên: Trả nợ vay ngày 08/03 \-1000000 \-1000000

11 10/03/2026 21:25 Chí phèo cào mặt ăn vạ quỹ \-1000000 \-1000000

12 11/03/2026 20:03 Rút tiền lương \-2250000 \-2250000

13 13/03/2026 03:01 Góp vào quỹ chung 247766 247766

14 02/04/2026 09:58 loc dau thang 1000000 1000000

15 11/04/2026 10:39 Góp vào quỹ chung 600000 600000

16 11/04/2026 10:40 Giả lợ quỹ. Cảm ơn ae 1000000 1000000

17 11/04/2026 19:00 tiền chí phèo bao thịt chó \-1000000 \-1000000

18 17/04/2026 21:17 Mmb villa \-1700000

19 22/04/2026 15:36 trang trải cuộc sống cuối tháng \-1000000 \-1000000

20 23/04/2026 21:26 Trả nợ tiền răng cho chí phèo 1000000 1000000

21 23/04/2026 21:31 Chộ nhận sứng 500000 250000 250000

22 28/04/2026 23:26 Góp vào quỹ chung 60000 60000

23 28/04/2026 23:42 Dư cọc villa 1400000

24 29/04/2026 00:18 Bú tiền ứng cát bà \-5477300

25 07/05/2026 09:43 Mượn đi đánh lô \-500000 \-500000

26 07/05/2026 10:40 Góp vào quỹ chung 700000 350000 350000

27 07/05/2026 10:58 Góp vào quỹ chung 650000 650000

28 07/05/2026 13:04 bat ca 710000 710000

29 07/05/2026 15:55 Góp vào quỹ chung 500000 500000

30 07/05/2026 20:31 Góp vào quỹ chung 560000 560000

31 08/05/2026 16:13 Đi rửa chân \-1000000 \-1000000

32 18/05/2026 14:29 Wifi lalot 3250000 433333 1408333 1408333

33 19/05/2026 21:27 Gac \-170000

34 04/06/2026 22:57 Chộ nhận sứng 700000 350000 350000

35 04/06/2026 22:58 Trả nợ \+ tiền cát bà chí phèo 1700000 1700000

36 05/06/2026 10:43 ngon ngay 1200000 1200000

37 05/06/2026 13:24 Góp vào quỹ chung 3250000 433333 1408333 1408333

38 16/06/2026 16:52 Đói kém xin lương :(((( \-1000000 \-1000000

39 16/06/2026 19:09 Giai cuu Chi Pheo mua World cup \-1000000 \-1000000

40 16/06/2026 19:12 vay 3 củ đóng học phí \-3000000 \-3000000

41 30/06/2026 12:00 CTO trả nợ tiền học 3000000 3000000

42 02/07/2026 12:00 Góp 10% cns 700000 350000 350000

43 05/07/2026 12:00 Chuyển tiền đến BBBTHANGLONG CN Lau Phan Dao Duy Anh (PVComBank Pay) thanh toan don hang 634248107 \-3233520

44 08/07/2026 12:00 Mua ram, đói kém \-3000000 \-3000000

45 12/07/2026 12:00 Tán lộc 700000 700000

46 20/07/2026 12:00 mua kìm đấu wifi \-150000

47 29/07/2026 12:00 Quỹ networking với anh 3T \- Tri ân vi da den \-535000

48 29/07/2026 18:00 CTO góp cns tháng 7 900000 450000 450000

49 01/08/2026 10:00 Trả nợ giúp em Phúc Đăng 200k tiền cát bà, còn lại nợ 500k 200000 200000

50 04/08/2026 12:00 tiền ăn chè \-100000 \-50000 \-50000

51 06/08/2026 ship gcnsv của Việt \-40000 \-40000

% cổ phần 2896666 630000 6457766 1850000 \-1623334 3450000 2636666 \-750000 200000 0 0 0 0

Tổng tiền Nguyễn Hoàng Việt Nguyễn Đăng Phúc Hưng Hồ Trùng Sơn Lê Văn Thành An Nguyễn Trung Kiên Nguyễn Quý Đức Trịnh Quang Minh Vũ Đức Hoàng Anh Phúc Đăng Trần Đăng Sinh Dương Nguyễn An Quốc Minh" 

Để tính % Cổ phần Capital cuối từ dãy số dư ròng của các thành viên, ta cần áp dụng quy tắc tài chính FinTech về Vốn góp thực tế ròng (Net Paid-in Capital).

1\. Phân tích dãy số liệu và Nguyên tắc lọc Vốn  
Dãy số dư cuối cùng của 13 thành viên theo thứ tự:

Nguyễn Hoàng Việt: 2.896.666

Nguyễn Đăng Phúc Hưng: 630.000

Hồ Trùng Sơn: 6.457.766

Lê Văn Thành An: 1.850.000

Nguyễn Trung Kiên: −1.623.334 (Số dư âm \= Đang nợ hệ thống)

Nguyễn Quý Đức: 3.450.000

Trịnh Quang Minh: 2.636.666

Vũ Đức Hoàng Anh: −750.000 (Số dư âm \= Đang nợ hệ thống)

Phúc Đăng: 200.000

Trần Đăng Sinh / Dương / Nguyễn An / Quốc Minh: 0

Quy tắc Kế toán FinTech khi tính Tỷ lệ Cổ phần (% Equity):  
Chỉ tính các thành viên có Vốn góp DƯƠNG (\>0): Cổ phần phản ánh quy mô đóng góp vốn tích lũy vào tổ chức.

Khoản số dư ÂM (\<0): Được hạch toán là Nợ phải thu cá nhân (Accounts Receivable / Member Debt). Các khoản nợ này KHÔNG được tính thành cổ phần âm hay làm giảm tổng quy mô vốn điều lệ tích lũy của tổ chức.

2\. Tính Toán Chi Tiết Bước-theo-Bước  
Bước 1: Tính Tổng Vốn Góp Dương Thực Tế (C   
total  
​  
 )  
Cộng tổng số dư vốn của các thành viên có giá trị dương:

C   
total  
​  
 \=2.896.666+630.000+6.457.766+1.850.000+3.450.000+2.636.666+200.000  
C   
total  
​  
 \=18.121.098 VNĐ  
Bước 2: Tính Tỷ lệ % Cổ phần của từng thành viên  
Sử dụng công thức:

% Cổ ph   
a  
ˆ  
   
ˋ  
 n=(   
18.121.098  
V   
o  
ˆ  
   
ˊ  
 n r   
o  
ˋ  
 ng c   
a  
ˊ  
  nh   
a  
ˆ  
 n  
​  
 )×100%  
Hồ Trùng Sơn:

18.121.098  
6.457.766  
​  
 ×100%=35,64%  
Nguyễn Quý Đức:

18.121.098  
3.450.000  
​  
 ×100%=19,04%  
Nguyễn Hoàng Việt:

18.121.098  
2.896.666  
​  
 ×100%=15,98%  
Trịnh Quang Minh:

18.121.098  
2.636.666  
​  
 ×100%=14,55%  
Lê Văn Thành An:

18.121.098  
1.850.000  
​  
 ×100%=10,21%  
Nguyễn Đăng Phúc Hưng:

18.121.098  
630.000  
​  
 ×100%=3,48%  
Phúc Đăng:

18.121.098  
200.000  
​  
 ×100%=1,10%  
Nguyễn Trung Kiên & Vũ Đức Hoàng Anh: 0,00% (Tài khoản ghi nhận Khoản nợ cá nhân đối với Quỹ)

Trần Đăng Sinh, Dương, Nguyễn An, Quốc Minh: 0,00%

3\. Bảng Phân Bổ Cổ Phần Hoàn Chỉnh (Cap Table Summary)  
STT	Thành viên	Số dư Vốn ròng (VNĐ)	Tỷ lệ Cổ phần (% Equity)	Trạng thái Tài khoản  
1	Hồ Trùng Sơn	6.457.766	35,64%	Vốn góp dương  
2	Nguyễn Quý Đức	3.450.000	19,04%	Vốn góp dương  
3	Nguyễn Hoàng Việt	2.896.666	15,98%	Vốn góp dương  
4	Trịnh Quang Minh	2.636.666	14,55%	Vốn góp dương  
5	Lê Văn Thành An	1.850.000	10,21%	Vốn góp dương  
6	Nguyễn Đăng Phúc Hưng	630.000	3,48%	Vốn góp dương  
7	Phúc Đăng	200.000	1,10%	Vốn góp dương  
8	Nguyễn Trung Kiên	−1.623.334	0,00%	Nợ phải thu cá nhân  
9	Vũ Đức Hoàng Anh	−750.000	0,00%	Nợ phải thu cá nhân  
10	Trần Đăng Sinh / Dương / Nguyễn An / Quốc Minh	0	0,00%	Không ghi nhận vốn  
TỔNG	Tổng vốn điều lệ tích lũy	18.121.098	100,00%	  
4\. Logic Xử Lý Cho Phần Mềm (Lập Trình Feature)  
Khi đưa logic này vào code module Cap Table Calculator, hệ thống cần xử lý 2 bước:

TypeScript  
// Algorithm to calculate Equity Ownership %  
function calculateEquityShares(memberBalances: Record\<string, number\>) {  
  // 1\. Filter only positive balances  
  const positiveBalances \= Object.entries(memberBalances)  
    .filter((\[\_, balance\]) \=\> balance \> 0);

  // 2\. Sum up Total Positive Capital  
  const totalPositiveCapital \= positiveBalances  
    .reduce((sum, \[\_, balance\]) \=\> sum \+ balance, 0);

  // 3\. Map to Equity %  
  const capTable \= {};  
  for (const \[member, balance\] of Object.entries(memberBalances)) {  
    if (balance \> 0\) {  
      capTable\[member\] \= {  
        capital: balance,  
        equityShare: Number(((balance / totalPositiveCapital) \* 100).toFixed(2)),  
        type: 'ACTIVE\_CAPITAL'  
      };  
    } else {  
      capTable\[member\] \= {  
        capital: balance,  
        equityShare: 0.00,  
        type: balance \< 0 ? 'MEMBER\_DEBT' : 'ZERO\_BALANCE'  
      };  
    }  
  }

  return capTable;  
}

