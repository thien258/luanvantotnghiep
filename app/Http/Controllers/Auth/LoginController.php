<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Sau khi xác thực thành công, kiểm tra is_active trước khi cho vào.
     * Nếu tài khoản bị tắt → đăng xuất ngay + trả lỗi.
     */
    protected function authenticated(Request $request, $user)
    {
        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Tài khoản của bạn đã bị vô hiệu hóa. Vui lòng liên hệ quản trị viên.']);
        }
    }

    /**
    
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => 'Email hoặc mật khẩu không chính xác.',
        ]);
    }

    protected function redirectTo()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $role = $user->role;

        if ($role === 'root') {
            return '/admin';
        }
        if ($role === 'admin') {
            return '/admin';
        }
        if ($role === 'warehouse') {
            return '/admin/orders';
        }
        if ($role === 'manufacturer') {
            return '/admin/supplier-offers';
        }
        if ($role === 'director') {
            return '/admin';
        }

        return '/';
    }
}
