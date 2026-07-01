@extends('layout/admin')
@section('body')

<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">

            <h5 class="fw-bold mb-4 text-dark text-uppercase" style="letter-spacing:1px;">
                <i class="fa-regular fa-circle-user me-2"></i>Trang cá nhân
            </h5>

            @if(session('status'))
            <div class="alert alert-success alert-dismissible rounded-0 border-0 border-start border-success border-4 small">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger rounded-0 small">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- CARD THÔNG TIN ĐỀ TÀI --}}
            <div class="card rounded-0 border shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <span class="fw-bold small text-uppercase text-muted">
                        <i class="fa-solid fa-id-card me-1"></i> Thông tin cá nhân
                    </span>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('profile.update', $user->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-muted">Họ và Tên</label>
                            <input type="text" name="name"
                                class="form-control form-control-sm rounded-0 @error('name') is-invalid @enderror"
                                value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-muted">Email</label>
                            <input type="email" name="email"
                                class="form-control form-control-sm rounded-0 @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-uppercase text-muted">Số điện thoại</label>
                                <input type="text" name="phone"
                                    class="form-control form-control-sm rounded-0"
                                    value="{{ old('phone', $user->phone) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-uppercase text-muted">Địa chỉ</label>
                                <input type="text" name="address"
                                    class="form-control form-control-sm rounded-0"
                                    value="{{ old('address', $user->address) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-muted">
                                Mật khẩu mới
                                <span class="text-muted fw-normal">(để trống nếu không đổi)</span>
                            </label>
                            <input type="password" name="password"
                                class="form-control form-control-sm rounded-0 @error('password') is-invalid @enderror"
                                placeholder="Tối thiểu 8 ký tự">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <small class="text-muted">
                                Role hiện tại: <span class="badge bg-dark rounded-0">{{ $user->role }}</span>
                            </small>
                            <button type="submit" class="btn btn-dark btn-sm rounded-0 px-4">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
