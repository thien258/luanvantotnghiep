@extends('layout/home')
@section('body')
<div class="container" style="margin-top: 50px; margin-bottom: 50px;">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 text-primary"><i class="fa-solid fa-user-shield mr-2"></i>Thông tin tài khoản</h5>
                    <p class="text-muted small mb-0">Quản lý và cập nhật thông tin cá nhân của bạn</p>
                </div>

                <div class="card-body p-4">
                    {{-- Thông báo --}}
                    @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('status') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                    @endif
                    @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0" style="list-style: none; padding-left: 0;">
                            @foreach ($errors->all() as $error)
                            <li><i class="fa-solid fa-triangle-exclamation mr-2"></i> {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- Form cập nhật thông tin --}}
                    <form method="POST" action="{{ route('profile.update', $user->id) }}" class="mb-4 pb-3 border-bottom">
                        @csrf @method('PUT')
                        <div class="row align-items-center">
                            <div class="col-md-3 text-secondary font-weight-bold">Họ và Tên</div>
                            <div class="col-md-6">
                                <input type="text" name="name" class="form-control border-0 bg-light" value="{{ old('name', $user->name) }}" required>
                            </div>
                            <div class="col-md-3 text-right">
                                <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    <i class="fa fa-pencil-alt mr-1"></i> Cập nhật
                                </button>
                            </div>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('profile.update', $user->id) }}" class="mb-4 pb-3 border-bottom">
                        @csrf @method('PUT')
                        <div class="row align-items-center">
                            <div class="col-md-3 text-secondary font-weight-bold">Địa chỉ Email</div>
                            <div class="col-md-6">
                                <input type="email" name="email" class="form-control border-0 bg-light" value="{{ old('email', $user->email) }}" required>
                            </div>
                            <div class="col-md-3 text-right">
                                <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    <i class="fa fa-pencil-alt mr-1"></i> Cập nhật
                                </button>
                            </div>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('profile.update', $user->id) }}" class="mb-4 pb-3 border-bottom">
                        @csrf @method('PUT')
                        <div class="row align-items-center">
                            <div class="col-md-3 text-secondary font-weight-bold">Số điện thoại</div>
                            <div class="col-md-6">
                                <input type="text" name="phone" class="form-control border-0 bg-light" value="{{ old('phone', $user->phone) }}">
                            </div>
                            <div class="col-md-3 text-right">
                                <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    <i class="fa fa-pencil-alt mr-1"></i> Cập nhật
                                </button>
                            </div>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('profile.update', $user->id) }}" class="mb-4 pb-3 border-bottom">
                        @csrf @method('PUT')
                        <div class="row align-items-center">
                            <div class="col-md-3 text-secondary font-weight-bold">Địa chỉ nhận hàng</div>
                            <div class="col-md-6">
                                <input type="text" name="address" class="form-control border-0 bg-light" value="{{ old('address', $user->address) }}">
                            </div>
                            <div class="col-md-3 text-right">
                                <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    <i class="fa fa-pencil-alt mr-1"></i> Cập nhật
                                </button>
                            </div>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('profile.update', $user->id) }}" class="mt-2">
                        @csrf @method('PUT')
                        <div class="row align-items-center">
                            <div class="col-md-3 text-secondary font-weight-bold">Mật khẩu mới</div>
                            <div class="col-md-6">
                                <input type="password" name="password" class="form-control border-0 bg-light" placeholder="Nhập pass mới nếu muốn đổi">
                            </div>
                            <div class="col-md-3 text-right">
                                <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3">
                                    <i class="fa fa-key mr-1"></i> Đổi mật khẩu
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection