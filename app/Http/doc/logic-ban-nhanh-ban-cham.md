# Logic Phân Loại Bán Nhanh / Bán Chậm

## Tổng quan

Hệ thống phân loại sản phẩm thành 3 trạng thái dựa trên **tốc độ bán 30 ngày gần nhất** kết hợp với **tồn kho hiện tại**. Không dùng tỷ lệ nhập/bán vì lý do sau:

> Tỷ lệ nhập/bán bị ảnh hưởng bởi quyết định nhập hàng của admin, không phản ánh đúng nhu cầu thực của khách hàng. Ví dụ: sản phẩm đang bán tốt, admin nhập thêm 200 chai → tỷ lệ tụt xuống dù khách vẫn mua đều đặn.

---

## Các biến số

| Biến | Nguồn | Ý nghĩa |
|---|---|---|
| `sold_30` | `order_details` JOIN `orders` WHERE `status=4` AND `created_at >= 30 ngày trước` | Số lượng bán được trong 30 ngày gần nhất |
| `stock` | `products.quantity` | Tồn kho hiện tại |

---

## Ngưỡng phân loại

| Hằng số | Giá trị | Ý nghĩa |
|---|---|---|
| `FAST_THRESHOLD` | 10 | Bán >= 10 cái/tháng mới gọi là bán nhanh |
| `SLOW_THRESHOLD` | 3 | Bán <= 3 cái/tháng mới gọi là bán chậm |
| `STOCK_MONTHS` | 3 | Kho đủ bán > 3 tháng → ứ đọng |

---

## Điều kiện phân loại

### 🔥 Bán nhanh
```
sold_30 >= FAST_THRESHOLD (>= 10)
VÀ
stock < sold_30  (kho sắp hết so với tốc độ bán)
```
**Ý nghĩa:** Đang bán tốt, kho sắp hết → admin cần nhập thêm.

### 🐢 Bán chậm
```
TRƯỜNG HỢP 1: sold_30 = 0 VÀ stock > 0
  (Không ai mua trong 30 ngày mà kho vẫn còn hàng)

TRƯỜNG HỢP 2: sold_30 <= SLOW_THRESHOLD (<=3)
              VÀ stock > sold_30 * STOCK_MONTHS
  (Bán ít + kho đủ bán hơn 3 tháng → đang ứ đọng)
```
**Ý nghĩa:** Hàng không có người mua hoặc bán rất ít trong khi kho vẫn còn nhiều → cần upsale, đưa vào Festival, hoặc giảm giá.

### 😐 Bình thường
```
Tất cả trường hợp còn lại
```

---

## Bảng ví dụ số

| Tình huống | sold_30 | stock | Kết quả | Lý do |
|---|---|---|---|---|
| Bán tốt, kho sắp hết | 80 | 30 | 🔥 Bán nhanh | 80>=10 ✅ và 30<80 ✅ |
| Bán tốt, admin nhập thêm | 80 | 300 | 😐 Bình thường | 80>=10 ✅ nhưng 300>=80 ❌ |
| Bán vừa, kho vừa | 15 | 20 | 😐 Bình thường | Không thỏa nhanh hay chậm |
| Bán ít, kho ứ | 2 | 150 | 🐢 Bán chậm | 2<=3 ✅ và 150>6 ✅ |
| Không ai mua, kho còn | 0 | 50 | 🐢 Bán chậm | sold_30=0, stock>0 ✅ |
| Bán ít, kho cũng ít | 2 | 3 | 😐 Bình thường | 3 <= 2*3=6 ❌ không ứ |
| Hết hàng | 5 | 0 | — | Bỏ qua (stock=0) |

---

## Tại sao nhập thêm hàng không làm sai kết quả?

### Trường hợp nhập đúng lúc (khi bán nhanh):
```
Trước: sold_30=80, stock=30 → 🔥 Bán nhanh → Admin nhập thêm 200 chai
Sau:   sold_30=80, stock=230

Bán nhanh?: 80>=10 ✅ VÀ 230<80? ❌
→ 😐 Bình thường (đúng — kho đã đủ, không cần cảnh báo nữa)
```

### Trường hợp nhập quá nhiều:
```
Sản phẩm bán 2/tháng, admin nhập thêm 500 chai
sold_30=2, stock=500

Bán chậm?: 2<=3 ✅ VÀ 500>2*3=6 ✅
→ 🐢 Bán chậm (đúng — admin nhập quá tay, kho đang ứ)
```

---

## Luồng nghiệp vụ khép kín

```
Kho ứ đọng
    ↓
Hệ thống hiện cảnh báo 🐢 Bán chậm
    ↓
Admin thấy → Đưa sản phẩm vào Festival / Giảm giá
    ↓
Khách mua nhiều hơn → sold_30 tăng
    ↓
stock / sold_30 giảm xuống
    ↓
Tự động thoát khỏi cảnh báo Bán chậm ✅
```

---

## Vị trí trong code

| File | Vị trí | Mục đích |
|---|---|---|
| `AdminController.php` | Phần `── 4. SẢN PHẨM BÁN CHẬM` | Dashboard admin — hiện top 5 |
| `WarehouseController.php` | Hàm `getWarehouseData()` | Tab Cảnh báo Sale trong trang Kho |
| `ProductController.php` | Hàm `index()` | Badge 🐢 trong danh sách sản phẩm |

---

## Cách thay đổi ngưỡng

Tìm các hằng số sau trong từng file và điều chỉnh:

```php
$FAST_THRESHOLD = 10;  // Đổi nếu shop bán ít hơn hoặc nhiều hơn
$SLOW_THRESHOLD = 3;   // Đổi tùy quy mô shop
$STOCK_MONTHS   = 3;   // Đổi nếu muốn cảnh báo sớm/muộn hơn
```

Ví dụ shop nhỏ bán 5-10 chai/tháng là đã tốt thì có thể đặt:
```php
$FAST_THRESHOLD = 5;
$SLOW_THRESHOLD = 1;
```
