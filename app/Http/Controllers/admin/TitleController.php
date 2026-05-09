<?php

namespace App\Http\Controllers\Admin;

use App;
use App\Http\Controllers\Controller;
use App\Models\Title;
use Illuminate\Http\Request;
use App\Models\Header;

class TitleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $title= Title::orderBy( 'idTitle','desc')->get();
        view()->share('title',$title);
    }
    public function index()
    {
        //
        $title= Title::all();
        return view('admin.title.titleList',compact('title'));
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
        $title = Title::create([
            'title' => $request->title,
            'image' => $request->image,
            'button' => $request->button,
            'descrip' => $request->descrip,
            'idHeader' => $request->idHeader,
        ]);
        if ($title) 
             return redirect()->route('admin.title.index');
        else {
            return back();
        }
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
        $title = Title::find($id); // Tìm theo id, nếu không có sẽ báo lỗi 404
       
        return view('admin.title.editTitle', compact('title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $title= Title::find($id);
        $title->update([
            'title' => $request->title,
            'image' => $request->image,
            'button' => $request->button,
            'descrip' => $request->descrip,
           
        ]);
        if ($title)
            return redirect()->route('admin.title.index');
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
        $title = Title::find($id);
        if (!$title) {
            return back()->with('error', 'Tiêu đề không tồn tại.');
        }
        $title->delete();
        return redirect()->route('admin.title.index');
    }
}
