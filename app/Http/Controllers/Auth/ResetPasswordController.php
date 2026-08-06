<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Hash; // PHẢI CÓ DÒNG NÀY ĐỂ DÙNG Hash
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/login';
    protected function resetPassword($user, $password)
    {
     
        /** @var \App\Models\User $user */
        // Đặt lại mật khẩu cho user
      $user->update([
        'password' => Hash::make($password),
        'remember_token' => Str::random(60),
    ]);

      

        // Hiện thông báo thành công cho người dùng thấy
        session()->flash('status', 'Mật khẩu đã được đổi thành công. Vui lòng đăng nhập lại!');
    }
}
