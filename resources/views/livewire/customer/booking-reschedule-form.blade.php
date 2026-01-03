<div class="confirmation-card">
    <div class="card-header bg-warning text-dark">
        <h4><i class="bi bi-calendar-event me-2"></i>Request Booking Reschedule</h4>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Important:</strong> Reschedule requests must be made at least 48 hours before your check-in date.
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Current Booking Details:</h5>
                <ul class="list-unstyled">
                    <li><strong>Reference:</strong> #{{ $booking->id }}</li>
                    <li><strong>Property:</strong> {{ $booking->bookingable->name ?? 'N/A' }}</li>
                    <li><strong>Current Check-in:</strong> {{ $booking->check_in->format('d M Y') }}</li>
                    <li><strong>Current Check-out:</strong> {{ $booking->check_out->format('d M Y') }}</li>
                    <li><strong>Total Amount:</strong>
                        {{ currency_format(number_format($booking->total_amount ?? $booking->price, 2)) }}
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
                        <input type="date" id="newCheckIn-{{ $booking->id }}" wire:model.live="newCheckIn"
                            class="form-control" min="{{ $booking->check_out->format('Y-m-d') }}">
                    </div>
                    <small class="text-muted">Select the date for your boat trip (from
                        {{ $booking->check_out->format('d M Y') }} onwards)</small>
                    @error('newCheckIn')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            @else
                {{-- House/Room Booking - Date Range --}}
                <div class="mb-3">
                    <label class="form-label">
                        <strong>Select New Date Range <span class="text-danger">*</span></strong>
                    </label>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                                <input type="date" id="newCheckIn-{{ $booking->id }}" wire:model.live="newCheckIn"
                                    class="form-control" min="{{ $booking->check_out->format('Y-m-d') }}"
                                    placeholder="Check-in">
                            </div>
                            <small class="text-muted">Check-in Date (from {{ $booking->check_out->format('d M Y') }}
                                onwards)</small>
                            @error('newCheckIn')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar-x"></i></span>
                                <input type="date" id="newCheckOut-{{ $booking->id }}"
                                    wire:model.live="newCheckOut" class="form-control"
                                    min="{{ $newCheckIn ?? date('Y-m-d', strtotime('+2 day')) }}"
                                    placeholder="Check-out" {{ !$newCheckIn ? 'disabled' : '' }}>
                            </div>
                            <small class="text-muted">Check-out Date</small>
                            @error('newCheckOut')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
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

                <style>
                    .time-slot-btn {
                        cursor: pointer;
                    }

                    .time-slot-btn:hover .btn {
                        transform: scale(1.05);
                    }
                </style>
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
