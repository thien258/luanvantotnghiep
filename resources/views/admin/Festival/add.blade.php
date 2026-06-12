@extends('layout/admin')
@section('body')

<div class="container-fluid pt-4 px-4">
    <div class="bg-light rounded p-4" style="max-width: 600px;">

        <h5 class="fw-bold mb-4 border-bottom pb-3">Thêm Sự Kiện Mới</h5>

        {{-- Hiển thị lỗi validation --}}
        @if($errors->any())
        <div class="alert alert-danger rounded-0 mb-4">
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('admin.festival.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-bold">Tên sự kiện <span class="text-danger">*</span></label>
                <input type="text" class="form-control rounded-0" name="name"
                       value="{{ old('name') }}"
                       placeholder="Ví dụ: Tết Nguyên Đán, Giáng Sinh...">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Giảm giá (%) <span class="text-danger">*</span></label>
                <input type="number" class="form-control rounded-0" name="discount"
                       value="{{ old('discount') }}"
                       min="0" max="100"
                       placeholder="Nhập phần trăm giảm giá, ví dụ: 10, 20...">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="form-label fw-bold">Ngày bắt đầu <span class="text-danger">*</span></label>
                    <input type="date" class="form-control rounded-0" name="start_date"
                           value="{{ old('start_date') }}">
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold">Ngày kết thúc <span class="text-danger">*</span></label>
                    <input type="date" class="form-control rounded-0" name="end_date"
                           value="{{ old('end_date') }}">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Trạng thái</label>
                <select name="status" class="form-select rounded-0">
                    <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Bật (ON)</option>
                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Tắt (OFF)</option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-0 px-4">Lưu sự kiện</button>
                <a href="{{ route('admin.festival.index') }}" class="btn btn-secondary rounded-0 px-4">Quay lại</a>
            </div>
        </form>

    </div>
</div>

@endsection
