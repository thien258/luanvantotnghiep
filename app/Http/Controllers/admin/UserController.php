<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    //
    public function index()
    {
        $users = User::paginate(20);
        return view('admin.user.user-list', compact('users'));
    }
    public function update(User $user)
    {


        if ($user->id === Auth::id()) {
            return redirect()->back();
        }

        $user->role = $user->role === 'admin' ? 'customer' : 'admin';
        $user->save();

        return redirect()->back();
    }
    public function destroy($id)
    {
        $user = User::find($id);

        if ($user) {
            // 1. Duyệt qua từng đơn hàng của user
            foreach ($user->orders as $order) {
                // Sửa lỗi hiện tại: Xóa hết chi tiết của đơn hàng này trước
                // (Đảm bảo Model Order đã có relationship 'details' hoặc 'orderDetails')
                $order->details()->delete();
            }

            // 2. Sau khi các chi tiết đơn hàng đã sạch, xóa các đơn hàng
            $user->orders()->delete();

            // 3. Cuối cùng là xóa user
            $user->delete();

            return redirect()->route('admin.user.index');
        }

        return back();
    }
}
