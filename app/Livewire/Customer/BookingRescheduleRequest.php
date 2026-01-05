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
    public $bookingId;

    protected $rules = [
        'rescheduleReason' => 'required|string|min:10|max:500',
        'newCheckIn' => 'required|date',
    ];

    protected $messages = [
        'rescheduleReason.required' => 'Please provide a reason for rescheduling.',
        'rescheduleReason.min' => 'Reason must be at least 10 characters.',
        'rescheduleReason.max' => 'Reason must not exceed 500 characters.',
        'newCheckIn.required' => 'Please select a new check-in date.',
        'newCheckIn.after_or_equal' => 'New check-in date must be on or after your current check-out date.',
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

        // Add conditional validation
        if ($this->isBoatBooking) {
            $this->rules['selectedTimeSlot'] = 'required|string';
        } else {
            // For house and room bookings, newCheckOut is required
            $this->rules['newCheckOut'] = 'required|date|after:newCheckIn';
        }
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
        \Log::info('OpenModal called for booking: ' . $this->booking->id);
        $this->showModal = true;

        // Generate time slots if boat booking
        if ($this->isBoatBooking) {
            $this->generateTimeSlots();
        }

        // Dispatch event to initialize date picker
        $this->dispatch('modal-opened');

        \Log::info('Modal state after opening: ' . ($this->showModal ? 'true' : 'false'));
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

        // New check-in must be on or after current checkout date
        $checkoutDate = $this->booking->check_out->format('Y-m-d');
        $rules['newCheckIn'] = "required|date|after_or_equal:{$checkoutDate}";


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

    /**
     * Get all booked dates for this property (excluding current booking)
     * Returns array of dates that are already booked
     */
    public function getBookedDates()
    {
        if ($this->isBoatBooking) {
            // For boats, we don't need to block dates
            return [];
        }

        $bookedDates = [];
        $bookableType = $this->booking->bookingable_type;
        $bookableId = $this->booking->bookingable_id;

        // Get all bookings for this property (excluding current booking)
        $bookings = Booking::where('bookingable_type', $bookableType)
            ->where('bookingable_id', $bookableId)
            ->where('id', '!=', $this->booking->id)
            ->whereIn('status', ['pending', 'booked', 'checked_in'])
            ->get(['check_in', 'check_out']);

        // Generate array of booked dates
        foreach ($bookings as $booking) {
            $checkIn = Carbon::parse($booking->check_in);
            $checkOut = Carbon::parse($booking->check_out);

            // Add all dates in the range (excluding check-out date as per hotel standard)
            $currentDate = $checkIn->copy();
            while ($currentDate->lt($checkOut)) {
                $bookedDates[] = $currentDate->format('Y-m-d');
                $currentDate->addDay();
            }
        }

        return array_unique($bookedDates);
    }

    public function render()
    {
        return view('livewire.customer.booking-reschedule-request', [
            'bookedDates' => $this->getBookedDates(),
        ]);
    }
}

