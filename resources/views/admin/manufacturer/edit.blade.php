@extends('layout.admin')

@section('body')
<div class="mt-4">
    <h5 class="font-weight-bold text-dark mb-4">
        <i class="fa-solid fa-building mr-2 text-muted"></i>
        Cập nhật NSX: <span class="text-primary">{{ $mainufacturer->name }}</span>
    </h5>

    @if(session('success'))
        <div class="alert alert-success rounded-0">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger rounded-0">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger rounded-0">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Form cập nhật thông tin NSX --}}
    <div class="card shadow-none border rounded-0 mb-4">
        <div class="card-header bg-white py-2 border-bottom">
            <span class="small font-weight-bold text-uppercase text-muted">Thông tin NSX</span>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.manufacturer.update', $mainufacturer->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="small font-weight-bold">Tên Nhà sản xuất <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control rounded-0" value="{{ $mainufacturer->name }}" required>
                </div>
                <div class="form-group">
                    <label class="small font-weight-bold">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control rounded-0" value="{{ $mainufacturer->phone }}">
                </div>
                <div class="form-group">
                    <label class="small font-weight-bold">Địa chỉ</label>
                    <textarea name="address" class="form-control rounded-0" rows="3">{{ $mainufacturer->address }}</textarea>
                </div>
                <button type="submit" class="btn btn-dark rounded-0">Cập nhật</button>
                <a href="{{ route('admin.manufacturer.index') }}" class="btn btn-outline-secondary rounded-0 ml-2">Hủy</a>
            </form>
        </div>
    </div>

    {{-- Tài khoản đăng nhập --}}
    <div class="card shadow-none border rounded-0 mb-4">
        <div class="card-header bg-white py-2 border-bottom">
            <span class="small font-weight-bold text-uppercase text-muted">
                <i class="fa-solid fa-user mr-1"></i> Tài khoản đăng nhập
            </span>
        </div>
        <div class="card-body">
            @if($mainufacturer->user)
                {{-- Đã có tài khoản --}}
                <div class="alert alert-success rounded-0 small mb-0">
                    <i class="fa-solid fa-check-circle mr-1"></i>
                    NSX này đã có tài khoản:
                    <strong>{{ $mainufacturer->user->email }}</strong>
                    — Role: <span class="badge badge-primary">{{ $mainufacturer->user->role }}</span>
                </div>
            @else
                {{-- Chưa có tài khoản — hiện form tạo --}}
                <p class="small text-muted mb-3">NSX này chưa có tài khoản đăng nhập. Tạo tài khoản để họ có thể upload báo giá.</p>
                <form action="{{ route('admin.manufacturer.create-account', $mainufacturer->id) }}" method="POST">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <label class="small font-weight-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control rounded-0" placeholder="email@example.com" required>
                        </div>
                        <div class="form-group col-md-5">
                            <label class="small font-weight-bold">Mật khẩu <span class="text-danger">*</span></label>
                            <input type="text" name="password" class="form-control rounded-0" placeholder="Tối thiểu 8 ký tự" required minlength="8">
                        </div>
                        <div class="form-group col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-dark rounded-0 w-100">
                                <i class="fa-solid fa-user-plus mr-1"></i> Tạo TK
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
