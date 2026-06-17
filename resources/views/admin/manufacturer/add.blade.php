@extends('layout.admin')

@section('body')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Quản Lý Nhà Sản Xuất</h1>

    @if(session('error'))
        <div class="alert alert-danger shadow-sm">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow mb-4 border-left-primary">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-plus-circle"></i> Thêm Nhà Sản Xuất Mới</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.manufacturer.store') }}" method="POST">
                @csrf
                
                <div class="form-group row">
                    <div class="col-sm-6 mb-3 mb-sm-0">
                        <label class="font-weight-bold text-dark">Tên nhà sản xuất <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-user" placeholder="Ví dụ: Nhà máy Grasse, Công ty Thủy tinh Milan..." value="{{ old('name') }}" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="font-weight-bold text-dark">Số điện thoại hotline</label>
                        <input type="text" name="phone" class="form-control form-control-user" placeholder="Ví dụ: 0901234567" value="{{ old('phone') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold text-dark">Địa chỉ chi tiết nhà máy</label>
                    <textarea name="address" class="form-control" rows="3" placeholder="Nhập địa chỉ trụ sở hoặc xưởng sản xuất...">{{ old('address') }}</textarea>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary btn-icon-split shadow-sm">
                        <span class="icon text-white-50">
                            <i class="fas fa-save"></i>
                        </span>
                        <span class="text">Lưu nhà sản xuất</span>
                    </button>
                    
                    <a href="{{ route('admin.manufacturer.index') }}" class="btn btn-light btn-icon-split ml-2 border">
                        <span class="text text-muted">Hủy bỏ</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection