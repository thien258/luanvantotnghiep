<?php

namespace App\Http\Controllers\admin;

use App\Models\Brand;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BrandController extends Controller
{
      public function __construct()
    {
      $this->middleware('auth');
        $brands= Brand::orderBy( 'id','desc')->get();
        view()->share('brands',$brands);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $brands=Brand::all();
        return view('admin.brand.brand-list',compact('brands'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('admin.brand.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $brand=Brand::create([
            'name'=>$request->name,
           'image' => $request->image??'',
            'status'=>$request->status,
            
        ]);
        if($brand)          
              return redirect()->route('admin.brand.index');
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
        $brand = Brand::find($id);
        return view('admin.brand.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $brand = Brand::find($id);
        $brand->update([
            'name'=>$request->name,
            'image'=>$request->image,
            'status'=>$request->status,
        ]);
        if ($brand)          
              return redirect()->route('admin.brand.index');
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
                $brand = Brand::find($id);
                $brand->delete();
                if ($brand)          
                return redirect()->route('admin.brand.index');
            else {
                return back();
            }
    }
}
