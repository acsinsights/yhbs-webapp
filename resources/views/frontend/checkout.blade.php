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
            <form action="{{ route('booking.confirm') }}" method="POST" id="checkoutForm">
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
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Last Name *</label>
                                        <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                            id="last_name" name="last_name"
                                            value="{{ old('last_name', auth()->user()->last_name ?? '') }}" required>
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="email" name="email"
                                            value="{{ old('email', auth()->user()->email ?? '') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                            id="phone" name="phone"
                                            value="{{ old('phone', auth()->user()->phone ?? '') }}" required>
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3">{{ old('address', auth()->user()->address ?? '') }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
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
                                        <img src="{{ $booking->property_image ?? asset('frontend/img/default-room.jpg') }}"
                                            alt="Property" class="property-image">
                                        <h5 class="mt-3">{{ $booking->property_name ?? 'Luxury Room' }}</h5>
                                        <p class="text-muted mb-0">
                                            <i class="bi bi-geo-alt me-2"></i>{{ $booking->location ?? 'Location' }}
                                        </p>
                                    </div>

                                    <div class="booking-details-summary">
                                        <div class="detail-row">
                                            <div class="detail-label">
                                                <i class="bi bi-calendar-check me-2"></i>Check-in
                                            </div>
                                            <div class="detail-value">
                                                {{ $booking->check_in_display ?? ($booking->check_in ?? 'N/A') }}
                                            </div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">
                                                <i class="bi bi-calendar-x me-2"></i>Check-out
                                            </div>
                                            <div class="detail-value">
                                                {{ $booking->check_out_display ?? ($booking->check_out ?? 'N/A') }}
                                            </div>
                                        </div>

                                        <div class="detail-row">
                                            <div class="detail-label">
                                                <i class="bi bi-moon me-2"></i>Nights
                                            </div>
                                            <div class="detail-value">
                                                {{ $booking->nights ?? '1' }} Nights
                                            </div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">
                                                <i class="bi bi-people me-2"></i>Guests
                                            </div>
                                            <div class="detail-value">
                                                {{ $booking->guests ?? '2' }} Adults
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
                                            <div class="applied-coupon-badge d-flex align-items-center justify-content-between" style="padding: 10px; background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 8px;">
                                                <div style="flex: 1;">
                                                    <strong style="color: #0369a1;">{{ session('applied_coupon.code') }}</strong> <span style="color: #059669;">applied</span>
                                                    <div class="text-sm" style="color: #059669;">
                                                        <i class="bi bi-tag-fill"></i> Discount: {{ currency_format(number_format(session('applied_coupon.discount_amount'), 2)) }}
                                                    </div>
                                                </div>
                                                <div class="d-flex gap-2" style="gap: 8px;">
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        onclick="document.getElementById('removeCouponForm').submit()"
                                                        style="min-width: 80px;">
                                                        <i class="bi bi-arrow-repeat"></i> Change
                                                    </button>
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
                                        <div class="price-row">
                                            <span>Price per night</span>
                                            <span
                                                id="pricePerNight">{{ currency_format(number_format($booking->price_per_night ?? 0, 2)) }}</span>
                                        </div>
                                        <div class="price-row">
                                            <span>× <span id="nightsCount">{{ $booking->nights ?? '1' }}</span>
                                                nights</span>
                                            <span
                                                id="subtotal">{{ currency_format(number_format(($booking->price_per_night ?? 0) * ($booking->nights ?? 1), 2)) }}</span>
                                        </div>
                                        @if (($booking->service_fee ?? 0) > 0)
                                            <div class="price-row">
                                                <span>Service fee</span>
                                                <span
                                                    id="serviceFee">{{ currency_format(number_format($booking->service_fee ?? 0, 2)) }}</span>
                                            </div>
                                        @endif
                                        @if (($booking->tax ?? 0) > 0)
                                            <div class="price-row">
                                                <span>Taxes</span>
                                                <span
                                                    id="tax">{{ currency_format(number_format($booking->tax ?? 0, 2)) }}</span>
                                            </div>
                                        @endif
                                        @if (session('applied_coupon'))
                                            <div class="price-row discount-row">
                                                <span class="text-success">
                                                    <i class="bi bi-tag-fill me-1"></i>Coupon Discount
                                                    @if (session('applied_coupon.free_nights') > 0)
                                                        ({{ session('applied_coupon.free_nights') }}
                                                        night{{ session('applied_coupon.free_nights') > 1 ? 's' : '' }}
                                                        free)
                                                    @endif
                                                </span>
                                                <span class="text-success">
                                                    -{{ currency_format(number_format(session('applied_coupon.discount_amount'), 2)) }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="divider"></div>

                                    <div class="total-price">
                                        <span>Total Amount</span>
                                        <span>{{ currency_format(number_format(session('applied_coupon') ? session('applied_coupon.new_total') : $booking->total ?? 0, 2)) }}</span>
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
                                        <div class="invalid-feedback">{{ $message }}</div>
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
                            <input type="hidden" name="total"
                                value="{{ session('applied_coupon') ? session('applied_coupon.new_total') : $booking->total }}">
                            @if (session('applied_coupon'))
                                <input type="hidden" name="coupon_code" value="{{ session('applied_coupon.code') }}">
                                <input type="hidden" name="discount_amount"
                                    value="{{ session('applied_coupon.discount_amount') }}">
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
        <input type="hidden" name="booking_amount" value="{{ $booking->total }}">
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
        function applyCouponCode() {
            const couponCode = document.getElementById('couponCodeInput').value.trim();
            if (!couponCode) {
                alert('Please enter a coupon code');
                return;
            }
            document.getElementById('hiddenCouponCode').value = couponCode.toUpperCase();
            document.getElementById('applyCouponForm').submit();
        }
    </script>
@endsection

@section('styles')
    <style>
        .coupon-section {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px dashed #dee2e6;
        }

        .coupon-input-wrapper {
            display: flex;
            gap: 10px;
        }

        .coupon-input-wrapper input {
            flex: 1;
        }

        .applied-coupon-badge {
            display: none;
            align-items: center;
            justify-content: space-between;
            padding: 10px 15px;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 6px;
            color: #155724;
        }

        .remove-coupon {
            background: none;
            border: none;
            color: #155724;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .remove-coupon:hover {
            background: rgba(21, 87, 36, 0.1);
        }

        .discount-row {
            color: #28a745 !important;
            font-weight: 600;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .text-sm {
            font-size: 0.875rem;
        }
    </style>
@endsection
