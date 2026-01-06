<?php

namespace App\Livewire\Customer;

use App\Models\Boat;
use App\Models\Booking;
use App\Models\House;
use App\Models\Room;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingRescheduleRequestMail;

class BookingRescheduleForm extends Component
{
    public $bookingId;
    public $booking;
    public $newCheckIn;
    public $newCheckOut;
    public $rescheduleReason;
    public $rescheduleFee = 0;
    public $isBoatBooking = false;
    public $selectedTimeSlot;
    public $availableTimeSlots = [];
    public $originalNights = 0;

    protected $rules = [
        'newCheckIn' => 'required|date',
        'rescheduleReason' => 'required|string|min:10|max:500',
    ];

    protected $messages = [
        'newCheckIn.required' => 'Please select check-in date.',
        'newCheckIn.after_or_equal' => 'New check-in date must be on or after your current check-out date.',
        'newCheckOut.required' => 'Please select check-out date.',
        'newCheckOut.after' => 'Check-out date must be after check-in date.',
        'rescheduleReason.required' => 'Please provide a reason for rescheduling.',
        'rescheduleReason.min' => 'Reason must be at least 10 characters.',
        'selectedTimeSlot.required' => 'Please select a time slot for boat booking.',
    ];

    public function mount($bookingId)
    {
        $this->bookingId = $bookingId;
        $this->booking = Booking::with('bookingable')->findOrFail($bookingId);
        $this->originalNights = $this->booking->check_in->diffInDays($this->booking->check_out);
        $this->calculateRescheduleFee();
        $this->checkIfBoatBooking();
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'newCheckIn' && $this->isBoatBooking && $this->newCheckIn) {
            $this->generateTimeSlots();
        }
    }

    private function checkIfBoatBooking()
    {
        $this->isBoatBooking = $this->booking->bookingable_type === Boat::class;

        if ($this->isBoatBooking) {
            $this->rules['selectedTimeSlot'] = 'required|string';
        } else {
            // For house and room bookings, newCheckOut is required
            $this->rules['newCheckOut'] = 'required|date|after:newCheckIn';
        }
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
            $checkIn = \Carbon\Carbon::parse($booking->check_in);
            $checkOut = \Carbon\Carbon::parse($booking->check_out);

            // Add all dates in the range (excluding check-out date as per hotel standard)
            $currentDate = $checkIn->copy();
            while ($currentDate->lt($checkOut)) {
                $bookedDates[] = $currentDate->format('Y-m-d');
                $currentDate->addDay();
            }
        }

        return array_unique($bookedDates);
    }

    private function generateTimeSlots()
    {
        // Generate time slots for boat bookings (e.g., 09:00 AM to 06:00 PM)
        $this->availableTimeSlots = [
            '09:00 AM - 11:00 AM',
            '11:00 AM - 01:00 PM',
            '01:00 PM - 03:00 PM',
            '03:00 PM - 05:00 PM',
            '05:00 PM - 07:00 PM',
        ];
    }

    private function calculateRescheduleFee()
    {
        if ($this->booking->bookingable_type === House::class) {
            $this->rescheduleFee = 50; // 50 KWD for house
        } elseif ($this->booking->bookingable_type === Room::class) {
            $this->rescheduleFee = 20; // 20 KWD for room
        } elseif ($this->booking->bookingable_type === Boat::class) {
            $adults = $this->booking->adults ?? 1;
            $this->rescheduleFee = $adults * 2; // 2 KWD per person for boat
        }
    }

    public function submitRescheduleRequest()
    {
        $this->validate();

        // Validate that the number of nights remains the same for non-boat bookings
        if (!$this->isBoatBooking && $this->newCheckIn && $this->newCheckOut) {
            $newCheckInDate = \Carbon\Carbon::parse($this->newCheckIn);
            $newCheckOutDate = \Carbon\Carbon::parse($this->newCheckOut);
            $newNights = (int) $newCheckInDate->diffInDays($newCheckOutDate);

            if ($newNights !== $this->originalNights) {
                $this->addError('newCheckOut', "Booking duration must remain the same. Original: {$this->originalNights} nights, Selected: {$newNights} nights.");
                return;
            }
        }

        try {
            DB::beginTransaction();

            // Update booking with reschedule request
            $this->booking->update([
                'new_check_in' => $this->newCheckIn,
                'new_check_out' => $this->newCheckOut,
                'reschedule_reason' => $this->rescheduleReason,
                'reschedule_status' => 'pending',
                'reschedule_fee' => $this->rescheduleFee,
                'reschedule_requested_at' => now(),
            ]);

            // If boat booking, store time slot info
            if ($this->isBoatBooking && $this->selectedTimeSlot) {
                $this->booking->update([
                    'requested_time_slot' => $this->selectedTimeSlot,
                ]);
            }

            // Send email notification to admin
            try {
                Mail::to(config('mail.from.address'))
                    ->send(new BookingRescheduleRequestMail($this->booking));
            } catch (\Exception $e) {
                Log::error('Failed to send reschedule request email: ' . $e->getMessage());
            }

            DB::commit();

            session()->flash('success', 'Reschedule request submitted successfully! We will review it shortly.');

            // Redirect to booking details page
            return redirect()->route('customer.booking-details', $this->booking->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reschedule request failed: ' . $e->getMessage());
            session()->flash('error', 'Failed to submit reschedule request. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.customer.booking-reschedule-form', [
            'bookedDates' => $this->getBookedDates(),
        ]);
    }
}
