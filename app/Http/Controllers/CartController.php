<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\Cart;
use App\Models\Product;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CartController extends Controller
{
    //
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $idVariant = $request->idVariant;
        $quantityToAdd = $request->quantity ?? 1;

        if (!$idVariant) {
            return back()->with('error', 'Sản phẩm chưa cập nhật dung tích!');
        }

        $variant = ProductVariant::with('product')->findOrFail($idVariant);

        if ($variant->stock < $quantityToAdd) {
            return back()->with('error', 'Sản phẩm này trong kho không đủ số lượng!');
        }


        $existingCart = Cart::where('idUser', Auth::id())
            ->where('idPV', $idVariant)
            ->first();

        if ($existingCart) {
            if ($variant->stock < ($existingCart->quantity + $quantityToAdd)) {
                return back()->with('error', 'Số lượng trong kho không đủ để thêm tiếp!');
            }
            $existingCart->increment('quantity', $quantityToAdd);
        } else {

            Cart::create([
                'idUser'   => Auth::id(),
                'idPV'     => $idVariant,
                'quantity' => $quantityToAdd
            ]);
        }

        return redirect()->route('carts.index')->with('status', 'Đã thêm sản phẩm vào giỏ hàng!');
    }
    public function destroy($id)
    {
        $cart = Cart::find($id);
        $cart->delete();

        return back();
    }
    public function index()
    {
        $carts = Cart::where('idUser', Auth::id())
            ->with(['productVariant.product', 'productVariant.volume'])
            ->get();

        $totalPrice = 0;
        foreach ($carts as $item) {
            // Kiểm tra xem liên kết productVariant có tồn tại dữ liệu không
            if ($item->productVariant) {
                // Đút ngược thông tin vào biến $item->variant để không phải sửa file Blade view nhiều lần
                $item->variant = $item->productVariant;

                $originalPrice = $item->productVariant->price;
                $productCha = $item->productVariant->product;

                // 🌟 Tính toán giá thực tế sau khi đi qua bộ lọc Lễ hội của Product cha
                $finalPrice = ($productCha && method_exists($productCha, 'getDiscountedPrice'))
                    ? $productCha->getDiscountedPrice($originalPrice)
                    : $originalPrice;

                // 4. Găm cái giá bán thực tế này vào đối tượng $item để ngoài file Blade chỉ việc gọi $item->final_price ra dùng
                $item->final_price = $finalPrice;

                // 5. 🌟 ĐÃ SỬA: Chỉ cộng dồn duy nhất giá đã giảm nhân với số lượng vào tổng tiền hóa đơn
                $totalPrice += $finalPrice * $item->quantity;
            }
        }

        return view('carts', compact('carts', 'totalPrice'));
    }
    public function update(Request $request, $id)
    {
        $cart = Cart::findOrFail($id);

        // 1. NẾU KHÁCH BẤM NÚT ĐỔI DUNG TÍCH (Thanh Navigation)
        if ($request->has('newIdVariant')) {

            // SỬA CHÍNH XÁC THÀNH idPV NHƯ TRONG DATABASE
            $cart->idPV = $request->newIdVariant;

            $cart->save();
            return back()->with('status', 'Đã chuyển sang dung tích mới!');
        }
        // 2. NẾU KHÁCH BẤM NÚT TĂNG/GIẢM SỐ LƯỢNG (+ / -)
        if ($request->has('change')) {
            if ($request->change == 'up') {
                $cart->quantity++;
            } elseif ($request->change == 'down' && $cart->quantity > 1) {
                $cart->quantity--;
            }
            $cart->save();
            return back();
        }

        return back();
    }
}
