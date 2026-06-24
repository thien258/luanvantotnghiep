# Hướng Dẫn Lấy và Hiển Thị Sản Phẩm trong Laravel

> **Tác giả:** Kiro AI Assistant  
> **Ngày tạo:** 23/06/2026  
> **Mục đích:** Hướng dẫn chi tiết cách query và hiển thị sản phẩm từ database

---

## 📋 Mục Lục

1. [Lấy 1 Sản Phẩm](#1-lấy-1-sản-phẩm)
2. [Lấy Nhiều Sản Phẩm](#2-lấy-nhiều-sản-phẩm)
3. [Lấy Sản Phẩm Theo Brand](#3-lấy-sản-phẩm-theo-brand)
4. [Lấy Sản Phẩm Theo NSX](#4-lấy-sản-phẩm-theo-nsx)
5. [Tạo Trang Demo](#5-tạo-trang-demo)
6. [Troubleshooting](#6-troubleshooting)

---

## 1. Lấy 1 Sản Phẩm

### 1.1. Lấy 1 sản phẩm bất kỳ

```php
use App\Models\Product;

// Lấy sản phẩm đầu tiên
$product = Product::first();

// Lấy sản phẩm theo ID
$product = Product::find(1);

// Lấy sản phẩm theo ID (throw exception nếu không tìm thấy)
$product = Product::findOrFail(1);
```

### 1.2. Lấy sản phẩm với điều kiện

```php
// Lấy sản phẩm đang bán (status = 1)
$product = Product::where('status', 1)->first();

// Lấy sản phẩm còn hàng
$product = Product::where('quantity', '>', 0)->first();

// Kết hợp nhiều điều kiện
$product = Product::where('status', 1)
    ->where('quantity', '>', 0)
    ->first();
```

### 1.3. Lấy sản phẩm với relationships (Eager Loading)

```php
// Lấy sản phẩm kèm thông tin brand, category, concentration
$product = Product::with(['brand', 'category', 'concentration'])
    ->first();

// Lấy sản phẩm kèm festivals để tính giá giảm
$product = Product::with('festivals')->first();

// Sử dụng trong view
echo $product->brand->title;      // Tên thương hiệu
echo $product->category->title;   // Tên danh mục
echo $product->getDiscountedPrice(); // Giá sau giảm
```

---

## 2. Lấy Nhiều Sản Phẩm

### 2.1. Lấy tất cả sản phẩm

```php
// Lấy tất cả (KHÔNG nên dùng nếu có nhiều dữ liệu)
$products = Product::all();

// Lấy sản phẩm đang bán
$products = Product::where('status', 1)->get();

// Lấy 10 sản phẩm đầu tiên
$products = Product::take(10)->get();

// Lấy 10 sản phẩm mới nhất
$products = Product::orderBy('created_at', 'desc')->take(10)->get();
```

### 2.2. Lấy sản phẩm với pagination

```php
// Phân trang 12 sản phẩm/trang
$products = Product::where('status', 1)->paginate(12);

// Trong view blade
@foreach($products as $product)
    <h3>{{ $product->title }}</h3>
@endforeach

{{ $products->links() }} {{-- Hiển thị nút phân trang --}}
```

### 2.3. Lọc sản phẩm theo nhiều điều kiện

```php
$query = Product::where('status', 1);

// Lọc theo giá
if ($request->has('min_price') && $request->has('max_price')) {
    $query->whereBetween('price', [$request->min_price, $request->max_price]);
}

// Lọc theo danh mục
if ($request->has('category_id')) {
    $query->where('idCategory', $request->category_id);
}

// Lọc theo thương hiệu
if ($request->has('brand_id')) {
    $query->where('idBrand', $request->brand_id);
}

$products = $query->get();
```

---

## 3. Lấy Sản Phẩm Theo Brand (Thương Hiệu)

### 3.1. Query cơ bản

```php
// Lấy tất cả sản phẩm của brand GUCCI
$products = Product::whereHas('brand', function($query) {
    $query->where('title', 'GUCCI');
})->get();

// Lấy 1 sản phẩm Gucci đầu tiên
$product = Product::whereHas('brand', function($query) {
    $query->where('title', 'GUCCI');
})->first();
```

### 3.2. Query theo ID brand

```php
// Nếu biết ID của brand
$brandId = 1;
$products = Product::where('idBrand', $brandId)
    ->where('status', 1)
    ->get();
```

### 3.3. Eager loading brand

```php
// Lấy sản phẩm kèm thông tin brand
$products = Product::with('brand')
    ->whereHas('brand', function($query) {
        $query->where('title', 'GUCCI');
    })
    ->get();

// Sử dụng trong view
@foreach($products as $product)
    <p>Brand: {{ $product->brand->title }}</p>
@endforeach
```

---

## 4. Lấy Sản Phẩm Theo NSX (Manufacturer)

### 4.1. Hiểu mô hình dữ liệu

```
Manufacturer (NSX)
    └── hasMany: PurchaseOrder (Đơn đặt hàng)
            └── hasMany: PurchaseOrderItem
                    └── belongsTo: Product
```

**Quan trọng:** Sản phẩm KHÔNG có quan hệ trực tiếp với Manufacturer. Phải query qua Purchase Order.

### 4.2. Thêm relationship vào Model

**File:** `app/Models/ManuFacturer.php`

```php
/**
 * Các đơn đặt hàng từ NSX này.
 */
public function purchaseOrders()
{
    return $this->hasMany(PurchaseOrder::class, 'manufacturer_id');
}
```

### 4.3. Query sản phẩm từ NSX

```php
use App\Models\ManuFacturer;

// Lấy NSX theo tên
$manufacturer = ManuFacturer::where('name', 'a')->first();

// Hoặc theo ID
$manufacturer = ManuFacturer::find(1);

// Lấy sản phẩm từ các Purchase Order
$products = collect();

foreach ($manufacturer->purchaseOrders as $order) {
    foreach ($order->items as $item) {
        if ($item->product && $item->product->status == 1) {
            $products->push($item->product);
        }
    }
}

// Loại bỏ sản phẩm trùng lặp
$products = $products->unique('id')->values();
```

### 4.4. Query với Eager Loading (tối ưu hiệu suất)

```php
// Eager load để tránh N+1 query problem
$manufacturer = ManuFacturer::where('name', 'a')
    ->with([
        'purchaseOrders.items.product.brand',
        'purchaseOrders.items.product.category'
    ])
    ->first();

$products = collect();

if ($manufacturer) {
    foreach ($manufacturer->purchaseOrders as $order) {
        foreach ($order->items as $item) {
            if ($item->product && $item->product->status == 1) {
                $products->push($item->product);
            }
        }
    }
    
    $products = $products->unique('id')->values();
}
```

---

## 5. Tạo Trang Demo

### 5.1. Demo 1: Hiển thị 1 sản phẩm Gucci

#### Bước 1: Tạo Controller Method

**File:** `app/Http/Controllers/HomeController.php`

```php
use App\Models\Product;
use App\Models\Brand;
use App\Models\Title;
use App\Models\Footer;
use App\Models\Festival;

public function showGucciProduct()
{
    // Lấy 1 sản phẩm Gucci
    $product = Product::whereHas('brand', function($query) {
        $query->where('title', 'GUCCI');
    })->with(['brand', 'category', 'concentration', 'festivals'])->first();

    // Fallback nếu không có Gucci
    if (!$product) {
        $product = Product::with(['brand', 'category', 'concentration'])->first();
    }

    // Lấy dữ liệu cho layout
    $title   = Title::all();
    $footers = Footer::all();
    $brands = Brand::where('status', 1)->get();
    $festivals = Festival::active()->get();

    return view('gucci-demo', compact('product', 'title', 'footers', 'brands', 'festivals'));
}
```

#### Bước 2: Thêm Route

**File:** `routes/web.php`

```php
Route::get('/gucci-demo', [HomeController::class, 'showGucciProduct'])
    ->name('gucci.demo');
```

#### Bước 3: Tạo View (Sử dụng layout có sẵn)

**File:** `resources/views/gucci-demo.blade.php`

```blade
@extends('layout.home')

@section('body')
<div class="container my-5">
    @if($product)
    <div class="row">
        <div class="col-md-6">
            <img src="{{ $product->image }}" class="img-fluid" alt="{{ $product->title }}">
        </div>
        <div class="col-md-6">
            <h2>{{ $product->title }}</h2>
            <p><strong>Brand:</strong> {{ $product->brand->title ?? 'N/A' }}</p>
            <p><strong>Category:</strong> {{ $product->category->title ?? 'N/A' }}</p>
            <p><strong>Price:</strong> {{ number_format($product->getDiscountedPrice()) }} VND</p>
            <p>{{ $product->decription }}</p>
        </div>
    </div>
    @else
    <div class="alert alert-warning">Không tìm thấy sản phẩm</div>
    @endif
</div>
@endsection
```

**Lưu ý:** Layout `layout.home` sử dụng `@yield('body')`, không phải `@yield('main')`.

### 5.2. Demo 2: Hiển thị sản phẩm từ NSX

#### Bước 1: Controller Method

**File:** `app/Http/Controllers/HomeController.php`

```php
public function showManufacturerProducts()
{
    $manufacturer = \App\Models\ManuFacturer::where('name', 'a')
        ->with(['purchaseOrders.items.product.brand', 'purchaseOrders.items.product.category'])
        ->first();

    $products = collect();
    
    if ($manufacturer) {
        foreach ($manufacturer->purchaseOrders as $order) {
            foreach ($order->items as $item) {
                if ($item->product && $item->product->status == 1) {
                    $products->push($item->product);
                }
            }
        }
        
        $products = $products->unique('id')->values();
    }

    $title   = Title::all();
    $footers = Footer::all();
    $brands = Brand::where('status', 1)->get();
    $festivals = Festival::active()->get();

    return view('manufacturer-demo', compact('manufacturer', 'products', 'title', 'footers', 'brands', 'festivals'));
}
```

#### Bước 2: Route

```php
Route::get('/manufacturer-demo', [HomeController::class, 'showManufacturerProducts'])
    ->name('manufacturer.demo');
```

#### Bước 3: View

**File:** `resources/views/manufacturer-demo.blade.php`

Xem file đầy đủ tại: `resources/views/manufacturer-demo.blade.php`

---

## 6. Troubleshooting

### 6.1. Lỗi: Call to undefined relationship [purchaseOrders]

**Nguyên nhân:** Model `ManuFacturer` chưa có relationship `purchaseOrders()`

**Giải pháp:** Thêm method vào Model

```php
// File: app/Models/ManuFacturer.php
public function purchaseOrders()
{
    return $this->hasMany(PurchaseOrder::class, 'manufacturer_id');
}
```

### 6.2. Lỗi: Trying to get property of non-object

**Nguyên nhân:** Relationship chưa được eager load

**Giải pháp:** Sử dụng `with()` để load trước

```php
// SAI - Gây N+1 query
$products = Product::all();
foreach ($products as $product) {
    echo $product->brand->title; // Query mới cho mỗi sản phẩm
}

// ĐÚNG - Eager loading
$products = Product::with('brand')->get();
foreach ($products as $product) {
    echo $product->brand->title; // Không query thêm
}
```

### 6.3. Trang chỉ hiển thị header/footer, không có content

**Nguyên nhân:** Section name không khớp với layout

**Giải pháp:** Kiểm tra layout sử dụng `@yield('body')` hay `@yield('main')`

```blade
{{-- Layout: layout.home --}}
@yield('body')

{{-- View phải dùng: --}}
@section('body')
    <!-- Nội dung -->
@endsection
```

### 6.4. Không tìm thấy sản phẩm từ NSX

**Nguyên nhân:** Chưa có Purchase Order nào từ NSX đó

**Giải pháp:** 
1. Vào Admin Panel
2. Tạo Purchase Order từ NSX
3. Nhận hàng và nhập kho

### 6.5. Hiển thị giá sai (không có discount)

**Nguyên nhân:** Chưa eager load festivals

**Giải pháp:**

```php
// ĐÚNG
$product = Product::with('festivals')->first();
$price = $product->getDiscountedPrice();

// SAI
$product = Product::first();
$price = $product->getDiscountedPrice(); // Không có discount
```

---

## 7. Best Practices

### 7.1. Luôn sử dụng Eager Loading

```php
// ❌ BAD - N+1 Problem
$products = Product::all();
foreach ($products as $product) {
    echo $product->brand->title;
    echo $product->category->title;
}

// ✅ GOOD
$products = Product::with(['brand', 'category'])->get();
foreach ($products as $product) {
    echo $product->brand->title;
    echo $product->category->title;
}
```

### 7.2. Sử dụng whereHas khi filter theo relationship

```php
// ✅ GOOD - Chỉ lấy sản phẩm có brand GUCCI
$products = Product::whereHas('brand', function($query) {
    $query->where('title', 'GUCCI');
})->with('brand')->get();
```

### 7.3. Kiểm tra tồn tại trước khi truy cập

```php
// ✅ GOOD
if ($product && $product->brand) {
    echo $product->brand->title;
}

// Hoặc dùng optional helper
echo optional($product->brand)->title;

// Hoặc null coalescing
echo $product->brand->title ?? 'N/A';
```

### 7.4. Sử dụng Collection methods

```php
// Loại bỏ trùng lặp
$products = $products->unique('id');

// Lọc sản phẩm còn hàng
$inStock = $products->filter(fn($p) => $p->quantity > 0);

// Sắp xếp theo giá
$sorted = $products->sortBy('price');

// Lấy 10 sản phẩm đầu
$top10 = $products->take(10);
```

---

## 8. Tài Liệu Tham Khảo

- **Laravel Eloquent:** https://laravel.com/docs/eloquent
- **Laravel Relationships:** https://laravel.com/docs/eloquent-relationships
- **Laravel Collections:** https://laravel.com/docs/collections
- **N+1 Query Problem:** https://laravel.com/docs/eloquent-relationships#eager-loading

---

## 9. Demo URLs

Sau khi tạo xong, truy cập các URL sau để xem demo:

- **Demo Gucci:** `http://localhost:8000/gucci-demo`
- **Demo NSX:** `http://localhost:8000/manufacturer-demo`

---

**Ghi chú:** Document này được tạo dựa trên các câu hỏi và vấn đề thực tế gặp phải trong quá trình phát triển dự án Aroma Shop.
