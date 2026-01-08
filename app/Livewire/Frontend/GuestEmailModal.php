<?php

namespace App\Livewire\Frontend;

use App\Models\User;
use Livewire\Component;

class GuestEmailModal extends Component
{
    public $email = '';
    public $showModal = false;
    public $error = '';

    public function mount()
    {
        // Show modal if user is guest
        $this->showModal = auth()->guest();
    }

    public function checkEmailAndProceed()
    {
        $this->error = '';

        // Validate email
        $this->validate([
            'email' => 'required|email'
        ]);

        // Check if email exists in database
        $user = User::where('email', $this->email)->first();

        // Prepare return URL to checkout
        $returnUrl = url()->current();

        if ($user) {
            // User exists - set redirect URL for JavaScript
            $redirectUrl = route('customer.login', ['return_url' => $returnUrl, 'email' => $this->email]);
        } else {
            // User doesn't exist - set redirect URL for JavaScript
            $redirectUrl = route('customer.register', ['return_url' => $returnUrl, 'email' => $this->email]);
        }

        // Use JavaScript to redirect to avoid Livewire routing issues
        $this->dispatch('redirectToAuth', url: $redirectUrl);
    }

    public function render()
    {
        return view('livewire.frontend.guest-email-modal');
    }
}
