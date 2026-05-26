@extends('layout.home')
@section('body')

<div class="container py-5">
  <div class="text-center mb-5 pb-3 border-bottom">
    @hasSection('product_header_zone')
    @yield('product_header_zone')
    @else
    <h2 class="display-5 text-dark mb-3" style="font-family: serif;">Bộ sưu tập</h2>
    <p class="text-muted">Khám phá những kiệt tác hương thơm được tinh tuyển, mang đậm dấu ấn nghệ thuật và sự xa xỉ thầm lặng.</p>
    @endif
  </div>
  <div class="row">
    <div class="col-lg-3 pe-lg-5 mb-5 mb-lg-0">
      <div class="d-flex justify-content-between align-items-end border-bottom pb-2 mb-4">
        <span class="text-uppercase fw-bold small tracking-widest">Bộ lọc</span>
        <a href="{{ url()->current() }}" class="text-muted small text-decoration-none">Xóa bộ lọc</a>
      </div>

      <form action="" method="GET">
        @if(!isset($category) && isset($categories))
        <div class="mb-5">
          <p class="text-uppercase fw-bold small mb-3">Danh mục</p>
          @foreach($categories as $cat)
          <div class="form-check mb-2">
            <input class="form-check-input rounded-0 border-dark" type="checkbox" name="categories[]" value="{{ $cat->id }}" id="cat_{{ $cat->id }}" {{ (is_array(request('categories')) && in_array($cat->id, request('categories'))) ? 'checked' : '' }}>
            <label class="form-check-label text-muted small" for="cat_{{ $cat->id }}">{{ $cat->name }}</label>
          </div>
          @endforeach
        </div>
        @endif
        <div class="mb-5">
          <p class="text-uppercase fw-bold small mb-3">Mức giá</p>

          <div id="price-range" class="mb-4 mt-2 px-2"></div>

          <div class="d-flex align-items-center justify-content-between small fw-bold text-danger">
            <span id="price-min-display">0đ</span>
            <span id="price-max-display">10.000.000đ</span>
          </div>

          <input type="hidden" name="min_price" id="min_price" value="{{ request('min_price', 0) }}">
          <input type="hidden" name="max_price" id="max_price" value="{{ request('max_price', 100000000) }}">
        </div>
        {{-- LỌC THƯƠNG HIỆU: Chỉ hiện nếu KHÔNG PHẢI đang ở trang thương hiệu --}}
        @if(!isset($brand) && isset($all_brands))
        <div class="mb-5">
          <p class="text-uppercase fw-bold small mb-3">Thương hiệu</p>
          @foreach($all_brands as $b)
          <div class="form-check mb-2">
            <input class="form-check-input rounded-0 border-dark" type="checkbox" name="brands[]" value="{{ $b->id }}" id="brand_{{ $b->id }}" {{ (is_array(request('brands')) && in_array($b->id, request('brands'))) ? 'checked' : '' }}>
            <label class="form-check-label text-muted small" for="brand_{{ $b->id }}">{{ $b->name }}</label>
          </div>
          @endforeach
        </div>
        @endif

        {{-- LỌC NỒNG ĐỘ: Luôn hiện --}}
        <div class="mb-5">
          <p class="text-uppercase fw-bold small mb-3">Nồng độ</p>
          @foreach($all_concentrations as $concentration)
          <div class="form-check mb-2">
            <input class="form-check-input rounded-0 border-dark" type="checkbox" name="concentrations[]" value="{{ $concentration->id }}" id="conc_{{ $concentration->id }}" {{ (is_array(request('concentrations')) && in_array($concentration->id, request('concentrations'))) ? 'checked' : '' }}>
            <label class="form-check-label text-muted small" for="conc_{{ $concentration->id }}">{{ $concentration->concentration }}</label>
          </div>
          @endforeach
        </div>
        @if(isset($all_volumes))
        <div class="mb-5">
          <p class="text-uppercase fw-bold small mb-3">Dung tích</p>
          @foreach($all_volumes as $vol)
          <div class="form-check mb-2">
            <input class="form-check-input rounded-0 border-dark" type="checkbox" name="volumes[]" value="{{ $vol->id }}" id="vol_{{ $vol->id }}" {{ (is_array(request('volumes')) && in_array($vol->id, request('volumes'))) ? 'checked' : '' }}>
            <label class="form-check-label text-muted small" for="vol_{{ $vol->id }}">{{ $vol->name }}</label>
          </div>
          @endforeach
        </div>
        @endif
        <button type="submit" class="btn btn-dark w-100 rounded-0 text-uppercase fw-bold py-2">Áp dụng</button>
      </form>
    </div>

    <div class="col-lg-9">

      <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <span class="text-muted small">Hiển thị {{ $products->count() }} sản phẩm</span>

    <div class="d-flex align-items-center">
        <span class="text-uppercase small fw-bold me-2">Sắp xếp:</span>
        <select name="sort" class="form-select form-select-sm rounded-0 border-dark w-auto shadow-none" onchange="this.form.submit()">
            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Mới nhất</option>
            <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Giá: Thấp đến Cao</option>
            <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Giá: Cao đến Thấp</option>
        </select>
    </div>
</div>

      <div class="row d-flex flex-wrap">
        @forelse($products as $product)
        @php
        // 1. Lấy biến thể đầu tiên làm đại diện hiển thị
        $defaultVariant = $product->variants->first();
        $originalPrice = $defaultVariant ? $defaultVariant->price : 0;

        // 2. TÌM % GIẢM GIÁ CAO NHẤT (Sản phẩm vs Dung tích đại diện)
        $productDiscount = $product->festivals ? $product->festivals->where('status', 1)->max('discount') ?? 0 : 0;
        $variantDiscount = ($defaultVariant && $defaultVariant->specificFestivals)
        ? $defaultVariant->specificFestivals->where('status', 1)->max('discount') ?? 0
        : 0;

        // Lấy cái nào to hơn
        $maxDiscount = max($productDiscount, $variantDiscount);

        // 3. Lấy giá cuối cùng (Sử dụng hàm getFinalPriceAttribute trong Model ProductVariant)
        $finalPrice = $defaultVariant ? $defaultVariant->final_price : $originalPrice;
        @endphp
        <div class="col-12 col-md-6 col-lg-4 mb-4">
          <div class="card border-0 h-100 text-center bg-transparent d-flex flex-column">
            @if($maxDiscount > 0)
            <span class="badge bg-danger text-white position-absolute fw-bold px-3 py-2 shadow-sm"
              style="top: 15px; left: 15px; z-index: 5; font-size: 13px; border-radius: 0 10px 10px 10px;">
              <i class="fa-solid fa-fire me-1"></i> Gợi ý -{{ $maxDiscount }}%
            </span>
            @endif
            <a href="{{ route('single_product', ['id'=>$product->id]) }}" class="text-decoration-none">
              <div class="ratio ratio-1x1 bg-light mb-3 rounded-1">
                <img src="{{ $product->image }}"
                  class="object-fit-contain p-4 w-100 h-100"
                  alt="{{ $product->title }}">
              </div>
            </a>

            <div class="card-body p-0 d-flex flex-column flex-grow-1">


              <h5 class="card-title mb-3 flex-grow-1">
                <a href="{{ route('single_product', ['id'=>$product->id]) }}" class="text-decoration-none text-dark fs-5 fw-bold">
                  {{ $product->title }}
                </a>
              </h5>

              @if($finalPrice < $originalPrice)
                <p class="mb-0 fs-5">
                <span class="text-danger fw-bold fs-4 me-2">{{ number_format($finalPrice) }}đ</span>

                <span class="text-muted small" style="text-decoration: line-through; color: #6c757d; font-size: 14px;">
                  {{ number_format($originalPrice) }}đ
                </span>
                </p>
                @else
                {{-- Nếu sản phẩm KHÔNG có giảm giá: Chỉ hiện 1 giá gốc màu đen bình thường --}}
                <p class="text-dark mb-0 fs-5 fw-bold">{{ number_format($originalPrice) }}đ</p>
                @endif
            </div>
          </div>
        </div>

        @empty
        <div class="col-12 text-center py-5">
          <h4 class="text-muted">Chưa có sản phẩm nào trong danh mục này.</h4>
        </div>
        @endforelse

      </div>
      @if($products->count() > 0)
      <div class="text-center mt-5 pt-4 border-top">
        <button class="btn btn-outline-dark rounded-0 px-5 py-2 text-uppercase fw-bold">Tải thêm</button>
      </div>
      @endif

    </div>
  </div>
</div>

@endsection
@section('script')
<link rel="stylesheet" href="{{ asset('vendors/nouislider/nouislider.min.css') }}">
<script src="{{ asset('vendors/nouislider/nouislider.min.js') }}"></script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var slider = document.getElementById('price-range');
    var minInput = document.getElementById('min_price');
    var maxInput = document.getElementById('max_price');
    var minDisplay = document.getElementById('price-min-display');
    var maxDisplay = document.getElementById('price-max-display');

    // Lấy giá trị hiện tại (nếu đang lọc dở) hoặc lấy mặc định 0 -> 10 củ
    var currentMin = parseInt(minInput.value) || 0;
    var currentMax = parseInt(maxInput.value) || 10000000;

    // Khởi tạo thanh kéo
    noUiSlider.create(slider, {
      start: [currentMin, currentMax], // Vị trí bắt đầu
      connect: true, // Nối thanh màu ở giữa
      step: 100000, // Mỗi lần kéo nhảy 100k
      range: {
        'min': 0,
        'max': 10000000 // Tối đa 10 triệu (ông có thể tự chỉnh)
      },
      format: {
        to: function(value) {
          return Math.round(value).toLocaleString('vi-VN'); // Format dấu chấm
        },
        from: function(value) {
          return Number(value.replace(/[^0-9.-]+/g, ""));
        }
      }
    });

    // Sự kiện khi khách hàng kéo thanh trượt
    slider.noUiSlider.on('update', function(values, handle) {
      if (handle === 0) {
        minDisplay.innerHTML = values[0] + 'đ';
        minInput.value = values[0].replace(/\./g, ''); // Xóa dấu chấm để nhét vào db
      } else {
        maxDisplay.innerHTML = values[1] + 'đ';
        maxInput.value = values[1].replace(/\./g, '');
      }
    });
  });
</script>
@endsection