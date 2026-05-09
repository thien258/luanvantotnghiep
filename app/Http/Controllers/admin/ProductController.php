<?php

namespace App\Http\Controllers\admin;

use App\Models\Product;
use App\Models\Category;
use App\Models\Concentration;
use App\Models\Volume;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth");
        $products = Product::orderBy('id', 'desc')->get();
        view()->share('products', $products);
    }   
    public function index()
    {
        $products = Product::all();
        return view('admin.product.product-list', compact('products'));
    }
    public function create()
    {
        $categories = Category::all();
        $concentrations = Concentration::all();
        $volumes = Volume::all();
        return view('admin.product.add', compact('categories', 'concentrations', 'volumes'));
    }
    public function store(Request $request)
    {
        $product = product::create([
            'title' => $request->title,
            'image' => $request->image,
            'decription' => $request->decription,
            'price' => $request->price,
            'status' => $request->status,
            'stock' => $request->stock,
            'idConcentration' => $request->idConcentration, 
            'idVolume' => $request->idVolume,
            'idCategory' => $request->idCategory,

        ]);
        if ($product)
            return redirect()->route('admin.product.index');
        else {
            return back();
        }
    }
    public function edit($id)
    {
        $product = Product::find($id);
        $categories = Category::all();
        $concentrations = Concentration::all();
        $volumes = Volume::all();
        return view('admin.product.edit', compact('product','categories','concentrations','volumes'));
    }
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        $product->update([
            'title' => $request->title,
            'image' => $request->image,
            'decription' => $request->decription,
            'price' => $request->price,
            'status' => $request->status,
            'idConcentration' => $request->idConcentration,
            'stock' => $request->stock,
            'idVolume' => $request->idVolume,
            'idCategory' => $request->idCategory,
        ]);
        if ($product)
            return redirect()->route('admin.product.index');
        else {
            return back();
        }
    }
    public function destroy($id)
    {
     $product = Product::find($id);

    if (!$product) {
  
        return back()->with('error', 'Sản phẩm không tồn tại.');
    }
     $product->delete();

    return redirect()->route('admin.product.index');
    }
}
