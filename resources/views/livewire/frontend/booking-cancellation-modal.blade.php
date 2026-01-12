<?php

use Livewire\Volt\Component;
use App\Models\Booking;
use Illuminate\Support\Facades\{Auth, Mail};
use App\Mail\BookingCancellationRequestMail;
use App\Notifications\BookingStatusNotification;

new class extends Component {
    public Booking $booking;
    public string $cancellationReason = '';
    public bool $showModal = false;

    public function mount(int $bookingId): void
    {
        $this->booking = Booking::with(['user', 'bookingable'])->findOrFail($bookingId);

        if ($this->booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to booking.');
        }
    }

    public function openModal(): void
    {
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->cancellationReason = '';
        $this->resetValidation();
    }

    public function submitCancellationRequest(): void
    {
        $this->validate(
            [
                'cancellationReason' => 'required|string|min:10|max:500',
            ],
            [
                'cancellationReason.required' => 'Please provide a reason for cancellation.',
                'cancellationReason.min' => 'Reason must be at least 10 characters.',
                'cancellationReason.max' => 'Reason must not exceed 500 characters.',
            ],
        );

        if (!$this->booking->canBeCancelled()) {
            session()->flash('error', 'This booking cannot be cancelled.');
            $this->closeModal();
            return;
        }

        $this->booking->update([
            'cancellation_requested_at' => now(),
            'cancellation_status' => 'pending',
            'cancellation_reason' => $this->cancellationReason,
        ]);

        try {
            Mail::to(config('mail.admin_email', 'admin@yhbs.com'))->send(new BookingCancellationRequestMail($this->booking));
        } catch (\Exception $e) {
            \Log::error('Failed to send cancellation request email: ' . $e->getMessage());
        }

        // Send notification to all admin and superadmin users
        $admins = \App\Models\User::role(['admin', 'superadmin'])->get();
        foreach ($admins as $admin) {
            $admin->notify(new BookingStatusNotification($this->booking, 'cancellation_request'));
        }

        session()->flash('success', 'Your cancellation request has been submitted successfully. Our team will review it and get back to you soon.');
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
    </style>

    <!-- Cancel Booking Button -->
    @if ($booking->canBeCancelled())
        <button wire:click="openModal" wire:loading.attr="disabled" class="btn btn-danger w-100" type="button">
            <span wire:loading.remove wire:target="openModal">
                <i class="bi bi-x-circle me-2"></i>Request Cancellation
            </span>
            <span wire:loading wire:target="openModal">
                <i class="bi bi-hourglass-split me-2"></i>Loading...
            </span>
        </button>
    @elseif($booking->hasCancellationRequest())
        <div class="alert alert-warning mt-2">
            <i class="bi bi-clock me-2"></i>
            <strong>Cancellation Request Pending</strong>
            <p class="mb-0 mt-2">Your cancellation request is being reviewed by our team.</p>
        </div>
    @elseif($booking->isCancelled())
        <div class="alert alert-info mt-2">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Booking Cancelled</strong>
            <p class="mb-0 mt-2">This booking has been cancelled.</p>
        </div>
    @endif

    <!-- Cancellation Modal -->
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title text-white">
                            <i class="bi bi-exclamation-triangle me-2"></i>Request Booking Cancellation
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Important:</strong> Your cancellation request will be reviewed by our team. Refund
                            will be processed as per our cancellation policy.
                        </div>

                        <div class="mb-3">
                            <h6>Booking Details:</h6>
                            <ul class="list-unstyled">
                                <li><strong>Reference:</strong> #{{ $booking->id }}</li>
                                @if($booking->check_in)
                                    <li><strong>Check-in:</strong> {{ $booking->check_in->format('d M Y') }}</li>
                                @endif
                                @if($booking->check_out)
                                    <li><strong>Check-out:</strong> {{ $booking->check_out->format('d M Y') }}</li>
                                @endif
                                <li><strong>Total Amount:</strong>
                                    {{ currency_format($booking->total_amount) }}</li>
                            </ul>
                        </div>

                        <form wire:submit.prevent="submitCancellationRequest">
                            <div class="mb-3">
                                <label for="cancellationReason" class="form-label">
                                    <strong>Reason for Cancellation <span class="text-danger">*</span></strong>
                                </label>
                                <textarea wire:model="cancellationReason" class="form-control @error('cancellationReason') is-invalid @enderror"
                                    rows="4" placeholder="Please provide a detailed reason for cancelling this booking..." required></textarea>
                                @error('cancellationReason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Minimum 10 characters required</small>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-secondary flex-fill" wire:click="closeModal">
                                    <i class="bi bi-x me-2"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-danger flex-fill" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="submitCancellationRequest">
                                        <i class="bi bi-send me-2"></i>Submit Request
                                    </span>
                                    <span wire:loading wire:target="submitCancellationRequest">
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
</div>
