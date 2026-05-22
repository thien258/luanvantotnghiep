@extends('layout.home')
@section('body')

<div class="container py-5">
  <div class="text-center mb-5 pb-3 border-bottom">
    {{-- 1. Xử lý biệt lập cho trang Thương hiệu --}}
    @if(isset($brand))
    <h2 class="display-5 text-dark mb-3" style="font-family: serif;">{{ $brand->name }}</h2>
    <p class="text-muted">{{ $brand->descrip ?? 'Lựa chọn hoàn hảo dành riêng cho bạn' }}</p>
    @endif

    {{-- 2. Xử lý biệt lập cho trang Danh mục --}}
    @if(isset($category))
    <h2 class="display-5 text-dark mb-3" style="font-family: serif;">{{ $category->name }}</h2>
    <p class="text-muted">Những sản phẩm thuộc danh mục {{ $category->name }}</p>
    @endif

    {{-- 3. Xử lý biệt lập cho trang Tất cả sản phẩm (Mặc định) --}}
    @if(!isset($brand) && !isset($category))
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

      <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2">
        <span class="text-muted small">Hiển thị {{ $products->count() }} sản phẩm</span>

        <!-- <div class="d-flex align-items-center">
          <span class="text-uppercase small fw-bold me-2">Sắp xếp:</span>
          <select class="form-select form-select-sm rounded-0 border-dark w-auto shadow-none">
            <option>Mới nhất</option>
            <option>Giá tăng dần</option>
            <option>Giá giảm dần</option>
          </select>
        </div> -->
      </div>

      <div class="row d-flex flex-wrap">
        @forelse($products as $product)
        <div class="col-12 col-md-6 col-lg-4 mb-4">
          <div class="card border-0 h-100 text-center bg-transparent d-flex flex-column">

            <a href="{{ route('single_product', ['category'=>$product->id]) }}" class="text-decoration-none">
              <div class="ratio ratio-1x1 bg-light mb-3 rounded-1">
                <img src="{{ $product->image }}"
                  class="object-fit-contain p-4 w-100 h-100"
                  alt="{{ $product->title }}">
              </div>
            </a>

            <div class="card-body p-0 d-flex flex-column flex-grow-1">
              <p class="text-muted text-uppercase small mb-1 fw-bold tracking-widest">AROMA</p>

              <h5 class="card-title mb-3 flex-grow-1">
                <a href="{{ route('single_product', ['category'=>$product->id]) }}" class="text-decoration-none text-dark fs-5 fw-bold">
                  {{ $product->title }}
                </a>
              </h5>

              <p class="text-secondary mb-0 fs-5">{{ number_format($product->variants->first()?->price ?? 0) }}đ</p>
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