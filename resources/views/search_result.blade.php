@extends('layout.home')

@section('body')
<div class="container py-5">

    <div class="text-center mb-5 pb-3 border-bottom">
        <h2 class="display-5 text-dark mb-3" style="font-family: serif;">Kết quả tìm kiếm</h2>
        <p class="text-muted">
            Tìm thấy <strong>{{ $products->count() }}</strong> sản phẩm cho từ khóa:
            <span class="text-danger fw-bold">"{{ $keyword }}"</span>
        </p>
    </div>

    <div class="row">

        {{-- FILTER --}}
        <div class="col-lg-3 pe-lg-5 mb-5 mb-lg-0">

            <form id="filterForm" action="{{ route('home.search') }}" method="GET">

                <input type="hidden" name="keyword" value="{{ $keyword }}">

                <div class="d-flex justify-content-between align-items-end border-bottom pb-2 mb-4">
                    <span class="text-uppercase fw-bold small">Bộ lọc</span>
                    <a href="{{ route('home.search') }}?keyword={{ urlencode($keyword) }}" class="text-muted small text-decoration-none">Xóa bộ lọc</a>
                </div>

                {{-- PRICE --}}
                <div class="mb-5">
                    <p class="text-uppercase fw-bold small mb-3">Mức giá</p>
                    <div id="price-range" class="mb-4 mt-2 px-2"></div>
                    <div class="d-flex align-items-center justify-content-between small fw-bold text-danger">
                        <span id="price-min-display">0đ</span>
                        <span id="price-max-display">10.000.000đ</span>
                    </div>
                    <input type="hidden" name="min_price" id="min_price" value="{{ request('min_price', 0) }}">
                    <input type="hidden" name="max_price" id="max_price" value="{{ request('max_price', 10000000) }}">
                </div>

                {{-- CATEGORY --}}
                @if(isset($categories) && $categories->count())
                <div class="mb-5">
                    <p class="text-uppercase fw-bold small mb-3">Danh mục</p>
                    @foreach($categories as $cat)
                    <div class="form-check mb-2">
                        <input
                            class="form-check-input rounded-0 border-dark"
                            type="checkbox"
                            name="categories[]"
                            value="{{ $cat->id }}"
                            id="cat_{{ $cat->id }}"
                            {{ (is_array(request('categories')) && in_array($cat->id, request('categories'))) ? 'checked' : '' }}>
                        <label class="form-check-label text-muted small" for="cat_{{ $cat->id }}">
                            {{ $cat->name }}
                        </label>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- BRAND --}}
                @if(isset($all_brands) && $all_brands->count())
                <div class="mb-5">
                    <p class="text-uppercase fw-bold small mb-3">Thương hiệu</p>
                    @foreach($all_brands as $b)
                    <div class="form-check mb-2">
                        <input
                            class="form-check-input rounded-0 border-dark"
                            type="checkbox"
                            name="brands[]"
                            value="{{ $b->id }}"
                            id="brand_{{ $b->id }}"
                            {{ (is_array(request('brands')) && in_array($b->id, request('brands'))) ? 'checked' : '' }}>
                        <label class="form-check-label text-muted small" for="brand_{{ $b->id }}">
                            {{ $b->name }}
                        </label>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- CONCENTRATION --}}
                @if(isset($all_concentrations) && $all_concentrations->count())
                <div class="mb-5">
                    <p class="text-uppercase fw-bold small mb-3">Nồng độ</p>
                    @foreach($all_concentrations as $concentration)
                    <div class="form-check mb-2">
                        <input
                            class="form-check-input rounded-0 border-dark"
                            type="checkbox"
                            name="concentrations[]"
                            value="{{ $concentration->id }}"
                            id="conc_{{ $concentration->id }}"
                            {{ (is_array(request('concentrations')) && in_array($concentration->id, request('concentrations'))) ? 'checked' : '' }}>
                        <label class="form-check-label text-muted small" for="conc_{{ $concentration->id }}">
                            {{ $concentration->concentration }}
                        </label>
                    </div>
                    @endforeach
                </div>
                @endif

            </form>

        </div>

        {{-- PRODUCTS --}}
        <div class="col-lg-9">

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <span class="text-muted small">Hiển thị {{ $products->count() }} sản phẩm</span>
                <div class="d-flex align-items-center mt-2 mt-md-0">
                    <span class="text-uppercase small fw-bold me-2">Sắp xếp:</span>
                    <select id="sort" name="sort" class="form-select form-select-sm rounded-0 border-dark shadow-none">
                        <option value="latest" {{ request('sort', 'latest') == 'latest' ? 'selected' : '' }}>Mới nhất</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Giá: Thấp đến Cao</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Giá: Cao đến Thấp</option>
                    </select>
                </div>
            </div>

            <div id="product-container">
                <div class="row d-flex flex-wrap">

                    @forelse($products as $product)

                    @php
                        $today = \Carbon\Carbon::today()->toDateString();
                        $originalPrice = $product->price;
                        $productDiscount = $product->festivals
                            ? $product->festivals
                                ->where('status', 1)
                                ->filter(fn($f) => $f->start_date <= $today && $f->end_date >= $today)
                                ->max('discount') ?? 0
                            : 0;
                        $maxDiscount = $productDiscount;
                        $finalPrice = $product->getDiscountedPrice();
                    @endphp

                    <div class="col-12 col-md-6 col-lg-4 mb-4">
                        <div class="card border-0 h-100 text-center bg-transparent d-flex flex-column">

                            @if($maxDiscount > 0)
                            <span
                                class="badge bg-danger text-white position-absolute fw-bold px-3 py-2 shadow-sm"
                                style="top: 15px; left: 15px; z-index: 5; font-size: 13px; border-radius: 0 10px 10px 10px;">
                                <i class="fa-solid fa-fire me-1"></i>
                                Ưu đãi -{{ $maxDiscount }}%
                            </span>
                            @endif

                            <a href="{{ route('single_product', $product->id) }}" class="text-decoration-none">
                                <div class="ratio ratio-1x1 bg-light mb-3 rounded-1">
                                    <img
                                        src="{{ $product->image }}"
                                        class="object-fit-contain p-4 w-100 h-100"
                                        alt="{{ $product->title }}">
                                </div>
                            </a>

                            <div class="card-body p-0 d-flex flex-column flex-grow-1">
                                <h5 class="card-title mb-1">
                                    <a href="{{ route('single_product', $product->id) }}"
                                        class="text-decoration-none text-dark fs-5 fw-bold">
                                        {{ $product->title }}
                                    </a>
                                </h5>

                                <div class="mb-2">
                                    <span class="badge bg-light text-secondary border small">
                                        {{ $product->volume ?? 'Cố định' }}
                                    </span>
                                </div>

                                <div class="mt-auto">
                                    @if($finalPrice < $originalPrice)
                                    <p class="mb-0 fs-5">
                                        <span class="text-danger fw-bold fs-4 me-2">
                                            {{ number_format($finalPrice) }}đ
                                        </span>
                                        <span class="text-muted small" style="text-decoration: line-through;">
                                            {{ number_format($originalPrice) }}đ
                                        </span>
                                    </p>
                                    @else
                                    <p class="text-dark mb-0 fs-5 fw-bold">
                                        {{ number_format($originalPrice) }}đ
                                    </p>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>

                    @empty
                    <div class="col-12 text-center py-5">
                        <h4 class="text-muted">
                            <i class="fa-solid fa-box-open mb-3" style="font-size: 40px;"></i><br>
                            Không tìm thấy sản phẩm nào!
                        </h4>
                        <p>Vui lòng thử lại với từ khóa khác.</p>
                        <a href="{{ route('welcome') }}" class="btn btn-dark rounded-0 mt-2">Quay lại trang chủ</a>
                    </div>
                    @endforelse

                </div>
            </div>

        </div>

    </div>

</div>
@endsection

@section('script')
<link rel="stylesheet" href="{{ asset('vendors/nouislider/nouislider.min.css') }}">
<script src="{{ asset('vendors/nouislider/nouislider.min.js') }}"></script>
<script src="{{ asset('js/showProduct.js') }}"></script>
@endsection
