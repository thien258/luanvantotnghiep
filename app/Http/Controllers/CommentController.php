<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Order;

class CommentController extends Controller
{
    //
        public function store(Request $request)
    {
        // [VALIDATION ADDED] - Kiểm tra dữ liệu đầu vào trước khi lưu bình luận.
        // idProduct phải là số nguyên và phải tồn tại trong bảng products (FK hợp lệ).
        // name và chat bắt buộc có, giới hạn độ dài để tránh dữ liệu rác vào DB.
        $request->validate([
            'idProduct' => 'required|integer|exists:products,id',
            'name'      => 'required|string|max:100',
            'chat'      => 'required|string|max:1000',
        ], [
            'idProduct.required' => 'Thiếu thông tin sản phẩm.',
            'idProduct.exists'   => 'Sản phẩm không tồn tại.',
            'name.required'      => 'Vui lòng nhập tên của bạn.',
            'name.max'           => 'Tên không được vượt quá 100 ký tự.',
            'chat.required'      => 'Vui lòng nhập nội dung đánh giá.',
            'chat.max'           => 'Nội dung đánh giá không được vượt quá 1000 ký tự.',
        ]);

         Comment::create([
           'idProduct' => $request->idProduct,
            'name' => $request->name,
            'chat' => $request->chat,

        ]);
      
            return back();
        
    }
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
    }

    return redirect()->route('order.history')->with('success', 'Cảm ơn bạn đã đánh giá!');
}
}
