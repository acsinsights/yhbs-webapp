<div>
    <!-- Cancel Booking Button -->
    @if ($booking->canBeCancelled())
        <button wire:click="openModal" class="btn btn-danger w-100">
            <i class="bi bi-x-circle me-2"></i>Request Cancellation
        </button>
    @elseif($booking->hasCancellationRequest())
        <div class="alert alert-warning">
            <i class="bi bi-clock me-2"></i>
            <strong>Cancellation Request Pending</strong>
            <p class="mb-0 mt-2">Your cancellation request is being reviewed by our team.</p>
        </div>
    @elseif($booking->isCancelled())
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Booking Cancelled</strong>
            <p class="mb-0 mt-2">This booking has been cancelled.</p>
        </div>
    @endif

    <!-- Cancellation Modal -->
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
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
                                <li><strong>Check-in:</strong> {{ $booking->check_in->format('d M Y') }}</li>
                                <li><strong>Check-out:</strong> {{ $booking->check_out->format('d M Y') }}</li>
                                <li><strong>Total Amount:</strong>
                                    {{ currency_format(number_format($booking->total_amount, 2)) }}</li>
                            </ul>
                        </div>

                        <form wire:submit.prevent="submitCancellationRequest">
                            <div class="mb-3">
                                <label for="cancellationReason" class="form-label">
                                    <strong>Reason for Cancellation <span class="text-danger">*</span></strong>
                                </label>
                                <textarea wire:model="cancellationReason" id="cancellationReason"
                                    class="form-control @error('cancellationReason') is-invalid @enderror" rows="4"
                                    placeholder="Please provide a detailed reason for cancelling this booking..." required></textarea>
                                @error('cancellationReason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Minimum 10 characters required</small>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-secondary flex-fill" wire:click="closeModal">
                                    <i class="bi bi-x me-2"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-danger flex-fill">
                                    <i class="bi bi-send me-2"></i>Submit Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
