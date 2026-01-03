<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use App\Models\Booking;
use App\Models\Boat;
use App\Models\House;
use App\Models\Room;
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
    public $selectedTimeSlot = '';
    public $rescheduleFee = 0;
    public $showModal = false;
    public $isBoatBooking = false;
    public $availableTimeSlots = [];

    protected $rules = [
        'rescheduleReason' => 'required|string|min:10|max:500',
        'newCheckIn' => 'required|date|after:today',
    ];

    protected $messages = [
        'rescheduleReason.required' => 'Please provide a reason for rescheduling.',
        'rescheduleReason.min' => 'Reason must be at least 10 characters.',
        'rescheduleReason.max' => 'Reason must not exceed 500 characters.',
        'newCheckIn.required' => 'Please select a new check-in date.',
        'newCheckIn.after' => 'New check-in date must be in the future.',
        'newCheckOut.required' => 'Please select a new check-out date.',
        'newCheckOut.after' => 'New check-out date must be after check-in date.',
        'selectedTimeSlot.required' => 'Please select a time slot.',
    ];

    public function mount($bookingId)
    {
        $this->booking = Booking::with(['user', 'bookingable'])->findOrFail($bookingId);

        // Check if user owns this booking
        if ($this->booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to booking.');
        }

        // Check if this is a boat booking
        $this->checkIfBoatBooking();

        // Calculate reschedule fee
        $this->calculateRescheduleFee();
    }

    public function checkIfBoatBooking()
    {
        $this->isBoatBooking = $this->booking->bookingable_type === 'App\\Models\\Boat' ||
            $this->booking->bookingable instanceof Boat;
    }

    public function calculateRescheduleFee()
    {
        $bookingableType = $this->booking->bookingable_type;

        if ($bookingableType === 'App\\Models\\House' || $this->booking->bookingable instanceof House) {
            $this->rescheduleFee = 50; // 50 KWD for houses
        } elseif ($bookingableType === 'App\\Models\\Room' || $this->booking->bookingable instanceof Room) {
            $this->rescheduleFee = 20; // 20 KWD for rooms
        } elseif ($bookingableType === 'App\\Models\\Boat' || $this->booking->bookingable instanceof Boat) {
            // 2 KWD per person for boats
            $numberOfGuests = $this->booking->number_of_guests ?? 1;
            $this->rescheduleFee = 2 * $numberOfGuests;
        } else {
            $this->rescheduleFee = 30; // Default fee
        }
    }

    public function generateTimeSlots()
    {
        $this->availableTimeSlots = [
            '09:00 AM - 11:00 AM',
            '11:00 AM - 01:00 PM',
            '01:00 PM - 03:00 PM',
            '03:00 PM - 05:00 PM',
            '05:00 PM - 07:00 PM',
        ];
    }

    public function updatedNewCheckIn($value)
    {
        if ($this->isBoatBooking && $value) {
            $this->generateTimeSlots();
        }
    }

    public function openModal()
    {
        $this->showModal = true;

        // Generate time slots if boat booking
        if ($this->isBoatBooking) {
            $this->generateTimeSlots();
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->rescheduleReason = '';
        $this->newCheckIn = '';
        $this->newCheckOut = '';
        $this->selectedTimeSlot = '';
        $this->availableTimeSlots = [];
        $this->resetValidation();
    }

    public function submitRescheduleRequest()
    {
        // Dynamic validation based on booking type
        $rules = $this->rules;

        if (!$this->isBoatBooking) {
            $rules['newCheckOut'] = 'required|date|after:newCheckIn';
        } else {
            $rules['selectedTimeSlot'] = 'required|string';
        }

        $this->validate($rules);

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

        // Prepare update data
        $updateData = [
            'reschedule_requested_at' => now(),
            'reschedule_status' => 'pending',
            'reschedule_reason' => $this->rescheduleReason,
            'new_check_in' => Carbon::parse($this->newCheckIn),
            'reschedule_fee' => $this->rescheduleFee,
        ];

        // Add checkout date for non-boat bookings
        if (!$this->isBoatBooking && $this->newCheckOut) {
            $updateData['new_check_out'] = Carbon::parse($this->newCheckOut);
        }

        // Add time slot for boat bookings
        if ($this->isBoatBooking && $this->selectedTimeSlot) {
            $updateData['requested_time_slot'] = $this->selectedTimeSlot;
        }

        // Update booking with reschedule request
        $this->booking->update($updateData);

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

