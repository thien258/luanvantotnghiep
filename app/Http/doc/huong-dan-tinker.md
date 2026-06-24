# Hướng dẫn sử dụng Laravel Tinker

Tinker là shell tương tác trực tiếp với app Laravel — dùng để kiểm tra dữ liệu, test query, debug nhanh mà không cần vào trình duyệt.

---

## Cách lấy dữ liệu — Bảng so sánh

### Lấy 1 bản ghi

| Lệnh | Ý nghĩa |
|------|---------|
| `find(1)` | Tìm theo **id = 1** |
| `find([1,2,3])` | Tìm nhiều id cùng lúc, trả về Collection |
| `first()` | Lấy **bản ghi đầu tiên** trong bảng |
| `firstOrFail()` | Lấy đầu tiên, không có → **lỗi 404** |
| `findOrFail(5)` | Tìm id=5, không có → **lỗi 404** |

### Lấy nhiều / tất cả bản ghi

| Lệnh | Ý nghĩa |
|------|---------|
| `all()` | Lấy **tất cả**, mọi cột |
| `all(['id','name'])` | Lấy tất cả nhưng **chỉ một số cột** |
| `get()` | Lấy tất cả — dùng **sau `where`, `orderBy`** |
| `get(['id','name'])` | Lấy tất cả, chỉ một số cột |

> **Khác nhau giữa `all()` và `get()`:**
> - `all()` — không kèm điều kiện, lấy thẳng toàn bảng
> - `get()` — phải dùng sau `where`, `orderBy`, `limit`, ...

```php
// all() — không cần where
App\Models\User::all();

// get() — dùng sau các điều kiện
App\Models\User::where('role', 'admin')->get();
App\Models\User::orderBy('id', 'desc')->limit(10)->get();
```

---

## Khởi động Tinker

```cmd
cd d:\DoanCN\laravel
php artisan tinker
```

Thoát tinker:
```php
exit
```

---

## User

```php
// Đếm tổng số tài khoản
App\Models\User::count();

// Xem danh sách user
App\Models\User::all();

// Xem chọn lọc cột
App\Models\User::select('id', 'name', 'email', 'role')->get();

// Tìm user theo id
App\Models\User::find(1);

// Tìm user theo email
App\Models\User::where('email', 'test@gmail.com')->first();

// Xem các user có role admin
App\Models\User::where('role', 'admin')->get();
```

---

## Product

```php
// Tổng số sản phẩm
App\Models\Product::count();

// Danh sách sản phẩm (id, tên, giá, tồn kho)
App\Models\Product::select('id', 'title', 'price', 'quantity')->get();

// Sản phẩm đang bán (status=1)
App\Models\Product::where('status', 1)->get();

// Sản phẩm hết hàng (quantity=0)
App\Models\Product::where('quantity', 0)->get();

// Tìm sản phẩm theo tên
App\Models\Product::where('title', 'like', '%chanel%')->get();

// Xem sản phẩm kèm thương hiệu và danh mục
App\Models\Product::with('brand', 'category')->find(1);
```

---

## ManuFacturer (Nhà Sản Xuất)

```php
// Danh sách tất cả NSX
App\Models\ManuFacturer::all(['id', 'name', 'phone']);

// Xem NSX có bao nhiêu sản phẩm
App\Models\ManuFacturer::withCount('products')->get();

// Lấy sản phẩm của NSX theo id
$nsx = App\Models\ManuFacturer::with('products')->find(1);
$nsx->products;

// Chỉ xem tên sản phẩm
$nsx->products->pluck('title', 'id')->all();

// In từng sản phẩm dễ đọc
foreach($nsx->products as $p) { echo $p->id . ' | ' . $p->title . PHP_EOL; }

// Tìm NSX theo tên rồi lấy sản phẩm
$nsx = App\Models\ManuFacturer::where('name', 'like', '%tên nsx%')->with('products')->first();
$nsx->name;           // tên NSX tìm được
$nsx->products->count(); // số sản phẩm
```

---

## Order (Đơn hàng)

```php
// Tổng số đơn hàng
App\Models\Order::count();

// Đơn hàng theo trạng thái
// status: 0=chờ thanh toán, 1=đang xử lý, 3=đang giao, 4=hoàn thành, 5=hoàn hàng, 6=hàng hỏng
App\Models\Order::where('status', 1)->count();

// Đơn của 1 user cụ thể
App\Models\Order::where('idUser', 1)->get();

// Xem đơn kèm chi tiết sản phẩm
$order = App\Models\Order::with('details.product')->find(1);
$order->details;

// Tổng doanh thu (đơn hoàn thành)
App\Models\Order::where('status', 4)->sum('total_price');
```

---

## OrderDetail (Chi tiết đơn hàng)

```php
// Chi tiết các sản phẩm trong đơn id=1
App\Models\OrderDetail::where('idOrder', 1)->with('product')->get();

// Xem tên + số lượng + giá
App\Models\OrderDetail::where('idOrder', 1)
    ->get(['name', 'quantity', 'price']);
```

---

## Tips hiển thị output trong Tinker

Tinker hay thu gọn kết quả dài thành `…5`. Để xem đầy đủ:

```php
// Dùng print_r
print_r($collection->all());

// Dùng foreach in từng dòng
foreach($collection as $item) { echo $item->id . ' | ' . $item->title . PHP_EOL; }

// Dùng dump
$collection->each(fn($item) => dump($item->id, $item->title));

// Chuyển sang array rồi xem
$collection->toArray();
```

---

## Lưu ý

- Tên quan hệ trong `with()` phải **khớp đúng tên method** trong Model.
  - ✅ `with('products')` — vì method là `public function products()`
  - ❌ `with('product')` — sẽ báo lỗi `RelationNotFoundException`
- Dùng `->first()` khi chắc chắn chỉ lấy 1 bản ghi, `->get()` khi lấy nhiều.
- Dùng `->firstOrFail()` / `->findOrFail()` nếu muốn báo lỗi khi không tìm thấy.
