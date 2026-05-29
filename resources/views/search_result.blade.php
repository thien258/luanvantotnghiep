@extends('layout.home')

@section('body')
<div class="container py-5">
    <div class="mb-5 border-bottom pb-3">
        <h3 class="fw-bold text-dark">Kết quả tìm kiếm</h3>
        <p class="text-muted">
            Tìm thấy <strong>{{ $products->total() }}</strong> sản phẩm cho từ khóa: <span class="text-danger fw-bold">"{{ $keyword }}"</span>
        </p>
    </div>

    <div class="row">
        @forelse($products as $product)
        <div class="col-md-4 col-lg-3 mb-4">
            <div class="card h-100 border-0 shadow-sm rounded-0">
                {{-- Hình ảnh sản phẩm --}}
                <img src="{{ $product->image }}" class="card-img-top rounded-0" alt="{{ $product->title }}" style="height: 250px; object-fit: contain; background: #f8f9fa;">

                <div class="card-body text-center">
                    <h6 class="card-title fw-bold text-dark text-truncate" title="{{ $product->title }}">{{ $product->title }}</h6>

                    {{-- 🌟 LOGIC MỚI: Lấy trực tiếp từ thuộc tính price của bảng products phẳng --}}
                    @php
                    $price = $product->price;
                    @endphp

                    <p class="text-danger fw-bold mb-3">
                        {{ $price > 0 ? number_format($price).'đ' : 'Đang cập nhật' }}
                    </p>

                    {{-- Nút xem chi tiết --}}
                    <a href="{{ route('single_product', $product->id) }}" class="btn btn-outline-dark btn-sm rounded-0 w-100">Xem chi tiết</a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <h4 class="text-muted"><i class="fa-solid fa-box-open mb-3" style="font-size: 40px;"></i><br>Không tìm thấy sản phẩm nào!</h4>
            <p>Vui lòng thử lại với từ khóa khác.</p>
            <a href="{{ route('welcome') }}" class="btn btn-dark rounded-0 mt-2">Quay lại trang chủ</a>
        </div>
        @endforelse
    </div>

    {{-- Phân trang (Nếu có nhiều hơn 12 sản phẩm) --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $products->appends(['keyword' => $keyword])->links('pagination::bootstrap-4') }}
    </div>
</div>
@endsection