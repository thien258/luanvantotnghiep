@extends('layout.admin')

@section('body')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white fw-bold">THÊM SẢN PHẨM MỚI</div>
        <div class="card-body">
            <form action="{{route('admin.product.store')}}" method="POST">
                @csrf()
                <div class="row">
                    <div class="col-md-6 border-end pr-4">
                        <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">1. Thông tin cơ bản</h6>
                        <div class="mb-3">
                            <label class="form-label">Tên sản phẩm</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" name="decription" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Link Ảnh</label>
                            <input type="text" class="form-control" name="image">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nồng độ</label>
                                <select name="idConcentration" class="form-select">
                                    @foreach($concentrations as $item) <option value="{{ $item->id }}">{{ $item->concentration }}</option> @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select name="status" class="form-select">
                                    <option value="1">Đang bán (ON)</option>
                                    <option value="0">Ngừng bán (OFF)</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Danh mục</label>
                                <select name="idCategory" class="form-select">
                                    @foreach($categories as $item) <option value="{{ $item->id }}">{{ $item->name }}</option> @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Thương hiệu</label>
                                <select name="idBrand" class="form-select">
                                    @foreach($brands as $item) <option value="{{ $item->id }}">{{ $item->name }}</option> @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 pl-4">
                        <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">2. Cấu hình Dung Tích & Giá</h6>
                        <p class="small text-muted mb-3">Tích chọn các dung tích bạn muốn bán và nhập giá tiền tương ứng.</p>

                        @foreach($volumes as $volume)
                        <div class="row align-items-center mb-3 bg-light p-2 rounded">
                            <div class="col-4">
                                <div class="form-check">
                                    <input class="form-check-input volume-checkbox" type="checkbox" name="variants[{{ $volume->id }}][checked]" value="1" id="vol_{{ $volume->id }}">
                                    <label class="form-check-label fw-bold text-dark" for="vol_{{ $volume->id }}">
                                        {{ $volume->name }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-4 px-1">
                                <input type="number" class="form-control form-control-sm variant-input" name="variants[{{ $volume->id }}][price]" placeholder="Giá bán (VNĐ)" disabled required>
                            </div>
                            <div class="col-4 px-1">
                                <input type="number" class="form-control form-control-sm variant-input" name="variants[{{ $volume->id }}][stock]" placeholder="Kho" disabled required>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-primary px-5 fw-bold">LƯU SẢN PHẨM</button>
                    <a href="{{ route('admin.product.index') }}" class="btn btn-secondary px-4 ms-2">QUAY LẠI</a>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection
@section('script')
    <script src="{{ asset('js/addProduct.js') }}"></script>
@endsection