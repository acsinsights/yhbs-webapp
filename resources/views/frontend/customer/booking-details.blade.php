@extends('frontend.layouts.app')
@section('title', 'Booking Details - YHBS')
@section('content')
    <!-- Breadcrumb section Start-->
    <div class="breadcrumb-section"
        style="background-image:linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url({{ asset('frontend/img/innerpages/breadcrumb-bg6.jpg') }});">
        <div class="container">
            <div class="banner-content">
                <h1>Booking Details</h1>
                <ul class="breadcrumb-list">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li><a href="{{ route('customer.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('customer.bookings') }}">Bookings</a></li>
                    <li>Booking Details</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Booking Details Section Start -->
    <div class="customer-bookings-section pt-100 pb-100">
        <div class="container">
            <div class="row">
                <!-- Booking Information -->
                <div class="col-lg-8 mb-4">
                    <div class="confirmation-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><i class="bi bi-receipt me-2"></i>Booking Information</h4>
                            <div>
                                <span class="badge {{ $booking->status?->badgeColor() ?? 'badge-secondary' }}">
                                    {{ $booking->status?->label() ?? 'Pending' }}
                                </span>

                                @if ($booking->cancellation_status === 'pending')
                                    <span class="badge bg-warning ms-2">
                                        <i class="bi bi-clock me-1"></i>Cancellation Pending
                                    </span>
                                @elseif($booking->cancellation_status === 'rejected')
                                    <span class="badge bg-info ms-2">
                                        <i class="bi bi-info-circle me-1"></i>Cancellation Declined
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Cancellation Alert -->
                        @if ($booking->cancellation_status === 'pending')
                            <div class="alert alert-warning mb-0 rounded-0">
                                <small><i class="bi bi-info-circle me-2"></i>Your cancellation request is under
                                    review</small>
                            </div>
                        @elseif($booking->cancellation_status === 'approved' || $booking->cancelled_at)
                            <div class="alert alert-success mb-0 rounded-0">
                                <small>
                                    <i class="bi bi-check-circle me-2"></i>Booking cancelled successfully
                                    @if ($booking->refund_amount > 0)
                                        - Refund: {{ currency_format($booking->refund_amount) }}
                                    @endif
                                </small>
                            </div>
                        @elseif($booking->cancellation_status === 'rejected')
                            <div class="alert alert-danger mb-0 rounded-0">
                                <small><i class="bi bi-x-circle me-2"></i>Cancellation request was declined</small>
                            </div>
                        @endif

                        <div class="card-body">
                            <!-- Property Image -->
                            @if ($booking->bookingable)
                                @php
                                    $propertyImage = asset('frontend/img/default-property.jpg');
                                    if ($booking->bookingable->image) {
                                        $image = $booking->bookingable->image;
                                        if (str_starts_with($image, 'http')) {
                                            $propertyImage = $image;
                                        } elseif (
                                            str_starts_with($image, '/default') ||
                                            str_starts_with($image, '/frontend')
                                        ) {
                                            $propertyImage = asset($image);
                                        } elseif (
                                            str_starts_with($image, 'default/') ||
                                            str_starts_with($image, 'frontend/')
                                        ) {
                                            $propertyImage = asset($image);
                                        } elseif (str_starts_with($image, 'storage/')) {
                                            $propertyImage = asset($image);
                                        } else {
                                            $propertyImage = asset('storage/' . $image);
                                        }
                                    }
                                @endphp
                                <img src="{{ $propertyImage }}" alt="{{ $booking->bookingable->name ?? 'Property' }}"
                                    class="confirmation-image mb-4"
                                    onerror="this.src='{{ asset('frontend/img/default-property.jpg') }}'">
                            @endif

                            <!-- Property Details -->
                            <h5 class="mb-3">{{ $booking->bookingable->name ?? 'Property' }}</h5>
                            <p class="text-muted mb-4">
                                <i class="bi bi-geo-alt me-2"></i>{{ $booking->bookingable->location ?? 'N/A' }}
                            </p>

                            <!-- Booking Details Grid -->
                            <div class="detail-grid">
                                @php
                                    $isBoat = $booking->bookingable_type === \App\Models\Boat::class;
                                    $boatDetails = $booking->guest_details['boat_details'] ?? null;
                                @endphp

                                <!-- Booking ID -->
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <i class="bi bi-hash"></i>
                                    </div>
                                    <div>
                                        <small>Booking ID</small>
                                        <p><strong>{{ $booking->booking_id }}</strong></p>
                                    </div>
                                </div>

                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <i class="bi bi-calendar-check"></i>
                                    </div>
                                    <div>
                                        <small>{{ $isBoat ? 'Booking Date' : 'Check-in' }}</small>
                                        <p>{{ $booking->check_in ? \Carbon\Carbon::parse($booking->check_in)->format('M d, Y') : 'N/A' }}
                                        </p>
                                    </div>
                                </div>

                                @if (!$isBoat)
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="bi bi-calendar-x"></i>
                                        </div>
                                        <div>
                                            <small>Check-out</small>
                                            <p>{{ $booking->check_out ? \Carbon\Carbon::parse($booking->check_out)->format('M d, Y') : 'N/A' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="bi bi-moon-stars"></i>
                                        </div>
                                        <div>
                                            <small>Duration</small>
                                            <p>
                                                @if ($booking->check_in && $booking->check_out)
                                                    {{ \Carbon\Carbon::parse($booking->check_in)->diffInDays(\Carbon\Carbon::parse($booking->check_out)) }}
                                                    Nights
                                                @else
                                                    N/A
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                @else
                                    <!-- Boat-specific fields - Time Slot instead of Start Time -->
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="bi bi-clock"></i>
                                        </div>
                                        <div>
                                            <small>Time Slot</small>
                                            <p>
                                                @if ($booking->check_in && $booking->check_out)
                                                    {{ $booking->check_in->format('h:i A') }} -
                                                    {{ $booking->check_out->format('h:i A') }}
                                                @elseif($boatDetails && isset($boatDetails['start_time']))
                                                    {{ $boatDetails['start_time'] }}
                                                @else
                                                    N/A
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                @if ($boatDetails && isset($boatDetails['duration']))
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="bi bi-hourglass-split"></i>
                                        </div>
                                        <div>
                                            <small>Duration</small>
                                            <p>{{ $boatDetails['duration'] }} Hour(s)</p>
                                        </div>
                                    </div>
                                @endif

                                @if ($boatDetails && isset($boatDetails['ferry_type']))
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="bi bi-ticket"></i>
                                        </div>
                                        <div>
                                            <small>Ferry Type</small>
                                            <p>{{ ucfirst(str_replace('_', ' ', $boatDetails['ferry_type'])) }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if ($boatDetails && isset($boatDetails['experience_duration']))
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="bi bi-star"></i>
                                        </div>
                                        <div>
                                            <small>Experience</small>
                                            <p>
                                                @if ($boatDetails['experience_duration'] === 'full')
                                                    Full Experience
                                                @else
                                                    {{ $boatDetails['experience_duration'] }} Minutes
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div>
                                        <small>{{ $isBoat ? 'Passengers' : 'Guests' }}</small>
                                        <p>{{ ($booking->adults ?? 0) + ($booking->children ?? 0) }}
                                            {{ $isBoat ? 'Passenger(s)' : 'Guests' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Passenger Names for Boats -->
                            @if ($isBoat && $booking->guest_details)
                                @php
                                    $guestDetails = is_array($booking->guest_details)
                                        ? $booking->guest_details
                                        : json_decode($booking->guest_details, true);
                                    $adultNames = $guestDetails['adult_names'] ?? [];
                                    $childrenNames = $guestDetails['children_names'] ?? [];
                                @endphp

                                @if (!empty($adultNames) || !empty($childrenNames))
                                    <div class="mt-4">
                                        <h6 class="mb-3"><i class="bi bi-person-badge me-2"></i>Passenger Names</h6>
                                        <div class="row g-2">
                                            @foreach ($adultNames as $index => $name)
                                                @if ($name)
                                                    <div class="col-md-6">
                                                        <div class="d-flex align-items-center gap-2 p-2 bg-light rounded">
                                                            <i class="bi bi-person-circle text-primary"></i>
                                                            <span class="flex-grow-1">{{ $name }}</span>
                                                            <span class="badge bg-primary">Passenger
                                                                {{ $index + 1 }}</span>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach

                                            @foreach ($childrenNames as $index => $name)
                                                @if ($name)
                                                    <div class="col-md-6">
                                                        <div class="d-flex align-items-center gap-2 p-2 bg-light rounded">
                                                            <i class="bi bi-person-circle text-secondary"></i>
                                                            <span class="flex-grow-1">{{ $name }}</span>
                                                            <span class="badge bg-secondary">Child
                                                                {{ $index + 1 }}</span>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endif

                            <!-- Additional Information -->
                            @if ($booking->notes)
                                <div class="mt-4">
                                    <h6><i class="bi bi-chat-left-text me-2"></i>Special Requests</h6>
                                    <p class="text-muted">{{ $booking->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="col-lg-4">
                    <div class="confirmation-card mb-4">
                        <div class="card-header">
                            <h4><i class="bi bi-credit-card me-2"></i>Payment Summary</h4>
                        </div>
                        <div class="card-body">
                            <div class="payment-breakdown">
                                @php
                                    // Get values from booking object (already calculated in controller)
                                    $nights = $booking->nights ?? 1;
                                    $pricePerNight = $booking->price_per_night ?? 0;
                                    $serviceFee = $booking->service_fee ?? 0;
                                    $tax = $booking->tax ?? 0;
                                    $discount = $booking->discount_amount ?? 0;
                                    $walletUsed = $booking->wallet_amount_used ?? 0;

                                    // Use actual stored price (accounts for tiered pricing)
                                    $baseAmount =
                                        isset($booking->total_amount) && $booking->total_amount > 0
                                            ? $booking->total_amount
                                            : $booking->price ?? 0;
                                    $subtotal = $booking->price ?? $pricePerNight * $nights;
                                @endphp

                                @if (!$isBoat)
                                    {{-- Show price per night only for non-boat bookings --}}
                                    <div class="payment-row">
                                        <span>Price per night</span>
                                        <span>{{ currency_format($pricePerNight) }}</span>
                                    </div>
                                    <div class="payment-row">
                                        <span>× {{ $nights }} {{ $nights > 1 ? 'nights' : 'night' }}</span>
                                        <span>{{ currency_format($subtotal) }}</span>
                                    </div>
                                @else
                                    {{-- For boat bookings, show booking amount directly --}}
                                    <div class="payment-row">
                                        <span>Booking Amount
                                            @if (isset($boatDetails['duration']))
                                                ({{ $boatDetails['duration'] }}
                                                hour{{ $boatDetails['duration'] > 1 ? 's' : '' }})
                                            @endif
                                        </span>
                                        <span>{{ currency_format($subtotal) }}</span>
                                    </div>
                                @endif

                                @if ($serviceFee > 0)
                                    <div class="payment-row">
                                        <span>Service fee</span>
                                        <span>{{ currency_format($serviceFee) }}</span>
                                    </div>
                                @endif

                                @if ($tax > 0)
                                    <div class="payment-row">
                                        <span>Taxes</span>
                                        <span>{{ currency_format($tax) }}</span>
                                    </div>
                                @endif

                                @if (isset($booking->reschedule_fee) && $booking->reschedule_fee > 0)
                                    <div class="payment-row text-warning">
                                        <span><i class="bi bi-calendar-check me-1"></i>Reschedule Fee</span>
                                        <span>{{ currency_format($booking->reschedule_fee) }}</span>
                                    </div>
                                @endif

                                @if (isset($booking->extra_fee) && $booking->extra_fee > 0)
                                    <div class="payment-row text-warning">
                                        <span>
                                            <i class="bi bi-plus-circle me-1"></i>Extra Fee
                                            @if (isset($booking->extra_fee_remark) && $booking->extra_fee_remark)
                                                <br><small class="text-muted">({{ $booking->extra_fee_remark }})</small>
                                            @endif
                                        </span>
                                        <span>{{ currency_format($booking->extra_fee) }}</span>
                                    </div>
                                @endif

                                {{-- Show subtotal before deductions if there are any discounts or wallet usage --}}
                                @if ($discount > 0 || $walletUsed > 0)
                                    <div class="divider"></div>
                                    <div class="payment-row" style="font-weight: 600;">
                                        <span>Subtotal</span>
                                        <span>{{ currency_format($baseAmount + ($booking->reschedule_fee ?? 0) + ($booking->extra_fee ?? 0)) }}</span>
                                    </div>
                                @endif

                                @if ($discount > 0)
                                    <div class="payment-row text-success">
                                        <span>
                                            <i class="bi bi-tag-fill me-1"></i>Discount
                                            @if (isset($booking->coupon_code) && $booking->coupon_code)
                                                <small>({{ $booking->coupon_code }})</small>
                                            @endif
                                        </span>
                                        <span>-{{ currency_format($discount) }}</span>
                                    </div>
                                @endif

                                @if ($walletUsed > 0)
                                    <div class="payment-row text-info">
                                        <span><i class="bi bi-wallet2 me-1"></i>Wallet Used</span>
                                        <span>-{{ currency_format($walletUsed) }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="divider"></div>

                            <div class="total-amount">
                                <span>Total Paid</span>
                                <span>{{ currency_format($baseAmount + ($booking->reschedule_fee ?? 0) + ($booking->extra_fee ?? 0) - ($booking->discount_amount ?? 0) - ($booking->wallet_amount_used ?? 0)) }}</span>
                            </div>

                            <div class="payment-status mt-3">
                                <span class="badge {{ $booking->payment_status?->badgeColor() ?? 'badge-warning' }}">
                                    Payment {{ $booking->payment_status?->label() ?? 'Pending' }}
                                </span>
                                <p class="text-muted mt-2 mb-0">
                                    <small>Method: {{ ucfirst($booking->payment_method?->value ?? 'N/A') }}</small>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="confirmation-card">
                        <div class="card-header">
                            <h4><i class="bi bi-gear me-2"></i>Actions</h4>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                {{-- Download Receipt Button - Only show after check-in --}}
                                @if (
                                    $booking->status == App\Enums\BookingStatusEnum::CHECKED_IN ||
                                        $booking->status == App\Enums\BookingStatusEnum::CHECKED_OUT)
                                    <a href="{{ route('customer.booking.download-receipt', $booking->id) }}"
                                        class="btn btn-primary" target="_blank">
                                        <i class="bi bi-download me-2"></i>Download Receipt (PDF)
                                    </a>
                                @endif

                                @if (
                                    !$booking->cancellation_status &&
                                        !$booking->cancelled_at &&
                                        in_array($booking->status, [
                                            App\Enums\BookingStatusEnum::PENDING,
                                            App\Enums\BookingStatusEnum::BOOKED,
                                            App\Enums\BookingStatusEnum::CHECKED_IN,
                                        ]))
                                    <livewire:customer.booking-cancellation-request :bookingId="$booking->id" />

                                    <livewire:customer.booking-reschedule-request :bookingId="$booking->id" />
                                @endif

                                <a href="{{ route('customer.bookings') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Back to Bookings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
