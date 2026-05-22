<?php

namespace App\Http\Controllers\admin;

use App\Models\home;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class homeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
         $this->middleware('auth');
        $homes= Home::orderBy( 'id','desc')->get();
        View::share('homes',$homes);

    }
    public function index()
    {
        //
             $homes=Home::all();
            return view('admin.home.home-list',compact('homes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('admin.home.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $home=Home::create([
            'name'=>$request->name,
            'description'=>$request->description,
            'image'=>$request->image,
            
        ]);
        if($home)           
             return redirect()->route('admin.home.index');
        else{
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
        $home = Home::find($id);
        return view('admin.home.edit', compact('home'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $home = Home::find($id);
        $home->update([
            'name'=>$request->name,
            'description'=>$request->description,            
            'image'=>$request->image,
        ]);
        if ($home)
            return redirect()->route('admin.home.index');
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
        $home = Home::find($id);
        $home->delete();
         if ($home)
            return redirect()->route('admin.home.index');
        else {
            return back();        
        }
    }
}
