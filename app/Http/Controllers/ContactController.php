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
         Contact::create([
           'name' => $request->name,
            'email' => $request->email,
            'message' => $request->message,

        ]);
      
            return back();
        
    }
}
