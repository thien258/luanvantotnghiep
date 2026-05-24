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

                    <div class="col-md-6 pl-4">
                        <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">2. Cấu hình Dung Tích & Giá</h6>
                        <p class="small text-muted mb-3">Tích chọn các dung tích bạn muốn bán và nhập giá tiền tương ứng.</p>

                        @foreach($volumes as $volume)
                        @php
                        $currentVariant = $product->variants->where('idVolume', $volume->id)->first();
                        @endphp
                        <div class="row align-items-center mb-3 bg-light p-2 rounded border {{ $currentVariant ? 'border-primary' : '' }}">
                            <div class="col-4">
                                <div class="form-check">
                                    <input class="form-check-input volume-checkbox" type="checkbox" name="variants[{{ $volume->id }}][checked]" value="1" id="vol_{{ $volume->id }}" {{ $currentVariant ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark" for="vol_{{ $volume->id }}">
                                        {{ $volume->name }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-4 px-1">
                                <input type="number" class="form-control form-control-sm variant-input" name="variants[{{ $volume->id }}][price]" placeholder="Giá bán (VNĐ)" value="{{ $currentVariant?->price }}" {{ $currentVariant ? '' : 'disabled' }} required>
                            </div>
                            <div class="col-4 px-1">
                                <input type="number" class="form-control form-control-sm variant-input" name="variants[{{ $volume->id }}][stock]" placeholder="Kho" value="{{ $currentVariant?->stock }}" {{ $currentVariant ? '' : 'disabled' }} required>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">3. Chương trình Lễ hội / Giảm giá</h6>
                        <p class="small text-muted mb-2">Tích chọn các lễ hội muốn áp dụng cho sản phẩm này.</p>
                        <div class="d-flex flex-wrap gap-2 border p-3 bg-light rounded" style="max-height: 180px; overflow-y: auto;">
                            @forelse($festivals as $festival)
                            <div class="form-check w-100 mb-2">
                                <input class="form-check-input"
                                    type="checkbox"
                                    name="idFestival[]"
                                    value="{{ $festival->id }}"
                                    id="fest_{{ $festival->id }}"
                                    {{ in_array($festival->id, $selectedFestivalIds) ? 'checked' : '' }}
                                    style="cursor: pointer;">
                                <label class="form-check-label text-dark fw-bold" for="fest_{{ $festival->id }}" style="cursor: pointer;">
                                    {{ $festival->name }} <span class="text-danger">(-{{ $festival->discount }}%)</span>
                                </label>
                            </div>
                            @empty
                            <span class="text-muted small"><em>Không có lễ hội nào đang kích hoạt.</em></span>
                            @endforelse
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
@section('script')
<script src="{{ asset('js/editProduct.js') }}"></script>
@endsection