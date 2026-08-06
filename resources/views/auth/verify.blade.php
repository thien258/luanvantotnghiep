<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Verify Email</title>
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <div class="collapse navbar-collapse">
                    <ul class="navbar-nav ms-auto">
                        @auth
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ Auth::user()->name }}
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('logout') }}">Đăng xuất</a>
                            </div>
                        </li>
                        @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Đăng nhập</a>
                        </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">XÁC NHẬN EMAIL CỦA BẠN</div>

                            <div class="card-body">
                                {{-- Thông báo lỗi khi bị chặn đăng nhập vì chưa verify --}}
                                @if ($errors->any())
                                    <div class="alert alert-warning" role="alert">
                                        {{ $errors->first('email') }}
                                    </div>
                                @endif

                                @if (session('resent'))
                                    <div class="alert alert-success" role="alert">
                                        Link xác nhận mới đã được gửi đến email của bạn.
                                    </div>
                                @endif

                                <p>Trước khi tiếp tục, vui lòng kiểm tra email của bạn để tìm liên kết xác nhận.</p>

                                @if (session('resend_email'))
                                    <p class="text-muted">Email đã gửi đến: <strong>{{ session('resend_email') }}</strong></p>
                                @endif

                                @auth
                                {{-- Nếu đang đăng nhập (trường hợp chưa verify mà vào thẳng) thì cho gửi lại --}}
                                <p>
                                    Nếu bạn không nhận được email,
                                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">nhấn vào đây để gửi lại</button>.
                                    </form>
                                </p>
                                @else
                                <p>
                                    Hãy <a href="{{ route('login') }}">đăng nhập lại</a> sau khi xác nhận email,
                                    hoặc <a href="{{ route('register') }}">đăng ký tài khoản mới</a>.
                                </p>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
