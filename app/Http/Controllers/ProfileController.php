<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/**
 * ProfileController — Quản lý hồ sơ cá nhân của người dùng.
 *
 * Chức năng chính:
 *   - index()  : Hiển thị thông tin cá nhân (tự động chọn view theo role)
 *   - update() : Cập nhật tên, email, SĐT, địa chỉ, mật khẩu
 *
 * Bảo mật: tất cả route trong controller này yêu cầu đăng nhập (middleware auth trong __construct).
 */
class ProfileController extends Controller
{
    // Yêu cầu đăng nhập cho tất cả action của controller này
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Trang xem hồ sơ.
     * Admin/warehouse/manufacturer → dùng layout admin.profile
     * Khách hàng thường → dùng view profile
     */
    public function index()
    {
        $user = Auth::user();

        // Admin/warehouse/manufacturer/director dùng layout admin
        if (in_array($user->role, ['admin', 'warehouse', 'manufacturer', 'director'])) {
            return view('admin.profile', compact('user'));
        }

        return view('profile', compact('user'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Cập nhật thông tin cá nhân.
     *
     * Lưu ý bảo mật:
     *   - Email unique loại trừ chính user hiện tại (tránh báo lỗi khi giữ nguyên email)
     *   - Mật khẩu chỉ cập nhật khi user điền vào (filled), dùng bcrypt để hash
     *   - Không cho user tự đổi role
     */
    public function update(Request $request, string $id)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'name'     => 'required|string|max:255',
            // unique bỏ qua chính user này để không báo lỗi khi email không đổi
            'email'    => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone'    => 'nullable|string|max:20',
            'address'  => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8',
        ], [
            'email.unique'    => 'Email này đã có người khác sử dụng.',
            'email.email'     => 'Địa chỉ email không đúng định dạng.',
            'name.required'   => 'Họ và tên không được để trống.',
            'password.min'    => 'Mật khẩu mới phải từ 8 ký tự trở lên.',
        ]);

        // Chỉ cập nhật các trường cho phép (không bao gồm role)
        $data = [
            'name'    => $request->name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'address' => $request->address,
        ];

        // Chỉ hash và cập nhật mật khẩu khi user có điền vào ô mật khẩu mới
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return redirect()->route('profile.index')->with('status', 'Cập nhật thông tin thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
