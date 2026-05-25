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
                        <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2 d-flex justify-content-between align-items-center">
                            <span>2. Cấu hình Dung Tích & Giá</span>
                        </h6>
                        <p class="small text-muted mb-3">Tích chọn các dung tích bạn muốn bán và nhập giá tiền tương ứng.</p>

                        @foreach($volumes as $volume)
                        <div class="row align-items-center mb-3 bg-light p-2 rounded border">
                            <div class="col-12 mb-2 d-flex justify-content-between border-bottom pb-2">
                                <div class="form-check">
                                    <input class="form-check-input volume-checkbox" type="checkbox" name="variants[{{ $volume->id }}][checked]" value="1" id="vol_{{ $volume->id }}">
                                    <label class="form-check-label fw-bold text-dark" for="vol_{{ $volume->id }}">
                                        {{ $volume->name }}
                                    </label>
                                </div>
                                <div class="d-flex gap-2">
                                    <input type="number" class="form-control form-control-sm variant-input" style="width: 150px;" name="variants[{{ $volume->id }}][price]" placeholder="Giá bán (VNĐ)" disabled required>
                                    <input type="number" class="form-control form-control-sm variant-input stock-input" style="width: 100px;" name="variants[{{ $volume->id }}][stock]" placeholder="Kho" disabled required>
                                </div>
                            </div>

                            <div class="col-12 mt-1">
                                <div class="d-flex align-items-center justify-content-between">
                                    <label class="small text-muted mb-0"><i class="fa-solid fa-tags"></i> Lễ hội áp dụng riêng (Tùy chọn):</label>
                                    
                                    {{-- Nút bấm dấu cộng (Mặc định bị disabled khi chưa tích chọn dung tích) --}}
                                    <button class="btn btn-sm btn-outline-secondary rounded-circle shadow-none variant-input" type="button" 
                                            data-bs-toggle="collapse" data-toggle="collapse" 
                                            data-bs-target="#collapseFestival_{{ $volume->id }}" data-target="#collapseFestival_{{ $volume->id }}" 
                                            aria-expanded="false" style="width: 28px; height: 28px; padding: 0; line-height: 1;" disabled>
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>

                                <div class="collapse mt-2" id="collapseFestival_{{ $volume->id }}">
                                    <div class="d-flex flex-wrap gap-2 border p-2 bg-white rounded" style="max-height: 130px; overflow-y: auto;">
                                        @forelse($festivals as $f)
                                        <div class="form-check w-100">
                                            <input class="form-check-input variant-input" 
                                                   type="checkbox" 
                                                   name="variant_festivals[{{ $volume->id }}][]" 
                                                   value="{{ $f->id }}" 
                                                   id="var_fest_{{ $volume->id }}_{{ $f->id }}"
                                                   disabled>
                                            <label class="form-check-label small w-100 d-flex justify-content-between" for="var_fest_{{ $volume->id }}_{{ $f->id }}">
                                                <span>{{ $f->name }}</span>
                                                <strong class="text-danger">(-{{ $f->discount }}%)</strong>
                                            </label>
                                        </div>
                                        @empty
                                        <span class="text-muted small">Không có lễ hội nào.</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <div class="mt-4">
                            <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">3. Lễ hội chung toàn sản phẩm</h6>
                            <p class="small text-muted mb-2">Sẽ áp dụng cho các dung tích KHÔNG được setup Lễ hội riêng ở trên.</p>
                            <div class="d-flex flex-wrap gap-2 border p-3 bg-light rounded" style="max-height: 180px; overflow-y: auto;">
                                @forelse($festivals as $festival)
                                <div class="form-check w-100 mb-2">
                                    <input class="form-check-input" type="checkbox" name="idFestival[]" value="{{ $festival->id }}" id="fest_{{ $festival->id }}" style="cursor: pointer;">
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