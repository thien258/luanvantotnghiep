@extends('layout.home')

@section('body')

<div class="container py-5">

<div class="text-center mb-5 pb-3 border-bottom">
  <h2 class="display-5 text-dark mb-3" style="font-family: serif;">Sản phẩm nổi bật {{ $category->name }}</h2>
  <p class="text-muted">Lựa chọn hoàn hảo dành riêng cho bạn</p>
</div>

 
  <div class="row">
    <div class="col-lg-3 pe-lg-5 mb-5 mb-lg-0">
      <div class="d-flex justify-content-between align-items-end border-bottom pb-2 mb-4">
        <span class="text-uppercase fw-bold small">Bộ lọc</span>
        <a href="{{ route('welcome') }}" class="text-muted small text-decoration-none">Xóa bộ lọc</a>
      </div>

      <form action="" method="GET">


        <div class="mb-4">
          <p class="text-uppercase fw-bold small mb-3">Mức giá tối đa</p>
          <input type="range" class="form-range" min="0" max="10000000" step="500000" id="priceRange" name="max_price">
          <div class="d-flex justify-content-between mt-2">
            <span class="small fw-bold">0đ</span>
            <span class="small fw-bold">10 Tr</span>
          </div>
        </div>

          <div class="mb-5">
          <p class="text-uppercase fw-bold small mb-3">Nồng độ</p>
          
       
          
        </div>

        <button type="submit" class="btn btn-dark w-100 rounded-0 text-uppercase fw-bold py-2">Áp dụng</button>
      </form>
    </div>

    <div class="col-lg-9">

      <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <span class="text-muted small">Hiển thị <strong>{{ $products->count() }}</strong> sản phẩm</span>

        <div class="d-flex align-items-center">
          <span class="text-uppercase small fw-bold me-2">Sắp xếp:</span>
          <select class="form-select form-select-sm rounded-0 border-dark w-auto shadow-none">
            <option value="newest">Mới nhất</option>
            <option value="price_asc">Giá tăng dần</option>
            <option value="price_desc">Giá giảm dần</option>
          </select>
        </div>
      </div>

      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">

        @forelse($products as $product)
        <div class="col-12 col-md-6 col-lg-4">

          <div class="card border-0 h-100 text-center bg-transparent d-flex flex-column shadow-sm rounded-1 overflow-hidden">

            <a href="{{ route('single_product', ['category'=>$product->id]) }}" class="d-block ratio ratio-1x1 bg-light text-decoration-none">
              <img src="{{ $product->image }}"
                class="object-fit-contain p-4 w-100 h-100"
                alt="{{ $product->title }}">
            </a>

            <div class="card-body p-4 d-flex flex-column flex-grow-1 bg-white">

              <h5 class="card-title mb-2">
                <a href="{{ route('single_product', ['category'=>$product->id]) }}" class="text-decoration-none text-dark fs-6 fw-bold">
                  {{ $product->title }}
                </a>
              </h5>

              <p class="card-text text-muted small mb-3 flex-grow-1">
                {{ Str::limit($product->description, 50) }}
              </p>

              <div class="mt-auto">
                <p class="text-dark fs-5 fw-bold mb-3">{{ number_format($product->price) }} VNĐ</p>

                <a href="{{ route('single_product', ['category'=>$product->id]) }}" class="btn btn-outline-dark w-100 rounded-0 text-uppercase fw-bold py-2 small">
                  Xem chi tiết
                </a>
              </div>
            </div>

          </div>
        </div>
        @empty
        <div class="col-12 text-center py-5 w-100">
          <div class="p-5 bg-light rounded-1">
            <h4 class="text-muted mb-0">Không tìm thấy sản phẩm nào phù hợp.</h4>
          </div>
        </div>
        @endforelse

      </div>

    </div>
  </div>
</div>

@endsection