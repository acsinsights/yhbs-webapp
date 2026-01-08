<?php

use Carbon\Carbon;
use Livewire\Volt\Component;
use App\Models\{Booking, Boat, House, Room};
use Illuminate\Support\Facades\{Auth, Mail};
use App\Mail\BookingRescheduleRequestMail;
use App\Notifications\BookingRescheduleRequestNotification;

new class extends Component {
    public Booking $booking;
    public string $rescheduleReason = '';
    public ?string $newCheckIn = null;
    public ?string $newCheckOut = null;
    public ?string $selectedTimeSlot = null;
    public float $rescheduleFee = 0;
    public bool $showModal = false;
    public bool $isBoatBooking = false;
    public array $availableTimeSlots = [];
    public array $bookedDates = [];
    public int $originalNights = 0;

    public function mount(int $bookingId): void
    {
        $this->booking = Booking::with(['user', 'bookingable'])->findOrFail($bookingId);

        if ($this->booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to booking.');
        }

        $this->isBoatBooking = $this->booking->bookingable instanceof Boat;
        $this->originalNights = $this->booking->check_in->diffInDays($this->booking->check_out);
        $this->calculateRescheduleFee();
        $this->loadBookedDates();
    }

    public function getBookingDuration(): string
    {
        if (!$this->isBoatBooking) {
            return '';
        }

        // For boat bookings, calculate duration from check_in and check_out times
        if (!$this->booking->check_in || !$this->booking->check_out) {
            return 'N/A';
        }

        // Calculate duration in hours
        $duration = $this->booking->check_in->diffInHours($this->booking->check_out);

        if ($duration === 0) {
            // If same hour, try diffInMinutes
            $minutes = $this->booking->check_in->diffInMinutes($this->booking->check_out);
            if ($minutes === 15) {
                return '15 Minutes';
            } elseif ($minutes === 30) {
                return '30 Minutes';
            } elseif ($minutes < 60) {
                return $minutes . ' Minutes';
            }
            $duration = 1; // Default to 1 hour if less than an hour
        }

        if ($duration === 1) {
            return '1 Hour';
        } elseif ($duration === 2) {
            return '2 Hours';
        } elseif ($duration === 3) {
            return '3 Hours';
        } else {
            return $duration . ' Hours';
        }
    }

    public function calculateRescheduleFee(): void
    {
        $this->rescheduleFee = match (true) {
            $this->booking->bookingable instanceof House => 50,
            $this->booking->bookingable instanceof Room => 20,
            $this->booking->bookingable instanceof Boat => 2 * ($this->booking->number_of_guests ?? 1),
            default => 30,
        };
    }

    public function loadBookedDates(): void
    {
        if ($this->isBoatBooking) {
            $this->bookedDates = [];
            return;
        }

        $bookings = Booking::where('bookingable_type', get_class($this->booking->bookingable))
            ->where('bookingable_id', $this->booking->bookingable_id)
            ->where('id', '!=', $this->booking->id)
            ->whereIn('status', ['pending', 'booked', 'checked_in'])
            ->where('check_out', '>=', $this->booking->check_out->format('Y-m-d'))
            ->get(['check_in', 'check_out']);

        $dates = [];
        foreach ($bookings as $bkg) {
            $current = Carbon::parse($bkg->check_in);
            $end = Carbon::parse($bkg->check_out);
            while ($current->lt($end)) {
                $dates[] = $current->format('Y-m-d');
                $current->addDay();
            }
        }

        $this->bookedDates = array_unique($dates);
    }

    public function generateTimeSlots(): void
    {
        // Get the original duration from check_in and check_out
        $originalDuration = $this->booking->check_in->diffInHours($this->booking->check_out);

        // For very short durations, check minutes
        if ($originalDuration === 0) {
            $minutes = $this->booking->check_in->diffInMinutes($this->booking->check_out);
            if ($minutes === 15 || $minutes === 30) {
                // For limousine 15-min or 30-min bookings
                $this->availableTimeSlots = match ($minutes) {
                    15 => ['09:00 AM - 09:15 AM', '09:30 AM - 09:45 AM', '10:00 AM - 10:15 AM', '10:30 AM - 10:45 AM', '11:00 AM - 11:15 AM'],
                    30 => ['09:00 AM - 09:30 AM', '09:30 AM - 10:00 AM', '10:00 AM - 10:30 AM', '10:30 AM - 11:00 AM', '11:00 AM - 11:30 AM'],
                    default => [],
                };
                return;
            }
            $originalDuration = 1; // Default to 1 hour
        }

        // Generate slots based on duration
        $allSlots = [
            1 => ['09:00 AM - 10:00 AM', '10:00 AM - 11:00 AM', '11:00 AM - 12:00 PM', '12:00 PM - 01:00 PM', '01:00 PM - 02:00 PM', '02:00 PM - 03:00 PM', '03:00 PM - 04:00 PM', '04:00 PM - 05:00 PM', '05:00 PM - 06:00 PM', '06:00 PM - 07:00 PM'],
            2 => ['09:00 AM - 11:00 AM', '11:00 AM - 01:00 PM', '01:00 PM - 03:00 PM', '03:00 PM - 05:00 PM', '05:00 PM - 07:00 PM'],
            3 => ['09:00 AM - 12:00 PM', '12:00 PM - 03:00 PM', '03:00 PM - 06:00 PM'],
        ];

        $this->availableTimeSlots = $allSlots[$originalDuration] ?? $allSlots[2];
    }

    public function updatedNewCheckIn($value): void
    {
        if ($this->isBoatBooking && $value) {
            $this->generateTimeSlots();
        }
        // Clear previous validation errors when dates change
        $this->resetErrorBag('newCheckOut');
    }

    public function updatedNewCheckOut($value): void
    {
        // Clear previous validation errors when dates change
        $this->resetErrorBag('newCheckOut');

        // Validate nights on the fly for non-boat bookings
        if (!$this->isBoatBooking && $this->newCheckIn && $this->newCheckOut) {
            $newCheckInDate = Carbon::parse($this->newCheckIn);
            $newCheckOutDate = Carbon::parse($this->newCheckOut);
            $newNights = (int) $newCheckInDate->diffInDays($newCheckOutDate);

            if ($newNights !== $this->originalNights) {
                $this->addError('newCheckOut', "Booking duration must remain the same. Original: {$this->originalNights} nights, Selected: {$newNights} nights.");
            }
        }
    }

    public function openModal(): void
    {
        $this->showModal = true;
        if ($this->isBoatBooking) {
            $this->generateTimeSlots();
        }
        $this->dispatch('modal-opened');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->rescheduleReason = '';
        $this->newCheckIn = null;
        $this->newCheckOut = null;
        $this->selectedTimeSlot = null;
        $this->availableTimeSlots = [];
        $this->resetValidation();
    }

    public function submitRescheduleRequest(): void
    {
        $rules = [
            'rescheduleReason' => 'required|string|min:10|max:500',
            'newCheckIn' => 'required|date|after_or_equal:' . $this->booking->check_out->format('Y-m-d'),
        ];

        if (!$this->isBoatBooking) {
            $rules['newCheckOut'] = 'required|date|after:newCheckIn';
        } else {
            $rules['selectedTimeSlot'] = 'required|string';
        }

        $this->validate($rules);

        // Validate that the number of nights remains the same for non-boat bookings
        if (!$this->isBoatBooking && $this->newCheckIn && $this->newCheckOut) {
            $newCheckInDate = Carbon::parse($this->newCheckIn);
            $newCheckOutDate = Carbon::parse($this->newCheckOut);
            $newNights = (int) $newCheckInDate->diffInDays($newCheckOutDate);

            if ($newNights !== $this->originalNights) {
                $this->addError('newCheckOut', "Booking duration must remain the same. Original: {$this->originalNights} nights, Selected: {$newNights} nights.");
                return;
            }
        }

        if (!$this->booking->canBeRescheduled()) {
            session()->flash('error', 'This booking cannot be rescheduled.');
            $this->closeModal();
            return;
        }

        $checkInTime = Carbon::parse($this->booking->check_in);
        if (Carbon::now()->diffInHours($checkInTime, false) < 48) {
            session()->flash('error', 'Reschedule requests must be made at least 48 hours before check-in.');
            $this->closeModal();
            return;
        }

        $updateData = [
            'reschedule_requested_at' => now(),
            'reschedule_status' => 'pending',
            'reschedule_reason' => $this->rescheduleReason,
            'new_check_in' => Carbon::parse($this->newCheckIn),
            'reschedule_fee' => $this->rescheduleFee,
        ];

        if (!$this->isBoatBooking && $this->newCheckOut) {
            $updateData['new_check_out'] = Carbon::parse($this->newCheckOut);
        }

        if ($this->isBoatBooking && $this->selectedTimeSlot) {
            $updateData['requested_time_slot'] = $this->selectedTimeSlot;
        }

        $this->booking->update($updateData);

        try {
            Mail::to(config('mail.admin_email', 'admin@yhbs.com'))->send(new BookingRescheduleRequestMail($this->booking));
        } catch (\Exception $e) {
            \Log::error('Failed to send reschedule request email: ' . $e->getMessage());
        }

        // Send notification to all admin and superadmin users
        $admins = \App\Models\User::role(['admin', 'superadmin'])->get();
        foreach ($admins as $admin) {
            $admin->notify(new BookingRescheduleRequestNotification($this->booking));
        }

        session()->flash('success', 'Your reschedule request has been submitted successfully. A fee of ' . currency_format($this->rescheduleFee) . ' will be charged if approved.');
        $this->closeModal();
        $this->redirect(route('customer.bookings'), navigate: true);
    }
}; ?>

<div>
    <style>
        .modal {
            z-index: 1055 !important;
        }

        .modal-backdrop {
            z-index: 1050 !important;
        }

        .time-slot-btn {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .time-slot-btn:hover .btn {
            transform: scale(1.05);
        }

        /* Make booked dates red in Flatpickr */
        .flatpickr-day.disabled {
            background-color: #ffebee !important;
            color: #c62828 !important;
            cursor: not-allowed !important;
            border-color: #ffcdd2 !important;
        }

        .flatpickr-day.disabled:hover {
            background-color: #ef9a9a !important;
            color: #b71c1c !important;
        }
    </style>

    <!-- Reschedule Booking Button -->
    @php
        $checkInTime = \Carbon\Carbon::parse($booking->check_in);
        $hoursUntilCheckIn = \Carbon\Carbon::now()->diffInHours($checkInTime, false);
        $canRescheduleWithin48Hours = $hoursUntilCheckIn >= 48;
    @endphp

    @if ($booking->canBeRescheduled())
        @if ($canRescheduleWithin48Hours)
            <button wire:click="openModal" wire:loading.attr="disabled" class="btn btn-warning w-100 mt-2" type="button">
                <span wire:loading.remove wire:target="openModal">
                    <i class="bi bi-calendar-event me-2"></i>Request Reschedule
                </span>
                <span wire:loading wire:target="openModal">
                    <i class="bi bi-hourglass-split me-2"></i>Loading...
                </span>
            </button>
        @else
            <div class="alert alert-warning mt-2 mb-0">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Reschedule Not Available</strong>
                <p class="mb-0 mt-2 small">Reschedule requests must be made at least 48 hours before check-in time.</p>
            </div>
        @endif
    @elseif($booking->hasRescheduleRequest())
        <div class="alert alert-info mt-2">
            <i class="bi bi-clock me-2"></i>
            <strong>Reschedule Request Pending</strong>
            <p class="mb-0 mt-2">Your reschedule request is being reviewed by our team.</p>
            <div class="mt-2">
                <small><strong>New Date:</strong> {{ $booking->new_check_in?->format('d M Y') }}</small><br>
                @if (!$booking->bookingable instanceof \App\Models\Boat)
                    <small><strong>New Check-out:</strong> {{ $booking->new_check_out?->format('d M Y') }}</small><br>
                @else
                    @if ($booking->requested_time_slot)
                        <small><strong>New Time Slot:</strong> {{ $booking->requested_time_slot }}</small><br>
                    @endif
                @endif
                <small><strong>Reschedule Fee:</strong> {{ currency_format($booking->reschedule_fee ?? 0) }}</small>
            </div>
        </div>
    @endif

    <!-- Reschedule Modal -->
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">
                            <i class="bi bi-calendar-event me-2"></i>Request Booking Reschedule
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Important:</strong> Reschedule requests must be made at least 48 hours before your
                            check-in date.
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Current Booking Details:</h5>
                                <ul class="list-unstyled">
                                    <li><strong>Reference:</strong> #{{ $booking->id }}</li>
                                    <li><strong>Property:</strong> {{ $booking->bookingable->name ?? 'N/A' }}</li>
                                    @if ($isBoatBooking)
                                        <li><strong>Date:</strong> {{ $booking->check_in->format('d M Y') }}</li>
                                        <li><strong>Duration:</strong> {{ $this->getBookingDuration() }}</li>
                                    @else
                                        <li><strong>Duration:</strong> {{ $originalNights }}
                                            {{ Str::plural('night', $originalNights) }}</li>
                                        <li><strong>Current Check-in:</strong>
                                            {{ $booking->check_in->format('d M Y') }}
                                        </li>
                                        <li><strong>Current Check-out:</strong>
                                            {{ $booking->check_out->format('d M Y') }}
                                        </li>
                                    @endif
                                    <li><strong>Total Amount:</strong>
                                        {{ currency_format($booking->total_amount ?? $booking->price) }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-warning mb-0">
                                    <h5 class="mb-2"><i class="bi bi-cash-coin me-2"></i>Reschedule Fees:</h5>
                                    <ul class="mb-2 small">
                                        <li><strong>House:</strong> 50 KWD</li>
                                        <li><strong>Hotel Room:</strong> 20 KWD</li>
                                        <li><strong>Boat:</strong> 2 KWD per person</li>
                                    </ul>
                                    <div class="mt-2 pt-2 border-top">
                                        <strong>Your Reschedule Fee: {{ currency_format($rescheduleFee) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form wire:submit.prevent="submitRescheduleRequest">
                            @if ($isBoatBooking)
                                {{-- Boat Booking - Only Date (No Checkout) --}}
                                <div class="mb-3" wire:ignore>
                                    <label class="form-label">
                                        <strong>Select New Date <span class="text-danger">*</span></strong>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                                        <input type="text" id="rescheduleDate-{{ $booking->id }}"
                                            class="form-control" placeholder="Select date for your boat trip"
                                            autocomplete="off">
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Select the date for your boat trip (from
                                        {{ $booking->check_out->format('d M Y') }} onwards)
                                    </small>

                                    {{-- Hidden input for Livewire --}}
                                    <input type="hidden" wire:model="newCheckIn"
                                        id="rescheduleCheckIn-{{ $booking->id }}">
                                </div>

                                @error('newCheckIn')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror

                                @if ($newCheckIn)
                                    <div class="alert alert-success mt-2 py-2">
                                        <i class="bi bi-calendar-check me-2"></i>
                                        <strong>Selected Date:</strong>
                                        {{ \Carbon\Carbon::parse($newCheckIn)->format('d M Y') }}
                                    </div>
                                @endif

                                @if ($newCheckIn)
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <strong>Select Time ({{ $this->getBookingDuration() }} duration) <span
                                                    class="text-danger">*</span></strong>
                                        </label>
                                        <small class="text-muted d-block mb-2">
                                            <i class="bi bi-info-circle me-1"></i>
                                            You can only reschedule to the same duration as your original booking
                                        </small>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach ($availableTimeSlots as $slot)
                                                <label class="time-slot-btn">
                                                    <input class="d-none" type="radio"
                                                        wire:model.live="selectedTimeSlot" value="{{ $slot }}">
                                                    <span
                                                        class="btn btn-sm {{ $selectedTimeSlot === $slot ? 'btn-success' : 'btn-outline-secondary' }}">
                                                        <i class="bi bi-clock me-1"></i>{{ $slot }}
                                                        @if ($selectedTimeSlot !== $slot)
                                                            <span class="badge bg-success ms-1">Available</span>
                                                        @endif
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('selectedTimeSlot')
                                            <div class="text-danger small mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif
                            @else
                                {{-- House/Room Booking - Date Range with Flatpickr --}}
                                <div class="mb-3" wire:ignore>
                                    <label class="form-label">
                                        <strong>Select New Date Range <span class="text-danger">*</span></strong>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-calendar-range"></i></span>
                                        <input type="text" id="rescheduleDate-{{ $booking->id }}"
                                            class="form-control" placeholder="Select check-in and check-out dates"
                                            autocomplete="off">
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Select {{ $originalNights }}-night date range (from
                                        {{ $booking->check_out->format('d M Y') }} onwards). Duration must remain the
                                        same.
                                        <span class="text-danger">• Dates marked in red are already booked</span>
                                    </small>

                                    {{-- Hidden inputs for Livewire --}}
                                    <input type="hidden" wire:model="newCheckIn"
                                        id="rescheduleCheckIn-{{ $booking->id }}">
                                    <input type="hidden" wire:model="newCheckOut"
                                        id="rescheduleCheckOut-{{ $booking->id }}">
                                </div>

                                @error('newCheckIn')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                @error('newCheckOut')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror

                                @if ($newCheckIn && $newCheckOut)
                                    <div class="alert alert-success mt-2 py-2">
                                        <i class="bi bi-calendar-range me-2"></i>
                                        <strong>Selected Range:</strong>
                                        {{ \Carbon\Carbon::parse($newCheckIn)->format('d M Y') }}
                                        <i class="bi bi-arrow-right mx-2"></i>
                                        {{ \Carbon\Carbon::parse($newCheckOut)->format('d M Y') }}
                                        <span class="badge bg-success ms-2">
                                            {{ \Carbon\Carbon::parse($newCheckIn)->diffInDays(\Carbon\Carbon::parse($newCheckOut)) }}
                                            nights
                                        </span>
                                    </div>
                                @endif
                            @endif

                            <div class="mb-3">
                                <label for="rescheduleReason" class="form-label">
                                    <strong>Reason for Rescheduling <span class="text-danger">*</span></strong>
                                </label>
                                <textarea wire:model="rescheduleReason" class="form-control @error('rescheduleReason') is-invalid @enderror"
                                    rows="4" placeholder="Please provide a detailed reason for rescheduling this booking..." required></textarea>
                                @error('rescheduleReason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Minimum 10 characters required</small>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-warning flex-fill"
                                    wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="submitRescheduleRequest">
                                        <i class="bi bi-send me-2"></i>Submit Reschedule Request
                                    </span>
                                    <span wire:loading wire:target="submitRescheduleRequest">
                                        <i class="bi bi-hourglass-split me-2"></i>Processing...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    <script>
        document.addEventListener('livewire:init', function() {
            Livewire.on('modal-opened', function() {
                setTimeout(function() {
                    initReschedulePicker();
                }, 200);
            });
        });

        function initReschedulePicker() {
            const picker = document.getElementById('rescheduleDate-{{ $booking->id }}');
            if (!picker) return;

            // Load Flatpickr if not already loaded
            if (typeof flatpickr === 'undefined') {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css';
                document.head.appendChild(link);

                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/flatpickr';
                script.onload = function() {
                    initFlatpickrInstance();
                };
                document.head.appendChild(script);
            } else {
                initFlatpickrInstance();
            }
        }

        function initFlatpickrInstance() {
            const picker = document.getElementById('rescheduleDate-{{ $booking->id }}');
            const checkInInput = document.getElementById('rescheduleCheckIn-{{ $booking->id }}');
            const checkOutInput = document.getElementById('rescheduleCheckOut-{{ $booking->id }}');
            const isBoatBooking = {{ $isBoatBooking ? 'true' : 'false' }};

            if (!picker) return;

            if (picker._flatpickr) {
                picker._flatpickr.destroy();
            }

            const bookedDates = @json($bookedDates);
            const minDate = '{{ $booking->check_out->format('Y-m-d') }}';

            flatpickr(picker, {
                mode: isBoatBooking ? 'single' : 'range',
                minDate: minDate,
                dateFormat: 'Y-m-d',
                disable: bookedDates,
                onChange: function(selectedDates) {
                    if (isBoatBooking && selectedDates.length === 1) {
                        const checkIn = selectedDates[0].getFullYear() + '-' +
                            String(selectedDates[0].getMonth() + 1).padStart(2, '0') + '-' +
                            String(selectedDates[0].getDate()).padStart(2, '0');

                        checkInInput.value = checkIn;
                        checkInInput.dispatchEvent(new Event('input'));
                        @this.set('newCheckIn', checkIn);
                    } else if (!isBoatBooking && selectedDates.length === 2) {
                        const checkIn = selectedDates[0].getFullYear() + '-' +
                            String(selectedDates[0].getMonth() + 1).padStart(2, '0') + '-' +
                            String(selectedDates[0].getDate()).padStart(2, '0');
                        const checkOut = selectedDates[1].getFullYear() + '-' +
                            String(selectedDates[1].getMonth() + 1).padStart(2, '0') + '-' +
                            String(selectedDates[1].getDate()).padStart(2, '0');

                        checkInInput.value = checkIn;
                        checkOutInput.value = checkOut;
                        checkInInput.dispatchEvent(new Event('input'));
                        checkOutInput.dispatchEvent(new Event('input'));

                        @this.set('newCheckIn', checkIn);
                        @this.set('newCheckOut', checkOut);
                    }
                }
            });
        }
    </script>
</div>
