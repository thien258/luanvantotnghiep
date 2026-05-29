```blade
@extends('layout.home')

@section('body')

<div class="container py-5">

    <div class="text-center mb-5 pb-3 border-bottom">

        @hasSection('product_header_zone')

            @yield('product_header_zone')

        @else

            <h2 class="display-5 text-dark mb-3" style="font-family: serif;">
                Bộ sưu tập
            </h2>

            <p class="text-muted">
                Khám phá những kiệt tác hương thơm được tinh tuyển,
                mang đậm dấu ấn nghệ thuật và sự xa xỉ thầm lặng.
            </p>

        @endif

    </div>

    <div class="row">

        {{-- FILTER --}}
        <div class="col-lg-3 pe-lg-5 mb-5 mb-lg-0">

            <form id="filterForm" action="{{ url()->current() }}" method="GET">

                <div class="d-flex justify-content-between align-items-end border-bottom pb-2 mb-4">

                    <span class="text-uppercase fw-bold small tracking-widest">
                        Bộ lọc
                    </span>

                    <a href="{{ url()->current() }}"
                       class="text-muted small text-decoration-none">

                        Xóa bộ lọc

                    </a>

                </div>

                {{-- CATEGORY --}}
                @if(!isset($category) && isset($categories))

                <div class="mb-5">

                    <p class="text-uppercase fw-bold small mb-3">
                        Danh mục
                    </p>

                    @foreach($categories as $cat)

                    <div class="form-check mb-2">

                        <input
                            class="form-check-input rounded-0 border-dark"
                            type="checkbox"
                            name="categories[]"
                            value="{{ $cat->id }}"
                            id="cat_{{ $cat->id }}"
                            {{ (is_array(request('categories')) && in_array($cat->id, request('categories'))) ? 'checked' : '' }}>

                        <label
                            class="form-check-label text-muted small"
                            for="cat_{{ $cat->id }}">

                            {{ $cat->name }}

                        </label>

                    </div>

                    @endforeach

                </div>

                @endif

                {{-- PRICE --}}
                <div class="mb-5">

                    <p class="text-uppercase fw-bold small mb-3">
                        Mức giá
                    </p>

                    <div id="price-range" class="mb-4 mt-2 px-2"></div>

                    <div class="d-flex align-items-center justify-content-between small fw-bold text-danger">

                        <span id="price-min-display">
                            0đ
                        </span>

                        <span id="price-max-display">
                            10.000.000đ
                        </span>

                    </div>

                    <input
                        type="hidden"
                        name="min_price"
                        id="min_price"
                        value="{{ request('min_price', 0) }}">

                    <input
                        type="hidden"
                        name="max_price"
                        id="max_price"
                        value="{{ request('max_price', 10000000) }}">

                </div>

                {{-- BRAND --}}
                @if(!isset($brand) && isset($all_brands))

                <div class="mb-5">

                    <p class="text-uppercase fw-bold small mb-3">
                        Thương hiệu
                    </p>

                    @foreach($all_brands as $b)

                    <div class="form-check mb-2">

                        <input
                            class="form-check-input rounded-0 border-dark"
                            type="checkbox"
                            name="brands[]"
                            value="{{ $b->id }}"
                            id="brand_{{ $b->id }}"
                            {{ (is_array(request('brands')) && in_array($b->id, request('brands'))) ? 'checked' : '' }}>

                        <label
                            class="form-check-label text-muted small"
                            for="brand_{{ $b->id }}">

                            {{ $b->name }}

                        </label>

                    </div>

                    @endforeach

                </div>

                @endif

                {{-- CONCENTRATION --}}
                <div class="mb-5">

                    <p class="text-uppercase fw-bold small mb-3">
                        Nồng độ
                    </p>

                    @foreach($all_concentrations as $concentration)

                    <div class="form-check mb-2">

                        <input
                            class="form-check-input rounded-0 border-dark"
                            type="checkbox"
                            name="concentrations[]"
                            value="{{ $concentration->id }}"
                            id="conc_{{ $concentration->id }}"
                            {{ (is_array(request('concentrations')) && in_array($concentration->id, request('concentrations'))) ? 'checked' : '' }}>

                        <label
                            class="form-check-label text-muted small"
                            for="conc_{{ $concentration->id }}">

                            {{ $concentration->concentration }}

                        </label>

                    </div>

                    @endforeach

                </div>

            </form>

        </div>

        {{-- PRODUCTS --}}
        <div class="col-lg-9">

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">

                <span class="text-muted small">

                    Hiển thị {{ $products->count() }} sản phẩm

                </span>

                <div class="d-flex align-items-center mt-2 mt-md-0">

                    <span class="text-uppercase small fw-bold me-2">

                        Sắp xếp:

                    </span>

                    <select
                        id="sort"
                        name="sort"
                        form="filterForm"
                        class="form-select form-select-sm rounded-0 border-dark shadow-none">

                        <option
                            value="latest"
                            {{ request('sort') == 'latest' ? 'selected' : '' }}>

                            Mới nhất

                        </option>

                        <option
                            value="price_asc"
                            {{ request('sort') == 'price_asc' ? 'selected' : '' }}>

                            Giá: Thấp đến Cao

                        </option>

                        <option
                            value="price_desc"
                            {{ request('sort') == 'price_desc' ? 'selected' : '' }}>

                            Giá: Cao đến Thấp

                        </option>

                    </select>

                </div>

            </div>

            <div id="product-container">

                <div class="row d-flex flex-wrap">

                    @forelse($products as $product)

                    @php

                        $originalPrice = $product->price;

                        $productDiscount = $product->festivals
                            ? $product->festivals->where('status', 1)->max('discount') ?? 0
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

                            <a
                                href="{{ route('single_product', ['id'=>$product->id]) }}"
                                class="text-decoration-none">

                                <div class="ratio ratio-1x1 bg-light mb-3 rounded-1">

                                    <img
                                        src="{{ $product->image }}"
                                        class="object-fit-contain p-4 w-100 h-100"
                                        alt="{{ $product->title }}">

                                </div>

                            </a>

                            <div class="card-body p-0 d-flex flex-column flex-grow-1">

                                <h5 class="card-title mb-1">

                                    <a
                                        href="{{ route('single_product', ['id'=>$product->id]) }}"
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

                                        <span
                                            class="text-muted small"
                                            style="text-decoration: line-through; color: #6c757d; font-size: 14px;">

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

                            Chưa có sản phẩm nào trong bộ sưu tập này.

                        </h4>

                    </div>

                    @endforelse

                </div>

            </div>

        </div>

    </div>

</div>

@endsection

@section('script')

<link
    rel="stylesheet"
    href="{{ asset('vendors/nouislider/nouislider.min.css') }}">

<script src="{{ asset('vendors/nouislider/nouislider.min.js') }}"></script>

<script src="{{ asset('js/showProduct.js') }}"></script>

@endsection
```
