<?php

namespace App\Livewire\Frontend;

use App\Models\User;
use Livewire\Component;

class GuestEmailModal extends Component
{
    public $email = '';
    public $error = '';

    public function checkEmailAndProceed()
    {
        $this->error = '';

        // Validate email
        $this->validate([
            'email' => 'required|email'
        ]);

        // Check if email exists in database
        $user = User::where('email', $this->email)->first();

        // Get the stored checkout URL from session
        $returnUrl = session('checkout_return_url');

        if (!$returnUrl) {
            $this->error = 'Session expired. Please try again.';
            return;
        }

        if ($user) {
            // User exists - redirect to login with return URL
            $redirectUrl = route('customer.login', ['return_url' => urlencode($returnUrl), 'email' => $this->email]);
        } else {
            // User doesn't exist - redirect to register with return URL
            $redirectUrl = route('customer.register', ['return_url' => urlencode($returnUrl), 'email' => $this->email]);
        }

        // Use JavaScript to redirect to avoid Livewire routing issues
        $this->dispatch('redirectToAuth', url: $redirectUrl);
    }

    public function render()
    {
        return view('livewire.frontend.guest-email-modal');
    }
}
