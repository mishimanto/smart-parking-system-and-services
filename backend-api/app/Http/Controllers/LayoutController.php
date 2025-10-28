<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;

class LayoutController extends Controller
{
    public function getContact()
    {
        $contact = Contact::first();
        return response()->json($contact);
    }
}
