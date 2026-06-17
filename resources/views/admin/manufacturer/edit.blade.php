@extends('layout.admin')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Cập Nhật Nhà Sản Xuất</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.manufacturer.update', $mainufacturer->id) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Tên Nhà sản xuất:</label>
                    <input type="text" name="name" class="form-control" value="{{ $mainufacturer->name }}" required>
                </div>
                <div class="form-group">
                    <label>Số điện thoại hỗ trợ:</label>
                    <input type="text" name="phone" class="form-control" value="{{ $mainufacturer->phone }}">
                </div>
                <div class="form-group">
                    <label>Địa chỉ nhà máy:</label>
                    <textarea name="address" class="form-control" rows="3">{{ $mainufacturer->address }}</textarea>
                </div>
                
                <button type="submit" class="btn btn-success">Cập nhật ngay</button>
                <a href="{{ route('admin.manufacturer.index') }}" class="btn text-secondary">Hủy bỏ</a>
            </form>
        </div>
    </div>
</div>
@endsection