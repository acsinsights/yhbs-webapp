<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use App\Models\Booking;
use App\Enums\BookingStatusEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingCancellationRequestMail;

class BookingCancellationRequest extends Component
{
    public $booking;
    public $cancellationReason = '';
    public $showModal = false;

    protected $rules = [
        'cancellationReason' => 'required|string|min:10|max:500',
    ];

    protected $messages = [
        'cancellationReason.required' => 'Please provide a reason for cancellation.',
        'cancellationReason.min' => 'Reason must be at least 10 characters.',
        'cancellationReason.max' => 'Reason must not exceed 500 characters.',
    ];

    public function mount($bookingId)
    {
        $this->booking = Booking::with(['user', 'bookingable'])->findOrFail($bookingId);

        // Check if user owns this booking
        if ($this->booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to booking.');
        }
    }

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->cancellationReason = '';
        $this->resetValidation();
    }

    public function submitCancellationRequest()
    {
        $this->validate();

        // Check if booking can be cancelled
        if (!$this->booking->canBeCancelled()) {
            session()->flash('error', 'This booking cannot be cancelled.');
            $this->closeModal();
            return;
        }

        // Update booking with cancellation request
        $this->booking->update([
            'cancellation_requested_at' => now(),
            'cancellation_status' => 'pending',
            'cancellation_reason' => $this->cancellationReason,
        ]);

        // Send email notification to admin
        try {
            Mail::to(config('mail.admin_email', 'admin@yhbs.com'))
                ->send(new BookingCancellationRequestMail($this->booking));
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error('Failed to send cancellation request email: ' . $e->getMessage());
        }

        session()->flash('success', 'Your cancellation request has been submitted successfully. Our team will review it and get back to you soon.');

        $this->closeModal();

        // Redirect to bookings page
        return redirect()->route('customer.bookings');
    }

    public function render()
    {
        return view('livewire.customer.booking-cancellation-request');
    }
}
