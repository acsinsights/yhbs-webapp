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
        <button wire:click="openModal" class="btn btn-warning w-100 mt-2">
            <i class="bi bi-calendar-event me-2"></i>Request Reschedule
        </button>
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
                        @include('livewire.customer.booking-reschedule-form')
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
