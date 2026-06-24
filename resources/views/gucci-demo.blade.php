@extends('layout.home')

@section('body')
<div class="container my-5">
    {{-- Header --}}
    <div class="text-center mb-5">
        <h1 class="display-4 font-weight-bold text-primary">
            <i class="fas fa-spray-can mr-3"></i>Demo Sản Phẩm Gucci
        </h1>
        <p class="lead text-muted">Hiển thị thông tin chi tiết 1 sản phẩm từ database</p>
        <hr class="my-4">
    </div>

    @if($product)
    <div class="row">
        {{-- Hình ảnh sản phẩm --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow-lg border-0">
                <img src="{{ $product->image }}" 
                     class="card-img-top" 
                     alt="{{ $product->title }}"
                     style="height: 500px; object-fit: contain; background: #f8f9fa;">
            </div>
        </div>

        {{-- Thông tin sản phẩm --}}
        <div class="col-md-6">
            <div class="card shadow-lg border-0">
                <div class="card-body p-4">
                    {{-- Tên sản phẩm --}}
                    <h2 class="card-title font-weight-bold text-dark mb-3">
                        {{ $product->title }}
                    </h2>

                    {{-- Thương hiệu --}}
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge badge-primary badge-pill px-3 py-2 mr-2">
                            <i class="fas fa-tag mr-1"></i>
                            {{ $product->brand->title ?? 'N/A' }}
                        </span>
                        <span class="badge badge-info badge-pill px-3 py-2">
                            <i class="fas fa-layer-group mr-1"></i>
                            {{ $product->category->title ?? 'N/A' }}
                        </span>
                    </div>

                    {{-- Giá --}}
                    <div class="mb-4">
                        @php
                            $originalPrice = $product->price;
                            $discountedPrice = $product->getDiscountedPrice();
                            $hasDiscount = $originalPrice > $discountedPrice;
                        @endphp

                        @if($hasDiscount)
                            <div class="d-flex align-items-center">
                                <h3 class="text-danger font-weight-bold mb-0 mr-3">
                                    {{ number_format($discountedPrice) }} VND
                                </h3>
                                <small class="text-muted">
                                    <del>{{ number_format($originalPrice) }} VND</del>
                                </small>
                            </div>
                            <div class="mt-2">
                                <span class="badge badge-success badge-pill px-3 py-2">
                                    <i class="fas fa-percent mr-1"></i>
                                    Giảm {{ round((1 - $discountedPrice/$originalPrice) * 100) }}%
                                </span>
                            </div>
                        @else
                            <h3 class="text-primary font-weight-bold">
                                {{ number_format($originalPrice) }} VND
                            </h3>
                        @endif
                    </div>

                    <hr>

                    {{-- Chi tiết kỹ thuật --}}
                    <div class="mb-3">
                        <h5 class="font-weight-bold mb-3">
                            <i class="fas fa-info-circle text-info mr-2"></i>
                            Thông tin chi tiết
                        </h5>
                        
                        <table class="table table-sm table-borderless">
                            <tbody>
                                <tr>
                                    <td class="font-weight-bold" style="width: 40%;">
                                        <i class="fas fa-flask text-warning mr-2"></i>Nồng độ:
                                    </td>
                                    <td>{{ $product->concentration->title ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">
                                        <i class="fas fa-box text-info mr-2"></i>Dung tích:
                                    </td>
                                    <td>{{ $product->volume }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">
                                        <i class="fas fa-warehouse text-success mr-2"></i>Tồn kho:
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $product->quantity > 0 ? 'success' : 'danger' }} badge-pill px-3">
                                            {{ $product->quantity }} sản phẩm
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">
                                        <i class="fas fa-barcode text-secondary mr-2"></i>Mã SP:
                                    </td>
                                    <td>#{{ $product->id }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <hr>

                    {{-- Mô tả --}}
                    <div class="mb-4">
                        <h5 class="font-weight-bold mb-3">
                            <i class="fas fa-align-left text-success mr-2"></i>
                            Mô tả sản phẩm
                        </h5>
                        <p class="text-muted">{{ $product->decription ?? 'Chưa có mô tả' }}</p>
                    </div>

                    {{-- Nút hành động --}}
                    <div class="d-flex gap-2">
                        <a href="{{ route('single_product', $product->id) }}" 
                           class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-eye mr-2"></i>Xem chi tiết
                        </a>
                    </div>
                </div>
            </div>

            {{-- Card thông tin kỹ thuật --}}
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body bg-light">
                    <h6 class="font-weight-bold mb-3">
                        <i class="fas fa-code text-danger mr-2"></i>
                        Dữ liệu từ Database (JSON)
                    </h6>
                    <pre class="bg-white p-3 rounded border" style="font-size: 12px; max-height: 300px; overflow-y: auto;"><code>{{ json_encode($product->only(['id', 'title', 'price', 'volume', 'quantity']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                </div>
            </div>
        </div>
    </div>

    {{-- Hướng dẫn code --}}
    <div class="card shadow-lg border-0 mt-5">
        <div class="card-header bg-dark text-white">
            <h4 class="mb-0">
                <i class="fas fa-graduation-cap mr-2"></i>
                Hướng dẫn Code
            </h4>
        </div>
        <div class="card-body">
            <h5 class="font-weight-bold text-primary">1. Controller (HomeController.php)</h5>
            <pre class="bg-light p-3 rounded border"><code>public function showGucciProduct()
{
    // Lấy 1 sản phẩm Gucci từ database
    $product = Product::whereHas('brand', function($query) {
        $query->where('title', 'GUCCI');
    })->with(['brand', 'category', 'concentration'])->first();

    return view('gucci-demo', compact('product'));
}</code></pre>

            <h5 class="font-weight-bold text-primary mt-4">2. Route (web.php)</h5>
            <pre class="bg-light p-3 rounded border"><code>Route::get('/gucci-demo', [HomeController::class, 'showGucciProduct'])->name('gucci.demo');</code></pre>

            <h5 class="font-weight-bold text-primary mt-4">3. Blade View (gucci-demo.blade.php)</h5>
            <pre class="bg-light p-3 rounded border"><code>&lt;h2&gt;{{ $product->title }}&lt;/h2&gt;
&lt;p&gt;Giá: {{ number_format($product->getDiscountedPrice()) }} VND&lt;/p&gt;
&lt;p&gt;Thương hiệu: {{ $product->brand->title }}&lt;/p&gt;</code></pre>

            <h5 class="font-weight-bold text-primary mt-4">4. Truy cập</h5>
            <p class="mb-0">
                URL: <code class="bg-light px-2 py-1 rounded">{{ route('gucci.demo') }}</code>
            </p>
        </div>
    </div>

    @else
    {{-- Không tìm thấy sản phẩm --}}
    <div class="alert alert-warning text-center" role="alert">
        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
        <h4>Không tìm thấy sản phẩm nào trong database</h4>
        <p class="mb-0">Vui lòng thêm sản phẩm từ trang Admin</p>
    </div>
    @endif

    {{-- Nút quay lại --}}
    <div class="text-center mt-5">
        <a href="{{ route('welcome') }}" class="btn btn-outline-secondary btn-lg">
            <i class="fas fa-arrow-left mr-2"></i>Quay lại trang chủ
        </a>
    </div>
</div>
@endsection
