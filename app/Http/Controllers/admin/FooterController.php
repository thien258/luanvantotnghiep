<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Footer;
use Illuminate\Http\Request;

class FooterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
         $footer = Footer::first();
        return view('admin.footer.footer-list',compact('footer'));
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
         $footer = Footer::find($id); // Tìm theo id, nếu không có sẽ báo lỗi 404
        return view('admin.footer.edit', compact('footer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $footer= Footer::find($id);
        $footer->update([
            'header'=>$request->header,
            'textheader'=>$request->textheader,
            'header2'=>$request->header2,
            'address'=>$request->address,
            'phone'=>$request->phone,
            'email'=>$request->email,

        ]);
          if ($footer)
            return redirect()->route('admin.footer.index');
        else {
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
