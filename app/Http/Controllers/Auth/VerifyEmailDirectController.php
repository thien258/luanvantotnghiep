<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Xác minh email không cần session — mở được trên mọi thiết bị/trình duyệt.
 *
 * Laravel mặc định dùng route /email/verify/{id}/{hash} kèm middleware 'auth'
 * → user phải đang đăng nhập trên cùng trình duyệt mới verify được.
 *
 * Controller này dùng signed URL (id + hash embed trong URL) nên không cần
 * session/cookie → bấm link trên điện thoại vẫn verify được bình thường.
 */
class VerifyEmailDirectController extends Controller
{
    public function __invoke(Request $request, $id, $hash)
    {
        // Tìm user theo id
        $user = User::findOrFail($id);

        // Kiểm tra hash khớp với email của user
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Liên kết xác minh không hợp lệ.');
        }

        // Nếu đã verify rồi thì redirect luôn
        if ($user->hasVerifiedEmail()) {
            // Nếu đang đăng nhập sẵn → về trang chủ / dashboard
            if (Auth::check()) {
                return redirect()->intended('/')->with('status', 'Email của bạn đã được xác minh trước đó.');
            }
            return redirect()->route('login')->with('status', 'Email đã được xác minh. Vui lòng đăng nhập.');
        }

        // Đánh dấu email đã được xác minh
        $user->markEmailAsVerified();

        // Nếu đang đăng nhập sẵn trên thiết bị này → redirect về trang phù hợp
        if (Auth::check() && Auth::id() === $user->id) {
            return redirect()->intended('/')->with('verified', true);
        }

        // Không đăng nhập (mở link trên thiết bị khác) → redirect về login
        return redirect()->route('login')
            ->with('status', 'Email đã được xác minh thành công! Vui lòng đăng nhập để tiếp tục.');
    }
}
