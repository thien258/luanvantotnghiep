# Luồng: Đăng yêu cầu nhập hàng công khai cho NSX chào giá

## MÔ TẢ

Admin chọn SP hết/sắp hết hàng → đăng danh sách lên 1 trang công khai
→ Tất cả NSX (khi đăng nhập vào trang supplier) đều thấy danh sách này
→ NSX thích SP nào thì chào giá vào → Admin xem + chọn

---

## LUỒNG CHI TIẾT

```
[Trang /admin/product]
         │
         │ Admin bấm nút "Đăng yêu cầu nhập hàng"
         ▼
┌─────────────────────────────────────────────────────────┐
│  BƯỚC 1: Modal hiện danh sách SP hết/sắp hết (qty < 5) │
│                                                         │
│  ┌────┬──────────────┬──────────┬──────────────────┐   │
│  │ ✓  │ Tên SP       │ Tồn kho  │ Số lượng cần nhập│   │
│  ├────┼──────────────┼──────────┼──────────────────┤   │
│  │ [✓]│ Chanel No5   │ Hết hàng │ [  10  ]         │   │
│  │ [✓]│ Bleu Chanel  │ 2        │ [  10  ]         │   │
│  └────┴──────────────┴──────────┴──────────────────┘   │
│                                                         │
│  Admin tick + điền số lượng → bấm "Đăng yêu cầu"      │
└─────────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────┐
│  BƯỚC 2: Tạo ProcurementRequest (Yêu cầu thu mua)      │
│                                                         │
│  Cần bảng mới: procurement_requests                     │
│    id | request_code | status | note | created_by       │
│    status: open / closed                                 │
│                                                         │
│  Cần bảng mới: procurement_request_items                │
│    id | request_id | product_id | product_name           │
│       | qty_needed | note                                │
│                                                         │
│  (KHÔNG dùng supplier_offers vì luồng đó NSX chủ động  │
│   còn luồng này ADMIN chủ động đăng trước)             │
└─────────────────────────────────────────────────────────┘
         │
         │ status = open → hiển thị công khai
         ▼
┌─────────────────────────────────────────────────────────┐
│  BƯỚC 3: Trang công khai cho NSX xem                   │
│  Route: GET /procurement (public hoặc cần đăng nhập)    │
│                                                         │
│  NSX vào xem danh sách yêu cầu đang mở:                │
│  ┌──────────────┬──────────┬────────────┬──────────┐   │
│  │ Tên SP       │ Cần nhập │ Hạn chót   │ Thao tác │   │
│  ├──────────────┼──────────┼────────────┼──────────┤   │
│  │ Chanel No5   │ 10 chai  │ 30/06/2026 │ Chào giá │   │
│  │ Bleu Chanel  │ 10 chai  │ 30/06/2026 │ Chào giá │   │
│  └──────────────┴──────────┴────────────┴──────────┘   │
└─────────────────────────────────────────────────────────┘
         │
         │ NSX bấm "Chào giá"
         ▼
┌─────────────────────────────────────────────────────────┐
│  BƯỚC 4: NSX nhập giá chào                             │
│                                                         │
│  Tạo supplier_offers liên kết với procurement_request   │
│  (thêm cột request_id vào supplier_offers)              │
│                                                         │
│  NSX điền: giá chào từng SP + ghi chú → Submit         │
│  → supplier_offers.status = submitted                   │
└─────────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────┐
│  BƯỚC 5: Admin xem tất cả báo giá nhận được             │
│  /admin/supplier-offers → lọc theo request_id           │
│                                                         │
│  So sánh giá từ nhiều NSX → chọn NSX phù hợp           │
│  → Tạo PurchaseOrder → luồng cũ tiếp tục               │
└─────────────────────────────────────────────────────────┘
```

---

## SO SÁNH 2 LUỒNG HIỆN CÓ VÀ LUỒNG MỚI

```
Luồng 1 (đã có): NSX chủ động upload file báo giá
  NSX upload → Admin xem → Admin đặt hàng

Luồng 2 (đã có): Admin tạo PO trực tiếp từ báo giá NSX
  Giống Luồng 1 nhưng Admin tick + điền qty

Luồng 3 (MỚI - luồng này): Admin đăng yêu cầu công khai
  Admin đăng SP cần nhập → NSX chào giá → Admin so sánh → Đặt hàng
```

---

## DB CẦN TẠO MỚI

### Bảng `procurement_requests` — Yêu cầu thu mua
```
id
request_code    string unique (PRQ-20260617-001)
status          enum: open / closed
note            text nullable
deadline        date nullable   ← hạn NSX chào giá
created_by      FK → users
created_at / updated_at
```

### Bảng `procurement_request_items` — SP trong yêu cầu
```
id
request_id      FK → procurement_requests
product_id      FK → products (nullable)
product_name    string
qty_needed      int
note            text nullable
created_at / updated_at
```

### Sửa bảng `supplier_offers` — Thêm cột liên kết
```
Thêm: request_id FK → procurement_requests (nullable)
Ý nghĩa: báo giá này trả lời cho yêu cầu nào
```

---

## VIEWS CẦN TẠO

```
resources/views/admin/procurement/
    index.blade.php     ← danh sách yêu cầu đang mở
    show.blade.php      ← chi tiết yêu cầu + danh sách báo giá nhận được

resources/views/procurement/         ← phía NSX (public)
    index.blade.php     ← danh sách yêu cầu đang mở
    show.blade.php      ← chi tiết + form chào giá
```

---

## ROUTES CẦN THÊM

```php
// Admin quản lý yêu cầu
Route::resource('procurement', ProcurementController::class)->only(['index','store','show']);
Route::post('procurement/{id}/close', [ProcurementController::class, 'close'])
     ->name('procurement.close');

// Tạo nhanh từ modal trang sản phẩm
Route::post('product/order-request', [ProductController::class, 'createOrderRequest'])
     ->name('product.createOrderRequest');

// Phía NSX xem yêu cầu (public hoặc auth)
Route::get('/procurement', [ProcurementPublicController::class, 'index']);
Route::get('/procurement/{id}', [ProcurementPublicController::class, 'show']);
Route::post('/procurement/{id}/offer', [ProcurementPublicController::class, 'submitOffer']);
```

---

## THỨ TỰ LÀM

```
1. Migration: create procurement_requests
2. Migration: create procurement_request_items
3. Migration: add request_id to supplier_offers
4. php artisan migrate

5. Model: ProcurementRequest + ProcurementRequestItem
6. Cập nhật SupplierOffer model: thêm belongsTo ProcurementRequest

7. ProductController: thêm createOrderRequest()
   → tạo ProcurementRequest + items → redirect sang /admin/procurement/{id}

8. ProcurementController (admin): index, show, close
9. Views admin: procurement/index + show

10. Views public: /procurement (NSX xem + chào giá)
    (Hiện tại chưa có role → tạm public hoặc auth)

11. Thêm routes
12. Thêm link vào sidebar admin
```

---

## GHI CHÚ KHI CÓ ROLE SAU NÀY

Khi có role `supplier`:
- Route `/procurement` → middleware `role:supplier`
- NSX chỉ thấy yêu cầu đang `open`
- NSX chỉ thấy báo giá của chính mình
- Admin thấy tất cả báo giá từ mọi NSX để so sánh
