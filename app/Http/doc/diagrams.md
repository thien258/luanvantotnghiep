# Sơ đồ hệ thống — Aroma Shop

> Mở preview bằng `Ctrl + Shift + V`

---

## 1. Sơ đồ đăng nhập (Flowchart)

```mermaid
flowchart LR
    A([Khách hàng]) --> B[Chọn Đăng nhập]
    B --> C[Hiển thị form đăng nhập]
    C --> D[Nhập email + mật khẩu]
    D --> E{Thông tin hợp lệ?}
    E -->|Không hợp lệ| D
    E -->|Hợp lệ| F{Kiểm tra tài khoản}
    F -->|Không tồn tại| G[Thông báo lỗi]
    G --> D
    F -->|Tồn tại| H{Xác định role}
    H -->|admin / director| I[Chuyển /admin]
    H -->|warehouse| J[Chuyển /admin/orders]
    H -->|manufacturer| K[Chuyển /admin/supplier-offers]
    H -->|customer| L[Chuyển trang chủ /]
```

---

## 2. Sơ đồ đăng ký (Flowchart)

```mermaid
flowchart LR
    A([Khách hàng]) -->|Chọn| B[Đăng ký]
    B --> C[Hiển thị trang đăng ký]
    C --> D[Nhập thông tin]
    D --> E{Kiểm tra dữ liệu}
    E -->|Không hợp lệ| D
    E -->|Hợp lệ| F{Email đã tồn tại?}
    F -->|Đã tồn tại| G[Báo lỗi: Username đã tồn tại]
    G --> D
    F -->|Chưa tồn tại| H[Lưu tài khoản - role: customer]
    H --> I[Gửi email xác minh]
    I --> J([Hoàn tất])
```

---

## 3. Sơ đồ đặt hàng — COD & BANK TRANSFER (Flowchart)

```mermaid
flowchart TD
    A([Khách hàng]) --> B[Chọn sản phẩm vào giỏ hàng]
    B --> C[Tick sản phẩm → Nhấn Checkout]
    C --> D[Lưu cart_ids vào session]
    D --> E[Hiển thị trang thanh toán]
    E --> F[Nhập thông tin giao hàng]
    F --> G[Chọn phương thức thanh toán]
    G --> H{Validate thông tin}
    H -->|Không hợp lệ| F
    H -->|Hợp lệ| I[Tạo Order + OrderDetail trong DB]
    I --> J[Xóa cart items đã chọn]
    J --> K{Phương thức?}

    K -->|COD| L[status = 1 - Đã đặt]
    L --> M[Gửi email xác nhận]
    M --> N([Chuyển trang chủ])

    K -->|BANK TRANSFER| O[status = 0 - Chờ thanh toán]
    O --> P[Gọi API PayOS tạo link]
    P --> Q{PayOS thành công?}
    Q -->|Có| R[Redirect sang trang QR PayOS]
    Q -->|Không| S[Fallback: trang VietQR]
    R --> T{Khách thanh toán?}
    T -->|Thành công| U[PayOS Webhook → status 0→1]
    U --> V[Gửi email xác nhận]
    T -->|Hủy| W[payosCancel → xóa đơn, hoàn giỏ hàng]
```

---

## 4. Sơ đồ trạng thái đơn hàng (State Diagram)

```mermaid
stateDiagram-v2
    [*] --> pending_payment : BANK TRANSFER đặt hàng
    [*] --> confirmed : COD đặt hàng

    pending_payment --> confirmed : PayOS Webhook thanh toán thành công
    pending_payment --> [*] : Khách hủy → xóa đơn

    confirmed --> shipping : Admin xuất kho\n(trừ tồn kho tại đây)
    confirmed --> [*] : Khách hủy → hoàn giỏ hàng

    shipping --> delivered : Khách quét QR xác nhận nhận hàng
    delivered --> return_requested : Khách yêu cầu hoàn hàng
    delivered --> [*] : Hoàn tất

    return_requested --> damaged : Admin duyệt - hàng hỏng
    return_requested --> [*] : Admin xử lý xong

    note right of confirmed
        status = 1
    end note
    note right of shipping
        status = 3
    end note
    note right of delivered
        status = 4
    end note
    note right of return_requested
        status = 5
    end note
    note right of damaged
        status = 6
    end note
```

---

## 5. Sơ đồ quy trình nhập hàng (Flowchart)

```mermaid
flowchart TD
    A([Admin]) --> B[Tạo Yêu cầu nhập hàng\nProcurementRequest]
    B --> C[NSX nhận yêu cầu]
    C --> D[NSX gửi file Excel báo giá]
    D --> E[Admin upload → tạo SupplierOffer\n+ SupplierOfferItem]
    E --> F[Admin xem & so sánh các báo giá]
    F --> G{Duyệt báo giá?}
    G -->|Từ chối| H[status = rejected]
    G -->|Chấp nhận| I[Tạo PurchaseOrder\n+ PurchaseOrderItem]
    I --> J[SupplierOffer status = accepted]
    J --> K[Theo dõi trạng thái đơn\npending → confirmed → delivering]
    K --> L[Admin xác nhận nhận hàng\nstatus = received]
    L --> M[Tải file CSV từ đơn]
    M --> N[Upload vào trang Nhập Kho]
    N --> O[Tạo WarehouseReceipt\n+ WarehouseStockLog]
    O --> P[Cộng tồn kho Product.quantity]
    P --> Q([Hoàn tất nhập hàng])
```

---

## 6. Sơ đồ tuần tự — Thanh toán PayOS (Sequence Diagram)

```mermaid
sequenceDiagram
    actor KH as Khách hàng
    participant Web as Laravel Web
    participant DB as Database
    participant PayOS as PayOS API

    KH->>Web: POST /order/place (form thanh toán)
    Web->>DB: Tạo Order (status=0) + OrderDetail
    Web->>DB: Xóa Cart items
    Web->>PayOS: POST /v2/payment-requests (HMAC signature)
    PayOS-->>Web: checkoutUrl
    Web-->>KH: Redirect sang trang QR PayOS

    KH->>PayOS: Quét QR / chuyển khoản
    PayOS->>Web: POST /payos/webhook (code=00)
    Web->>DB: Update Order status 0→1
    Web->>KH: Gửi email xác nhận đơn hàng
    PayOS-->>KH: Redirect về /payos/success
```

---

## 7. Sơ đồ lớp — Quan hệ Model chính (Class Diagram)

```mermaid
classDiagram
    class User {
        +int id
        +string name
        +string email
        +string role
        +string phone
        +string address
        +isAdmin()
        +isCustomer()
        +hasRole()
    }

    class Order {
        +int id
        +int idUser
        +string fullname
        +string phone
        +string address
        +string payment_method
        +int total_price
        +int status
        +string tracking_code
    }

    class OrderDetail {
        +int id
        +int idOrder
        +int idProduct
        +string name
        +int quantity
        +int price
    }

    class Product {
        +int id
        +string title
        +int price
        +int quantity
        +string status
        +string volume
        +getDiscountedPrice()
    }

    class Category { +int id; +string name }
    class Brand { +int id; +string name }
    class Festival { +int id; +int discount; +date start_date; +date end_date }

    class ProcurementRequest {
        +int id
        +string request_code
        +string status
        +string deadline
    }

    class SupplierOffer {
        +int id
        +string offer_code
        +string status
        +int manufacturer_id
        +int request_id
    }

    class PurchaseOrder {
        +int id
        +string order_code
        +string status
        +int manufacturer_id
        +int offer_id
    }

    class ManuFacturer {
        +int id
        +string name
        +int user_id
    }

    class WarehouseReceipt {
        +int id
        +string receipt_code
        +string supplier
        +int total_items
    }

    class WarehouseStockLog {
        +int id
        +int product_id
        +int receipt_id
        +int quantity
        +date expiry_date
    }

    User "1" --> "N" Order : đặt hàng
    Order "1" --> "N" OrderDetail : chứa
    OrderDetail "N" --> "1" Product : thuộc về
    Product "N" --> "1" Category : thuộc danh mục
    Product "N" --> "1" Brand : thuộc thương hiệu
    Product "N" --> "N" Festival : áp dụng giảm giá
    Product "N" --> "N" ManuFacturer : cung cấp bởi

    ProcurementRequest "1" --> "N" SupplierOffer : nhận báo giá
    SupplierOffer "1" --> "1" PurchaseOrder : tạo đơn
    PurchaseOrder "N" --> "1" ManuFacturer : gửi tới
    ManuFacturer "1" --> "1" User : tài khoản đăng nhập

    WarehouseReceipt "1" --> "N" WarehouseStockLog : ghi log
    WarehouseStockLog "N" --> "1" Product : cộng tồn kho
```

---

## 8. Sơ đồ ER — Cơ sở dữ liệu (ER Diagram)

```mermaid
erDiagram
    users ||--o{ orders : "đặt hàng"
    orders ||--|{ order_details : "chứa"
    order_details }o--|| products : "sản phẩm"
    products }o--|| categories : "danh mục"
    products }o--o| brands : "thương hiệu"
    products }o--o| concentrations : "nồng độ"
    products }o--o{ festival_product : "giảm giá"
    festival_product }o--|| festivals : "festival"

    procurement_requests ||--o{ supplier_offers : "nhận báo giá"
    supplier_offers ||--o{ supplier_offer_items : "dòng SP"
    supplier_offers ||--o| purchase_orders : "tạo đơn"
    purchase_orders ||--|{ purchase_order_items : "dòng SP"
    purchase_orders }o--|| manufacturers : "gửi tới NSX"
    manufacturers }o--o| users : "tài khoản"

    warehouse_receipts ||--|{ warehouse_stock_logs : "log nhập kho"
    warehouse_stock_logs }o--|| products : "cộng tồn kho"

    users {
        int id PK
        string name
        string email
        string role
        string phone
    }
    orders {
        int id PK
        int idUser FK
        string payment_method
        int status
        string tracking_code
        int total_price
    }
    products {
        int id PK
        string title
        int price
        int quantity
        string status
        int idCategory FK
        int idBrand FK
    }
    purchase_orders {
        int id PK
        int offer_id FK
        int manufacturer_id FK
        string order_code
        string status
    }
```


---

## 9. Quy trình quên mật khẩu (Flowchart)

```mermaid
flowchart LR
    A([Khách hàng]) --> B[Nhấn Quên mật khẩu]
    B --> C[Nhập email]
    C --> D{Email tồn tại?}
    D -->|Không| E[Thông báo lỗi]
    E --> C
    D -->|Có| F[Gửi link reset về email]
    F --> G[Khách mở email, nhấn link]
    G --> H{Link còn hiệu lực?}
    H -->|Hết hạn| I[Thông báo link hết hạn]
    H -->|Còn hiệu lực| J[Hiển thị form nhập mật khẩu mới]
    J --> K[Nhập mật khẩu mới + xác nhận]
    K --> L{Validate}
    L -->|Không hợp lệ| K
    L -->|Hợp lệ| M[Cập nhật mật khẩu trong DB]
    M --> N([Chuyển trang đăng nhập])
```

---

## 10. Quy trình cập nhật profile khách hàng (Flowchart)

```mermaid
flowchart LR
    A([Khách hàng]) --> B[Vào trang Profile]
    B --> C[Hiển thị thông tin hiện tại]
    C --> D[Chỉnh sửa thông tin\nHọ tên / SĐT / Địa chỉ]
    D --> E{Validate}
    E -->|Không hợp lệ| D
    E -->|Hợp lệ| F[Lưu vào DB]
    F --> G[Thông báo cập nhật thành công]
    G --> H([Hoàn tất])
```

---

## 11. Quy trình quản lý địa chỉ nhận hàng (Flowchart)

```mermaid
flowchart TD
    A([Khách hàng]) --> B[Vào trang Địa chỉ]
    B --> C{Chọn thao tác}
    C -->|Thêm mới| D[Nhập thông tin địa chỉ]
    D --> E{Validate}
    E -->|Không hợp lệ| D
    E -->|Hợp lệ| F[Lưu UserAddress vào DB]
    C -->|Chỉnh sửa| G[Sửa thông tin địa chỉ]
    G --> F
    C -->|Xóa| H[Xóa địa chỉ khỏi DB]
    C -->|Đặt mặc định| I[Cập nhật is_default = 1\nCác địa chỉ khác = 0]
    F --> J([Hoàn tất])
    H --> J
    I --> J
```

---

## 12. Quy trình tìm kiếm và lọc sản phẩm (Flowchart)

```mermaid
flowchart LR
    A([Khách hàng]) --> B[Nhập từ khóa tìm kiếm]
    B --> C[AJAX gợi ý real-time\ntối đa 3 sản phẩm]
    C --> D{Nhấn Enter / Tìm kiếm?}
    D -->|Không| B
    D -->|Có| E[Hiển thị trang kết quả]
    E --> F{Áp dụng bộ lọc?}
    F -->|Lọc thương hiệu| G[whereIn idBrand]
    F -->|Lọc danh mục| H[whereIn idCategory]
    F -->|Lọc nồng độ| I[whereIn idConcentration]
    F -->|Lọc khoảng giá| J[filter getDiscountedPrice]
    F -->|Sắp xếp giá| K[sortBy / sortByDesc]
    G & H & I & J & K --> L[Hiển thị danh sách sản phẩm\ncó phân trang]
    L --> M([Khách chọn sản phẩm])
```

---

## 13. Quy trình quản lý giỏ hàng (Flowchart)

```mermaid
flowchart TD
    A([Khách hàng]) --> B{Chọn thao tác}
    B -->|Thêm vào giỏ| C{Đã đăng nhập?}
    C -->|Chưa| D[Redirect trang đăng nhập]
    C -->|Rồi| E{Kiểm tra tồn kho}
    E -->|Không đủ| F[Thông báo lỗi tồn kho]
    E -->|Đủ| G{Sản phẩm đã có trong giỏ?}
    G -->|Có| H[Tăng quantity]
    G -->|Chưa| I[Tạo Cart mới]
    B -->|Tăng / Giảm SL| J{Kiểm tra tồn kho}
    J -->|Hợp lệ| K[Cập nhật quantity - AJAX]
    B -->|Xóa sản phẩm| L[Xóa Cart record]
    B -->|Xem giỏ hàng| M[Tính giá sau Festival\ngetDiscountedPrice]
    M --> N[Hiển thị tổng tiền]
    H & I & K & L --> O([Hoàn tất])
```

---

## 14. Quy trình hủy đơn hàng (Flowchart)

```mermaid
flowchart LR
    A([Khách hàng]) --> B[Vào lịch sử đơn hàng]
    B --> C[Chọn đơn hàng cần hủy]
    C --> D{Đơn hàng status = 1?\nChưa xuất kho}
    D -->|Không - đã xuất kho| E[Không cho hủy\nHiển thị thông báo]
    D -->|Có| F[Hoàn từng sản phẩm\nvề giỏ hàng - Cart]
    F --> G[Xóa đơn hàng khỏi DB]
    G --> H[Chuyển về trang giỏ hàng]
    H --> I([Hoàn tất])
```

---

## 15. Quy trình xác nhận nhận hàng và hoàn hàng (Flowchart)

```mermaid
flowchart TD
    A([Shipper giao hàng]) --> B[Khách quét QR\ntrên thùng hàng]
    B --> C[Hệ thống tìm đơn\ntheo tracking_code]
    C --> D{Đơn đang status = 3?\nĐang giao}
    D -->|Không| E[Hiển thị trạng thái hiện tại]
    D -->|Có| F[Khách nhấn Xác nhận đã nhận]
    F --> G[status 3 → 4 - Hoàn tất]
    G --> H{Khách muốn hoàn hàng?}
    H -->|Không| I([Kết thúc])
    H -->|Có| J[Nhập lý do hoàn hàng]
    J --> K[status 4 → 5 - Yêu cầu hoàn]
    K --> L[Admin xem xét]
    L --> M[status 5 → 6 - Hàng hỏng\nChờ trả NSX]
    M --> I
```

---

## 16. Quy trình xử lý đơn hàng - Admin/Warehouse (Flowchart)

```mermaid
flowchart TD
    A([Nhân viên kho]) --> B[Vào danh sách đơn hàng\n/admin/orders]
    B --> C[Lọc đơn status = 1\nChờ xuất kho]
    C --> D[Xem chi tiết đơn hàng]
    D --> E[Nhấn Xuất kho]
    E --> F[Hệ thống trừ tồn kho\ntheo FIFO HSD]
    F --> G[Ghi WarehouseStockLog\ntype = export]
    G --> H[status 1 → 3 - Đang giao]
    H --> I{Có vấn đề khi giao?}
    I -->|Không| J[Chờ khách xác nhận QR]
    I -->|Có - khách từ chối| K[status 3 → 5 - Yêu cầu hoàn]
    K --> L[Admin duyệt hoàn hàng]
    L --> M[status 5 → 6 - Hàng hỏng]
    M --> N([Chờ trả NSX])
```

---

## 17. Quy trình nhập kho qua file CSV/Excel (Flowchart)

```mermaid
flowchart TD
    A([Nhân viên kho]) --> B[Upload file CSV/Excel\ntrang Nhập Kho]
    B --> C[Lưu WarehouseImport\nstatus = pending]
    C --> D([Admin])
    D --> E[Xem preview sản phẩm\ntrong file]
    E --> F{Duyệt file?}
    F -->|Từ chối| G[status = rejected]
    F -->|Duyệt| H[Tick chọn sản phẩm\ncần nhập]
    H --> I{Sản phẩm đã tồn tại?}
    I -->|Có| J[Cộng tồn kho\nProduct.quantity += qty]
    I -->|Chưa| K[Tạo Product mới]
    J & K --> L[Tạo WarehouseReceipt]
    L --> M[Ghi WarehouseStockLog\ntype = import + expiry_date]
    M --> N[status = approved]
    N --> O([Hoàn tất nhập kho])
```

---

## 18. Quy trình quản lý sản phẩm - Admin (Flowchart)

```mermaid
flowchart TD
    A([Admin]) --> B[Vào trang Quản lý Sản phẩm]
    B --> C{Chọn thao tác}
    C -->|Thêm mới| D[Điền thông tin sản phẩm\nTên / Giá / SL / Ảnh\nDanh mục / Thương hiệu / Nồng độ]
    D --> E{Validate}
    E -->|Không hợp lệ| D
    E -->|Hợp lệ| F[Lưu Product vào DB]
    F --> G[Gán Festival / NSX nếu có]
    C -->|Chỉnh sửa| H[Sửa thông tin sản phẩm]
    H --> I[sync Festival + NSX]
    C -->|Xóa| J[Xóa Product khỏi DB]
    C -->|Tạo yêu cầu nhập hàng| K[Tick SP cần nhập\nTạo ProcurementRequest]
    G & I & J & K --> L([Hoàn tất])
```

---

## 19. Quy trình quản lý khuyến mãi Festival (Flowchart)

```mermaid
flowchart TD
    A([Admin]) --> B[Vào trang Quản lý Festival]
    B --> C{Chọn thao tác}
    C -->|Tạo Festival| D[Nhập tên / mức giảm %\nNgày bắt đầu / kết thúc]
    D --> E{Validate}
    E -->|Không hợp lệ| D
    E -->|Hợp lệ| F[Lưu Festival vào DB]
    F --> G[Chọn sản phẩm cho Festival\nCheckbox / AJAX]
    G --> H[sync many-to-many\nfestival_product]
    C -->|Chỉnh sửa| I[Sửa thông tin Festival]
    C -->|Xóa| J[Xóa Festival]
    H & I & J --> K([Hoàn tất])
    K --> L{Festival active?}
    L -->|Có| M[Tự động áp dụng giá giảm\nkhi khách thêm giỏ / thanh toán]
    L -->|Không| N[Không áp dụng]
```

---

## 20. Quy trình quản lý người dùng và phân quyền (Flowchart)

```mermaid
flowchart TD
    A([Admin]) --> B[Vào trang Quản lý Users]
    B --> C[Xem danh sách tất cả users]
    C --> D{Chọn thao tác}
    D -->|Đổi role| E{Role mới là gì?}
    E -->|manufacturer| F[Tự động tạo\nrecord Manufacturer\nnếu chưa có]
    E -->|Khác| G[Hủy liên kết\nManufacturer.user_id = null]
    F & G --> H[Cập nhật User.role]
    D -->|Xóa user| I[Xóa OrderDetail\ncủa từng đơn]
    I --> J[Xóa Orders của user]
    J --> K[Xóa User]
    H & K --> L([Hoàn tất])
```

---

## 21. Quy trình quản lý nhà sản xuất (Flowchart)

```mermaid
flowchart TD
    A([Admin]) --> B[Vào trang Quản lý NSX]
    B --> C{Chọn thao tác}
    C -->|Thêm NSX| D[Nhập tên / SĐT / Địa chỉ]
    D --> E{Tạo tài khoản đăng nhập?}
    E -->|Có| F[Nhập Email + Mật khẩu\nTạo User role=manufacturer]
    F --> G[Liên kết user_id vào Manufacturer]
    E -->|Không| H[Tạo Manufacturer\nkhông có tài khoản]
    C -->|Chỉnh sửa| I[Cập nhật thông tin NSX]
    C -->|Tạo tài khoản sau| J[Nhập Email + MK\nTạo User mới\nGán user_id]
    C -->|Xóa| K[Xóa Manufacturer]
    G & H & I & J & K --> L([Hoàn tất])
```
