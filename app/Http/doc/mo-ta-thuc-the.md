# 3.2.1 Mô tả thực thể

---

## 3.2.1.1 Loại thực thể users (khách hàng / người dùng)

**Mô tả:** Thực thể users lưu trữ thông tin của tất cả người dùng trong hệ thống, bao gồm khách hàng, nhân viên, quản trị viên và nhà sản xuất.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | BigInt | X | X | X | Mã người dùng |
| name | Varchar(191) | | | X | Họ tên |
| email | Varchar(191) | | X | X | Email đăng nhập |
| email_verified_at | Timestamp | | | | Thời điểm xác thực email |
| phone | Varchar(15) | | | X | Số điện thoại |
| address | Varchar(255) | | | X | Địa chỉ |
| password | Varchar(191) | | | X | Mật khẩu (đã mã hóa) |
| remember_token | Varchar(100) | | | | Token ghi nhớ đăng nhập |
| role | Varchar(20) | | | X | Quyền hạn (mặc định: customer) |
| is_active | Boolean | | | X | Trạng thái tài khoản (mặc định: true) |
| created_at | Timestamp | | | X | Ngày tạo |
| updated_at | Timestamp | | | | Ngày cập nhật |

---

## 3.2.1.2 Loại thực thể user_addresses (địa chỉ người dùng)

**Mô tả:** Lưu trữ danh sách địa chỉ giao hàng của từng khách hàng, hỗ trợ nhiều địa chỉ và đánh dấu địa chỉ mặc định.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | BigInt | X | X | X | Mã địa chỉ |
| idUser | BigInt (FK) | | | X | Mã người dùng (FK → users.id) |
| name | Varchar(191) | | | X | Tên người nhận |
| phone | Varchar(20) | | | X | Số điện thoại nhận hàng |
| address | Varchar(255) | | | X | Địa chỉ giao hàng |
| is_default | Boolean | | | X | Địa chỉ mặc định (mặc định: false) |
| created_at | Timestamp | | | X | Ngày tạo |
| updated_at | Timestamp | | | | Ngày cập nhật |


---

## 3.2.1.3 Loại thực thể categories (danh mục sản phẩm)

**Mô tả:** Lưu trữ các danh mục phân loại sản phẩm trong cửa hàng.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | BigInt | X | X | X | Mã danh mục |
| name | Varchar(191) | | | X | Tên danh mục |
| status | Int | | | X | Trạng thái (0: ẩn, 1: hiện) |
| created_at | Timestamp | | | X | Ngày tạo |
| updated_at | Timestamp | | | | Ngày cập nhật |

---

## 3.2.1.4 Loại thực thể brands (thương hiệu)

**Mô tả:** Lưu trữ thông tin các thương hiệu sản phẩm được bán trong hệ thống.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | BigInt | X | X | X | Mã thương hiệu |
| name | Varchar(191) | | | X | Tên thương hiệu |
| image | Text | | | X | Đường dẫn ảnh logo thương hiệu |
| descrip | Text | | | X | Mô tả thương hiệu |
| status | Int | | | X | Trạng thái (0: ẩn, 1: hiện) |
| created_at | Timestamp | | | X | Ngày tạo |
| updated_at | Timestamp | | | | Ngày cập nhật |

---

## 3.2.1.5 Loại thực thể concentrations (nồng độ)

**Mô tả:** Lưu trữ các mức nồng độ nước hoa (ví dụ: EDT, EDP, Parfum), dùng để phân loại sản phẩm.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | BigInt | X | X | X | Mã nồng độ |
| concentration | Varchar(191) | | | X | Tên nồng độ (ví dụ: EDP, EDT) |
| status | Int | | | X | Trạng thái (0: ẩn, 1: hiện) |
| created_at | Timestamp | | | X | Ngày tạo |
| updated_at | Timestamp | | | | Ngày cập nhật |


---

## 3.2.1.6 Loại thực thể products (sản phẩm)

**Mô tả:** Lưu trữ thông tin chi tiết của các sản phẩm nước hoa được bán trên hệ thống.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | BigInt | X | X | X | Mã sản phẩm |
| title | Varchar(250) | | | X | Tên sản phẩm |
| decription | Text | | | X | Mô tả sản phẩm |
| volume | Varchar(191) | | | | Dung tích (ml) |
| price | Int | | | X | Giá bán (mặc định: 0) |
| quantity | Int | | | X | Số lượng tồn kho (mặc định: 0) |
| image | Text | | | X | Đường dẫn ảnh sản phẩm |
| idCategory | BigInt (FK) | | | X | Mã danh mục (FK → categories.id) |
| idBrand | BigInt (FK) | | | | Mã thương hiệu (FK → brands.id) |
| idConcentration | BigInt (FK) | | | | Mã nồng độ (FK → concentrations.id) |
| status | Text | | | X | Trạng thái sản phẩm |
| created_at | Timestamp | | | X | Ngày tạo |
| updated_at | Timestamp | | | | Ngày cập nhật |

---

## 3.2.1.7 Loại thực thể carts (giỏ hàng)

**Mô tả:** Lưu trữ các sản phẩm mà khách hàng đã thêm vào giỏ hàng, chờ thanh toán.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | BigInt | X | X | X | Mã giỏ hàng |
| idUser | BigInt (FK) | | | X | Mã người dùng (FK → users.id) |
| product_id | BigInt (FK) | | | X | Mã sản phẩm (FK → products.id) |
| quantity | Int | | | X | Số lượng (mặc định: 1) |
| created_at | Timestamp | | | X | Ngày tạo |
| updated_at | Timestamp | | | | Ngày cập nhật |

---

## 3.2.1.8 Loại thực thể orders (đơn hàng)

**Mô tả:** Lưu trữ thông tin các đơn đặt hàng của khách hàng, bao gồm thông tin giao hàng, thanh toán và trạng thái xử lý.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | BigInt | X | X | X | Mã đơn hàng |
| idUser | BigInt (FK) | | | X | Mã người dùng (FK → users.id) |
| fullname | Varchar(191) | | | | Họ tên người nhận |
| phone | Varchar(191) | | | | Số điện thoại người nhận |
| address | Varchar(255) | | | X | Địa chỉ giao hàng |
| payment_method | Varchar(191) | | | X | Phương thức thanh toán |
| total_price | Int | | | X | Tổng tiền đơn hàng |
| status | Int | | | X | Trạng thái đơn hàng |
| note | Text | | | | Ghi chú đơn hàng |
| tracking_code | Varchar(191) | | | | Mã theo dõi vận chuyển |
| created_at | Timestamp | | | X | Ngày đặt hàng |
| updated_at | Timestamp | | | | Ngày cập nhật |


---

## 3.2.1.9 Loại thực thể order_details (chi tiết đơn hàng)

**Mô tả:** Lưu trữ danh sách sản phẩm trong từng đơn hàng, ghi nhận số lượng và giá tại thời điểm đặt.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | BigInt | X | X | X | Mã chi tiết đơn hàng |
| idOrder | BigInt (FK) | | | X | Mã đơn hàng (FK → orders.id) |
| idProduct | BigInt (FK) | | | X | Mã sản phẩm (FK → products.id) |
| name | Varchar(191) | | | X | Tên sản phẩm tại thời điểm đặt |
| quantity | Int | | | X | Số lượng |
| price | Int | | | X | Đơn giá tại thời điểm đặt |
| created_at | Timestamp | | | X | Ngày tạo |
| updated_at | Timestamp | | | | Ngày cập nhật |

---

## 3.2.1.10 Loại thực thể festivals (chương trình khuyến mãi)

**Mô tả:** Lưu trữ các chương trình khuyến mãi / lễ hội giảm giá theo thời gian.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | BigInt | X | X | X | Mã chương trình khuyến mãi |
| name | Varchar(150) | | | X | Tên chương trình |
| discount | Int | | | X | Phần trăm giảm giá (mặc định: 0) |
| status | Int | | | X | Trạng thái (1: đang hoạt động) |
| start_date | Date | | | X | Ngày bắt đầu |
| end_date | Date | | | X | Ngày kết thúc |
| created_at | Timestamp | | | X | Ngày tạo |
| updated_at | Timestamp | | | | Ngày cập nhật |

---

## 3.2.1.11 Loại thực thể festival_product (sản phẩm trong khuyến mãi)

**Mô tả:** Bảng trung gian liên kết sản phẩm với chương trình khuyến mãi (many-to-many).

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | BigInt | X | X | X | Mã bản ghi |
| idFestival | BigInt (FK) | | | X | Mã chương trình (FK → festivals.id) |
| idProduct | BigInt (FK) | | | X | Mã sản phẩm (FK → products.id) |
| created_at | Timestamp | | | X | Ngày tạo |
| updated_at | Timestamp | | | | Ngày cập nhật |

---

## 3.2.1.12 Loại thực thể comments (bình luận sản phẩm)

**Mô tả:** Lưu trữ bình luận / đánh giá của người dùng về sản phẩm.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | BigInt | X | X | X | Mã bình luận |
| idProduct | BigInt (FK) | | | X | Mã sản phẩm (FK → products.id) |
| name | Varchar(191) | | | X | Tên người bình luận |
| chat | Text | | | X | Nội dung bình luận |
| created_at | Timestamp | | | X | Ngày tạo |
| updated_at | Timestamp | | | | Ngày cập nhật |


---

## 3.2.1.13 Loại thực thể contacts (liên hệ)

**Mô tả:** Lưu trữ các tin nhắn liên hệ / hỗ trợ mà người dùng gửi đến hệ thống.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | BigInt | X | X | X | Mã liên hệ |
| name | Varchar(191) | | | X | Tên người liên hệ |
| email | Varchar(191) | | | X | Email người liên hệ |
| message | Text | | | X | Nội dung tin nhắn |
| user_id | BigInt (FK) | | | | Mã người dùng (FK → users.id, nullable) |
| created_at | Timestamp | | | X | Ngày gửi |
| updated_at | Timestamp | | | | Ngày cập nhật |

---

## 3.2.1.14 Loại thực thể warehouse_receipts (phiếu nhập kho)

**Mô tả:** Lưu trữ thông tin các phiếu nhập hàng vào kho, mỗi phiếu ghi nhận một lô hàng nhập.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | BigInt | X | X | X | Mã phiếu nhập |
| receipt_code | Varchar(191) | | X | X | Mã phiếu (duy nhất) |
| supplier | Varchar(191) | | | | Tên nhà cung cấp |
| note | Text | | | | Ghi chú |
| total_items | Int | | | X | Tổng số mặt hàng (mặc định: 0) |
| created_at | Timestamp | | | X | Ngày tạo |
| updated_at | Timestamp | | | | Ngày cập nhật |

---

## 3.2.1.15 Loại thực thể warehouse_stock_logs (nhật ký tồn kho)

**Mô tả:** Ghi nhận toàn bộ lịch sử biến động số lượng tồn kho (nhập / xuất) của từng sản phẩm.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | BigInt | X | X | X | Mã nhật ký |
| receipt_id | BigInt (FK) | | | | Mã phiếu nhập (FK → warehouse_receipts.id) |
| product_id | BigInt (FK) | | | X | Mã sản phẩm (FK → products.id) |
| type | Enum | | | X | Loại giao dịch: import / export |
| quantity | Int | | | X | Số lượng biến động |
| stock_after | Int | | | X | Tồn kho sau giao dịch |
| reason | Varchar(191) | | | | Lý do |
| expiry_date | Date | | | | Hạn sử dụng lô hàng |
| created_at | Timestamp | | | X | Ngày ghi nhận |
| updated_at | Timestamp | | | | Ngày cập nhật |


---

## 3.2.1.16 Loại thực thể warehouse_imports (file nhập kho)

**Mô tả:** Quản lý các file Excel nhập kho do nhân viên tải lên, qua quy trình duyệt bởi quản trị viên trước khi áp dụng vào kho.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | BigInt | X | X | X | Mã file nhập |
| file_path | Varchar(191) | | | X | Đường dẫn lưu file |
| original_name | Varchar(191) | | | X | Tên file gốc |
| supplier | Varchar(191) | | | | Tên nhà cung cấp |
| note | Text | | | | Ghi chú |
| uploaded_by | BigInt (FK) | | | | Mã nhân viên tải lên (FK → users.id) |
| status | Enum | | | X | Trạng thái: pending / approved / rejected |
| approved_items | JSON | | | | Danh sách mặt hàng được duyệt |
| reviewed_by | BigInt (FK) | | | | Mã admin duyệt (FK → users.id) |
| reviewed_at | Timestamp | | | | Thời điểm duyệt |
| created_at | Timestamp | | | X | Ngày tạo |
| updated_at | Timestamp | | | | Ngày cập nhật |

---

## 3.2.1.17 Loại thực thể procurement_requests (yêu cầu thu mua)

**Mô tả:** Lưu trữ các yêu cầu thu mua hàng do admin/staff tạo ra, gửi đến các nhà sản xuất để báo giá.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | BigInt | X | X | X | Mã yêu cầu |
| request_code | Varchar(191) | | X | X | Mã yêu cầu (duy nhất) |
| status | Enum | | | X | Trạng thái: open / closed |
| note | Text | | | | Ghi chú |
| deadline | Date | | | | Hạn chót nhận báo giá |
| created_by | BigInt (FK) | | | X | Mã người tạo (FK → users.id) |
| created_at | Timestamp | | | X | Ngày tạo |
| updated_at | Timestamp | | | | Ngày cập nhật |

---

## 3.2.1.18 Loại thực thể procurement_request_items (chi tiết yêu cầu thu mua)

**Mô tả:** Lưu trữ danh sách sản phẩm cần thu mua trong từng yêu cầu.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | BigInt | X | X | X | Mã chi tiết |
| request_id | BigInt (FK) | | | X | Mã yêu cầu (FK → procurement_requests.id) |
| product_id | BigInt (FK) | | | | Mã sản phẩm (FK → products.id, nullable) |
| product_name | Varchar(191) | | | X | Tên sản phẩm tại thời điểm tạo |
| qty_needed | Int | | | X | Số lượng cần thu mua |
| note | Text | | | | Ghi chú |
| created_at | Timestamp | | | X | Ngày tạo |
| updated_at | Timestamp | | | | Ngày cập nhật |


---

## 3.2.1.19 Loại thực thể supplier_offers (báo giá nhà sản xuất)

**Mô tả:** Lưu trữ các bản báo giá từ nhà sản xuất gửi lên hệ thống, có thể đáp lại một yêu cầu thu mua cụ thể.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | BigInt | X | X | X | Mã báo giá |
| manufacturer_id | BigInt (FK) | | | X | Mã nhà sản xuất (FK → users.id) |
| request_id | BigInt (FK) | | | | Mã yêu cầu thu mua (FK → procurement_requests.id) |
| offer_code | Varchar(191) | | X | X | Mã báo giá (duy nhất) |
| note | Text | | | | Ghi chú |
| status | Enum | | | X | Trạng thái: draft / submitted / accepted / rejected |
| submitted_at | Timestamp | | | | Thời điểm gửi báo giá |
| created_at | Timestamp | | | X | Ngày tạo |
| updated_at | Timestamp | | | | Ngày cập nhật |

---

## 3.2.1.20 Loại thực thể supplier_offer_items (chi tiết báo giá)

**Mô tả:** Lưu trữ danh sách sản phẩm và đơn giá trong từng bản báo giá của nhà sản xuất.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | BigInt | X | X | X | Mã chi tiết báo giá |
| offer_id | BigInt (FK) | | | X | Mã báo giá (FK → supplier_offers.id) |
| product_id | BigInt (FK) | | | | Mã sản phẩm (FK → products.id, nullable) |
| product_name | Varchar(191) | | | X | Tên sản phẩm tại thời điểm báo giá |
| unit_price | Decimal(15,2) | | | X | Đơn giá đề xuất |
| note | Text | | | | Ghi chú |
| created_at | Timestamp | | | X | Ngày tạo |
| updated_at | Timestamp | | | | Ngày cập nhật |

---

## 3.2.1.21 Loại thực thể purchase_orders (đơn đặt hàng nhà sản xuất)

**Mô tả:** Lưu trữ các đơn đặt hàng mà admin gửi đến nhà sản xuất sau khi chấp nhận báo giá, theo dõi quá trình giao hàng.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | BigInt | X | X | X | Mã đơn đặt hàng |
| offer_id | BigInt (FK) | | | | Mã báo giá gốc (FK → supplier_offers.id) |
| manufacturer_id | BigInt (FK) | | | X | Mã nhà sản xuất (FK → users.id) |
| order_code | Varchar(191) | | X | X | Mã đơn (duy nhất) |
| total_amount | Decimal(15,2) | | | X | Tổng giá trị đơn hàng |
| status | Enum | | | X | Trạng thái: pending / confirmed / delivering / received / cancelled |
| expected_date | Date | | | | Ngày dự kiến nhận hàng |
| note | Text | | | | Ghi chú |
| created_by | BigInt (FK) | | | X | Mã admin tạo đơn (FK → users.id) |
| created_at | Timestamp | | | X | Ngày tạo |
| updated_at | Timestamp | | | | Ngày cập nhật |

---

## 3.2.1.22 Loại thực thể purchase_order_items (chi tiết đơn đặt hàng NSX)

**Mô tả:** Lưu trữ danh sách sản phẩm và số lượng trong từng đơn đặt hàng gửi nhà sản xuất.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | BigInt | X | X | X | Mã chi tiết |
| purchase_order_id | BigInt (FK) | | | X | Mã đơn đặt hàng (FK → purchase_orders.id) |
| product_id | BigInt (FK) | | | | Mã sản phẩm (FK → products.id, nullable) |
| product_name | Varchar(191) | | | X | Tên sản phẩm tại thời điểm đặt |
| quantity | Int | | | X | Số lượng đặt mua |
| unit_price | Decimal(15,2) | | | X | Đơn giá |
| created_at | Timestamp | | | X | Ngày tạo |
| updated_at | Timestamp | | | | Ngày cập nhật |


---

## 3.2.1.23 Loại thực thể root_activity_logs (nhật ký hoạt động root)

**Mô tả:** Ghi lại toàn bộ hành động của tài khoản root trong hệ thống, phục vụ kiểm toán và theo dõi bảo mật. Dữ liệu người dùng được lưu dạng snapshot để tránh mất log khi tài khoản bị xóa.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | BigInt | X | X | X | Mã nhật ký |
| user_id | BigInt | | | X | Mã người dùng (snapshot, không FK) |
| user_name | Varchar(191) | | | X | Họ tên tại thời điểm ghi log |
| user_email | Varchar(191) | | | X | Email tại thời điểm ghi log |
| action | Varchar(191) | | | X | Mô tả hành động thực hiện |
| created_at | Timestamp | | | X | Thời điểm thực hiện hành động |

---

## 3.2.1.24 Loại thực thể title (banner / slide trang chủ)

**Mô tả:** Lưu trữ nội dung các banner slider hiển thị trên trang chủ, do admin quản lý.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| idTitle | Int | X | X | X | Mã banner |
| title | Varchar(191) | | | X | Tiêu đề banner |
| image | Text | | | X | Đường dẫn ảnh banner |
| button | Varchar(191) | | | X | Văn bản nút CTA |
| descrip | Varchar(191) | | | X | Mô tả ngắn |
| created_by | BigInt (FK) | | | | Mã admin tạo (FK → users.id) |
| created_at | Timestamp | | | X | Ngày tạo |
| updated_at | Timestamp | | | | Ngày cập nhật |

---

## 3.2.1.25 Loại thực thể footer (nội dung chân trang)

**Mô tả:** Lưu trữ thông tin hiển thị ở chân trang web, bao gồm địa chỉ, số điện thoại và email liên hệ của cửa hàng.

| Thuộc tính | Kiểu | K | U | M | Diễn giải |
|---|---|---|---|---|---|
| id | Int | X | X | X | Mã footer |
| header | Text | | | X | Tiêu đề cột 1 |
| textheader | Text | | | X | Nội dung cột 1 |
| header2 | Text | | | X | Tiêu đề cột 2 |
| address | Text | | | X | Địa chỉ cửa hàng |
| phone | Int | | | X | Số điện thoại liên hệ |
| email | Text | | | X | Email liên hệ |
| created_by | BigInt (FK) | | | | Mã admin tạo (FK → users.id) |
| created_at | Timestamp | | | X | Ngày tạo |
| updated_at | Timestamp | | | | Ngày cập nhật |

---

> **Ghi chú ký hiệu:**
> - **K** = Khóa chính (Primary Key)
> - **U** = Unique (duy nhất)
> - **M** = Mandatory (bắt buộc / NOT NULL)
> - **FK** = Foreign Key (khóa ngoại)
