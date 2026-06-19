@extends('layout.admin')

@section('body')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Quản Lý Nhà Sản Xuất</h1>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách Nhà sản xuất</h6>
            <a href="{{ route('admin.manufacturer.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Thêm mới</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên Nhà Sản Xuất</th>
                            <th>Số Điện Thoại</th>
                            <th>Địa Chỉ Nhà Máy</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($manufacturers as $nsx)
                        <tr>
                            <td>{{ $nsx->id }}</td>
                            <td><strong>{{ $nsx->name }}</strong></td>
                            <td>{{ $nsx->phone ?? 'Chưa cập nhật' }}</td>
                            <td>{{ $nsx->address ?? 'Chưa cập nhật' }}</td>
                            <td>
                                <a href="{{ route('admin.manufacturer.edit', $nsx->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Sửa</a>
                                <form action="{{ route('admin.manufacturer.destroy', $nsx->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa không?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Xóa</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection