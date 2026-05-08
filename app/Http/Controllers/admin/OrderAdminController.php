<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderAdminController extends Controller
{
    //
    public function index()
    {
        $orders = Order::all();
        return view('admin.order.order-list', compact('orders'));
    }
    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('admin.orders.index');
  
    }
}
