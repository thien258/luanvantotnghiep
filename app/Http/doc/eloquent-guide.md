# 📚 HƯỚNG DẪN LARAVEL ELOQUENT

> Tài liệu chi tiết về Query Methods và Relationships trong Laravel Eloquent

---

## 📑 MỤC LỤC

1. [Query Methods (Controller)](#query-methods)
2. [Relationships (Model)](#relationships)
3. [Ví dụ thực tế](#vi-du-thuc-te)
4. [Best Practices](#best-practices)

---

## 🔍 QUERY METHODS (Controller) {#query-methods}

### 1. `find($id)` - Tìm theo ID

**Cú pháp:**
```php
$model = Model::find($id);
```

**SQL tương đương:**
```sql
SELECT * FROM table WHERE id = ? LIMIT 1
```

**Kết quả:**
- ✅ Tìm thấy → Trả về **1 object**
- ❌ Không tìm thấy → Trả về **`null`**

**Ví dụ:**
```php
$product = Product::find(5);

if ($product) {
    echo $product->title;  // "iPhone 15"
} else {
    echo "Không tìm thấy sản phẩm";
}
```

**Khi nào dùng:**
- ✅ Khi bạn cần xử lý logic nếu không tìm thấy
- ✅ Khi có thể không tồn tại bản ghi

---

### 2. `findOrFail($id)` - Tìm hoặc báo lỗi 404

**Cú pháp:**
```php
$model = Model::findOrFail($id);
```

**SQL tương đương:**
```sql
SELECT * FROM table WHERE id = ? LIMIT 1
```

**Kết quả:**
- ✅ Tìm thấy → Trả về **1 object**
- ❌ Không tìm thấy → **Throw ModelNotFoundException (404)**

**Ví dụ:**
```php
// Trong Controller
public function show($id)
{
    $product = Product::findOrFail($id);
    // Nếu không tìm thấy → tự động hiển thị trang 404
    // Không cần check null
    
    return view('product.show', compact('product'));
}
```

**Khi nào dùng:**
- ✅ Trang chi tiết sản phẩm
- ✅ Trang xem đơn hàng
- ✅ Bất kỳ trang nào PHẢI có data

---

### 3. `first()` - Lấy bản ghi đầu tiên

**Cú pháp:**
```php
$model = Model::where('column', 'value')->first();
```

**SQL tương đương:**
```sql
SELECT * FROM table WHERE column = 'value' LIMIT 1
```

**Kết quả:**
- ✅ Có data → Trả về **1 object**
- ❌ Không có → Trả về **`null`**

**Ví dụ:**
```php
// Lấy sản phẩm đầu tiên đang active
$product = Product::where('status', 1)
    ->orderBy('created_at', 'desc')
    ->first();

if ($product) {
    echo $product->title;
}
```

**Khi nào dùng:**
- ✅ Lấy 1 kết quả từ query phức tạp
- ✅ Lấy bản ghi mới nhất/cũ nhất

---

### 4. `firstOrFail()` - Lấy đầu tiên hoặc 404

**Cú pháp:**
```php
$model = Model::where('column', 'value')->firstOrFail();
```

**Giống `first()` nhưng throw 404 nếu không tìm thấy**

**Ví dụ:**
```php
// Lấy đơn hàng của user hiện tại
$order = Order::where('user_id', auth()->id())
    ->where('id', $orderId)
    ->firstOrFail();
```

---

### 5. `get()` - Lấy nhiều bản ghi

**Cú pháp:**
```php
$collection = Model::where('column', 'value')->get();
```

**SQL tương đương:**
```sql
SELECT * FROM table WHERE column = 'value'
```

**Kết quả:**
- Trả về **Collection** (giống mảng)
- Có thể rỗng (không bao giờ null)

**Ví dụ:**
```php
// Lấy tất cả sản phẩm đang bán
$products = Product::where('status', 1)->get();

foreach ($products as $product) {
    echo $product->title;
}

// Hoặc dùng trong view
return view('products.index', compact('products'));
```

**Khi nào dùng:**
- ✅ Lấy danh sách sản phẩm
- ✅ Lấy nhiều bản ghi với điều kiện

---

### 6. `all()` - Lấy tất cả

**Cú pháp:**
```php
$collection = Model::all();
```

**SQL tương đương:**
```sql
SELECT * FROM table
```

**Ví dụ:**
```php
// Lấy tất cả categories
$categories = Category::all();
```

**⚠️ Cảnh báo:**
- ❌ KHÔNG dùng với bảng lớn (>1000 records)
- ✅ Chỉ dùng với bảng nhỏ (categories, brands...)

---

### 7. `paginate()` - Phân trang

**Cú pháp:**
```php
$paginator = Model::paginate($perPage);
```

**Ví dụ:**
```php
// Controller
$products = Product::where('status', 1)->paginate(12);
return view('products.index', compact('products'));

// View (Blade)
@foreach($products as $product)
    <div>{{ $product->title }}</div>
@endforeach

{{ $products->links() }}  <!-- Nút phân trang -->
```

---

### 📊 Bảng so sánh Query Methods

| Method | Số lượng | Null? | 404? | Dùng khi |
|--------|----------|-------|------|----------|
| `find($id)` | 1 | ✅ | ❌ | Có thể không tìm thấy |
| `findOrFail($id)` | 1 | ❌ | ✅ | Chắc chắn phải có |
| `first()` | 1 | ✅ | ❌ | Lấy 1 từ query |
| `firstOrFail()` | 1 | ❌ | ✅ | Chắc chắn có kết quả |
| `get()` | N | ❌ | ❌ | Lấy nhiều |
| `all()` | N | ❌ | ❌ | Lấy tất (bảng nhỏ) |
| `paginate()` | N | ❌ | ❌ | Phân trang |

---

## 🔗 RELATIONSHIPS (Model) {#relationships}

### 1. `hasMany()` - Một có nhiều (One-to-Many)

**Khái niệm:**
- "1 cái **có nhiều** cái"
- Ví dụ: 1 User có nhiều Order

**Cú pháp:**
```php
public function relationName()
{
    return $this->hasMany(
        RelatedModel::class,  // Model liên kết
        'foreign_key',        // Khóa ngoại trong bảng con
        'local_key'           // Khóa chính trong bảng này (mặc định: id)
    );
}
```

**Ví dụ 1: User có nhiều Order**

```php
// Model: User
class User extends Model
{
    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }
}

// Sử dụng
$user = User::find(1);
$orders = $user->orders;  // Collection các Order

foreach ($orders as $order) {
    echo $order->order_code;
}
```

**SQL tương đương:**
```sql
SELECT * FROM orders WHERE user_id = 1
```

---

**Ví dụ 2: Order có nhiều OrderDetail**

```php
// Model: Order
class Order extends Model
{
    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'idOrder', 'id');
    }
}

// Sử dụng
$order = Order::find(1);
echo "Đơn hàng: " . $order->order_code;

foreach ($order->details as $detail) {
    echo "- " . $detail->product_name . ": " . $detail->quantity;
}
```

**Cấu trúc bảng:**
```
orders
  - id (PK)
  - order_code
  - user_id

order_details
  - id (PK)
  - idOrder (FK → orders.id)
  - product_name
  - quantity
```

---

### 2. `belongsTo()` - Thuộc về (Many-to-One)

**Khái niệm:**
- "Nhiều cái **thuộc về** 1 cái"
- Ví dụ: Nhiều Order thuộc về 1 User

**Cú pháp:**
```php
public function relationName()
{
    return $this->belongsTo(
        ParentModel::class,   // Model cha
        'foreign_key',        // Khóa ngoại trong bảng này
        'owner_key'           // Khóa chính trong bảng cha (mặc định: id)
    );
}
```

**Ví dụ 1: Order thuộc về User**

```php
// Model: Order
class Order extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}

// Sử dụng
$order = Order::find(1);
echo "Khách hàng: " . $order->user->name;
echo "Email: " . $order->user->email;
```

**SQL tương đương:**
```sql
SELECT * FROM users WHERE id = ?
```

---

**Ví dụ 2: OrderDetail thuộc về Product**

```php
// Model: OrderDetail
class OrderDetail extends Model
{
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    
    public function order()
    {
        return $this->belongsTo(Order::class, 'idOrder', 'id');
    }
}

// Sử dụng
$detail = OrderDetail::find(1);
echo "Sản phẩm: " . $detail->product->title;
echo "Giá: " . $detail->product->price;
echo "Thuộc đơn: " . $detail->order->order_code;
```

---

### 3. `belongsToMany()` - Nhiều-nhiều (Many-to-Many)

**Khái niệm:**
- "Nhiều cái thuộc về nhiều cái"
- Cần bảng trung gian (pivot table)
- Ví dụ: 1 Product có nhiều Festival, 1 Festival có nhiều Product

**Cú pháp:**
```php
public function relationName()
{
    return $this->belongsToMany(
        RelatedModel::class,   // Model liên kết
        'pivot_table',         // Bảng trung gian
        'foreign_pivot_key',   // Khóa ngoại của model này trong pivot
        'related_pivot_key'    // Khóa ngoại của model kia trong pivot
    );
}
```

**Ví dụ: Product và Festival**

```php
// Model: Product
class Product extends Model
{
    public function festivals()
    {
        return $this->belongsToMany(
            Festival::class,
            'festival_product_variant',  // Bảng trung gian
            'product_variant_id',        // FK của Product
            'festival_id'                // FK của Festival
        );
    }
}

// Model: Festival
class Festival extends Model
{
    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'festival_product_variant',
            'festival_id',
            'product_variant_id'
        );
    }
}
```

**Cấu trúc bảng:**
```
products
  - id (PK)
  - title
  - price

festivals
  - id (PK)
  - name
  - discount

festival_product_variant (bảng trung gian)
  - product_variant_id (FK → products.id)
  - festival_id (FK → festivals.id)
```

**Sử dụng:**
```php
// Lấy festivals của product
$product = Product::find(1);
foreach ($product->festivals as $festival) {
    echo $festival->name . " - Giảm " . $festival->discount . "%";
}

// Lấy products của festival
$festival = Festival::find(1);
foreach ($festival->products as $product) {
    echo $product->title;
}
```

**SQL tương đương:**
```sql
SELECT festivals.* 
FROM festivals
INNER JOIN festival_product_variant 
  ON festivals.id = festival_product_variant.festival_id
WHERE festival_product_variant.product_variant_id = ?
```

---

### 📊 Bảng so sánh Relationships

| Relationship | Ý nghĩa | Ví dụ | Trả về |
|--------------|---------|-------|--------|
| `hasMany()` | 1 có nhiều | User có nhiều Order | Collection |
| `belongsTo()` | Thuộc về 1 | Order thuộc User | Object |
| `belongsToMany()` | Nhiều-nhiều | Product-Festival | Collection |

---

## 💡 VÍ DỤ THỰC TẾ {#vi-du-thuc-te}

### Ví dụ 1: Hệ thống đơn hàng

**Cấu trúc quan hệ:**
```
User (Khách hàng)
  └── hasMany → Order (Đơn hàng)
        └── hasMany → OrderDetail (Chi tiết)
              └── belongsTo → Product (Sản phẩm)
```

**Model định nghĩa:**

```php
// User.php
class User extends Model
{
    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }
}

// Order.php
class Order extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }
}

// OrderDetail.php
class OrderDetail extends Model
{
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

// Product.php
class Product extends Model
{
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'product_id');
    }
}
```

**Sử dụng trong Controller:**

```php
// Lấy tất cả đơn hàng của user
public function userOrders($userId)
{
    $user = User::with('orders')->findOrFail($userId);
    
    foreach ($user->orders as $order) {
        echo "Mã đơn: " . $order->order_code . "<br>";
    }
    
    return view('user.orders', compact('user'));
}

// Lấy chi tiết đơn hàng
public function orderDetail($orderId)
{
    $order = Order::with(['user', 'details.product'])
        ->findOrFail($orderId);
    
    echo "Khách: " . $order->user->name . "<br>";
    echo "Sản phẩm:<br>";
    
    foreach ($order->details as $detail) {
        echo "- " . $detail->product->title;
        echo " x " . $detail->quantity . "<br>";
    }
    
    return view('order.show', compact('order'));
}
```

---

### Ví dụ 2: Eager Loading (Tối ưu N+1)

**❌ Cách SAI (N+1 Problem):**

```php
// Chạy 101 queries (1 + 100)
$orders = Order::all();  // 1 query

foreach ($orders as $order) {
    echo $order->user->name;  // 100 queries (nếu có 100 đơn)
}
```

**✅ Cách ĐÚNG (Eager Loading):**

```php
// Chỉ chạy 2 queries
$orders = Order::with('user')->get();  // 2 queries

foreach ($orders as $order) {
    echo $order->user->name;  // Không query thêm
}
```

**✅ Eager Loading nhiều relationship:**

```php
$orders = Order::with(['user', 'details.product'])->get();

foreach ($orders as $order) {
    echo "Khách: " . $order->user->name;
    
    foreach ($order->details as $detail) {
        echo "SP: " . $detail->product->title;
    }
}
```

---

### Ví dụ 3: whereHas() - Query qua relationship

**Lấy User có đơn hàng trạng thái "completed":**

```php
$users = User::whereHas('orders', function($query) {
    $query->where('status', 'completed');
})->get();
```

**SQL tương đương:**
```sql
SELECT * FROM users
WHERE EXISTS (
    SELECT * FROM orders 
    WHERE orders.user_id = users.id 
    AND orders.status = 'completed'
)
```

**Lấy Product thuộc Category đang active:**

```php
$products = Product::whereHas('category', function($query) {
    $query->where('status', 1);
})->get();
```

---

## 🎯 BEST PRACTICES {#best-practices}

### 1. Luôn dùng Eager Loading khi cần relationship

```php
// ❌ BAD
$orders = Order::all();
foreach ($orders as $order) {
    echo $order->user->name;
}

// ✅ GOOD
$orders = Order::with('user')->get();
foreach ($orders as $order) {
    echo $order->user->name;
}
```

---

### 2. Dùng `findOrFail()` cho trang chi tiết

```php
// ❌ BAD
public function show($id)
{
    $product = Product::find($id);
    if (!$product) {
        abort(404);
    }
    return view('product.show', compact('product'));
}

// ✅ GOOD
public function show($id)
{
    $product = Product::findOrFail($id);
    return view('product.show', compact('product'));
}
```

---

### 3. Đặt tên relationship phù hợp

```php
// ❌ BAD - Tên không rõ ý nghĩa
public function rel1() {
    return $this->hasMany(Order::class);
}

// ✅ GOOD - Tên rõ ràng
public function orders() {
    return $this->hasMany(Order::class);
}

public function completedOrders() {
    return $this->hasMany(Order::class)->where('status', 'completed');
}
```

---

### 4. Dùng `with()` khi biết trước cần relationship

```php
// ✅ Load sẵn relationship
$product = Product::with(['brand', 'category', 'festivals'])
    ->findOrFail($id);

// Trong view không cần query thêm
echo $product->brand->name;
echo $product->category->name;
```

---

### 5. Kiểm tra tồn tại relationship trước khi dùng

```php
// ❌ BAD - Có thể lỗi nếu không có user
echo $order->user->name;

// ✅ GOOD - Check trước
echo $order->user ? $order->user->name : 'Khách vãng lai';

// ✅ BETTER - Dùng optional helper
echo optional($order->user)->name ?? 'Khách vãng lai';
```

---

## 📌 TÓM TẮT

### Query Methods:
- `find()` → Tìm theo ID, có thể null
- `findOrFail()` → Tìm theo ID, 404 nếu không có
- `get()` → Lấy nhiều bản ghi
- `first()` → Lấy 1 bản ghi đầu tiên

### Relationships:
- `hasMany()` → 1 có nhiều (User có nhiều Order)
- `belongsTo()` → Thuộc về (Order thuộc User)
- `belongsToMany()` → Nhiều-nhiều (Product-Festival)

### Best Practices:
- ✅ Dùng Eager Loading (`with()`)
- ✅ Dùng `findOrFail()` cho trang chi tiết
- ✅ Đặt tên relationship rõ ràng
- ✅ Check null trước khi dùng relationship

---

**Tài liệu được tạo bởi AI Assistant**  
**Ngày:** {{ date('Y-m-d') }}
