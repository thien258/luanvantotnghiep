<?php

namespace App\Http\Controllers\admin;

use App\Models\Festival;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Models\Product;

class FestivalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $fetivals = Festival::orderBy('id', 'desc')->get();
        view::share('festivals', $fetivals);
    }
    public function index()
    {
        //
        $festivals = Festival::all();
        return view('admin.festival.festival-list', compact('festivals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //

        return view('admin.festival.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $festival = Festival::create([
            'name' => $request->name,
            'discount' => $request->discount,
            'status' => $request->status,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
        if ($festival)
            return redirect()->route('admin.festival.index');
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
        $festival = Festival::find($id);
        return view('admin.festival.edit', compact('festival'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $festival = Festival::find($id);
        $festival->update([
            'name' => $request->name,
            'discount' => $request->discount,
            'status' => $request->status,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
        if ($festival)
            return redirect()->route('admin.festival.index');
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
        $festival = Festival::find($id);
        $festival->delete();
        if ($festival)            return redirect()->route('admin.festival.index');
        else {
            return back();
        }
    }
    public function selectProducts(Request $request, $id)
    {
        $festival = Festival::findOrFail($id);
        $query = Product::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Nếu là AJAX, hãy lấy tất cả kết quả khớp thay vì paginate(15) 
        // để người dùng thấy đầy đủ kết quả tìm kiếm ngay lập tức
        if ($request->expectsJson() || $request->header('X-Requested-With') == 'XMLHttpRequest') {
            $products = $query->get(); // Dùng get() thay vì paginate() cho tìm kiếm nhanh
            $html = '';
            foreach ($products as $product) {
                $checked = $festival->products->contains($product->id) ? 'checked' : '';
                $html .= "<tr>
                <td><input type='checkbox' name='product_ids[]' value='{$product->id}' {$checked}></td>
                <td>{$product->title}</td>
                <td><img src='{$product->image}' style='width: 50px;'></td>
                <td>" . number_format($product->price) . "đ</td>
            </tr>";
            }
            return $html;
        }

        $products = $query->paginate(15);
        return view('admin.festival.select_products', compact('festival', 'products'));
    }
    public function updateProducts(Request $request, $id)
    {
        $festival = Festival::findOrFail($id);
        // sync() giúp cập nhật danh sách quan hệ, tự động xóa cái cũ, thêm cái mới
        $festival->products()->sync($request->product_ids);
        return redirect()->route('admin.festival.index')->with('status', 'Cập nhật sản phẩm thành công!');
    }
}
