<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm từ NSX {{ $manufacturer->name ?? 'N/A' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 40px 0;">
    <div class="container">
        {{-- Header --}}
        <div class="text-center text-white mb-5">
            <h1 class="display-3 font-weight-bold mb-3">
                <i class="fas fa-industry mr-3"></i>
                Sản phẩm từ NSX: {{ $manufacturer->name ?? 'N/A' }}
            </h1>
            <p class="lead">
                <i class="fas fa-phone mr-2"></i>{{ $manufacturer->phone ?? 'N/A' }}
                <span class="mx-3">|</span>
                <i class="fas fa-map-marker-alt mr-2"></i>{{ $manufacturer->address ?? 'N/A' }}
            </p>
            <hr class="bg-white my-4" style="width: 60%; opacity: 0.3;">
        </div>

        @if($products->isEmpty())
            {{-- Không có sản phẩm --}}
            <div class="card shadow-lg border-0">
                <div class="card-body text-center py-5">
                    <i class="fas fa-box-open fa-5x text-muted mb-4"></i>
                    <h3 class="text-muted">Chưa có sản phẩm nào từ NSX này</h3>
                    <p class="text-secondary">Vui lòng tạo Purchase Order và nhập hàng từ NSX "{{ $manufacturer->name ?? 'N/A' }}"</p>
                    <a href="{{ route('welcome') }}" class="btn btn-primary btn-lg mt-3">
                        <i class="fas fa-home mr-2"></i>Quay về trang chủ
                    </a>
                </div>
            </div>
        @else
            {{-- Thống kê --}}
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-white shadow border-0">
                        <div class="card-body text-center">
                            <i class="fas fa-box-open fa-3x text-primary mb-3"></i>
                            <h2 class="font-weight-bold text-dark mb-0">{{ $products->count() }}</h2>
                            <p class="text-muted mb-0">Sản phẩm</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-white shadow border-0">
                        <div class="card-body text-center">
                            <i class="fas fa-tags fa-3x text-success mb-3"></i>
                            <h2 class="font-weight-bold text-dark mb-0">{{ $products->pluck('brand.title')->unique()->count() }}</h2>
                            <p class="text-muted mb-0">Thương hiệu</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-white shadow border-0">
                        <div class="card-body text-center">
                            <i class="fas fa-warehouse fa-3x text-warning mb-3"></i>
                            <h2 class="font-weight-bold text-dark mb-0">{{ number_format($products->sum('quantity')) }}</h2>
                            <p class="text-muted mb-0">Tổng tồn kho</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Danh sách sản phẩm --}}
            <div class="row">
                @foreach($products as $product)
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="card shadow border-0 h-100 transition" style="transition: transform 0.3s, box-shadow 0.3s;" onmouseover="this.style.transform='translateY(-10px)'; this.style.boxShadow='0 10px 30px rgba(0,0,0,0.2)';" onmouseout="this.style.transform=''; this.style.boxShadow='';">
                        <img src="{{ $product->image }}" class="card-img-top" style="height: 250px; object-fit: contain; background: #f8f9fa;" alt="{{ $product->title }}">
                        <div class="card-body">
                            <h5 class="card-title font-weight-bold text-dark">{{ $product->title }}</h5>
                            
                            {{-- Badges --}}
                            <div class="mb-3">
                                <span class="badge badge-primary mr-2" style="font-size: 0.85rem; padding: 0.5rem 1rem;">
                                    <i class="fas fa-tag mr-1"></i>{{ $product->brand->title ?? 'N/A' }}
                                </span>
                                <span class="badge badge-info" style="font-size: 0.85rem; padding: 0.5rem 1rem;">
                                    <i class="fas fa-box mr-1"></i>{{ $product->quantity }} SP
                                </span>
                            </div>

                            {{-- Giá --}}
                            <div class="mb-3">
                                @php
                                    $originalPrice = $product->price;
                                    $discountedPrice = $product->getDiscountedPrice();
                                    $hasDiscount = $originalPrice > $discountedPrice;
                                @endphp

                                @if($hasDiscount)
                                    <h4 class="text-danger font-weight-bold mb-1">
                                        {{ number_format($discountedPrice) }} VND
                                    </h4>
                                    <small class="text-muted">
                                        <del>{{ number_format($originalPrice) }} VND</del>
                                        <span class="badge badge-success ml-2">
                                            -{{ round((1 - $discountedPrice/$originalPrice) * 100) }}%
                                        </span>
                                    </small>
                                @else
                                    <h4 class="text-primary font-weight-bold">
                                        {{ number_format($originalPrice) }} VND
                                    </h4>
                                @endif
                            </div>

                            {{-- Mô tả ngắn --}}
                            <p class="card-text text-muted small">
                                {{ \Str::limit($product->decription, 80) }}
                            </p>

                            {{-- Nút xem chi tiết --}}
                            <a href="{{ route('single_product', $product->id) }}" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-eye mr-2"></i>Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Hướng dẫn code --}}
            <div class="card shadow-lg border-0 mt-5 bg-white">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-code mr-2"></i>
                        Hướng dẫn Code - CÁCH 1: Lấy sản phẩm trực tiếp từ NSX
                    </h4>
                </div>
                <div class="card-body">
                    <h5 class="font-weight-bold text-primary">1. Controller Method (CÁCH 1 - Đơn giản)</h5>
                    <pre class="bg-light p-3 rounded border"><code>public function showManufacturerProducts()
{
    // Lấy NSX "a" với eager loading relationships
    $manufacturer = ManuFacturer::where('name', 'a')
        ->with(['products.brand', 'products.category'])
        ->first();

    $products = collect();
    
    if ($manufacturer) {
        // Lấy sản phẩm từ relationship có sẵn (manufacturers_product)
        // Lọc chỉ lấy sản phẩm đang bán (status = 1)
        $products = $manufacturer->products
            ->where('status', 1)
            ->values();
    }

    return view('manufacturer-demo', compact('manufacturer', 'products'));
}</code></pre>

                    <h5 class="font-weight-bold text-primary mt-4">2. Route</h5>
                    <pre class="bg-light p-3 rounded border"><code>Route::get('/manufacturer-demo', [HomeController::class, 'showManufacturerProducts'])
    ->name('manufacturer.demo');</code></pre>

                    <h5 class="font-weight-bold text-primary mt-4">3. Giải thích CÁCH 1</h5>
                    <ul>
                        <li><strong>manufacturers_product:</strong> Bảng trung gian many-to-many</li>
                        <li><strong>$manufacturer->products:</strong> Lấy trực tiếp từ relationship</li>
                        <li><strong>where('status', 1):</strong> Chỉ lấy sản phẩm đang bán</li>
                        <li><strong>values():</strong> Reset lại index của collection</li>
                    </ul>

                    <div class="alert alert-success mt-4">
                        <i class="fas fa-check-circle mr-2"></i>
                        <strong>Ưu điểm CÁCH 1:</strong> Đơn giản, nhanh (2-3 queries), dễ hiểu. 
                        Không cần quan tâm Purchase Orders.
                    </div>

                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Lưu ý:</strong> Bảng manufacturers_product tự động được cập nhật khi admin tạo Purchase Order.
                    </div>
                </div>
            </div>
        @endif

        {{-- Nút quay lại --}}
        <div class="text-center mt-5">
            <a href="{{ route('welcome') }}" class="btn btn-light btn-lg shadow">
                <i class="fas fa-arrow-left mr-2"></i>Quay về trang chủ
            </a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
