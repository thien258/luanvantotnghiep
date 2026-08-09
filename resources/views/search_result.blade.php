@extends('layout.home')

@section('body')

<div class="sp-wrap">

    {{-- PAGE HEADER --}}
    <div class="sp-header">
        <p class="sp-header-eyebrow">Tìm kiếm</p>
        <h1 class="sp-header-title">Kết Quả Tìm Kiếm</h1>
        <div class="ix-rule" style="margin:16px auto 20px;"></div>
        <p class="sp-header-sub">
            Tìm thấy <strong>{{ $products->count() }}</strong> sản phẩm cho từ khóa:
            <em>"{{ $keyword }}"</em>
        </p>
    </div>

    <div class="sp-layout">

        {{-- SIDEBAR FILTER --}}
        <aside class="sp-sidebar">
            <form id="filterForm" action="{{ route('home.search') }}" method="GET">
                <input type="hidden" name="keyword" value="{{ $keyword }}">

                <div class="sp-filter-header">
                    <span>Bộ lọc</span>
                    <a href="{{ route('home.search') }}?keyword={{ urlencode($keyword) }}">Xóa tất cả</a>
                </div>

                {{-- PRICE --}}
                <div class="sp-filter-group">
                    <p class="sp-filter-label">Price</p>
                    <div id="price-range" class="sp-price-slider"></div>
                    <div class="sp-price-display">
                        <span id="price-min-display">0đ</span>
                        <span id="price-max-display">10.000.000đ</span>
                    </div>
                    <input type="hidden" name="min_price" id="min_price" value="{{ request('min_price', 0) }}">
                    <input type="hidden" name="max_price" id="max_price" value="{{ request('max_price', 10000000) }}">
                </div>

                {{-- CATEGORY --}}
                @if(isset($categories) && $categories->count())
                <div class="sp-filter-group">
                    <p class="sp-filter-label">Danh mục</p>
                    @foreach($categories as $cat)
                    <label class="sp-check-label">
                        <input type="checkbox" class="sp-check-input" name="categories[]" value="{{ $cat->id }}"
                            {{ (is_array(request('categories')) && in_array($cat->id, request('categories'))) ? 'checked' : '' }}>
                        <span class="sp-check-box"></span>
                        <span>{{ $cat->name }}</span>
                    </label>
                    @endforeach
                </div>
                @endif

                {{-- BRAND --}}
                @if(isset($all_brands) && $all_brands->count())
                <div class="sp-filter-group">
                    <p class="sp-filter-label">Thương hiệu</p>
                    @foreach($all_brands as $b)
                    <label class="sp-check-label">
                        <input type="checkbox" class="sp-check-input" name="brands[]" value="{{ $b->id }}"
                            {{ (is_array(request('brands')) && in_array($b->id, request('brands'))) ? 'checked' : '' }}>
                        <span class="sp-check-box"></span>
                        <span>{{ $b->name }}</span>
                    </label>
                    @endforeach
                </div>
                @endif

                {{-- CONCENTRATION --}}
                @if(isset($all_concentrations) && $all_concentrations->count())
                <div class="sp-filter-group">
                    <p class="sp-filter-label">Nồng độ</p>
                    @foreach($all_concentrations as $concentration)
                    <label class="sp-check-label">
                        <input type="checkbox" class="sp-check-input" name="concentrations[]" value="{{ $concentration->id }}"
                            {{ (is_array(request('concentrations')) && in_array($concentration->id, request('concentrations'))) ? 'checked' : '' }}>
                        <span class="sp-check-box"></span>
                        <span>{{ $concentration->concentration }}</span>
                    </label>
                    @endforeach
                </div>
                @endif

            </form>
        </aside>

        {{-- PRODUCT AREA --}}
        <div class="sp-content">

            <div class="sp-toolbar">
                <span class="sp-count">Hiển thị {{ $products->count() }} sản phẩm</span>
                <div class="sp-sort-wrap">
                    <label class="sp-sort-label">Sắp xếp:</label>
                    <div class="sp-select-wrap">
                        <select id="sort" name="sort" class="sp-select">
                            <option value="latest" {{ request('sort', 'latest') == 'latest' ? 'selected' : '' }}>Mới nhất</option>
                            <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Giá: Thấp → Cao</option>
                            <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Giá: Cao → Thấp</option>
                        </select>
                        <i class="fas fa-chevron-down sp-select-icon"></i>
                    </div>
                </div>
            </div>

            <div id="product-container">
                <div class="sp-grid">
                    @forelse($products as $product)
                    @php
                        $today = \Carbon\Carbon::today()->toDateString();
                        $originalPrice = $product->price;
                        $maxDiscount = $product->festivals
                            ? $product->festivals->where('status', 1)
                                ->filter(fn($f) => $f->start_date <= $today && $f->end_date >= $today)
                                ->max('discount') ?? 0
                            : 0;
                        $finalPrice = $product->getDiscountedPrice();
                    @endphp

                    <a href="{{ route('single_product', $product->id) }}" class="sp-card">
                        <div class="sp-card-img-wrap">
                            @if($maxDiscount > 0)
                            <span class="sp-card-badge">-{{ $maxDiscount }}%</span>
                            @endif
                            <img src="{{ $product->image }}" alt="{{ $product->title }}" class="sp-card-img">
                            <div class="sp-card-overlay">
                                <span class="sp-card-cta">Xem chi tiết</span>
                            </div>
                        </div>
                        <div class="sp-card-body">
                            <h3 class="sp-card-name">{{ $product->title }}</h3>
                            @if($product->volume)
                            <span class="sp-card-volume">{{ $product->volume }}</span>
                            @endif
                            <div class="sp-card-pricing">
                                @if($finalPrice < $originalPrice)
                                <span class="sp-price-new">{{ number_format($finalPrice) }}đ</span>
                                <span class="sp-price-old">{{ number_format($originalPrice) }}đ</span>
                                @else
                                <span class="sp-price-regular">{{ number_format($originalPrice) }}đ</span>
                                @endif
                            </div>
                        </div>
                    </a>

                    @empty
                    <div class="sp-empty">
                        <i class="fas fa-box-open"></i>
                        <p>Không tìm thấy sản phẩm nào!</p>
                        <a href="{{ route('welcome') }}" class="ix-btn-outline" style="margin-top:16px;">Quay lại trang chủ</a>
                    </div>
                    @endforelse
                </div>
            </div>

            @if($products->hasPages())
            <div class="sp-pagination">
                {{ $products->links() }}
            </div>
            @endif

        </div>
    </div>
</div>

@endsection

@section('script')
<link rel="stylesheet" href="{{ asset('vendors/nouislider/nouislider.min.css') }}">
<script src="{{ asset('vendors/nouislider/nouislider.min.js') }}"></script>
<script src="{{ asset('js/showProduct.js') }}"></script>
@endsection
