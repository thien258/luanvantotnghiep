# Cách Lấy Sản Phẩm Từ NSX (Manufacturer)

## 📌 TL;DR (Tóm tắt)

```php
// CÁCH 1: Đơn giản - Lấy trực tiếp từ bảng manufacturers_product
$manufacturer = ManuFacturer::with('products.brand')->find(1);
$products = $manufacturer->products->where('status', 1);

// CÁCH 2: Phức tạp - Lấy qua Purchase Order
$manufacturer = ManuFacturer::with('purchaseOrders.items.product')->find(1);
foreach ($manufacturer->purchaseOrders as $order) {
    foreach ($order->items as $item) {
        $products->push($item->product);
    }
}
```

---

## 1. CÁCH 1: Lấy Trực Tiếp (Đơn Giản - Recommended ✅)

### Cấu trúc database:

```
manufacturers (NSX)
    ↕️ (many-to-many)
manufacturers_product (bảng trung gian)
    ↕️
products (Sản phẩm)
```

### Code Controller:

```php
use App\Models\ManuFacturer;
use App\Models\Brand;
use App\Models\Festival;

public function showManufacturerProducts()
{
    // Lấy NSX kèm sản phẩm
    $manufacturer = ManuFacturer::where('name', 'a')
        ->with([
            'products.brand',      // Eager load brand
            'products.category',   // Eager load category
            'products.concentration'
        ])
        ->first();

    // Lấy sản phẩm từ relationship
    $products = collect();
    
    if ($manufacturer) {
        // Lọc chỉ sản phẩm đang bán
        $products = $manufacturer->products
            ->where('status', 1)
            ->values();
    }

    // Lấy dữ liệu cho layout
    $title = Title::all();
    $footers = Footer::all();
    $brands = Brand::where('status', 1)->get();
    $festivals = Festival::active()->get();

    return view('manufacturer-demo', compact(
        'manufacturer', 
        'products', 
        'title', 
        'footers', 
        'brands', 
        'festivals'
    ));
}
```

### Ưu điểm:
✅ Code ngắn gọn, dễ hiểu  
✅ Chỉ 2-3 queries (rất nhanh)  
✅ Lấy được tất cả sản phẩm trong danh bạ  

### Nhược điểm:
❌ Chỉ là danh bạ, không biết đã đặt hàng chưa  
❌ Có thể có sản phẩm trong danh bạ nhưng chưa giao hàng  

### Khi nào dùng:
- Muốn hiển thị catalog/danh mục sản phẩm của NSX
- Không quan tâm lịch sử đặt hàng
- Cần code đơn giản, dễ maintain

---

## 2. CÁCH 2: Lấy Qua Purchase Order (Phức Tạp)

### Cấu trúc database:

```
manufacturers (NSX)
    ↓ (one-to-many: manufacturer_id)
purchase_orders (Đơn đặt hàng)
    ↓ (one-to-many: purchase_order_id)
purchase_order_items (Chi tiết đơn)
    ↓ (many-to-one: product_id)
products (Sản phẩm)
```

### Code Controller:

```php
public function showManufacturerProducts()
{
    // Lấy NSX với nested eager loading
    $manufacturer = ManuFacturer::where('name', 'a')
        ->with([
            'purchaseOrders.items.product.brand',
            'purchaseOrders.items.product.category'
        ])
        ->first();

    $products = collect();
    
    if ($manufacturer) {
        // Lặp qua Purchase Orders
        foreach ($manufacturer->purchaseOrders as $order) {
            // Lặp qua items trong mỗi order
            foreach ($order->items as $item) {
                // Chỉ lấy sản phẩm đang bán
                if ($item->product && $item->product->status == 1) {
                    $products->push($item->product);
                }
            }
        }
        
        // Loại bỏ trùng lặp
        $products = $products->unique('id')->values();
    }

    return view('manufacturer-demo', compact('manufacturer', 'products', ...));
}
```

### Giải thích Nested Eager Loading:

```php
'purchaseOrders.items.product.brand'
```

**Đọc từ trái qua phải:**
1. `purchaseOrders` - Lấy tất cả đơn đặt hàng
2. `.items` - Từ mỗi order, lấy tất cả items
3. `.product` - Từ mỗi item, lấy sản phẩm
4. `.brand` - Từ mỗi product, lấy brand

### Ưu điểm:
✅ Chỉ lấy sản phẩm đã thực sự đặt hàng  
✅ Có thể lọc theo trạng thái đơn (pending, received...)  
✅ Biết được lịch sử giao dịch  

### Nhược điểm:
❌ Code phức tạp hơn  
❌ Nhiều queries hơn (5-6 queries)  
❌ Cần hiểu rõ relationships  

### Khi nào dùng:
- Cần lọc theo trạng thái đơn hàng
- Muốn biết lịch sử đặt hàng
- Cần thống kê sản phẩm đã giao

---

## 3. So Sánh 2 Cách

| Tiêu chí | Cách 1 (Direct) | Cách 2 (Purchase Order) |
|----------|-----------------|-------------------------|
| **Độ phức tạp** | ⭐ Đơn giản | ⭐⭐⭐ Phức tạp |
| **Số queries** | 2-3 queries | 5-6 queries |
| **Tốc độ** | ⚡ Nhanh | 🐢 Chậm hơn |
| **Dữ liệu** | Danh bạ sản phẩm | Sản phẩm đã đặt |
| **Use case** | Hiển thị catalog | Lịch sử giao dịch |

---

## 4. Ví Dụ Thực Tế

### Tình huống 1: Hiển thị sản phẩm cho giảng viên

```php
// ✅ Dùng Cách 1 - Đơn giản, dễ demo
$manufacturer = ManuFacturer::with('products.brand')->find(1);
$products = $manufacturer->products->where('status', 1);
```

### Tình huống 2: Báo cáo sản phẩm đã nhập

```php
// ✅ Dùng Cách 2 - Lọc được theo trạng thái đơn
$manufacturer = ManuFacturer::with([
    'purchaseOrders' => fn($q) => $q->where('status', 'received')
])->with('purchaseOrders.items.product')->find(1);

$products = collect();
foreach ($manufacturer->purchaseOrders as $order) {
    foreach ($order->items as $item) {
        $products->push($item->product);
    }
}
```

---

## 5. Best Practices

### ✅ Luôn eager load relationships

```php
// ❌ BAD - N+1 Query Problem
$manufacturer = ManuFacturer::find(1);
foreach ($manufacturer->products as $product) {
    echo $product->brand->title; // Query mới cho mỗi product!
}

// ✅ GOOD - Eager Loading
$manufacturer = ManuFacturer::with('products.brand')->find(1);
foreach ($manufacturer->products as $product) {
    echo $product->brand->title; // Không query thêm
}
```

### ✅ Lọc ngay trong query

```php
// ✅ GOOD - Lọc trước khi load
$manufacturer = ManuFacturer::with([
    'products' => fn($q) => $q->where('status', 1)
])->find(1);

// ❌ OK nhưng load thừa - Lọc sau khi load
$manufacturer = ManuFacturer::with('products')->find(1);
$products = $manufacturer->products->where('status', 1);
```

### ✅ Kiểm tra null

```php
// ✅ GOOD
if ($manufacturer) {
    $products = $manufacturer->products;
}

// ✅ GOOD - Null coalescing
$products = $manufacturer->products ?? collect();
```

---

## 6. Kết Luận

**Recommendation:**  
👉 **Dùng CÁCH 1** cho hầu hết trường hợp (đơn giản, nhanh)  
👉 Chỉ dùng CÁCH 2 khi cần lọc theo trạng thái đơn hàng

**Code mẫu đầy đủ:**  
- Xem file: `app/Http/Controllers/HomeController.php`
- Method: `showManufacturerProducts()` (đã comment)
- Route: `/manufacturer-demo`
- View: `resources/views/manufacturer-demo.blade.php`

---

**Ghi chú:** Document này được tạo để trả lời câu hỏi "Lấy sản phẩm từ NSX như thế nào?" trong quá trình phát triển dự án Aroma Shop.
