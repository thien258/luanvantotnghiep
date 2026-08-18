<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Order;

class CommentController extends Controller
{

       public function destroy($id)
    {
        $comment = Comment::find($id);
        $comment->delete();
      
            return back();
       
    }

public function storeReview(Request $request, $orderId){
    $order= Order::where('id',$orderId)
    ->where('idUser',Auth::id())
    ->where('status',4)
       ->with('details.product')
        ->firstOrFail();

    $reviews = $request->input('reviews', []);

    $hasNewReview = false;

    foreach ($reviews as $detailId => $data) {
        // Bỏ qua nếu không chọn sao
        if (empty($data['rating'])) continue;

        // Bỏ qua nếu đã đánh giá order_detail này rồi
        $alreadyReviewed = Comment::where('order_detail_id', $detailId)
            ->where('user_id', Auth::id())
            ->exists();
        if ($alreadyReviewed) continue;

        $detail = $order->details->firstWhere('id', $detailId);
        if (!$detail) continue;

        Comment::create([
            'idProduct'       => $detail->idProduct,
            'name'            => Auth::user()->name,
            'chat'            => $data['chat'] ?? '',
            'rating'          => (int) $data['rating'],
            'user_id'         => Auth::id(),
            'order_detail_id' => $detailId,
        ]);

        $hasNewReview = true;
    }

    // Đánh dấu đơn đã được đánh giá → không cho hoàn hàng nữa
    if ($hasNewReview && !str_contains((string) $order->note, '[REVIEWED]')) {
        $order->note = ($order->note ? $order->note . ' | ' : '') . '[REVIEWED]';
        $order->save();
    }

    return redirect()->route('order.history')->with('success', 'Cảm ơn bạn đã đánh giá!');
}
}
