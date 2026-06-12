@extends('layout.admin')

@section('body')
<div class="container py-4">
    {{-- [FIX] Hiển thị lỗi validation --}}
    @if($errors->any())
    <div class="alert alert-danger rounded-0 mb-3">
        <ul class="mb-0 ps-3 small">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white fw-bold">THÊM SẢN PHẨM MỚI</div>
        <div class="card-body">
            <form action="{{route('admin.product.store')}}" method="POST">
                @csrf()
                <div class="row">
                    {{-- Cột bên trái: Thông tin cơ bản --}}
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

                    {{-- Cột bên phải: Giá, Số lượng, Dung tích cố định & Sự kiện --}}
                    <div class="col-md-6 pl-4">
                        <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">
                            <span>2. Cấu hình Giá bán & Dung tích</span>
                        </h6>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Dung tích sản phẩm</label>
                            <input type="text" class="form-control" name="volume" placeholder="Ví dụ: 100ml, 50ml, 10ml..." required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Giá bán gốc (VNĐ)</label>
                                <input type="number" class="form-control" name="price" placeholder="Nhập giá bán" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Số lượng kho ban đầu</label>
                                <input type="number" class="form-control" name="quantity" placeholder="Nhập số lượng kho" required>
                            </div>
                        </div>

                     
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