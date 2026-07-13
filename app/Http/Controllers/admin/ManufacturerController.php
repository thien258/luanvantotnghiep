<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ManuFacturer;

/**
 * ManufacturerController — Quản lý danh sách nhà sản xuất.
 *
 * Luồng tạo NSX:
 *   NSX tự đăng ký tài khoản bình thường → admin vào trang User đổi role = 'manufacturer'
 *   → UserController::update() tự động tạo record Manufacturer và liên kết user_id
 *
 * Controller này chỉ xử lý:
 *   - Xem danh sách NSX
 *   - Sửa thông tin NSX (tên, SĐT, địa chỉ)
 *   - Xóa NSX
 */
class ManufacturerController extends Controller
{
    /**
     * Danh sách tất cả nhà sản xuất.
     */
    public function index()
    {
        $manufacturers = ManuFacturer::all();
        return view('admin.manufacturer.manufacturer-list', compact('manufacturers'));
    }

    /**
     * Cập nhật thông tin NSX (tên, SĐT, địa chỉ).
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $manufacturer = ManuFacturer::find($id);

        if (!$manufacturer) {
            return redirect()->route('admin.manufacturer.index')->with('error', 'Manufacturer not found.');
        }

        $manufacturer->update([
            'name'    => $request->name,
            'phone'   => $request->phone,
            'address' => $request->address,
        ]);

        return redirect()->route('admin.manufacturer.index')->with('success', 'Cập nhật NSX thành công.');
    }

    /**
     * Xóa nhà sản xuất.
     * Lưu ý: không xóa User liên kết — chỉ xóa record Manufacturer.
     */
    public function destroy(string $id)
    {
        $manufacturer = ManuFacturer::find($id);

        if ($manufacturer) {
            $manufacturer->delete();
            return redirect()->route('admin.manufacturer.index')->with('success', 'Đã xóa NSX thành công.');
        }

        return redirect()->route('admin.manufacturer.index')->with('error', 'Manufacturer not found.');
    }
}
