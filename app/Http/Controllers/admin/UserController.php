<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * UserController — Quản lý người dùng trong trang admin.
 *
 * Chức năng:
 *   - index()   : Danh sách tất cả users, phân trang 20 dòng
 *   - update()  : Đổi role của user (admin/warehouse/manufacturer/customer)
 *   - destroy() : Xóa user kèm toàn bộ đơn hàng và chi tiết đơn hàng liên quan
 *
 * Lưu ý: Admin không thể tự đổi role của chính mình (tránh vô tình tự lock ra khỏi admin).
 */
class UserController extends Controller
{
    // Liệt kê toàn bộ user, phân trang 20 dòng/trang
    public function index()
    {
        $users = User::paginate(20);
        return view('admin.user.user-list', compact('users'));
    }

    /**
     * Đổi role của user được chọn.
     *
     * Bảo mật:
     *   - Không cho admin tự đổi role của chính mình
     *   - Validate role phải nằm trong danh sách hợp lệ
     */
    public function update(User $user, Request $request)
    {
        // Chặn admin tự đổi role của chính mình
        if ($user->id === Auth::id()) {
            return redirect()->back();
        }

        $newRole = $request->input('role');

        // Chỉ chấp nhận các role hợp lệ
        $validRoles = ['admin', 'warehouse', 'manufacturer', 'customer'];
        if (!in_array($newRole, $validRoles)) {
            return redirect()->back()->with('error', 'Role không hợp lệ.');
        }

        $user->role = $newRole;
        $user->save();

        return redirect()->back()->with('success', "Đã đổi quyền {$user->name} thành {$newRole}.");
    }

    /**
     * Xóa user và tất cả dữ liệu liên quan.
     *
     * Thứ tự xóa (phải xóa FK trước để tránh lỗi constraint):
     *   1. Xóa order_details của từng đơn hàng
     *   2. Xóa các đơn hàng của user
     *   3. Xóa user
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if ($user) {
            // Bước 1: Duyệt từng đơn hàng, xóa chi tiết trước (tránh lỗi FK constraint)
            foreach ($user->orders as $order) {
                // Xóa hết chi tiết của đơn hàng này trước
                // (Đảm bảo Model Order đã có relationship 'details' hoặc 'orderDetails')
                $order->details()->delete();
            }

            // Bước 2: Sau khi chi tiết đã xóa, xóa các đơn hàng
            $user->orders()->delete();

            // Bước 3: Cuối cùng xóa user
            $user->delete();

            return redirect()->route('admin.user.index');
        }

        return back();
    }
}
