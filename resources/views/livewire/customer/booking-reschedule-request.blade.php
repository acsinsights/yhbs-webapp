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
    </style>

    <!-- Reschedule Booking Button -->
    @if ($booking->canBeRescheduled())
        <button wire:click="openModal" wire:loading.attr="disabled" class="btn btn-warning w-100 mt-2" type="button">
            <span wire:loading.remove wire:target="openModal">
                <i class="bi bi-calendar-event me-2"></i>Request Reschedule
            </span>
            <span wire:loading wire:target="openModal">
                <i class="bi bi-hourglass-split me-2"></i>Loading...
            </span>
        </button>
        @if (config('app.debug'))
            <small class="text-muted d-block mt-1">Debug: Modal State = {{ $showModal ? 'Open' : 'Closed' }}</small>
        @endif
    @elseif($booking->hasRescheduleRequest())
        <div class="alert alert-info mt-2">
            <i class="bi bi-clock me-2"></i>
            <strong>Reschedule Request Pending</strong>
            <p class="mb-0 mt-2">Your reschedule request is being reviewed by our team.</p>
            <div class="mt-2">
                <small><strong>New Check-in:</strong> {{ $booking->new_check_in?->format('d M Y') }}</small><br>
                @if (!$booking->bookingable instanceof \App\Models\Boat)
                    <small><strong>New Check-out:</strong> {{ $booking->new_check_out?->format('d M Y') }}</small><br>
                @endif
                @if ($booking->requested_time_slot)
                    <small><strong>Requested Time:</strong> {{ $booking->requested_time_slot }}</small><br>
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
                                    <li><strong>Current Check-in:</strong> {{ $booking->check_in->format('d M Y') }}
                                    </li>
                                    <li><strong>Current Check-out:</strong> {{ $booking->check_out->format('d M Y') }}
                                    </li>
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
                                <div class="mb-3">
                                    <label class="form-label">
                                        <strong>Select New Date <span class="text-danger">*</span></strong>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                                        <input type="date" id="newCheckIn-{{ $booking->id }}"
                                            wire:model.live="newCheckIn" class="form-control"
                                            min="{{ $booking->check_out->format('Y-m-d') }}">
                                    </div>
                                    <small class="text-muted">Select the date for your boat trip (from
                                        {{ $booking->check_out->format('d M Y') }} onwards)</small>
                                    @error('newCheckIn')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            @else
                                {{-- House/Room Booking - Date Range with DateRangePicker --}}
                                <div class="mb-3">
                                    <label class="form-label">
                                        <strong>Select New Date Range <span class="text-danger">*</span></strong>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-calendar-range"></i></span>
                                        <input type="text" id="rescheduleDate-{{ $booking->id }}"
                                            class="form-control" placeholder="Select check-in and check-out dates"
                                            data-booked-dates='@json($bookedDates)'
                                            data-min-date="{{ $booking->check_out->format('Y-m-d') }}"
                                            data-booking-id="{{ $booking->id }}" autocomplete="off">
                                        <button type="button" class="btn btn-outline-secondary"
                                            id="calendarBtn-{{ $booking->id }}">
                                            <i class="bi bi-calendar3"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Select new dates (from {{ $booking->check_out->format('d M Y') }} onwards)
                                        <span class="text-danger">• Dates marked in red are already booked</span>
                                    </small>

                                    {{-- Hidden inputs for Livewire --}}
                                    <input type="hidden" wire:model="newCheckIn"
                                        id="rescheduleCheckIn-{{ $booking->id }}">
                                    <input type="hidden" wire:model="newCheckOut"
                                        id="rescheduleCheckOut-{{ $booking->id }}">

                                    @error('newCheckIn')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    @error('newCheckOut')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror

                                    <div id="availabilityMessage-{{ $booking->id }}" class="alert mt-2 d-none"></div>

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
                                </div>
                            @endif

                            @if ($isBoatBooking && $newCheckIn)
                                <div class="mb-3">
                                    <label class="form-label">
                                        <strong>Select Time Slot <span class="text-danger">*</span></strong>
                                    </label>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($availableTimeSlots as $slot)
                                            <label class="time-slot-btn">
                                                <input class="d-none" type="radio" wire:model.live="selectedTimeSlot"
                                                    value="{{ $slot }}" id="timeSlot{{ $loop->index }}">
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

                            <div class="mb-3">
                                <label for="rescheduleReason" class="form-label">
                                    <strong>Reason for Rescheduling <span class="text-danger">*</span></strong>
                                </label>
                                <textarea wire:model="rescheduleReason" id="rescheduleReason"
                                    class="form-control @error('rescheduleReason') is-invalid @enderror" rows="4"
                                    placeholder="Please provide a detailed reason for rescheduling this booking..." required></textarea>
                                @error('rescheduleReason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Minimum 10 characters required</small>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-warning flex-fill">
                                    <i class="bi bi-send me-2"></i>Submit Reschedule Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    @script
    <script>
        // Listen for modal opened event
        $wire.on('modal-opened', () => {
            console.log('Modal opened event received');
            setTimeout(function() {
                if (typeof initRescheduleDatePicker === 'function') {
                    console.log('Initializing reschedule date picker from modal');
                    initRescheduleDatePicker();
                } else {
                    console.error('initRescheduleDatePicker function not found');
                }
            }, 500);
        });
    </script>
    @endscript
</div>
