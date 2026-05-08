<?php

namespace App\Http\Controllers\admin;

use App\Models\Product;
use App\Models\Category;
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
        return view('admin.product.add', compact('categories'));
    }
    public function store(Request $request)
    {
        $product = product::create([
            'title' => $request->title,
            'image' => $request->image,
            'decription' => $request->decription,
            'price' => $request->price,
            'status' => $request->status,
            'concentration' => $request->concentration,
            'stock' => $request->stock,
            'volume' => $request->volume,
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
        return view('admin.product.edit', compact('product','categories'));
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
            'concentration' => $request->concentration,
            'stock' => $request->stock,
            'volume' => $request->volume,
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
