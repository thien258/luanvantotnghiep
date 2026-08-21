@extends('layout/home')
@section('body')
<div class="container" style="margin-top: 50px; margin-bottom: 50px;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-user-shield me-2 text-dark"></i>Thông tin tài khoản</h5>
                    <p class="text-muted small mb-0">Chỉnh sửa thông tin rồi nhấn Lưu thay đổi</p>
                </div>

                <div class="card-body p-4">

                    {{-- Thông báo --}}
                    @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show rounded-0">
                        <i class="fa-solid fa-circle-check me-2"></i>{{ session('status') }}
                    </div>
                    @endif

                    @if ($errors->any())
                    <div class="alert alert-danger rounded-0">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- 1 form duy nhất --}}
                    <form method="POST" action="{{ route('profile.update', $user->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Họ và Tên</label>
                            <input type="text" name="name"
                                class="form-control rounded-0 bg-light border-0 border-bottom border-dark"
                                value="{{ old('name', $user->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Địa chỉ Email</label>
                            <input type="email" name="email"
                                class="form-control rounded-0 bg-light border-0 border-bottom border-dark"
                                value="{{ old('email', $user->email) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Số điện thoại</label>
                            <input type="text" name="phone"
                                class="form-control rounded-0 bg-light border-0 border-bottom border-dark"
                                value="{{ old('phone', $user->phone) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Địa chỉ nhận hàng</label>
                            <input type="text" name="address"
                                class="form-control rounded-0 bg-light border-0 border-bottom border-dark"
                                value="{{ old('address', $user->address) }}">
                        </div>

                        <hr class="my-4">

                        <div class="mb-4">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Mật khẩu mới <span class="text-muted fw-normal">(để trống nếu không đổi)</span></label>
                            <input type="password" name="password"
                                class="form-control rounded-0 bg-light border-0 border-bottom border-dark"
                                placeholder="Tối thiểu 8 ký tự">
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-dark rounded-0 px-5">
                                <i class="fa-solid fa-floppy-disk me-2"></i>Lưu thay đổi
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
