@extends('layout/home')
@section('body')

<!-- Banner giữ nguyên -->
<section class="hero-banner mb-5">
    @forelse($title as $banner)
    <div class="position-relative w-100 d-flex align-items-center justify-content-center text-white text-center" 
         style="background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('{{ $banner->image }}') no-repeat center center / cover; min-height: 500px;">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h1 class="display-3 fw-bold mb-3 text-white" style="font-family: 'Playfair Display', serif;">
                        {{ $banner->title }}
                    </h1>
                    <p class="lead mb-4 fs-5 opacity-75">{{ $banner->descrip }}</p>
                    <div>
                        <a href="show-products" class="btn btn-light rounded-0 px-5 py-3 fw-bold text-uppercase shadow-sm">
                            {{ $banner->button }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    @endforelse
</section>

<div class="container">
    <!-- Logo thương hiệu -->
    @if(isset($brands) && $brands->count() > 0)
    <section class="mb-5">
        <div class="text-center mb-4">
            <h5 class="text-uppercase fw-bold text-secondary" style="letter-spacing: 1px;">Thương hiệu nổi tiếng</h5>
        </div>
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-4 justify-content-center">
            @foreach($brands as $brand)
            <div class="col">
                <a href="{{ route('brand_product', $brand->id) }}" 
                   class="d-flex align-items-center justify-content-center p-4 border rounded bg-white text-decoration-none shadow-sm transition"
                   style="min-height: 120px; transition: all 0.3s ease;">
                    @if($brand->image)
                    <img src="{{ $brand->image }}" 
                         alt="{{ $brand->name }}" 
                         class="img-fluid"
                         style="max-height: 60px; max-width: 100%; object-fit: contain; filter: grayscale(100%); transition: filter 0.3s ease;"
                         onmouseover="this.style.filter='grayscale(0%)'"
                         onmouseout="this.style.filter='grayscale(100%)'">
                    @else
                    <span class="text-muted fw-semibold">{{ $brand->name }}</span>
                    @endif
                </a>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Sản phẩm nổi bật (Carousel style) -->
    <section class="mb-5">
        <div class="text-center mb-4">
            <p class="text-uppercase text-muted small mb-2" style="letter-spacing: 2px;">Nước hoa cao cấp</p>
            <h3 class="fw-bold">SẢN PHẨM NỔI BẬT</h3>
        </div>

        <div class="row g-4 justify-content-center">
            @forelse($products->take(6) as $product)
            <div class="col-6 col-md-4 col-lg-2">
                <div class="text-center">
                    <a href="{{ route('single_product', $product->id) }}" class="text-decoration-none d-block">
                        <!-- Ảnh -->
                        <div class="bg-white border rounded mb-3 p-3" style="aspect-ratio: 3/4;">
                            @if($product->image)
                            <img src="{{ $product->image }}" 
                                 alt="{{ $product->title }}" 
                                 class="w-100 h-100 object-fit-contain">
                            @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light">
                                <span class="text-muted small">No Image</span>
                            </div>
                            @endif
                        </div>

                        <!-- Tên -->
                        <h6 class="fw-bold text-dark mb-2 small" 
                            style="min-height: 2.5em; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                            {{ $product->title }}
                        </h6>

                        <!-- Giá -->
                        <p class="text-dark fw-semibold mb-0">
                            {{ number_format($product->price) }}₫
                        </p>
                    </a>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <p class="text-muted">Chưa có sản phẩm nào</p>
            </div>
            @endforelse
        </div>

        <!-- Xem thêm -->
        <div class="text-center mt-4">
            <a href="{{ route('show_products') }}" class="btn btn-outline-dark rounded-0 px-4">
                XEM TẤT CẢ SẢN PHẨM
            </a>
        </div>
    </section>

    <!-- Chương trình khuyến mãi -->
    @if(isset($activeFestivals) && $activeFestivals->count() > 0)
        @foreach($activeFestivals as $festival)
        <section class="mb-5">
            <!-- Header chương trình -->
            <div class="text-center mb-4">
                <p class="text-uppercase text-danger small mb-2 fw-bold" style="letter-spacing: 2px;">Ưu đãi đặc biệt</p>
                <h3 class="fw-bold">{{ $festival->name }}</h3>
                <p class="text-muted">
                    <span class="badge text-danger me-2">-{{ $festival->discount }}%</span>
                    {{ $festival->start_date->format('d/m/Y') }} - {{ $festival->end_date->format('d/m/Y') }}
                </p>
            </div>

            <!-- Danh sách sản phẩm trong chương trình -->
            <div class="row g-4 justify-content-center">
                @foreach($festival->products as $product)
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="text-center">
                        <a href="{{ route('single_product', $product->id) }}" class="text-decoration-none d-block">
                            <!-- Ảnh -->
                            <div class="bg-white border rounded mb-3 p-3 position-relative" style="aspect-ratio: 3/4;">
                                <!-- Badge giảm giá -->
                                <div class="position-absolute top-0 end-0 m-2">
                                    <span class="badge text-danger">-{{ $festival->discount }}%</span>
                                </div>
                                
                                @if($product->image)
                                <img src="{{ $product->image }}" 
                                     alt="{{ $product->title }}" 
                                     class="w-100 h-100 object-fit-contain">
                                @else
                                <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light">
                                    <span class="text-muted small">No Image</span>
                                </div>
                                @endif
                            </div>

                            <!-- Tên -->
                            <h6 class="fw-bold text-dark mb-2 small" 
                                style="min-height: 2.5em; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                {{ $product->title }}
                            </h6>

                            <!-- Giá -->
                            <div class="mb-0">
                                @php
                                    $discountedPrice = $product->price * (1 - $festival->discount / 100);
                                @endphp
                                <p class="text-muted text-decoration-line-through small mb-1">
                                    {{ number_format($product->price) }}₫
                                </p>
                                <p class="text-danger fw-bold mb-0">
                                    {{ number_format($discountedPrice) }}₫
                                </p>
                            </div>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Xem thêm -->
            <div class="text-center mt-4">
                <a href="{{ route('festival_product', $festival->id) }}" class="btn btn-outline-danger rounded-0 px-4">
                    XEM TẤT CẢ SẢN PHẨM
                </a>
            </div>
        </section>
        @endforeach
    @endif
</div>

@endsection