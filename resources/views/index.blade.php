@extends('layout/home')
@section('body')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show rounded-0 mb-0 text-center" role="alert" style="font-size:0.9rem;">
    <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

<!-- ===== HERO BANNER (giữ nguyên) ===== -->
<section class="hero-banner">
    @forelse($title as $banner)
    <div class="position-relative w-100 d-flex align-items-center justify-content-center text-white text-center"
         style="background: linear-gradient(rgba(0,0,0,0.35), rgba(0,0,0,0.35)), url('{{ $banner->image }}') no-repeat center center / cover; min-height: 520px;">
        <div class="container">
            <div class="col-lg-7 mx-auto">
                <p class="ix-eyebrow">Discover The Essence</p>
                <h1 class="ix-hero-title">{{ $banner->title }}</h1>
                <p class="ix-hero-sub">{{ $banner->descrip }}</p>
                <a href="{{ route('show_products') }}" class="ix-btn-hero">{{ $banner->button }}</a>
            </div>
        </div>
    </div>
    @empty
    @endforelse
</section>

<div class="ix-main">

    <!-- ===== BRANDS ===== -->
    @if(isset($brands) && $brands->count() > 0)
    <section class="ix-brands-section">
        <p class="ix-label-center">Thương Hiệu Nổi Tiếng</p>
        <div class="ix-brands-row">
            @foreach($brands as $brand)
            <a href="{{ route('brand_product', $brand->id) }}" class="ix-brand-item">
                @if($brand->image)
                <img src="{{ $brand->image }}" alt="{{ $brand->name }}">
                @else
                <span>{{ $brand->name }}</span>
                @endif
            </a>
            @endforeach
        </div>
    </section>
    @endif

    <!-- ===== SẢN PHẨM NỔI BẬT ===== -->
    <section class="ix-section">
        <div class="ix-section-head">
            <p class="ix-label-center">Nước Hoa Cao Cấp</p>
            <h2 class="ix-section-title">Sản Phẩm Nổi Bật</h2>
            <div class="ix-rule"></div>
        </div>

        <div class="ix-grid">
            @forelse($products->take(4) as $product)
            <a href="{{ route('single_product', $product->id) }}" class="ix-card">
                <div class="ix-card-img-wrap">
                    @if($product->image)
                    <img src="{{ $product->image }}" alt="{{ $product->title }}" class="ix-card-img">
                    @else
                    <div class="ix-card-no-img"><i class="fas fa-spray-can-sparkles"></i></div>
                    @endif
                    <div class="ix-card-hover">
                        <span class="ix-card-hover-text">Xem chi tiết</span>
                    </div>
                </div>
                <div class="ix-card-body">
                    <h3 class="ix-card-name">{{ $product->title }}</h3>
                    <p class="ix-card-price">{{ number_format($product->price) }}₫</p>
                </div>
            </a>
            @empty
            <div class="ix-empty"><i class="fas fa-box-open"></i><p>Chưa có sản phẩm</p></div>
            @endforelse
        </div>

        <div class="ix-section-foot">
            <a href="{{ route('show_products') }}" class="ix-btn-outline">Xem Tất Cả Sản Phẩm</a>
        </div>
    </section>


    <!-- ===== FESTIVAL / KHUYẾN MÃI ===== -->
    @if(isset($activeFestivals) && $activeFestivals->count() > 0)
        @foreach($activeFestivals as $festival)
        <section class="ix-section ix-festival">
            <div class="ix-section-head">
                <p class="ix-label-center ix-label-sale">Ưu Đãi Đặc Biệt</p>
                <h2 class="ix-section-title">{{ $festival->name }}</h2>
                <div class="ix-rule ix-rule-sale"></div>
                <div class="ix-festival-meta">
                    <span class="ix-discount-badge">-{{ $festival->discount }}%</span>
                    <span class="ix-festival-date">
                        {{ $festival->start_date->format('d/m/Y') }} — {{ $festival->end_date->format('d/m/Y') }}
                    </span>
                </div>
            </div>

            <div class="ix-grid">
                @foreach($festival->products->take(4) as $product)
                @php $discountedPrice = $product->price * (1 - $festival->discount / 100); @endphp
                <a href="{{ route('single_product', $product->id) }}" class="ix-card">
                    <div class="ix-card-img-wrap">
                        <span class="ix-sale-tag">-{{ $festival->discount }}%</span>
                        @if($product->image)
                        <img src="{{ $product->image }}" alt="{{ $product->title }}" class="ix-card-img">
                        @else
                        <div class="ix-card-no-img"><i class="fas fa-spray-can-sparkles"></i></div>
                        @endif
                        <div class="ix-card-hover">
                            <span class="ix-card-hover-text">Xem chi tiết</span>
                        </div>
                    </div>
                    <div class="ix-card-body">
                        <h3 class="ix-card-name">{{ $product->title }}</h3>
                        <div class="ix-price-wrap">
                            <span class="ix-price-old">{{ number_format($product->price) }}₫</span>
                            <span class="ix-price-new">{{ number_format($discountedPrice) }}₫</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

            <div class="ix-section-foot">
                <a href="{{ route('festival_product', $festival->id) }}" class="ix-btn-outline ix-btn-sale">
                    Xem Tất Cả Ưu Đãi
                </a>
            </div>
        </section>
        @endforeach
    @endif

</div>{{-- .ix-main --}}

@endsection
