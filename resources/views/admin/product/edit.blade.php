@extends('layout.admin')

@section('body')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-warning text-dark fw-bold">CHỈNH SỬA SẢN PHẨM: {{ $product->title }}</div>
        <div class="card-body">
            <form action="{{route('admin.product.update', ['product'=>$product->id])}}" method="POST">
                @csrf()
                {{method_field('put')}}
                <div class="row">
                    {{-- Cột bên trái: Thông tin cơ bản --}}
                    <div class="col-md-6 border-end pr-4">
                        <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">1. Thông tin cơ bản</h6>
                        <div class="mb-3">
                            <label class="form-label">Tên sản phẩm</label>
                            <input type="text" class="form-control" name="title" value="{{ $product->title }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" name="decription" rows="3">{{ $product->decription }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Link Ảnh</label>
                            <input type="text" class="form-control" name="image" value="{{ $product->image }}">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nồng độ</label>
                                <select name="idConcentration" class="form-select">
                                    @foreach($concentrations as $item)
                                    <option value="{{ $item->id }}" {{ $product->idConcentration == $item->id ? 'selected' : '' }}>{{ $item->concentration }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select name="status" class="form-select">
                                    <option value="1" {{ $product->status == 1 ? 'selected' : '' }}>Đang bán (ON)</option>
                                    <option value="0" {{ $product->status == 0 ? 'selected' : '' }}>Ngừng bán (OFF)</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Danh mục</label>
                                <select name="idCategory" class="form-select">
                                    @foreach($categories as $item)
                                    <option value="{{ $item->id }}" {{ $product->idCategory == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Thương hiệu</label>
                                <select name="idBrand" class="form-select">
                                    @foreach($brands as $item)
                                    <option value="{{ $item->id }}" {{ $product->idBrand == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Cột bên phải: Giá, Số lượng, Dung tích & Sự kiện --}}
                    <div class="col-md-6 pl-4">
                        <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">
                            <span>2. Thông số bán hàng & Giá</span>
                        </h6>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Dung tích sản phẩm</label>
                            <input type="text" class="form-control" name="volume" value="{{ $product->volume }}" placeholder="Ví dụ: 50ml, 100ml..." required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Giá bán (VNĐ)</label>
                                <input type="number" class="form-control" name="price" value="{{ $product->price }}" placeholder="Nhập giá bán tiền" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Số lượng kho hàng</label>
                                <input type="number" class="form-control" name="quantity" value="{{ $product->quantity }}" placeholder="Nhập số lượng nhập kho" required>
                            </div>
                        </div>

                       
                    </div>
                </div>

                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-warning px-5 fw-bold text-dark">CẬP NHẬT SẢN PHẨM</button>
                    <a href="{{ route('admin.product.index') }}" class="btn btn-secondary px-4 ms-2">QUAY LẠI</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection