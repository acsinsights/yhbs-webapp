@extends('frontend.layouts.app')
@section('title', 'Checkout - YHBS')
@section('content')
    <!-- Breadcrumb section Start-->
    <div class="breadcrumb-section"
        style="background-image:linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url({{ asset('frontend/img/innerpages/breadcrumb-bg6.jpg') }});">
        <div class="container">
            <div class="banner-content">
                <h1>Checkout</h1>
                <ul class="breadcrumb-list">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li>Checkout</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Checkout Section Start -->
    <div class="checkout-section pt-100 pb-100">
        <div class="container">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i>Please fix the following
                        errors:</h5>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('booking.confirm') }}" method="POST" id="checkoutForm" novalidate>
                @csrf
                <div class="row">
                    <!-- Left Side - Customer Details -->
                    <div class="col-lg-7 mb-4">

                        <!-- Guest Details -->
                        <div class="checkout-card mb-4">
                            <div class="card-header">
                                <h4><i class="bi bi-person-lines-fill me-2"></i>Guest Information</h4>
                                <small class="text-muted">Atleast one guest details required</small>
                            </div>
                            <div class="card-body">
                                <div id="guestDetailsContainer">
                                    <!-- First Guest (Required) -->
                                    <div class="guest-detail-item mb-4 pb-3 border-bottom" data-guest-index="0">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0">Guest 1 *</h6>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Full Name *</label>
                                                <input type="text"
                                                    class="form-control @error('guests.0.name') is-invalid @enderror"
                                                    name="guests[0][name]" required value="{{ old('guests.0.name') }}"
                                                    placeholder="Enter guest full name">
                                                @error('guests.0.name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Email *</label>
                                                <input type="email"
                                                    class="form-control @error('guests.0.email') is-invalid @enderror"
                                                    name="guests[0][email]" required value="{{ old('guests.0.email') }}"
                                                    placeholder="Enter guest email">
                                                @error('guests.0.email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Phone Number *</label>
                                                <input type="tel"
                                                    class="form-control @error('guests.0.phone') is-invalid @enderror"
                                                    name="guests[0][phone]" required value="{{ old('guests.0.phone') }}"
                                                    placeholder="Enter guest phone">
                                                @error('guests.0.phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addGuestBtn">
                                    <i class="bi bi-plus-circle me-2"></i>Add Another Guest
                                </button>
                            </div>
                        </div>
                        <!-- Special Requests -->
                        <div class="checkout-card mb-4">
                            <div class="card-header">
                                <h4><i class="bi bi-chat-left-text me-2"></i>Special Requests</h4>
                            </div>
                            <div class="card-body">
                                <label for="special_requests" class="form-label">Any special requests? (Optional)</label>
                                <textarea class="form-control" id="special_requests" name="special_requests" rows="4"
                                    placeholder="Early check-in, late check-out, dietary requirements, etc.">{{ old('special_requests') }}</textarea>
                            </div>
                        </div>
                        <!-- Customer Information -->
                        <div class="checkout-card mt-4">
                            <div class="card-header">
                                <h4><i class="bi bi-person-lines-fill me-2"></i>Your Account Information</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">First Name *</label>
                                        <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                            id="first_name" name="first_name"
                                            value="{{ old('first_name', auth()->user()->first_name ?? '') }}" required>
                                        @error('first_name')
                                            <div class="invalid-feedback" data-backend>{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Last Name *</label>
                                        <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                            id="last_name" name="last_name"
                                            value="{{ old('last_name', auth()->user()->last_name ?? '') }}" required>
                                        @error('last_name')
                                            <div class="invalid-feedback" data-backend>{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="email" name="email"
                                            value="{{ old('email', auth()->user()->email ?? '') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback" data-backend>{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                            id="phone" name="phone"
                                            value="{{ old('phone', auth()->user()->phone ?? '') }}" required>
                                        @error('phone')
                                            <div class="invalid-feedback" data-backend>{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Booking Summary -->
                    <div class="col-lg-5">
                        <div class="booking-summary-sticky">
                            <!-- Booking Summary -->
                            <div class="checkout-card mb-4">
                                <div class="card-header">
                                    <h4><i class="bi bi-card-checklist me-2"></i>Booking Summary</h4>
                                </div>
                                <div class="card-body">
                                    <div class="property-preview mb-4">
                                        @php
                                            // property_image already comes as full asset path from controller
                                            $propertyImagePath =
                                                $booking->property_image ??
                                                asset('frontend/assets/img/innerpages/hotel-img1.jpg');
                                        @endphp
                                        <img src="{{ $propertyImagePath }}" alt="Property" class="property-image">
                                        <h5 class="mt-3">{{ $booking->property_name ?? 'Luxury Room' }}</h5>
                                        <p class="text-muted mb-0">
                                            <i class="bi bi-geo-alt me-2"></i>{{ $booking->location ?? 'Location' }}
                                        </p>
                                    </div>

                                    <div class="booking-details-summary">
                                        <div class="detail-row">
                                            <div class="detail-label">
                                                <i class="bi bi-calendar-check me-2"></i>
                                                @if ($booking->type === 'boat')
                                                    Booking Date
                                                @else
                                                    Check-in
                                                @endif
                                            </div>
                                            <div class="detail-value">
                                                {{ $booking->check_in_display ?? ($booking->check_in ?? 'N/A') }}
                                            </div>
                                        </div>

                                        @if ($booking->type !== 'boat')
                                            <div class="detail-row">
                                                <div class="detail-label">
                                                    <i class="bi bi-calendar-x me-2"></i>Check-out
                                                </div>
                                                <div class="detail-value">
                                                    {{ $booking->check_out_display ?? ($booking->check_out ?? 'N/A') }}
                                                </div>
                                            </div>
                                        @endif

                                        @if ($booking->type === 'boat')
                                            <!-- Boat-specific details -->
                                            @if (isset($booking->start_time))
                                                <div class="detail-row">
                                                    <div class="detail-label">
                                                        <i class="bi bi-clock me-2"></i>Time Slot
                                                    </div>
                                                    <div class="detail-value">
                                                        @php
                                                            // Calculate end time based on duration
                                                            $startTime = $booking->start_time;
                                                            $duration = 0;

                                                            if (
                                                                $booking->service_type === 'hourly' &&
                                                                isset($booking->duration)
                                                            ) {
                                                                $duration = (float) $booking->duration;
                                                            } elseif (
                                                                $booking->service_type === 'experience' &&
                                                                isset($booking->experience_duration)
                                                            ) {
                                                                $duration =
                                                                    $booking->experience_duration === 'full'
                                                                        ? 1
                                                                        : (float) $booking->experience_duration / 60;
                                                            } else {
                                                                $duration = 1; // Default 1 hour for ferry
                                                            }

                                                            try {
                                                                $start = \Carbon\Carbon::createFromFormat(
                                                                    'H:i',
                                                                    $startTime,
                                                                );
                                                                $end = $start->copy()->addHours($duration);
                                                                $timeSlot =
                                                                    $start->format('h:i A') .
                                                                    ' - ' .
                                                                    $end->format('h:i A');
                                                            } catch (\Exception $e) {
                                                                $timeSlot = $startTime;
                                                            }
                                                        @endphp
                                                        {{ $timeSlot }}
                                                    </div>
                                                </div>
                                            @endif

                                            @if ($booking->service_type === 'hourly' && isset($booking->duration))
                                                <div class="detail-row">
                                                    <div class="detail-label">
                                                        <i class="bi bi-hourglass-split me-2"></i>Duration
                                                    </div>
                                                    <div class="detail-value">
                                                        {{ $booking->duration ?? 1 }} Hour(s)
                                                    </div>
                                                </div>
                                            @elseif ($booking->service_type === 'ferry_service' && isset($booking->ferry_type))
                                                <div class="detail-row">
                                                    <div class="detail-label">
                                                        <i class="bi bi-ticket me-2"></i>Ferry Type
                                                    </div>
                                                    <div class="detail-value">
                                                        {{ ucfirst(str_replace('_', ' ', $booking->ferry_type)) }}
                                                    </div>
                                                </div>
                                            @elseif ($booking->service_type === 'experience' && isset($booking->experience_duration))
                                                <div class="detail-row">
                                                    <div class="detail-label">
                                                        <i class="bi bi-star me-2"></i>Experience
                                                    </div>
                                                    <div class="detail-value">
                                                        @if ($booking->experience_duration === 'full')
                                                            Full Boat Experience
                                                        @else
                                                            {{ $booking->experience_duration }} Minutes
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        @else
                                            <div class="detail-row">
                                                <div class="detail-label">
                                                    <i class="bi bi-moon me-2"></i>Nights
                                                </div>
                                                <div class="detail-value">
                                                    {{ $booking->nights ?? '1' }} Nights
                                                </div>
                                            </div>
                                        @endif

                                        <div class="detail-row">
                                            <div class="detail-label">
                                                <i class="bi bi-people me-2"></i>
                                                @if ($booking->type === 'boat')
                                                    Passengers
                                                @else
                                                    Guests
                                                @endif
                                            </div>
                                            <div class="detail-value">
                                                {{ $booking->guests ?? '2' }}
                                                @if ($booking->type === 'boat')
                                                    Passenger(s)
                                                @else
                                                    Adults
                                                @endif
                                                @if (isset($booking->children) && $booking->children > 0)
                                                    , {{ $booking->children }} Children
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="divider"></div>

                                    <!-- Coupon Section -->
                                    <div class="coupon-section mb-3">
                                        @livewire('frontend.coupon-input', [
                                            'bookingAmount' => $booking->subtotal + ($booking->service_fee ?? 0) + ($booking->tax ?? 0),
                                            'pricePerNight' => $booking->price_per_night ?? 0,
                                            'nights' => $booking->nights ?? 1,
                                            'propertyType' => $booking->type ?? '',
                                            'propertyId' => $booking->property_id ?? null,
                                        ])
                                    </div>

                                    <div class="price-breakdown">
                                        @if ($booking->type === 'boat')
                                            <!-- For boats, show subtotal directly without breaking it down -->
                                            <div class="price-row">
                                                <span>
                                                    @if ($booking->service_type === 'hourly')
                                                        Booking Amount ({{ $booking->duration ?? 1 }} hour(s))
                                                    @elseif ($booking->service_type === 'ferry_service')
                                                        Ferry Trip Amount
                                                    @elseif ($booking->service_type === 'experience')
                                                        Experience Amount
                                                    @else
                                                        Booking Amount
                                                    @endif
                                                </span>
                                                @php
                                                    $displaySubtotalCalc = floatval($booking->subtotal ?? 0);
                                                @endphp
                                                <span id="subtotal">{{ currency_format($displaySubtotalCalc) }}</span>
                                            </div>
                                        @else
                                            <!-- For houses/rooms, show price per night breakdown -->
                                            <div class="price-row">
                                                <span>Price per night</span>
                                                <span
                                                    id="pricePerNight">{{ currency_format($booking->price_per_night ?? 0) }}</span>
                                            </div>
                                            <div class="price-row">
                                                <span>× <span id="nightsCount">{{ $booking->nights ?? '1' }}</span>
                                                    nights</span>
                                                @php
                                                    // Debug logging for houses
                                                    if (($booking->type ?? '') === 'house') {
                                                        \Log::info('Checkout View - House Booking Data', [
                                                            'type' => $booking->type ?? 'unknown',
                                                            'property_id' => $booking->property_id ?? 'missing',
                                                            'price_per_night' => $booking->price_per_night ?? 'missing',
                                                            'price_per_night_type' => gettype(
                                                                $booking->price_per_night ?? null,
                                                            ),
                                                            'nights' => $booking->nights ?? 'missing',
                                                            'nights_type' => gettype($booking->nights ?? null),
                                                            'subtotal' => $booking->subtotal ?? 'missing',
                                                            'subtotal_type' => gettype($booking->subtotal ?? null),
                                                            'booking_object' => json_encode($booking),
                                                        ]);
                                                    }

                                                    // Always prefer backend subtotal if available, otherwise calculate
                                                    $backend = floatval($booking->subtotal ?? 0);
                                                    $calculated =
                                                        floatval($booking->price_per_night ?? 0) *
                                                        floatval($booking->nights ?? 1);

                                                    \Log::info('Subtotal Calculation', [
                                                        'backend' => $backend,
                                                        'calculated' => $calculated,
                                                        'backend_gt_0' => $backend > 0,
                                                        'result' => $backend > 0 ? 'using backend' : 'using calculated',
                                                    ]);

                                                    // Use backend if it exists and is not 0, otherwise use calculated
                                                    $displaySubtotalCalc = $backend > 0 ? $backend : $calculated;

                                                    \Log::info('Display Value', [
                                                        'displaySubtotalCalc' => $displaySubtotalCalc,
                                                        'number_format_result' => number_format(
                                                            $displaySubtotalCalc,
                                                            2,
                                                        ),
                                                        'currency_format_result' => currency_format(
                                                            number_format($displaySubtotalCalc, 2),
                                                        ),
                                                    ]);
                                                @endphp
                                                <span id="subtotal">{{ currency_format($displaySubtotalCalc) }}</span>
                                            </div>

                                            @php
                                                // Calculate savings (only for houses/rooms, not boats)
                                                $regularPrice =
                                                    floatval($booking->price_per_night ?? 0) *
                                                    floatval($booking->nights ?? 1);
                                                $actualPrice = $displaySubtotalCalc;
                                                $tieredPricingSavings = $regularPrice - $actualPrice;
                                            @endphp
                                        @endif
                                        @if (($booking->service_fee ?? 0) > 0)
                                            <div class="price-row">
                                                <span>Service fee</span>
                                                <span
                                                    id="serviceFee">{{ currency_format($booking->service_fee ?? 0) }}</span>
                                            </div>
                                        @endif
                                        @if (($booking->tax ?? 0) > 0)
                                            <div class="price-row">
                                                <span>Taxes</span>
                                                <span id="tax">{{ currency_format($booking->tax ?? 0) }}</span>
                                            </div>
                                        @endif
                                        @if (session('applied_coupon'))
                                            @php
                                                // Calculate discount - always prefer backend if available
                                                $calculated =
                                                    floatval($booking->price_per_night ?? 0) *
                                                    floatval($booking->nights ?? 1);
                                                $backend = floatval($booking->subtotal ?? 0);

                                                $displaySubtotal = $backend > 0 ? $backend : $calculated;

                                                $displayBase =
                                                    $displaySubtotal +
                                                    (float) ($booking->service_fee ?? 0) +
                                                    (float) ($booking->tax ?? 0);

                                                $displayDiscount = 0;
                                                $coupon = session('applied_coupon');
                                                $discountType = $coupon['discount_type'] ?? 'fixed';
                                                $discountValue = (float) ($coupon['discount_value'] ?? 0);
                                                $maxDiscount =
                                                    isset($coupon['max_discount_amount']) &&
                                                    $coupon['max_discount_amount']
                                                        ? (float) $coupon['max_discount_amount']
                                                        : null;

                                                if ($discountType === 'percentage') {
                                                    $displayDiscount = round(($displayBase * $discountValue) / 100);
                                                    if ($maxDiscount && $displayDiscount > $maxDiscount) {
                                                        $displayDiscount = round($maxDiscount);
                                                    }
                                                } else {
                                                    // Fixed amount
                                                    $displayDiscount = round($discountValue);
                                                }

                                                $displayDiscount = min($displayDiscount, $displayBase);
                                            @endphp
                                            <div class="price-row discount-row">
                                                <span class="text-success">
                                                    <i class="bi bi-tag-fill me-1"></i>Coupon Discount
                                                </span>
                                                <span class="text-success">
                                                    -{{ currency_format($displayDiscount) }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="divider"></div>

                                    @php
                                        // Calculate total savings
                                        $totalSavings = 0;

                                        // 1. Tiered pricing savings (for rooms/houses)
                                        if ($booking->type !== 'boat') {
                                            $totalSavings += $tieredPricingSavings ?? 0;
                                        }

                                        // 2. Coupon discount savings
                                        if (session('applied_coupon')) {
                                            $coupon = session('applied_coupon');
                                            $discountType = $coupon['discount_type'] ?? 'fixed';
                                            $discountValue = floatval($coupon['discount_value'] ?? 0);
                                            $maxDiscount =
                                                isset($coupon['max_discount_amount']) && $coupon['max_discount_amount']
                                                    ? floatval($coupon['max_discount_amount'])
                                                    : null;

                                            $backend = floatval($booking->subtotal ?? 0);
                                            $calculated =
                                                floatval($booking->price_per_night ?? 0) *
                                                floatval($booking->nights ?? 1);
                                            $displaySubtotal = $backend > 0 ? $backend : $calculated;
                                            $displayBase =
                                                $displaySubtotal +
                                                (float) ($booking->service_fee ?? 0) +
                                                (float) ($booking->tax ?? 0);

                                            $couponDiscount = 0;
                                            if ($discountType === 'percentage') {
                                                $couponDiscount = round(($displayBase * $discountValue) / 100);
                                                if ($maxDiscount && $couponDiscount > $maxDiscount) {
                                                    $couponDiscount = round($maxDiscount);
                                                }
                                            } else {
                                                $couponDiscount = round($discountValue);
                                            }
                                            $couponDiscount = min($couponDiscount, $displayBase);
                                            $totalSavings += $couponDiscount;
                                        }

                                        $walletBalance = auth()->user()->wallet_balance ?? 0;
                                    @endphp

                                    <!-- Wallet Balance Section -->
                                    @if ($walletBalance > 0)
                                        <div class="wallet-section mb-3">
                                            <div class="wallet-card">
                                                <div class="wallet-header">
                                                    <div class="wallet-icon-wrapper">
                                                        <i class="bi bi-wallet2"></i>
                                                    </div>
                                                    <div class="wallet-info">
                                                        <h6 class="mb-0 text-white">Wallet Balance</h6>
                                                        <p class="wallet-amount mb-0">
                                                            {{ currency_format($walletBalance) }}</p>
                                                    </div>
                                                </div>
                                                <div class="wallet-toggle-section">
                                                    <label class="wallet-toggle-label">
                                                        <input class="wallet-checkbox" type="checkbox"
                                                            id="use_wallet_balance" name="use_wallet_balance"
                                                            value="1" onchange="toggleWalletUsage(this)">
                                                        <span class="wallet-label-text">
                                                            <i class="bi bi-check-circle"></i> Use wallet for this booking
                                                        </span>
                                                    </label>
                                                </div>
                                                <div class="wallet-note">
                                                    <i class="bi bi-info-circle"></i>
                                                    Your wallet balance will be automatically applied to reduce the total
                                                    amount
                                                </div>
                                            </div>
                                        </div>
                                        <div class="divider"></div>
                                    @endif

                                    <div class="total-price">
                                        <span>Total Amount</span>
                                        @php
                                            // Always prefer backend subtotal if available
                                            $backend = floatval($booking->subtotal ?? 0);
                                            $calculated =
                                                floatval($booking->price_per_night ?? 0) *
                                                floatval($booking->nights ?? 1);
                                            $calculatedSubtotal = $backend > 0 ? $backend : $calculated;

                                            $serviceFee = floatval($booking->service_fee ?? 0);
                                            $tax = floatval($booking->tax ?? 0);
                                            $baseAmount = $calculatedSubtotal + $serviceFee + $tax;

                                            // Get discount if coupon applied
                                            $discount = 0;
                                            if (session('applied_coupon')) {
                                                $coupon = session('applied_coupon');
                                                $discountType = $coupon['discount_type'] ?? 'fixed';
                                                $discountValue = floatval($coupon['discount_value'] ?? 0);
                                                $maxDiscount =
                                                    isset($coupon['max_discount_amount']) &&
                                                    $coupon['max_discount_amount']
                                                        ? floatval($coupon['max_discount_amount'])
                                                        : null;

                                                if ($discountType === 'percentage') {
                                                    $discount = round(($baseAmount * $discountValue) / 100);
                                                    if ($maxDiscount && $discount > $maxDiscount) {
                                                        $discount = round($maxDiscount);
                                                    }
                                                } else {
                                                    // Fixed amount
                                                    $discount = round($discountValue);
                                                }

                                                $discount = min($discount, $baseAmount);
                                            }

                                            // Calculate final total: Base - Discount
                                            $finalAmount = max(0, $baseAmount - $discount);
                                        @endphp
                                        <span id="totalAmount">{{ currency_format($finalAmount) }}</span>
                                        <input type="hidden" id="originalTotal" value="{{ $finalAmount }}">
                                        <input type="hidden" id="walletBalance" value="{{ $walletBalance ?? 0 }}">
                                    </div>

                                    @if ($totalSavings > 0)
                                        <div class="price-row my-3"
                                            style="background: #d4edda; padding: 12px; border-radius: 8px; border: 2px solid #28a745;">
                                            <span style="color: #155724; font-weight: 600; font-size: 16px;">
                                                <i class="bi bi-piggy-bank-fill me-2"></i>You Saved
                                            </span>
                                            <span id="totalSavingsDisplay"
                                                style="color: #155724; font-weight: 600; font-size: 16px;">
                                                {{ currency_format($totalSavings) }}
                                            </span>
                                        </div>
                                    @endif

                                    <!-- Wallet Applied Amount -->
                                    <div id="walletAppliedRow" class="wallet-applied-row"
                                        style="display: none; margin-top: 1rem;">
                                        <div class="wallet-applied-content">
                                            <span>
                                                <i class="bi bi-wallet2 me-2"></i>Wallet Balance Used
                                            </span>
                                            <span class="wallet-applied-badge"
                                                id="walletAppliedAmount">-{{ currency_format(0) }}</span>
                                        </div>
                                    </div>

                                    <!-- Amount to Pay -->
                                    <div id="amountToPayRow" class="amount-to-pay-row"
                                        style="display: none; margin-top: 0.75rem;">
                                        <span class="pay-label">
                                            <i class="bi bi-cash-coin me-2"></i><strong>Amount to Pay</strong>
                                        </span>
                                        <span class="pay-amount"
                                            id="amountToPay"><strong>{{ currency_format($finalAmount) }}</strong></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="checkout-card mb-4">
                                <div class="card-header">
                                    <h4><i class="bi bi-cash-coin me-2"></i>Payment Method</h4>
                                </div>
                                <div class="card-body">
                                    <div class="payment-methods">
                                        <div class="payment-option mb-3">
                                            <input type="radio" class="form-check-input" id="pay_at_property"
                                                name="payment_method" value="cash" checked>
                                            <label class="form-check-label" for="pay_at_property">
                                                <i class="bi bi-building me-2"></i>Pay at Property
                                            </label>
                                            <small class="text-muted d-block mt-2">
                                                <i class="bi bi-info-circle me-1"></i>
                                                You can pay cash or card at the property upon check-in.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Terms & Conditions -->
                            <div class="terms-section mb-3">
                                <div class="form-check">
                                    <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox"
                                        id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" class="text-decoration-none">Terms &
                                            Conditions</a>
                                        and <a href="#" class="text-decoration-none">Cancellation Policy</a>
                                    </label>
                                    @error('terms')
                                        <div class="invalid-feedback" data-backend>{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Hidden Fields -->
                            <input type="hidden" name="type" value="{{ $booking->type }}">
                            <input type="hidden" name="property_id" value="{{ $booking->property_id }}">
                            <input type="hidden" name="check_in" value="{{ $booking->check_in }}">
                            <input type="hidden" name="check_out" value="{{ $booking->check_out }}">
                            <input type="hidden" name="adults" value="{{ $booking->guests }}">
                            <input type="hidden" name="children" value="{{ $booking->children }}">

                            @if ($booking->type === 'boat')
                                <!-- Boat-specific hidden fields -->
                                @if (isset($booking->start_time))
                                    <input type="hidden" name="start_time" value="{{ $booking->start_time }}">
                                @endif
                                @if (isset($booking->duration))
                                    <input type="hidden" name="duration" value="{{ $booking->duration }}">
                                @endif
                                @if (isset($booking->ferry_type))
                                    <input type="hidden" name="ferry_type" value="{{ $booking->ferry_type }}">
                                @endif
                                @if (isset($booking->experience_duration))
                                    <input type="hidden" name="experience_duration"
                                        value="{{ $booking->experience_duration }}">
                                @endif
                                @if (isset($booking->trip_type))
                                    <input type="hidden" name="trip_type" value="{{ $booking->trip_type }}">
                                @endif
                            @endif

                            @php
                                // Calculate form total - always prefer backend if available
                                $calculated =
                                    floatval($booking->price_per_night ?? 0) * floatval($booking->nights ?? 1);
                                $backend = floatval($booking->subtotal ?? 0);

                                $formCalculatedSubtotal = $backend > 0 ? $backend : $calculated;

                                $formServiceFee = (float) ($booking->service_fee ?? 0);
                                $formTax = (float) ($booking->tax ?? 0);
                                $formBase = $formCalculatedSubtotal + $formServiceFee + $formTax;

                                $formDiscount = 0;
                                if (session('applied_coupon')) {
                                    $coupon = session('applied_coupon');
                                    $discountType = $coupon['discount_type'] ?? 'fixed';
                                    $discountValue = (float) ($coupon['discount_value'] ?? 0);
                                    $maxDiscount =
                                        isset($coupon['max_discount_amount']) && $coupon['max_discount_amount']
                                            ? (float) $coupon['max_discount_amount']
                                            : null;

                                    if ($discountType === 'percentage') {
                                        $formDiscount = round(($formBase * $discountValue) / 100);
                                        if ($maxDiscount && $formDiscount > $maxDiscount) {
                                            $formDiscount = round($maxDiscount);
                                        }
                                    } else {
                                        // Fixed amount
                                        $formDiscount = round($discountValue);
                                    }

                                    $formDiscount = min($formDiscount, $formBase);
                                }

                                $formFinalTotal = max(0, $formBase - $formDiscount);
                            @endphp
                            <input type="hidden" name="total" value="{{ $formFinalTotal }}">
                            @if (session('applied_coupon'))
                                <input type="hidden" name="coupon_code"
                                    value="{{ session('applied_coupon')['code'] ?? '' }}">
                                <input type="hidden" name="discount_amount" value="{{ $formDiscount }}">
                            @endif

                            @if (isset($booking->adult_names))
                                @foreach ($booking->adult_names as $adultName)
                                    <input type="hidden" name="adult_names[]" value="{{ $adultName }}">
                                @endforeach
                            @endif

                            @if (isset($booking->children_names))
                                @foreach ($booking->children_names as $childName)
                                    <input type="hidden" name="children_names[]" value="{{ $childName }}">
                                @endforeach
                            @endif

                            <!-- Confirm Button -->
                            <button type="submit" class="btn btn-primary w-100 btn-lg">
                                <i class="bi bi-check-circle me-2"></i>Confirm Booking
                            </button>

                            <p class="text-center text-muted mt-3 mb-0">
                                <i class="bi bi-shield-check me-2"></i>Your booking is secure and encrypted
                            </p>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Guest Email Modal - Plain HTML (Only for guests) -->
            @guest
                <div id="unifiedAuthModal"
                    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
                    <div
                        style="background: white; border-radius: 15px; padding: 40px; max-width: 450px; width: 90%; position: relative; box-shadow: 0 10px 50px rgba(0,0,0,0.3);">

                        <div id="emailStep">
                            <h3 style="margin: 0 0 10px 0; color: #1a1a1a; font-size: 24px;">Welcome!</h3>
                            <p style="margin: 0 0 25px 0; color: #666;">Please enter your email to continue with checkout</p>

                            <form id="emailCheckForm">
                                <div style="margin-bottom: 20px;">
                                    <label style="display: block; margin-bottom: 8px; color: #333; font-weight: 500;">Email
                                        Address</label>
                                    <input type="email" id="guestEmail" required
                                        style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; transition: border-color 0.3s;"
                                        onfocus="this.style.borderColor='#136497'" onblur="this.style.borderColor='#e0e0e0'"
                                        placeholder="Enter your email">
                                </div>

                                <div id="emailErrorMsg"
                                    style="display: none; color: #dc3545; margin-bottom: 15px; font-size: 14px;">
                                </div>

                                <button type="submit" id="emailSubmitBtn"
                                    style="width: 100%; padding: 14px; background: #136497; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.3s;"
                                    onmouseover="this.style.background='#0d4d75'"
                                    onmouseout="this.style.background='#136497'">
                                    Continue
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endguest
        </div>
    </div>

@endsection

@section('scripts')
    <script src="{{ asset('frontend/js/checkout.js') }}"></script>
    <script>
        // Initialize checkout page on DOM ready
        document.addEventListener('DOMContentLoaded', function() {
            const oldGuests = @json(old('guests', []));
            const isGuest = {{ auth()->guest() ? 'true' : 'false' }};

            // Initialize guest management only
            initGuestManagement(oldGuests);

            // Show email modal for guest users
            if (isGuest) {
                document.getElementById('unifiedAuthModal').style.display = 'flex';
                document.getElementById('guestEmail').focus();
            }

            // Handle email form submission
            const emailForm = document.getElementById('emailCheckForm');
            if (emailForm) {
                emailForm.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const email = document.getElementById('guestEmail').value;
                    const submitBtn = document.getElementById('emailSubmitBtn');
                    const errorDiv = document.getElementById('emailErrorMsg');

                    // Disable button
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Checking...';
                    errorDiv.style.display = 'none';

                    try {
                        const response = await fetch('{{ route('customer.check-email') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                email: email
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            const returnUrl = encodeURIComponent(window.location.href);

                            if (data.exists) {
                                // User exists - redirect to login
                                window.location.href = '{{ route('customer.login') }}?return_url=' +
                                    returnUrl + '&email=' + encodeURIComponent(email);
                            } else {
                                // User doesn't exist - redirect to register
                                window.location.href = '{{ route('customer.register') }}?return_url=' +
                                    returnUrl + '&email=' + encodeURIComponent(email);
                            }
                        } else {
                            throw new Error(data.message || 'An error occurred');
                        }
                    } catch (error) {
                        errorDiv.textContent = error.message || 'An error occurred. Please try again.';
                        errorDiv.style.display = 'block';
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Continue';
                    }
                });
            }

            // Set currency symbol for JS functions
            document.body.setAttribute('data-currency-symbol', '{{ currency_symbol() }}');

            // Listen for coupon events and reload page
            if (typeof Livewire !== 'undefined') {
                Livewire.on('coupon-applied', () => {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                });

                Livewire.on('coupon-removed', () => {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                });
            }
        });

        // Toggle wallet usage
        function toggleWalletUsage(checkbox) {
            console.log('Toggle wallet called, checked:', checkbox.checked);

            const totalAmount = parseFloat(document.getElementById('originalTotal').value);
            const walletBalance = parseFloat(document.getElementById('walletBalance').value);
            const currencySymbol = document.body.getAttribute('data-currency-symbol') || 'KWD';

            console.log('Total Amount:', totalAmount, 'Wallet Balance:', walletBalance);

            const walletAppliedRow = document.getElementById('walletAppliedRow');
            const amountToPayRow = document.getElementById('amountToPayRow');
            const walletAppliedAmount = document.getElementById('walletAppliedAmount');
            const amountToPay = document.getElementById('amountToPay');
            const totalSavingsDisplay = document.getElementById('totalSavingsDisplay');

            console.log('Elements found:', {
                walletAppliedRow: !!walletAppliedRow,
                amountToPayRow: !!amountToPayRow,
                walletAppliedAmount: !!walletAppliedAmount,
                amountToPay: !!amountToPay
            });

            // Get base savings (tiered pricing + coupon)
            const baseSavings = {{ $totalSavings ?? 0 }};

            if (checkbox.checked) {
                // Calculate wallet usage
                const walletUsed = Math.min(walletBalance, totalAmount);
                const finalAmount = Math.max(0, totalAmount - walletUsed);

                console.log('Wallet Used:', walletUsed, 'Final Amount:', finalAmount);

                // Update savings display to include wallet
                const totalSavingsWithWallet = baseSavings + walletUsed;
                if (totalSavingsDisplay) {
                    totalSavingsDisplay.textContent = currencySymbol + ' ' + totalSavingsWithWallet.toFixed(2);
                }

                // Show wallet applied and amount to pay rows
                walletAppliedAmount.innerHTML = '-' + currencySymbol + ' ' + walletUsed.toFixed(2);
                const amountToPayStrong = amountToPay.querySelector('strong');
                if (amountToPayStrong) {
                    amountToPayStrong.textContent = currencySymbol + ' ' + finalAmount.toFixed(2);
                }

                walletAppliedRow.style.display = 'block';
                amountToPayRow.style.display = 'flex';

                // Add show class for animation
                setTimeout(() => {
                    walletAppliedRow.classList.add('show');
                    amountToPayRow.classList.add('show');
                }, 10);

                console.log('Rows should be visible now');
            } else {
                // Reset savings to base only (no wallet)
                if (totalSavingsDisplay) {
                    totalSavingsDisplay.textContent = currencySymbol + ' ' + baseSavings.toFixed(2);
                }

                // Hide wallet applied and amount to pay rows with animation
                walletAppliedRow.classList.remove('show');
                amountToPayRow.classList.remove('show');

                setTimeout(() => {
                    walletAppliedRow.style.display = 'none';
                    amountToPayRow.style.display = 'none';
                }, 300); // Wait for animation to complete

                console.log('Rows hidden');
            }
        }
    </script>
@endsection
