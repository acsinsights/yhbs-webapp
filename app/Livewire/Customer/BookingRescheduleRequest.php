<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use App\Models\Booking;
use App\Enums\BookingStatusEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingRescheduleRequestMail;
use Carbon\Carbon;

class BookingRescheduleRequest extends Component
{
    public $booking;
    public $rescheduleReason = '';
    public $newCheckIn = '';
    public $newCheckOut = '';
    public $rescheduleFee = 0;
    public $showModal = false;

    protected $rules = [
        'rescheduleReason' => 'required|string|min:10|max:500',
        'newCheckIn' => 'required|date|after:today',
        'newCheckOut' => 'required|date|after:newCheckIn',
    ];

    protected $messages = [
        'rescheduleReason.required' => 'Please provide a reason for rescheduling.',
        'rescheduleReason.min' => 'Reason must be at least 10 characters.',
        'rescheduleReason.max' => 'Reason must not exceed 500 characters.',
        'newCheckIn.required' => 'Please select a new check-in date.',
        'newCheckIn.after' => 'New check-in date must be in the future.',
        'newCheckOut.required' => 'Please select a new check-out date.',
        'newCheckOut.after' => 'New check-out date must be after check-in date.',
    ];

    public function mount($bookingId)
    {
        $this->booking = Booking::with(['user', 'bookingable'])->findOrFail($bookingId);

        // Check if user owns this booking
        if ($this->booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to booking.');
        }

        // Calculate reschedule fee
        $this->rescheduleFee = $this->booking->calculateRescheduleFee();
    }

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->rescheduleReason = '';
        $this->newCheckIn = '';
        $this->newCheckOut = '';
        $this->resetValidation();
    }

    public function submitRescheduleRequest()
    {
        $this->validate();

        // Check if booking can be rescheduled
        if (!$this->booking->canBeRescheduled()) {
            session()->flash('error', 'This booking cannot be rescheduled.');
            $this->closeModal();
            return;
        }

        // Check if reschedule is requested at least 48 hours before check-in
        $checkInTime = Carbon::parse($this->booking->check_in);
        $hoursUntilCheckIn = Carbon::now()->diffInHours($checkInTime, false);

        if ($hoursUntilCheckIn < 48) {
            session()->flash('error', 'Reschedule requests must be made at least 48 hours before check-in.');
            $this->closeModal();
            return;
        }

        // Update booking with reschedule request
        $this->booking->update([
            'reschedule_requested_at' => now(),
            'reschedule_status' => 'pending',
            'reschedule_reason' => $this->rescheduleReason,
            'new_check_in' => Carbon::parse($this->newCheckIn),
            'new_check_out' => Carbon::parse($this->newCheckOut),
            'reschedule_fee' => $this->rescheduleFee,
        ]);

        // Send email notification to admin
        try {
            Mail::to(config('mail.admin_email', 'admin@yhbs.com'))
                ->send(new BookingRescheduleRequestMail($this->booking));
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error('Failed to send reschedule request email: ' . $e->getMessage());
        }

        session()->flash('success', 'Your reschedule request has been submitted successfully. A fee of ' . currency_format($this->rescheduleFee) . ' will be charged if approved. Our team will review it and get back to you soon.');

        $this->closeModal();

        // Redirect to bookings page
        return redirect()->route('customer.bookings');
    }

    public function render()
    {
        return view('livewire.customer.booking-reschedule-request');
    }
}
