<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ManuFacturer;

class ManufacturerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $manufacturers = ManuFacturer::all();
        return view('admin.manufacturer.manufacturer-list', compact('manufacturers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('admin.manufacturer.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'nullable|string|max:20',
            'address'  => 'nullable|string|max:500',
            'email'    => 'nullable|email|unique:users,email',
            'password' => 'nullable|min:8',
        ], [
            'email.unique' => 'Email này đã được dùng cho tài khoản khác.',
        ]);

        $manufacturer = ManuFacturer::create([
            'name'    => $request->name,
            'phone'   => $request->phone,
            'address' => $request->address,
        ]);

        // Tạo tài khoản nếu email được điền
        if ($request->filled('email') && $request->filled('password')) {
            $user = \App\Models\User::create([
                'name'              => $request->name,
                'email'             => $request->email,
                'phone'             => $request->phone ?? '',
                'address'           => $request->address ?? '',
                'password'          => bcrypt($request->password),
                'role'              => 'manufacturer',
                'email_verified_at' => now(),
            ]);
            $manufacturer->update(['user_id' => $user->id]);
        }

        return redirect()->route('admin.manufacturer.index')
            ->with('success', 'Đã tạo NSX' . ($request->filled('email') ? ' và tài khoản đăng nhập' : '') . ' thành công.');
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
        $mainufacturer = ManuFacturer::with('user')->find($id);
        if (!$mainufacturer) {
            return redirect()->route('admin.manufacturer.index')->with('error', 'Manufacturer not found.');
        }
        return view('admin.manufacturer.edit', compact('mainufacturer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        // Đã sửa thành find() giống hàm edit
        $manufacturer = ManuFacturer::find($id);

        if (!$manufacturer) {
            return redirect()->route('admin.manufacturer.index')->with('error', 'Manufacturer not found.');
        }

        $manufacturer->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return redirect()->route('admin.manufacturer.index')->with('success', 'Manufacturer updated successfully.');
    }

    /**
     * Tạo tài khoản đăng nhập cho NSX và liên kết với manufacturer.
     */
    public function createAccount(Request $request, string $id)
    {
        $manufacturer = ManuFacturer::findOrFail($id);

        // Nếu đã có tài khoản rồi thì không tạo thêm
        if ($manufacturer->user_id) {
            return back()->with('error', 'NSX này đã có tài khoản đăng nhập rồi.');
        }

        $request->validate([
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ], [
            'email.unique' => 'Email này đã được dùng cho tài khoản khác.',
        ]);

        $user = \App\Models\User::create([
            'name'               => $manufacturer->name,
            'email'              => $request->email,
            'phone'              => $manufacturer->phone ?? '',
            'address'            => $manufacturer->address ?? '',
            'password'           => bcrypt($request->password),
            'role'               => 'manufacturer',
            'email_verified_at'  => now(),
        ]);

        // Liên kết user với manufacturer
        $manufacturer->update(['user_id' => $user->id]);

        return back()->with('success', 'Đã tạo tài khoản ' . $request->email . ' cho NSX ' . $manufacturer->name . '.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $manufacturer = Manufacturer::find($id);

        if ($manufacturer) {
            $manufacturer->delete();
            return redirect()->route('admin.manufacturer.index')->with('success', 'Manufacturer deleted successfully.');
        }

        return redirect()->route('admin.manufacturer.index')->with('error', 'Manufacturer not found.');
    }
}
