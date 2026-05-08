<?php

namespace App\Http\Controllers;
use App\Models\Love;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LoveController extends Controller
{
    //
           public function store(Request $request)
    {
         if (!Auth::check()) {
            return redirect()->route('login');
        }
         Love::create([
                'idUser'   => Auth::id(),
           'idProduct' => $request->idProduct,

        ]);
      
            return back();
        
    }
           public function destroy($id)
    {
        $love = Love::find($id);
        $love->delete();
      
            return back();
       
    }
       public function index()
    {
              $loves = Love::where('idUser', Auth::id())->get();

        return view('loves', compact('loves'));
    }
}
