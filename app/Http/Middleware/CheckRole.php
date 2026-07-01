<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckRole — Middleware kiểm tra quyền truy cập theo role.
 *
 * Cách dùng trong routes:
 *   ->middleware('role:admin')           — chỉ admin
 *   ->middleware('role:admin,staff')     — admin hoặc staff
 *   ->middleware('role:admin,staff,nsx') — admin, staff hoặc nsx
 *
 * Nếu chưa đăng nhập → redirect về trang login.
 * Nếu đã đăng nhập nhưng không đúng role → 403 Forbidden.
 */
class CheckRole
{
    /**
     * Xử lý request: kiểm tra authentication rồi kiểm tra role.
     *
     * @param  string  ...$roles  Danh sách role được phép (truyền qua middleware parameter)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Chưa đăng nhập → chuyển về trang login thay vì báo 403
        // (tránh lộ thông tin về sự tồn tại của trang admin)
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Kiểm tra role của user hiện tại có nằm trong danh sách cho phép không
        // in_array dùng strict mode mặc định → so sánh chính xác chuỗi
        if (!in_array(Auth::user()->role, $roles)) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        // Đủ điều kiện → cho request đi tiếp
        return $next($request);
    }
}
