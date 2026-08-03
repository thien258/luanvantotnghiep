<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SupplierOffer;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Auth;

/**
 * UserController — Quản lý người dùng trong trang admin.
 *
 * Chức năng:
 *   - index()        : Danh sách tất cả users, phân trang 20 dòng
 *   - update()       : Đổi role của user
 *   - toggleStatus() : Tắt/bật tài khoản (không xóa data)
 *
 * Lưu ý: Admin không thể tự đổi role của chính mình.
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $email = $request->input('email');
        $users = User::when($email, fn($q) => $q->where('email', 'like', "%{$email}%"))
                     ->paginate(20)
                     ->withQueryString();
        return view('admin.user.user-list', compact('users', 'email'));
    }

    public function update(User $user, Request $request)
    {
        $operator = Auth::user();

        if ($user->id === $operator->id) {
            return redirect()->back()->with('error', 'Bạn không thể đổi role của chính mình.');
        }

        $newRole = $request->input('role');

        // Director không được đổi role — chỉ có thể tắt/bật tài khoản
        if ($user->role === 'director') {
            return redirect()->back()->with('error', 'Không thể đổi role của Giám đốc. Chỉ được tắt/bật tài khoản.');
        }

        // Nếu user đang là manufacturer và muốn đổi sang role khác,
        // kiểm tra xem họ có dữ liệu NSX (báo giá / đơn đặt hàng) không.
        if ($user->role === 'manufacturer' && $newRole !== 'manufacturer') {
            $hasOffers = SupplierOffer::where('manufacturer_id', $user->id)->exists();
            $hasOrders = PurchaseOrder::where('manufacturer_id', $user->id)->exists();

            if ($hasOffers || $hasOrders) {
                return redirect()->back()->with(
                    'error',
                    "Không thể đổi role của {$user->name} vì tài khoản này đã có dữ liệu nhà sản xuất (báo giá / đơn đặt hàng). Chỉ có thể tắt/bật tài khoản."
                );
            }
        }

        // Xác định role nào operator được phép gán
        $allowedRoles = match ($operator->role) {
            'root'     => ['admin', 'warehouse', 'manufacturer', 'customer', 'director', 'root'],
            'admin'    => ['warehouse', 'manufacturer', 'customer', 'admin'],   // không được gán director, root
            'director' => ['admin'],                                          // chỉ được gán admin
            default    => [],
        };

        if (!in_array($newRole, $allowedRoles)) {
            return redirect()->back()->with('error', 'Bạn không có quyền gán role này.');
        }

        $user->role = $newRole;
        $user->save();

        return redirect()->back()->with('success', "Đã đổi quyền {$user->name} thành {$newRole}.");
    }

    /**
     * Toggle trạng thái hoạt động của user (is_active).
     *
     * Phân quyền:
     *   - Director: chỉ toggle customer, warehouse, manufacturer
     *   - Admin: toggle tất cả trừ director, root
     */
    public function toggleStatus(User $user)
    {
        $operator = Auth::user();

        if ($user->id === $operator->id) {
            return redirect()->back()->with('error', 'Bạn không thể tắt tài khoản của chính mình.');
        }

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
