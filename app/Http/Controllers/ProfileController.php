<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        //
        $user = Auth::user();
      
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        /** @var User $user */
      $user = Auth::user();
      $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:users,email,' . $user->id, // Loại trừ ID của chính mình
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:255',
            'password' => 'sometimes|nullable|string|min:8',
        ], [
            'email.unique' => 'Email này đã có người khác sử dụng, vui lòng chọn email khác!',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'name.required' => 'Họ và tên không được để trống.',
            'password.min' => 'Mật khẩu mới phải từ 8 ký tự trở lên.',
        ]);
      $data = [];

        // Kiểm tra xem form nào được gửi lên thì mới nhét vào mảng $data
        if ($request->has('name'))     $data['name'] = $request->name;
        if ($request->has('email'))    $data['email'] = $request->email;
        if ($request->has('phone'))    $data['phone'] = $request->phone;
        if ($request->has('address'))  $data['address'] = $request->address;

        // Riêng mật khẩu thì kiểm tra xem có gõ chữ nào không
        if ($request->has('password') && $request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        // Chỉ cập nhật những trường thực sự được gửi lên
        $user->update($data);
      return redirect()->route('profile.index')->with('status', 'Cập nhật thông tin cá nhân thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
