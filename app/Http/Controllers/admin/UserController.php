<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
class UserController extends Controller
{
    //
    public function index(){
        $users= User::all();
        return view('admin.user.user-list',compact('users'));
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
        $user->delete();
        if ($user)
            return redirect()->route('admin.user.index');
        else {
            return back();
        }
    }
}
