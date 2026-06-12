<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;

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


}
