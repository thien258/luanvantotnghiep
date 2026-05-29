<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // Thêm sản phẩm vào giỏ hàng
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $productId = $request->product_id;
        $quantityToAdd = $request->quantity ?? 1;

        $product = Product::findOrFail($productId);

        // Kiểm tra tồn kho
        if ($product->quantity < $quantityToAdd) {
            return back()->with('error', 'Sản phẩm này trong kho không đủ số lượng!');
        }

        // Tìm xem sản phẩm đã có trong giỏ chưa
        $existingCart = Cart::where('idUser', Auth::id())
            ->where('product_id', $productId)
            ->first();

        if ($existingCart) {
            if ($product->quantity < ($existingCart->quantity + $quantityToAdd)) {
                return back()->with('error', 'Số lượng trong kho không đủ!');
            }
            $existingCart->increment('quantity', $quantityToAdd);
        } else {
            Cart::create([
                'idUser'   => Auth::id(),
                'product_id' => $productId,
                'quantity' => $quantityToAdd
            ]);
        }

        return redirect()->route('carts.index')->with('status', 'Đã thêm vào giỏ hàng!');
    }

    // Hiển thị giỏ hàng
    public function index()
    {
        $carts = Cart::where('idUser', Auth::id())->with('product')->get();
        $totalPrice = 0;

        foreach ($carts as $item) {
            if ($item->product) {
                // TỰ ĐỘNG TÍNH TOÁN GIÁ DỰA TRÊN LỄ HỘI CỦA SẢN PHẨM
                $finalPrice = $item->product->getDiscountedPrice();

                // Gán vào để view hiển thị
                $item->final_price = $finalPrice;

                // CỘNG DỒN TỔNG TIỀN HÓA ĐƠN
                $totalPrice += ($finalPrice * $item->quantity);
            }
        }

        return view('carts', compact('carts', 'totalPrice'));
    }

    // Cập nhật số lượng
    public function update(Request $request, $id)
    {
        $cart = Cart::findOrFail($id);

        // Logic tăng/giảm số lượng
        if ($request->has('change')) {
            if ($request->change == 'up' && $cart->quantity < $cart->product->quantity) {
                $cart->increment('quantity');
            } elseif ($request->change == 'down' && $cart->quantity > 1) {
                $cart->decrement('quantity');
            }
        }

        return response()->json(['success' => true, 'new_quantity' => $cart->quantity]);
    }

    // Xóa sản phẩm
    public function destroy($id)
    {
        Cart::where('id', $id)->where('idUser', Auth::id())->delete();
        return back()->with('status', 'Đã xóa khỏi giỏ hàng!');
    }
}
