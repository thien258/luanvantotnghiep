@extends('layout.home')
@section('body')

<div class="container py-5">
  <div class="text-center mb-5 pb-3 border-bottom">
    <h2 class="display-5 text-dark mb-3" style="font-family: serif;">Bộ sưu tập</h2>
    <p class="text-muted">Khám phá những kiệt tác hương thơm được tinh tuyển, mang đậm dấu ấn nghệ thuật và sự xa xỉ thầm lặng.</p>
  </div>

  <div class="row">
    <div class="col-lg-3 pe-lg-5 mb-5 mb-lg-0">
      <div class="d-flex justify-content-between align-items-end border-bottom pb-2 mb-4">
        <span class="text-uppercase fw-bold small tracking-widest">Bộ lọc</span>
        <a href="#" class="text-muted small text-decoration-none">Xóa bộ lọc</a>
      </div>

      <form action="" method="GET">
        <div class="mb-5">
          <p class="text-uppercase fw-bold small mb-3">Thương hiệu</p>
          <div class="form-check mb-2">
            <input class="form-check-input rounded-0 border-dark" type="checkbox" value="" id="brandChanel">
            <label class="form-check-label text-muted small" for="brandChanel">Chanel</label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input rounded-0 border-dark" type="checkbox" value="" id="brandDior">
            <label class="form-check-label text-muted small" for="brandDior">Dior</label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input rounded-0 border-dark" type="checkbox" value="" id="brandGucci" checked>
            <label class="form-check-label text-muted small" for="brandGucci">Gucci</label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input rounded-0 border-dark" type="checkbox" value="" id="brandLeLabo">
            <label class="form-check-label text-muted small" for="brandLeLabo">Le Labo</label>
          </div>
        </div>

        <div class="mb-5">
          <p class="text-uppercase fw-bold small mb-3">Mức giá tối đa</p>
          <input type="range" class="form-range" min="0" max="10000000" id="priceRange">
          <div class="d-flex justify-content-between mt-2">
            <span class="small fw-bold">0đ</span>
            <span class="small fw-bold">10.000.000đ</span>
          </div>
        </div>

        <div class="mb-5">
          <p class="text-uppercase fw-bold small mb-3">Nồng độ</p>
          <div class="form-check mb-2">
            <input class="form-check-input rounded-0 border-dark" type="checkbox" value="" id="concExtrait">
            <label class="form-check-label text-muted small" for="concExtrait">Extrait de Parfum (20-30%)</label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input rounded-0 border-dark" type="checkbox" value="" id="concEDP">
            <label class="form-check-label text-muted small" for="concEDP">Eau de Parfum (EDP) (15-20%)</label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input rounded-0 border-dark" type="checkbox" value="" id="concEDT">
            <label class="form-check-label text-muted small" for="concEDT">Eau de Toilette (EDT)(5-15%)</label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input rounded-0 border-dark" type="checkbox" value="" id="concEDT">
            <label class="form-check-label text-muted small" for="concEDT">Eau de Cologne (EDC) (3-8%)</label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input rounded-0 border-dark" type="checkbox" value="" id="concEDT">
            <label class="form-check-label text-muted small" for="concEDT">Eau Fraiche (0-3%)</label>
          </div>
        </div>

        <button type="submit" class="btn btn-dark w-100 rounded-0 text-uppercase fw-bold py-2">Áp dụng</button>
      </form>
    </div>

    <div class="col-lg-9">

      <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2">
        <span class="text-muted small">Hiển thị {{ $products->count() }} sản phẩm</span>

        <div class="d-flex align-items-center">
          <span class="text-uppercase small fw-bold me-2">Sắp xếp:</span>
          <select class="form-select form-select-sm rounded-0 border-dark w-auto shadow-none">
            <option>Mới nhất</option>
            <option>Giá tăng dần</option>
            <option>Giá giảm dần</option>
          </select>
        </div>
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

              <p class="text-secondary mb-0 fs-5">{{ number_format($product->price) }}đ</p>
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