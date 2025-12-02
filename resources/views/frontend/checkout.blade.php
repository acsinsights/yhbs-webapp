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
                                                {{ $booking->check_in ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">
                                                <i class="bi bi-calendar-x me-2"></i>Check-out
                                            </div>
                                            <div class="detail-value">
                                                {{ $booking->check_out ?? 'N/A' }}
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
                                            </div>
                                        </div>
                                    </div>

                                    <div class="divider"></div>

                                    <div class="price-breakdown">
                                        <div class="price-row">
                                            <span>Price per night</span>
                                            <span>${{ number_format($booking->price_per_night ?? 0, 2) }}</span>
                                        </div>
                                        <div class="price-row">
                                            <span>× {{ $booking->nights ?? '1' }} nights</span>
                                            <span>${{ number_format(($booking->price_per_night ?? 0) * ($booking->nights ?? 1), 2) }}</span>
                                        </div>
                                        @if (($booking->service_fee ?? 0) > 0)
                                            <div class="price-row">
                                                <span>Service fee</span>
                                                <span>${{ number_format($booking->service_fee ?? 0, 2) }}</span>
                                            </div>
                                        @endif
                                        @if (($booking->tax ?? 0) > 0)
                                            <div class="price-row">
                                                <span>Taxes</span>
                                                <span>${{ number_format($booking->tax ?? 0, 2) }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="divider"></div>

                                    <div class="total-price">
                                        <span>Total Amount</span>
                                        <span>${{ number_format($booking->total ?? 0, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="checkout-card mb-4">
                                <div class="card-header">
                                    <h4><i class="bi bi-credit-card me-2"></i>Payment Method</h4>
                                </div>
                                <div class="card-body">
                                    <div class="payment-methods">
                                        <div class="payment-option mb-3">
                                            <input type="radio" class="form-check-input" id="card"
                                                name="payment_method" value="card" checked>
                                            <label class="form-check-label" for="card">
                                                <i class="bi bi-credit-card me-2"></i>Credit / Debit Card
                                            </label>
                                        </div>
                                        <div class="payment-option mb-3">
                                            <input type="radio" class="form-check-input" id="paypal"
                                                name="payment_method" value="paypal">
                                            <label class="form-check-label" for="paypal">
                                                <i class="bi bi-paypal me-2"></i>PayPal
                                            </label>
                                        </div>
                                        <div class="payment-option">
                                            <input type="radio" class="form-check-input" id="bank"
                                                name="payment_method" value="bank">
                                            <label class="form-check-label" for="bank">
                                                <i class="bi bi-bank me-2"></i>Bank Transfer
                                            </label>
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


    <script>
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const termsCheckbox = document.getElementById('terms');
            if (!termsCheckbox.checked) {
                e.preventDefault();
                alert('Please accept the terms and conditions to continue');
                termsCheckbox.focus();
            }
        });
    </script>
@endsection
