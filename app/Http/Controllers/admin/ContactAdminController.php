<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;

class ContactAdminController extends Controller
{
    //
     public function index()
    {
        $contacts = Contact::all();
        return view('admin.contact.contact-list', compact('contacts'));
    }
       public function destroy($id)
    {
        $contacts = Contact::find($id);
        $contacts->delete();
        if ($contacts)
            return redirect()->route('admin.contacts.index');
        else {
            return back();
        }
    }
}
