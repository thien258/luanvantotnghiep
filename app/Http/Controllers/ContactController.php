<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;


class ContactController extends Controller
{
    //
    public function index()
    {
        return view('contact');
    }
          public function store(Request $request)
    {
        // [VALIDATION ADDED] - Kiểm tra dữ liệu form liên hệ trước khi lưu.
        // email phải đúng định dạng để tránh dữ liệu không hợp lệ.
        // message giới hạn 2000 ký tự để tránh nội dung spam quá lớn.
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'message' => 'required|string|max:2000',
        ], [
            'name.required'    => 'Vui lòng nhập họ tên.',
            'name.max'         => 'Họ tên không được vượt quá 255 ký tự.',
            'email.required'   => 'Vui lòng nhập địa chỉ email.',
            'email.email'      => 'Địa chỉ email không đúng định dạng.',
            'email.max'        => 'Email không được vượt quá 255 ký tự.',
            'message.required' => 'Vui lòng nhập nội dung liên hệ.',
            'message.max'      => 'Nội dung không được vượt quá 2000 ký tự.',
        ]);

         Contact::create([
           'name' => $request->name,
            'email' => $request->email,
            'message' => $request->message,

        ]);
      
            return back();
        
    }
}
