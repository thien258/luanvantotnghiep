# CHANGELOG — Aura & Essence Laravel Project
> Ghi chép chi tiết từng thay đổi đã thực hiện, file nào, dòng nào, lý do gì.

---

## [2026-07-10] Thêm Role Root + Activity Log cho Director

### Tổng quan
Thêm role `root` có toàn quyền (xem hết như admin + doanh thu như director) và hệ thống ghi log tự động để director theo dõi root làm gì.

### File tạo mới
| File | Mô tả |
|------|-------|
| `database/migrations/2026_07_10_093005_create_root_activity_logs_table.php` | Tạo bảng `root_activity_logs` (id, user_id, user_name, user_email, action, created_at) |
| `app/Models/RootActivityLog.php` | Model cho bảng trên |
| `app/Http/Middleware/LogRootActivity.php` | Middleware tự động ghi log mọi request của root trong /admin, map URL → mô tả tiếng Việt |
| `app/Http/Controllers/admin/ActivityLogController.php` | Controller cho director xem log, có filter theo ngày và tên/email |
| `resources/views/admin/activity-log/index.blade.php` | View bảng log với badge màu theo loại hành động, phân trang 50 dòng |

### File sửa
| File | Thay đổi |
|------|----------|
| `bootstrap/app.php` | Đăng ký middleware alias `log-root` → `LogRootActivity` |
| `routes/web.php` | Thêm `root` vào middleware group `/admin`, thêm middleware `log-root`, thêm route `GET /admin/activity-log` |
| `app/Http/Controllers/admin/AdminController.php` | Thêm nhánh `root` → `rootDashboard()` (dashboard đầy đủ có doanh thu) |
| `app/Http/Controllers/admin/UserController.php` | Thêm `'root'` vào `$validRoles` để admin có thể gán role root |
| `resources/views/layout/admin.blade.php` | Thêm root vào tất cả `@if` check role trong menu sidebar, thêm link "Lịch sử hoạt động Root" cho director và root |

### Cách dùng
1. Chạy `php artisan migrate` để tạo bảng `root_activity_logs`
2. Vào `/admin/user` → đổi role user bất kỳ thành `root`
3. Đăng nhập bằng tài khoản root → thao tác bình thường trong admin
4. Đăng nhập bằng director → vào menu "Lịch sử hoạt động Root" để xem log

### Lưu ý
- Log chỉ ghi khi role = `root`, không ghi các role khác
- GET không map được trong actionMap → bỏ qua (không ghi)
- POST/PUT/DELETE không map được → ghi raw URL làm fallback

---


## [2026-06-11] Sửa Validation & Error Display

---

### 1. `app/Http/Controllers/OrderController.php` — method `placeOrder()`

**Vấn đề:** validation không kiểm tra giá trị hợp lệ của `payment_method`, không giới hạn độ dài các trường.

**Đã sửa — Thay đổi rules từ:**
```php
// TRƯỚC
$request->validate([
    'fullname'       => 'required|string',
    'phone'          => 'required|string',
    'address'        => 'required|string',
    'payment_method' => 'required|string',
]);
```

**Thành:**
```php
// SAU
$request->validate([
    'fullname'       => 'required|string|max:255',
    'phone'          => 'required|string|max:20|regex:/^[0-9]{9,11}$/',
    'address'        => 'required|string|max:500',
    'payment_method' => 'required|in:COD,BANK TRANSFER',   // ← THÊM: chặn giá trị lạ
    'note'           => 'nullable|string|max:1000',          // ← THÊM: field mới
]);
```

**Lý do:** `payment_method=FREE` trước đây được chấp nhận → đơn không vào PayOS cũng không vào COD, bị mất đơn.

---

### 2. `app/Http/Controllers/admin/OrderAdminController.php` — method `updateStatus()`

**Vấn đề:** không có validation nào → gửi `status=999` được chấp nhận, đơn hàng rơi vào trạng thái không xác định.

**Đã thêm vào đầu method:**
```php
// THÊM MỚI — trước không có dòng nào
$request->validate([
    'status'      => 'nullable|integer|in:1,3,4,5,6',  // chỉ nhận status hợp lệ
    'action_type' => 'nullable|in:export_warehouse',    // chỉ nhận action đã định nghĩa
]);
```

---

### 3. `app/Http/Controllers/CartController.php` — method `store()`

**Vấn đề:** không validate `product_id` tồn tại trong DB, `quantity` có thể là 0 hoặc âm.

**Đã thêm vào đầu method (sau `Auth::check()`):**
```php
// THÊM MỚI
$request->validate([
    'product_id' => 'required|integer|exists:products,id',
    'quantity'   => 'nullable|integer|min:1|max:100',
]);
```

---

### 4. `app/Http/Controllers/CommentController.php` — method `store()`

**Vấn đề:** không có validate nào → `name`, `chat` rỗng vào DB; `idProduct` không tồn tại gây lỗi FK.

**Đã thêm toàn bộ (trước không có gì):**
```php
// THÊM MỚI — trước: không có validate, chỉ có Comment::create()
$request->validate([
    'idProduct' => 'required|integer|exists:products,id',
    'name'      => 'required|string|max:100',
    'chat'      => 'required|string|max:1000',
]);
```

---

### 5. `app/Http/Controllers/ContactController.php` — method `store()`

**Vấn đề:** không có validate nào → email sai format, nội dung spam dài vô hạn.

**Đã thêm toàn bộ:**
```php
// THÊM MỚI
$request->validate([
    'name'    => 'required|string|max:255',
    'email'   => 'required|email|max:255',
    'message' => 'required|string|max:2000',
]);
```

---

### 6. `app/Http/Controllers/admin/ProductController.php` — method `store()` và `update()`

**Vấn đề:** không validate gì → `idCategory=9999` (không tồn tại) được lưu, gây lỗi khi query `$product->category`.

**Đã thêm vào đầu `store()` và `update()` (cùng rules):**
```php
// THÊM MỚI vào store() và update()
$request->validate([
    'title'           => 'required|string|max:255',
    'price'           => 'required|numeric|min:0',
    'quantity'        => 'required|integer|min:0',
    'status'          => 'required|in:0,1',
    'volume'          => 'nullable|string|max:50',
    'image'           => 'nullable|string|max:500',
    'decription'      => 'nullable|string|max:5000',
    'idConcentration' => 'required|integer|exists:concentrations,id',
    'idCategory'      => 'required|integer|exists:categories,id',
    'idBrand'         => 'required|integer|exists:brands,id',
]);
```

---

### 7. `app/Http/Controllers/admin/FestivalController.php` — method `store()` và `update()`

**Vấn đề:** `discount=150` → giá âm; `end_date < start_date` → festival không bao giờ chạy.

**Đã sửa/thêm vào `store()` và `update()`:**
```php
// SỬA/THÊM — thêm min/max cho discount, thêm after_or_equal cho end_date
$request->validate([
    'name'       => 'required|string|max:255',
    'discount'   => 'required|numeric|min:0|max:100',  // ← THÊM: giới hạn 0-100%
    'status'     => 'required|in:0,1',
    'start_date' => 'required|date',
    'end_date'   => 'required|date|after_or_equal:start_date',  // ← THÊM: ngày kết thúc phải >= bắt đầu
]);
```

---

### 8. Error Display — 3 View

**Vấn đề chung:** validation backend chặn được nhưng người dùng không thấy lỗi vì không có `@if($errors->any())` trong view.

#### `resources/views/admin/product/add.blade.php`
**Thêm block sau `@section('body')`:**
```blade
{{-- THÊM MỚI: Hiển thị lỗi validation --}}
@if($errors->any())
<div class="alert alert-danger rounded-0 mb-3">
    <ul class="mb-0 ps-3 small">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
```

#### `resources/views/admin/product/edit.blade.php`
**Thêm cùng block ở vị trí tương tự.**

#### `resources/views/layout/single_product.blade.php` (form bình luận)
**Thêm block ngay trên form comment:**
```blade
{{-- THÊM MỚI: Hiển thị lỗi khi submit comment thất bại --}}
@if($errors->any())
<div class="alert alert-danger rounded-0 mb-3">
    ...
</div>
@endif
```

---

## [2026-06-11] Tự Động Hóa Model Product

### `app/Models/Product.php`

**Thêm `booted()` event:**
```php
// THÊM MỚI — tự động tắt status khi hết hàng
protected static function booted(): void
{
    static::saving(function (Product $product) {
        if ($product->isDirty('quantity') && $product->quantity <= 0) {
            $product->quantity = 0;
            $product->status   = 0;  // Hết hàng → tắt tự động
        }
    });
}
```

**Lý do:** khi admin xuất kho và số lượng về 0, sản phẩm tự ẩn khỏi trang user, không cần thao tác thủ công.

---

## [2026-06-10] Tính Năng Nhập Kho Qua File (Upload → Duyệt)

### Migration mới
- `2026_06_10_151135_create_warehouse_imports_table.php`
  - Bảng `warehouse_imports`: lưu file upload chờ duyệt
  - Columns: `file_path`, `original_name`, `supplier`, `note`, `uploaded_by`, `status` (`pending/approved/rejected`), `reviewed_by`, `reviewed_at`

### Model mới
- `app/Models/WarehouseImport.php`
  - Quan hệ: `uploader()` → `User`, `reviewer()` → `User`

### Routes mới (trong prefix `/admin`)
```php
Route::get('warehouse/imports', ...)         ->name('warehouse.imports');
Route::post('warehouse/imports/upload', ...) ->name('warehouse.imports.upload');
Route::get('warehouse/imports/{import}', ...) ->name('warehouse.imports.show');
Route::post('warehouse/imports/{import}/approve', ...) ->name('warehouse.imports.approve');
Route::post('warehouse/imports/{import}/reject', ...)  ->name('warehouse.imports.reject');
```

### Views mới
- `resources/views/admin/product/import-list.blade.php`
  - Danh sách file upload + form upload mới
  - Badge trạng thái: Chờ duyệt / Đã duyệt / Từ chối
- `resources/views/admin/product/import-show.blade.php`
  - Preview bảng sản phẩm từ file (có thể sửa tên, SL, giá trước khi duyệt)
  - Nút "Duyệt & Nhập kho" / "Từ chối"

### Methods mới trong `ProductController`
- `importList()` — hiển thị danh sách
- `importUpload()` — lưu file vào `storage/app/private/warehouse_imports/`
- `importShow()` — đọc CSV hoặc Excel, trả về preview
- `importApprove()` — tạo `WarehouseReceipt` + `WarehouseStockLog`, cập nhật sản phẩm
- `importReject()` — đổi status → rejected
- `mapRowToPreview()` — private helper, map dòng CSV/Excel theo thứ tự cột chuẩn

### Format file nhập kho
| Cột | A | B | C | D | E | F | G | H | I |
|-----|---|---|---|---|---|---|---|---|---|
| **Field** | title | image | decription | price | quantity | volume | category | brand | concentration |

---

## [2026-06-09] Tính Năng Kho & Cảnh Báo Sale

### Migration mới
- `2026_06_09_215347_create_warehouse_receipts_table.php` — bảng `warehouse_receipts`
- `2026_06_09_221023_create_warehouse_stock_logs_table.php` — bảng `warehouse_stock_logs`

### Models mới
- `app/Models/WarehouseReceipt.php`
- `app/Models/WarehouseStockLog.php`

### Trang Kho (`/admin/product/warehouse`)
- **Tab 1:** Cảnh báo Sale — sản phẩm có `sale_rate = 0%` (chưa bán được cái nào)
- **Tab 2:** Biến động kho — lịch sử stock logs
- **Tab 3:** Lịch sử nhập kho — danh sách phiếu nhập

---

## [2026-06-03 → 2026-06-08] Hệ Thống Đơn Hàng & Thanh Toán PayOS

### Luồng Thanh Toán
```
Giỏ hàng → Checkout → placeOrder()
    ├── COD     → status=1 → Admin thấy ngay
    └── BANK    → PayOS API → status=0 (pending)
                   ├── Thanh toán xong → Webhook → status=1
                   └── Hủy → payosCancel() → Xóa đơn, hoàn giỏ hàng
```

### PayOS Integration (`OrderController`)
- `createPayOSLink($order)` — gọi API PayOS, trả về `checkoutUrl`
- Signature: `hash_hmac('sha256', "amount=...&cancelUrl=...&...", $checksumKey)`
- `payosWebhook()` — nhận callback từ PayOS, đổi `status 0→1`
- `payosCancel()` — xóa đơn + hoàn giỏ hàng khi khách hủy

### Luồng Giao Hàng (Admin → Khách)
```
Admin xuất kho → status=3 (Đang giao)
    → In QR (order-show.js + api.qrserver.com)
    → Khách quét QR → confirm-delivery.blade.php
    → Bấm "Đã nhận hàng" → status=4 (Hoàn tất)
```

### Luồng Hoàn Hàng
```
Admin bấm "Hoàn hàng" (status=3) → return.blade.php
    ├── Bank Transfer: chọn "Bom đơn" hoặc "Hoàn trả"
    │   └── "Hoàn trả" → hiện nút hoàn tiền
    └── Xử lý → status=6 (Hàng hỏng, chờ trả NCC)
```

### Bảng Status Đơn Hàng
| Status | Ý nghĩa | Admin | User |
|--------|---------|-------|------|
| `0` | Chờ TT PayOS | ❌ | ❌ |
| `1` | Đang lấy hàng | ✅ | ✅ |
| `3` | Đang giao | ✅ | ✅ |
| `4` | Hoàn tất | ✅ | ✅ |
| `5` | Hoàn hàng NV | ✅ | ✅ (`Hoàn hàng`) |
| `6` | Hàng hỏng/NCC | ✅ (Damaged) | ✅ (`Hoàn hàng`) |

---

## [Trước 2026-06-03] Tính Năng Cơ Bản

| Tính năng | Controller | View |
|---|---|---|
| Trang chủ, tìm kiếm | `HomeController` | `layout/home.blade.php` |
| Chi tiết sản phẩm + comment | `HomeController` | `single_product.blade.php` |
| Lọc theo Category/Brand/Festival | `HomeController` | nhiều view |
| Giỏ hàng (thêm/sửa/xóa) | `CartController` | `carts.blade.php` |
| Sổ địa chỉ (AJAX) | `UserAddressController` | modal trong `order/index.blade.php` |
| Lịch sử đơn hàng | `OrderController` | `order/history.blade.php` |
| Yêu cầu hoàn trả (user) | `OrderController::customerReturn()` | modal trong `history.blade.php` |
| CRUD sản phẩm admin | `ProductController` | `admin/product/` |
| CRUD danh mục/thương hiệu | `CategoryController`, `BrandController` | `admin/category/`, `admin/brand/` |
| Festival + giảm giá | `FestivalController` | `admin/Festival/` |
| Quản lý user admin | `UserController` | `admin/user/` |
| Liên hệ | `ContactController` | `contact.blade.php` |
