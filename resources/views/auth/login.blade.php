<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} — Đăng nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light min-vh-100 d-flex align-items-center justify-content-center py-5">

    <div class="w-100" style="max-width:480px">

        {{-- Back --}}
        <div class="text-center mb-4">
            <a href="{{ url('/') }}" class="text-secondary text-decoration-none small">
                <i class="bi bi-arrow-left me-1"></i> Quay về trang chủ
            </a>
        </div>

        {{-- Card --}}
        <div class="card border-0 shadow-sm rounded-0 px-4 px-md-5 py-5">

            <h1 class="h3 text-center fw-semibold mb-1">Đăng nhập</h1>
            <p class="text-center text-secondary small mb-4">
                Truy cập tài khoản và khám phá bộ sưu tập của bạn.
            </p>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="mb-4">
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
                        autofocus
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="password" class="form-label fw-semibold small text-uppercase text-dark mb-0">
                            Mật khẩu
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="small text-secondary text-decoration-none">
                                Quên mật khẩu?
                            </a>
                        @endif
                    </div>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-control rounded-0 bg-light border-0 border-bottom @error('password') is-invalid @enderror"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-dark rounded-0 w-100 py-3 fw-semibold text-uppercase small tracking-wide mt-2">
                    Đăng nhập
                </button>
            </form>

            <p class="text-center small text-secondary mt-4 mb-0">
                Chưa có tài khoản?
                <a href="{{ route('register') }}" class="text-dark fw-semibold text-decoration-underline">
                    Đăng ký ngay
                </a>
            </p>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
