<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    
public function store(Request $request)
{
    

    $order = Order::create([
        'idUser'    => Auth::id(),
        'idProduct' => $request->idProduct,
        'price'     => $request->price,
        'address'   => $request->address,
    ]);

    return back();
}
      public function index()
    {
              $orders = Order::where('idUser', Auth::id())->orderBy('id','desc')->get();

        return view('order.index', compact('orders'));
    }
}
