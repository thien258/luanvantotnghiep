<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;

class CommentController extends Controller
{
    //
        public function store(Request $request)
    {
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
