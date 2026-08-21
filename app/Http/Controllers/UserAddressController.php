<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAddressController extends Controller
{
    // Lấy danh sách địa chỉ (AJAX)
    // Nếu bảng trống thì tự seed từ profile user
    public function index()
    {
        $user = Auth::user();

        $count = UserAddress::where('idUser', $user->id)->count();

        if ($count === 0 && ($user->phone || $user->address)) {
            UserAddress::create([
                'idUser'     => $user->id,
                'name'       => $user->name,
                'phone'      => $user->phone ?? '',
                'address'    => $user->address ?? '',
                'is_default' => true,
            ]);
        }

        $addresses = UserAddress::where('idUser', $user->id)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($addresses);
    }

    // Thêm địa chỉ mới
    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:100',
            'phone'   => ['required', 'regex:/^0\d{9}$/'],
            'address' => 'required|string|max:255',
        ], [
            'name.required'    => 'Vui lòng nhập họ tên người nhận.',
            'name.max'         => 'Họ tên không được vượt quá 100 ký tự.',
            'phone.required'   => 'Vui lòng nhập số điện thoại.',
            'phone.regex'      => 'Số điện thoại phải gồm đúng 10 chữ số và bắt đầu bằng số 0.',
            'address.required' => 'Vui lòng nhập địa chỉ.',
            'address.max'      => 'Địa chỉ không được vượt quá 255 ký tự.',
        ]);

        if ($request->boolean('is_default')) {
            UserAddress::where('idUser', Auth::id())->update(['is_default' => false]);
        }

        $addr = UserAddress::create([
            'idUser'     => Auth::id(),
            'name'       => $request->name,
            'phone'      => $request->phone,
            'address'    => $request->address,
            'is_default' => $request->boolean('is_default'),
        ]);

        return response()->json(['success' => true, 'address' => $addr]);
    }

    // Cập nhật địa chỉ
    public function update(Request $request, $id)
    {
        $addr = UserAddress::where('id', $id)->where('idUser', Auth::id())->firstOrFail();

        $request->validate([
            'name'    => 'required|string|max:100',
            'phone'   => ['required', 'regex:/^0\d{9}$/'],
            'address' => 'required|string|max:255',
        ], [
            'name.required'    => 'Vui lòng nhập họ tên người nhận.',
            'name.max'         => 'Họ tên không được vượt quá 100 ký tự.',
            'phone.required'   => 'Vui lòng nhập số điện thoại.',
            'phone.regex'      => 'Số điện thoại phải gồm đúng 10 chữ số và bắt đầu bằng số 0.',
            'address.required' => 'Vui lòng nhập địa chỉ.',
            'address.max'      => 'Địa chỉ không được vượt quá 255 ký tự.',
        ]);

        if ($request->boolean('is_default')) {
            UserAddress::where('idUser', Auth::id())->update(['is_default' => false]);
        }

        $addr->update([
            'name'       => $request->name,
            'phone'      => $request->phone,
            'address'    => $request->address,
            'is_default' => $request->boolean('is_default') || $addr->is_default,
        ]);

        return response()->json(['success' => true, 'address' => $addr]);
    }

    // Xóa địa chỉ
    public function destroy($id)
    {
        $addr = UserAddress::where('id', $id)->where('idUser', Auth::id())->firstOrFail();
        $wasDefault = $addr->is_default;
        $addr->delete();

        if ($wasDefault) {
            $next = UserAddress::where('idUser', Auth::id())->latest()->first();
            if ($next) $next->update(['is_default' => true]);
        }

        return response()->json(['success' => true]);
    }

    // Đặt làm địa chỉ mặc định
    public function setDefault($id)
    {
        UserAddress::where('idUser', Auth::id())->update(['is_default' => false]);
        UserAddress::where('id', $id)->where('idUser', Auth::id())->update(['is_default' => true]);

        return response()->json(['success' => true]);
    }
}
