# Hướng làm: NSX tự tạo báo giá → Admin chọn số lượng → Đặt hàng

---

## BỐI CẢNH HIỆN TẠI

- Chưa phân role supplier (sẽ bổ sung sau)
- Tạm thời: **mọi user đã đăng nhập** đều có thể truy cập trang NSX
- Khi có role → chỉ cần thêm middleware `role = 'supplier'` vào route là xong
- NSX phải được **liên kết với 1 tài khoản user** trong hệ thống

---

## THAY ĐỔI CẦN THÊM VÀO DB HIỆN TẠI

### Thêm cột `user_id` vào bảng `manufacturers` (đã có)
```
php artisan make:migration add_user_id_to_manufacturers_table
```
Thêm cột:
- `user_id` FK → users (nullable, để sau gán được)

Ý nghĩa: biết tài khoản nào là của NSX nào.

---

## CÁC BẢNG CẦN TẠO MỚI

### 1. `supplier_offers` — Phiếu báo giá NSX tạo
```
id
manufacturer_id   FK → manufacturers
offer_code        string unique (tự sinh: OFR-20260617-001)
note              text nullable
status            enum: draft / submitted / accepted / rejected
submitted_at      timestamp nullable
created_at / updated_at
```

### 2. `supplier_offer_items` — Dòng sản phẩm trong báo giá
```
id
offer_id          FK → supplier_offers
product_id        FK → products (nullable — sp có thể chưa có trong hệ thống)
product_name      string  ← NSX tự nhập tên (dự phòng nếu product_id null)
unit_price        decimal(15,2)  ← giá NSX chào
note              text nullable
created_at / updated_at
```
> Không có `quantity` ở đây — vì NSX chỉ chào giá,
> ADMIN mới là người quyết định mua bao nhiêu cái.

### 3. `purchase_orders` — Đơn đặt hàng admin tạo
```
id
offer_id          FK → supplier_offers (nullable)
manufacturer_id   FK → manufacturers
order_code        string unique (PO-20260617-001)
total_amount      decimal(15,2)
status            enum: pending / confirmed / delivering / received / cancelled
expected_date     date nullable
note              text nullable
created_by        FK → users
created_at / updated_at
```

### 4. `purchase_order_items` — Dòng sản phẩm trong đơn đặt hàng
```
id
purchase_order_id FK → purchase_orders
product_id        FK → products
product_name      string
quantity          int   ← ADMIN điền số lượng muốn mua
unit_price        decimal(15,2)
created_at / updated_at
```

---

## LUỒNG ĐI CHI TIẾT

```
[NSX đăng nhập vào web]
         │
         ▼
┌─────────────────────────────────────────────────────────┐
│  BƯỚC 1: NSX tạo báo giá                                │
│  Route: GET  /supplier/offers/create                    │
│         POST /supplier/offers                           │
│                                                         │
│  NSX thấy danh sách sản phẩm của mình                   │
│  (từ manufacturers_product — danh bạ)                   │
│                                                         │
│  NSX điền:                                              │
│  ┌──────────────────┬──────────────┬─────────────────┐  │
│  │ Tên sản phẩm     │ Giá chào (₫) │ Ghi chú         │  │
│  ├──────────────────┼──────────────┼─────────────────┤  │
│  │ Chanel No5 100ml │ 2.000.000    │ Hàng mới về     │  │
│  │ Bleu de Chanel   │ 3.500.000    │                 │  │
│  └──────────────────┴──────────────┴─────────────────┘  │
│  (NSX KHÔNG điền số lượng — chỉ chào giá thôi)          │
│                                                         │
│  Bấm "Gửi báo giá" → status = 'submitted'              │
└─────────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────┐
│  BƯỚC 2: Admin nhận báo giá                             │
│  Route: GET /admin/supplier-offers                      │
│                                                         │
│  Admin thấy danh sách báo giá đang chờ (submitted)      │
│  Bấm vào xem chi tiết từng báo giá                      │
└─────────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────┐
│  BƯỚC 3: Admin xem chi tiết + điền số lượng             │
│  Route: GET /admin/supplier-offers/{id}                 │
│                                                         │
│  Admin thấy bảng sản phẩm NSX đã chào:                  │
│  ┌────┬──────────────┬──────────────┬───────────────┐   │
│  │ ✓  │ Tên SP       │ Giá chào     │ Số lượng mua  │   │
│  ├────┼──────────────┼──────────────┼───────────────┤   │
│  │ [✓]│ Chanel No5   │ 2.000.000₫   │ [  100  ]     │   │
│  │ [✓]│ Bleu Chanel  │ 3.500.000₫   │ [   50  ]     │   │
│  │ [ ]│ Chance       │ 1.800.000₫   │ (bỏ qua)      │   │
│  └────┴──────────────┴──────────────┴───────────────┘   │
│                                                         │
│  Admin tick chọn + điền số lượng muốn mua               │
│  Bấm "Đặt hàng"                                         │
└─────────────────────────────────────────────────────────┘
         │
         │ POST /admin/purchase-orders (store)
         ▼
┌─────────────────────────────────────────────────────────┐
│  BƯỚC 4: Tạo Purchase Order                             │
│                                                         │
│  - Tạo purchase_orders (sinh mã PO tự động)             │
│  - Tạo purchase_order_items (chỉ sp được tick + qty)    │
│  - supplier_offers.status = 'accepted'                  │
│  - Đồng thời: syncWithoutDetaching() manufacturers_     │
│    product nếu có sp mới NSX chưa từng cung cấp         │
└─────────────────────────────────────────────────────────┘
         │
         │ status: pending → confirmed → delivering
         ▼
┌─────────────────────────────────────────────────────────┐
│  BƯỚC 5: Hàng về → Admin bấm "Đã nhận hàng"            │
│  Route: POST /admin/purchase-orders/{id}/receive        │
│                                                         │
│  1. purchase_orders.status = 'received'                 │
│  2. Tạo WarehouseReceipt                                │
│  3. Mỗi item trong PO:                                  │
│     - products.quantity += item.quantity                │
│     - Tạo WarehouseStockLog (type = 'import')           │
└─────────────────────────────────────────────────────────┘
         │
         ▼
  [NSX xem lịch sử báo giá + trạng thái đơn]
  Route: GET /supplier/offers (index — chỉ thấy của mình)
```

---

## HAI BẢNG BỔ TRỢ NHAU

```
manufacturers_product              supplier_offer_items
(Danh bạ — NSX hay bán gì)         (Báo giá lần này — giá bao nhiêu)
──────────────────────────         ──────────────────────────────────
manufacturer_id                    offer_id
product_id                         product_id (nullable)
                                   product_name
                                   unit_price
                                   note

→ Dùng để GỢI Ý khi NSX tạo        → Lưu giá chào từng lần cụ thể
  báo giá: "bạn hay bán gì?"
```

---

## CONTROLLERS CẦN TẠO

### SupplierController (phía NSX — route /supplier/...)
```
index()   → danh sách báo giá của NSX đang đăng nhập
create()  → form tạo báo giá (load sp từ manufacturers_product)
store()   → lưu offer + items, status = 'submitted'
show()    → xem chi tiết + trạng thái đơn đặt hàng
```

### SupplierOfferController (phía Admin — route /admin/supplier-offers/...)
```
index()  → danh sách tất cả báo giá (lọc theo status)
show()   → xem chi tiết + form điền số lượng + checkbox
```

### PurchaseOrderController (phía Admin — route /admin/purchase-orders/...)
```
store()         → tạo PO từ items được tick + qty admin điền
index()         → danh sách đơn đặt hàng
show()          → chi tiết đơn
updateStatus()  → cập nhật trạng thái
receive()       → nhận hàng → cộng tồn kho
```

---

## ROUTES CẦN THÊM

```php
// ── PHÍA NSX (chưa có role, dùng auth tạm) ──────────────
Route::middleware('auth')->prefix('supplier')->name('supplier.')->group(function () {
    Route::resource('offers', SupplierController::class);
    // Sau này thêm: ->middleware('role:supplier')
});

// ── PHÍA ADMIN ───────────────────────────────────────────
// (trong group prefix('admin'))
Route::resource('supplier-offers', SupplierOfferController::class)->only(['index','show']);
Route::resource('purchase-orders', PurchaseOrderController::class);
Route::post('purchase-orders/{id}/receive', [PurchaseOrderController::class, 'receive'])
     ->name('purchase-orders.receive');
```

---

## VIEWS CẦN TẠO

```
resources/views/supplier/
├── offers/
│   ├── index.blade.php    ← NSX xem danh sách báo giá + trạng thái
│   ├── create.blade.php   ← NSX tạo báo giá (bảng sp + giá)
│   └── show.blade.php     ← NSX xem chi tiết + trạng thái PO

resources/views/admin/
├── supplier-offer/
│   ├── index.blade.php    ← Admin xem danh sách báo giá chờ duyệt
│   └── show.blade.php     ← Admin xem + tick + điền qty + đặt hàng
├── purchase-order/
│   ├── index.blade.php    ← Danh sách đơn đặt hàng
│   └── show.blade.php     ← Chi tiết + nút cập nhật trạng thái
```

---

## THỨ TỰ LÀM

```
1. Migration: add user_id to manufacturers
2. Migration: create supplier_offers
3. Migration: create supplier_offer_items
4. Migration: create purchase_orders
5. Migration: create purchase_order_items
6. php artisan migrate

7. Cập nhật Model ManuFacturer: thêm belongsTo(User)
   Cập nhật Model User: thêm hasOne(ManuFacturer)
8. Tạo Model SupplierOffer + SupplierOfferItem
9. Tạo Model PurchaseOrder + PurchaseOrderItem

10. Tạo SupplierController (NSX tạo báo giá)
11. Views: supplier/offers/create + index + show

12. Tạo SupplierOfferController (Admin xem báo giá)
13. Views: admin/supplier-offer/index + show (có checkbox + qty input)

14. Tạo PurchaseOrderController
15. Views: admin/purchase-order/index + show

16. Thêm routes vào web.php
17. Thêm link vào sidebar admin + thêm link vào nav NSX
```

---

## GHI CHÚ KHI BỔ SUNG ROLE SAU NÀY

Khi thêm role `supplier` vào bảng `users`:
1. Thêm `->middleware('role:supplier')` vào group route `/supplier/...`
2. Thêm `->middleware('role:admin')` vào group route `/admin/supplier-offers/...`
3. Middleware kiểm tra `Auth::user()->role === 'supplier'`
4. Liên kết: khi NSX đăng ký → admin gán `manufacturers.user_id = user.id`

Không cần sửa logic controller vì đã dùng
`Auth::user()->manufacturer` để lấy đúng NSX đang đăng nhập.
