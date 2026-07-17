<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ManuFacturer;
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
        $validRoles = ['admin', 'warehouse', 'manufacturer', 'customer', 'director', 'root'];
        if (!in_array($newRole, $validRoles)) {
            return redirect()->back()->with('error', 'Role không hợp lệ.');
        }

        $user->role = $newRole;
        $user->save();

        // Khi set role = manufacturer → tự tạo record trong bảng manufacturers nếu chưa có
        if ($newRole === 'manufacturer') {
            ManuFacturer::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name'    => $user->name,
                    'phone'   => $user->phone ?? '',
                    'address' => $user->address ?? '',
                ]
            );
        }

        // Khi bỏ role manufacturer → hủy liên kết (xóa user_id, giữ record NSX)
        if ($newRole !== 'manufacturer') {
            ManuFacturer::where('user_id', $user->id)->update(['user_id' => null]);
        }

        return redirect()->back()->with('success', "Đã đổi quyền {$user->name} thành {$newRole}.");
    }

    /**
     * Toggle trạng thái hoạt động của user (is_active).
     * Không xóa dữ liệu — chỉ tắt/bật tài khoản.
     *
     * Phân quyền:
     *   - Không thể tự tắt chính mình
     *   - Director: chỉ được toggle customer, warehouse, manufacturer
     *   - Admin: được toggle tất cả trừ director, root
     */
    public function toggleStatus(User $user)
    {
        $operator = Auth::user();

        // Không tự tắt chính mình
        if ($user->id === $operator->id) {
            return redirect()->back()->with('error', 'Bạn không thể tắt tài khoản của chính mình.');
        }

        // Danh sách role mà operator được phép toggle
        $allowedTargets = match ($operator->role) {
            'director' => ['customer', 'warehouse', 'manufacturer'],
            'admin'    => ['customer', 'warehouse', 'manufacturer', 'admin'],
            'root'     => ['customer', 'warehouse', 'manufacturer', 'admin', 'director'],
            default    => [],
        };

        if (!in_array($user->role, $allowedTargets)) {
            return redirect()->back()->with('error', 'Bạn không có quyền thay đổi trạng thái tài khoản này.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'kích hoạt' : 'vô hiệu hóa';
        return redirect()->back()->with('success', "Đã {$status} tài khoản {$user->name}.");
    }
}
