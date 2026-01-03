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
            <form action="{{ route('booking.confirm') }}" method="POST" id="checkoutForm" novalidate>
                @csrf
                <div class="row">
                    <!-- Left Side - Customer Details -->
                    <div class="col-lg-7 mb-4">
                        <!-- Customer Information -->
                        <div class="checkout-card mb-4">
                            <div class="card-header">
                                <h4><i class="bi bi-person-lines-fill me-2"></i>Customer Information</h4>
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

                                    <div class="col-12 mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3">{{ old('address', auth()->user()->address ?? '') }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback" data-backend>{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Special Requests -->
                        <div class="checkout-card">
                            <div class="card-header">
                                <h4><i class="bi bi-chat-left-text me-2"></i>Special Requests</h4>
                            </div>
                            <div class="card-body">
                                <label for="special_requests" class="form-label">Any special requests? (Optional)</label>
                                <textarea class="form-control" id="special_requests" name="special_requests" rows="4"
                                    placeholder="Early check-in, late check-out, dietary requirements, etc.">{{ old('special_requests') }}</textarea>
                            </div>
                        </div>

                        <!-- Guest Details -->
                        @if (isset($booking->guest_names) && count($booking->guest_names) > 0)
                            <div class="checkout-card mt-4">
                                <div class="card-header">
                                    <h4><i class="bi bi-person-lines-fill me-2"></i>Guest Names</h4>
                                </div>
                                <div class="card-body">
                                    <div class="guest-names-list">
                                        @foreach ($booking->guest_names as $index => $guestName)
                                            <div class="guest-name-item mb-2">
                                                <input type="text" name="guest_names[]" class="form-control"
                                                    value="{{ $guestName }}" readonly>
                                                <input type="hidden" name="guest_names_hidden[]"
                                                    value="{{ $guestName }}">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
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
                                        @if (isset($booking->guest_names) && count($booking->guest_names) > 0)
                                            <div class="detail-row">
                                                <div class="detail-label">
                                                    <i class="bi bi-person-lines-fill me-2"></i>Total Guests
                                                </div>
                                                <div class="detail-value">
                                                    {{ count($booking->guest_names) }} Persons
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="divider"></div>

                                    <!-- Coupon Section -->
                                    <div class="coupon-section mb-3">
                                        @if (session('applied_coupon'))
                                            <div class="applied-coupon-badge d-flex align-items-center justify-content-between"
                                                style="padding: 10px; background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 8px;">
                                                <div style="flex: 1;">
                                                    <strong
                                                        style="color: #0369a1;">{{ session('applied_coupon.code') }}</strong>
                                                    <span style="color: #059669;">applied</span>
                                                    @php
                                                        $badgeCoupon = session('applied_coupon');
                                                        $badgeType = $badgeCoupon['discount_type'] ?? 'fixed';
                                                        $badgeValue = $badgeCoupon['discount_value'] ?? 0;
                                                        $badgeTypeText = '';
                                                        if ($badgeType === 'percentage') {
                                                            $badgeTypeText = $badgeValue . '% off';
                                                        } else {
                                                            $badgeTypeText =
                                                                currency_format(round($badgeValue)) . ' off';
                                                        }
                                                    @endphp
                                                    <div class="text-sm" style="color: #059669;">
                                                        <i class="bi bi-tag-fill"></i> Discount: {{ $badgeTypeText }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                        onclick="document.getElementById('removeCouponForm').submit()"
                                                        style="min-width: 80px;">
                                                        <i class="bi bi-x-circle"></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            <div class="coupon-input-wrapper">
                                                <input type="text" id="couponCodeInput"
                                                    class="form-control @error('coupon_code') is-invalid @enderror"
                                                    placeholder="Enter coupon code" style="text-transform: uppercase;"
                                                    value="{{ old('coupon_code') }}">
                                                <button type="button" class="btn btn-primary"
                                                    onclick="applyCouponCode()">Apply</button>
                                            </div>
                                            @error('coupon_code')
                                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                                            @enderror
                                        @endif

                                        @if (session('coupon_error'))
                                            <div class="alert alert-danger mt-2">{{ session('coupon_error') }}</div>
                                        @endif

                                        @if (session('coupon_success'))
                                            <div class="alert alert-success mt-2">{{ session('coupon_success') }}</div>
                                        @endif
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

                                    <!-- Wallet Balance Section -->
                                    @php
                                        $walletBalance = auth()->user()->wallet_balance ?? 0;
                                    @endphp
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

                                    <!-- Wallet Applied Amount -->
                                    <div id="walletAppliedRow" class="wallet-applied-row mt-3" style="display: none;">
                                        <div class="wallet-applied-content">
                                            <span>
                                                <i class="bi bi-wallet2 me-2"></i>Wallet Balance Used
                                            </span>
                                            <span class="wallet-applied-badge"
                                                id="walletAppliedAmount">-{{ currency_format(0) }}</span>
                                        </div>
                                    </div>

                                    <!-- Amount to Pay -->
                                    <div id="amountToPayRow" class="amount-to-pay-row" style="display: none;">
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
        </div>
    </div>

    <!-- Hidden Coupon Forms (Outside main form) -->
    <form id="applyCouponForm" action="{{ route('booking.apply-coupon') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="coupon_code" id="hiddenCouponCode">
        <input type="hidden" name="booking_amount"
            value="{{ $booking->subtotal + ($booking->service_fee ?? 0) + ($booking->tax ?? 0) }}">
        <input type="hidden" name="price_per_night" value="{{ $booking->price_per_night ?? 0 }}">
        <input type="hidden" name="nights" value="{{ $booking->nights ?? 1 }}">
        <input type="hidden" name="property_type" value="{{ $booking->type ?? '' }}">
        <input type="hidden" name="property_id" value="{{ $booking->property_id ?? '' }}">
    </form>

    <form id="removeCouponForm" action="{{ route('booking.remove-coupon') }}" method="POST" style="display: none;">
        @csrf
    </form>

@endsection

@section('scripts')
    <script>
        // Set currency symbol for JS functions
        document.body.setAttribute('data-currency-symbol', '{{ currency_symbol() }}');
    </script>
@endsection
