<div>
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
                <small><strong>New Check-out:</strong> {{ $booking->new_check_out?->format('d M Y') }}</small><br>
                <small><strong>Reschedule Fee:</strong> {{ currency_format($booking->reschedule_fee ?? 0) }}</small>
            </div>
        </div>
    @endif

    <!-- Reschedule Modal -->
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
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

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6>Current Booking Details:</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Reference:</strong> #{{ $booking->id }}</li>
                                    <li><strong>Property:</strong> {{ $booking->bookingable->name ?? 'N/A' }}</li>
                                    <li><strong>Current Check-in:</strong> {{ $booking->check_in->format('d M Y') }}
                                    </li>
                                    <li><strong>Current Check-out:</strong> {{ $booking->check_out->format('d M Y') }}
                                    </li>
                                    <li><strong>Total Amount:</strong>
                                        {{ currency_format(number_format($booking->total_amount ?? $booking->price, 2)) }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-warning mb-0">
                                    <h6 class="mb-2"><i class="bi bi-cash-coin me-2"></i>Reschedule Fees:</h6>
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
                            <div class="mb-3" wire:ignore>
                                <label for="rescheduleDate-{{ $booking->id }}" class="form-label">
                                    <strong>Select New Dates <span class="text-danger">*</span></strong>
                                </label>
                                <input type="text" id="rescheduleDate-{{ $booking->id }}"
                                    class="form-control reschedule-daterange"
                                    placeholder="Select check-in and check-out dates" readonly>
                            </div>
                            <input type="hidden" wire:model="newCheckIn" id="rescheduleCheckIn-{{ $booking->id }}">
                            <input type="hidden" wire:model="newCheckOut" id="rescheduleCheckOut-{{ $booking->id }}">
                            @error('newCheckIn')
                                <div class="text-danger small mb-2">{{ $message }}</div>
                            @enderror
                            @error('newCheckOut')
                                <div class="text-danger small mb-2">{{ $message }}</div>
                            @enderror

                            <script>
                                (function() {
                                    const bookingId = '{{ $booking->id }}';
                                    const inputId = 'rescheduleDate-' + bookingId;

                                    function initDatePicker() {
                                        if (typeof jQuery === 'undefined' || typeof moment === 'undefined') {
                                            setTimeout(initDatePicker, 100);
                                            return;
                                        }

                                        const $ = jQuery;
                                        const $input = $('#' + inputId);

                                        if (!$input.length) {
                                            setTimeout(initDatePicker, 100);
                                            return;
                                        }

                                        // Destroy existing instance if any
                                        if ($input.data('daterangepicker')) {
                                            $input.data('daterangepicker').remove();
                                        }

                                        const today = moment();
                                        const tomorrow = moment().add(1, 'days');
                                        const defaultEnd = moment().add(3, 'days');

                                        $input.daterangepicker({
                                            opens: 'center',
                                            startDate: tomorrow,
                                            endDate: defaultEnd,
                                            minDate: today,
                                            autoUpdateInput: false,
                                            locale: {
                                                format: 'DD MMM YYYY',
                                                separator: ' - ',
                                                applyLabel: 'Apply',
                                                cancelLabel: 'Clear',
                                            }
                                        });

                                        $input.on('apply.daterangepicker', function(ev, picker) {
                                            const checkIn = picker.startDate.format('YYYY-MM-DD');
                                            const checkOut = picker.endDate.format('YYYY-MM-DD');
                                            const display = picker.startDate.format('DD MMM YYYY') + ' - ' + picker.endDate.format(
                                                'DD MMM YYYY');

                                            $(this).val(display);
                                            $('#rescheduleCheckIn-' + bookingId).val(checkIn).trigger('change');
                                            $('#rescheduleCheckOut-' + bookingId).val(checkOut).trigger('change');

                                            // Update Livewire component
                                            window.Livewire.find('{{ $_instance->getId() }}').set('newCheckIn', checkIn);
                                            window.Livewire.find('{{ $_instance->getId() }}').set('newCheckOut', checkOut);
                                        });

                                        $input.on('cancel.daterangepicker', function(ev, picker) {
                                            $(this).val('');
                                            $('#rescheduleCheckIn-' + bookingId).val('').trigger('change');
                                            $('#rescheduleCheckOut-' + bookingId).val('').trigger('change');
                                        });

                                        console.log('Reschedule daterangepicker initialized for booking #' + bookingId);
                                    }

                                    // Initialize on DOM ready
                                    if (document.readyState === 'loading') {
                                        document.addEventListener('DOMContentLoaded', initDatePicker);
                                    } else {
                                        initDatePicker();
                                    }

                                    // Re-initialize after small delay when modal opens
                                    setTimeout(initDatePicker, 500);
                                })();
                            </script>

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
                                <button type="button" class="btn btn-secondary flex-fill" wire:click="closeModal">
                                    <i class="bi bi-x me-2"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-warning flex-fill">
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
