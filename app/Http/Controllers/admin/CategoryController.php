<?php

namespace App\Http\Controllers\admin;
use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
      public function __construct()
    {
      $this->middleware('auth');
        $categories= Category::orderBy( 'id','desc')->get();
        view()->share('categories',$categories);
    }
         public function index(){
            $categories=Category::all();
            return view('admin.category.category-list',compact('categories'));
    }
    public function create(){
        return view('admin.category.add');
    }
      public function store(Request $request){
        $category=Category::create([
            'name'=>$request->name,
            'image'=>$request->image,
            'status'=>$request->status,
            
        ]);
        if($category)
            return redirect()->route('admin.category.index');
        else{
            return back();
        }
    }
      public function edit($id)
    {
        $category = Category::find($id);
        return view('admin.category.edit', compact('category'));
    }
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        $category->update([
            'name'=>$request->name,
            'image'=>$request->image,
            'status'=>$request->status,
        ]);
        if ($category)
            return redirect()->route('admin.category.index');
        else {
            return back();
        }
    }
    public function destroy($id){
             $category = Category::find($id);
             $category->delete();
              if ($category)
            return redirect()->route('admin.category.index');
        else {
            return back();
        }
    }
}
