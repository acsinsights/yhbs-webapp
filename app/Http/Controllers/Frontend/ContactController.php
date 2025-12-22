<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Mail\ContactFormMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'required|string',
        ]);

        $contact = Contact::create($validated);

        // Send email notification
        Mail::to(config('mail.from.address'))->send(new ContactFormMail($contact));

        return redirect()->back()->with('success', 'Thank you for contacting us! We will get back to you soon.');
    }
}
