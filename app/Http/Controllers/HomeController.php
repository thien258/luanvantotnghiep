<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Footer;
use App\Models\Title;
use App\Models\Love;
use App\Models\Product;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
   
        view()->share('categories', Category::where('status', '1')->get());
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
            $categories = Category::where("status", '1')->get();
        $products = Product::where('status', 1)
            ->whereHas('category', function ($q) {
                $q->where('status', 1);
            })
            ->get();
            $title = Title::All();
                $footers = Footer::All();
        
            return view('index', compact("categories", "products" ,"footers", "title"));
    }
    public function logout()
    {
        if (Auth::check()) {
            Auth::logout();
        }
        return redirect('/');
    }
    public function category_product($id)
    {
        $category = Category::find($id);
        $products = Product::where( [
            ['idCategory','=',$id] ,
            ['status','=','1'] 
              ])->get();
        return view('layout.category_product', compact("category", "products"));
    }
    public function single_product($id)
    {
         $products = Product::with('comment')
        ->where('id', $id)
        ->where('status', 1)
        ->get();
        return view('layout.single_product', compact('products'));
    }

}
