<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} — Đăng ký</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light min-vh-100 d-flex align-items-center justify-content-center py-5">

    {{-- Loading overlay --}}
    <div id="loading-overlay" class="d-none position-fixed top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex flex-column align-items-center justify-content-center" style="z-index:9999">
        <div class="spinner-border text-dark mb-3" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="small text-secondary mb-0">Đang xử lý, vui lòng đợi...</p>
    </div>

    <div class="w-100" style="max-width:600px">

        {{-- Back --}}
        <div class="text-center mb-4">
            <a href="{{ url('/') }}" class="text-secondary text-decoration-none small">
                <i class="bi bi-arrow-left me-1"></i> Quay về trang chủ
            </a>
        </div>

        {{-- Card --}}
        <div class="card border-0 shadow-sm rounded-0 px-4 px-md-5 py-5">

            <h1 class="h3 text-center fw-semibold mb-1">Tạo tài khoản</h1>
            <p class="text-center text-secondary small mb-4">
                Tạo tài khoản để trải nghiệm mua sắm được cá nhân hóa.
            </p>

            <form method="POST" action="{{ route('register') }}" id="register-form">
                @csrf

                {{-- Row 1: Name + Email --}}
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label for="name" class="form-label fw-semibold small text-uppercase text-dark">
                            Họ và tên
                        </label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            class="form-control rounded-0 bg-light border-0 border-bottom @error('name') is-invalid @enderror"
                            value="{{ old('name') }}"
                            placeholder="Nguyễn Văn A"
                            required
                            autocomplete="name"
                            autofocus
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="email" class="form-label fw-semibold small text-uppercase text-dark">
                            Địa chỉ Email
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            class="form-control rounded-0 bg-light border-0 border-bottom @error('email') is-invalid @enderror"
                            value="{{ old('email') }}"
                            placeholder="email@example.com"
                            required
                            autocomplete="email"
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Row 2: Phone + Address --}}
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label for="phone" class="form-label fw-semibold small text-uppercase text-dark">
                            Số điện thoại
                        </label>
                        <input
                            id="phone"
                            type="text"
                            name="phone"
                            class="form-control rounded-0 bg-light border-0 border-bottom @error('phone') is-invalid @enderror"
                            value="{{ old('phone') }}"
                            placeholder="0901 234 567"
                            required
                            autocomplete="tel"
                        >
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="address" class="form-label fw-semibold small text-uppercase text-dark">
                            Địa chỉ
                        </label>
                        <input
                            id="address"
                            type="text"
                            name="address"
                            class="form-control rounded-0 bg-light border-0 border-bottom @error('address') is-invalid @enderror"
                            value="{{ old('address') }}"
                            placeholder="123 Đường ABC, Hà Nội"
                            required
                            autocomplete="street-address"
                        >
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Row 3: Password + Confirm --}}
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label for="password" class="form-label fw-semibold small text-uppercase text-dark">
                            Tạo mật khẩu
                        </label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="form-control rounded-0 bg-light border-0 border-bottom @error('password') is-invalid @enderror"
                            placeholder="••••••••"
                            required
                            autocomplete="new-password"
                        >
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="password-confirm" class="form-label fw-semibold small text-uppercase text-dark">
                            Xác nhận mật khẩu
                        </label>
                        <input
                            id="password-confirm"
                            type="password"
                            name="password_confirmation"
                            class="form-control rounded-0 bg-light border-0 border-bottom"
                            placeholder="••••••••"
                            required
                            autocomplete="new-password"
                        >
                    </div>
                </div>

            

                <button type="submit" class="btn btn-dark rounded-0 w-100 py-3 fw-semibold text-uppercase small mt-1" id="register-btn">
                    Tạo tài khoản
                </button>
            </form>

            <p class="text-center small text-secondary mt-4 mb-0">
                Đã có tài khoản?
                <a href="{{ route('login') }}" class="text-dark fw-semibold text-decoration-underline">
                    Đăng nhập
                </a>
            </p>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('register-form').addEventListener('submit', function () {
            const overlay = document.getElementById('loading-overlay');
            overlay.classList.remove('d-none');
            overlay.classList.add('d-flex');
        });
    </script>
</body>
</html>
