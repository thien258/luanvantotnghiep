<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductShowController extends Controller
{
    //
    public function index()
    {
        $products = Product::all();
        return view('show-product', compact('products'));
    }
}
