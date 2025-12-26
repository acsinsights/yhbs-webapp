@extends('frontend.layouts.app')
@section('title', 'Booking Confirmed - YHBS')
@section('styles')
    <style>
        .confirmation-section {
            background: #f8f9fa;
        }

        .success-card {
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .success-icon {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .success-icon i {
            font-size: 80px;
            color: #28a745;
            animation: scaleIn 0.5s ease-in-out;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
            }

            50% {
                transform: scale(1.2);
            }

            100% {
                transform: scale(1);
            }
        }

        .success-card h2 {
            color: #212529;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .booking-reference {
            background: #136497;
            padding: 25px;
            border-radius: 10px;
            margin-top: 25px;
            box-shadow: 0 4px 15px rgba(19, 100, 151, 0.3);
        }

        .booking-reference span {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.9);
            display: block;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .booking-reference h3 {
            color: #fff;
            margin: 0;
            font-weight: 700;
            font-size: 28px;
            letter-spacing: 2px;
        }

        .confirmation-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .confirmation-card .card-header {
            background: #136497;
            color: #fff;
            padding: 20px 25px;
            border-bottom: none;
        }

        .confirmation-card .card-header h4 {
            margin: 0;
            color: #fff;
            font-size: 18px;
            font-weight: 600;
        }

        .confirmation-card .card-body {
            padding: 30px 25px;
        }

        .confirmation-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 20px;
        }

        .detail-item {
            display: flex;
            align-items: start;
            gap: 12px;
        }

        .detail-icon {
            width: 40px;
            height: 40px;
            background: #da4927;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
            flex-shrink: 0;
        }

        .detail-item small {
            display: block;
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 4px;
        }

        .detail-item p {
            font-weight: 600;
            color: #212529;
        }

        .guest-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .guest-item {
            padding: 12px 16px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #136497;
        }

        .guest-item i {
            color: #28a745;
            font-size: 18px;
        }

        .guest-item span:first-of-type {
            flex: 1;
            font-weight: 500;
        }

        .info-item {
            display: flex;
            align-items: start;
            gap: 12px;
        }

        .info-item i {
            color: #da4927;
            font-size: 20px;
            margin-top: 4px;
        }

        .info-item small {
            display: block;
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 4px;
        }

        .info-item p {
            font-weight: 500;
            color: #212529;
        }

        .payment-breakdown {
            padding: 20px 0;
        }

        .payment-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .payment-row:last-child {
            border-bottom: none;
        }

        .divider {
            height: 2px;
            background: #136497;
            margin: 20px 0;
            opacity: 0.3;
        }

        .total-amount {
            display: flex;
            justify-content: space-between;
            font-size: 22px;
            font-weight: 700;
            color: #212529;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 10px;
        }

        .payment-status {
            text-align: center;
            margin-top: 20px;
        }

        .confirmation-actions {
            margin-top: 30px;
        }

        .info-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 10px;
            padding: 25px;
            margin-top: 30px;
        }

        .info-box h5 {
            color: #856404;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .info-box ul {
            margin: 0;
            padding-left: 20px;
            color: #856404;
        }

        .info-box ul li {
            margin-bottom: 8px;
        }

        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }

            .success-icon i {
                font-size: 60px;
            }

            .booking-reference h3 {
                font-size: 22px;
            }
        }
    </style>
@endsection
@section('content')
    <!-- Breadcrumb section Start-->
    <div class="breadcrumb-section"
        style="background-image:linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url({{ asset('frontend/img/innerpages/breadcrumb-bg6.jpg') }});">
        <div class="container">
            <div class="banner-content">
                <h1>Booking Confirmed</h1>
                <ul class="breadcrumb-list">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li>Confirmation</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Confirmation Section Start -->
    <div class="confirmation-section pt-100 pb-100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Success Message -->
                    <div class="success-card mb-4">
                        <dotlottie-wc src="https://lottie.host/589df893-9d96-4cdf-a78c-424208fafac2/aqE5BIE6Ah.lottie"
                            style="width: 200px; height: 200px; margin: 0 auto; display: block;" autoplay></dotlottie-wc>
                        <h2>Booking Confirmed!</h2>
                        <p class="text-muted">Thank you for your booking. Your {{ $booking->property_type ?? 'property' }}
                            has been reserved successfully.</p>
                        <div class="booking-reference">
                            <span>Booking Reference</span>
                            <h3>#{{ $booking->reference }}</h3>
                        </div>
                        <div class="mt-3">
                            <span class="badge bg-success me-2">{{ $booking->status ?? 'Confirmed' }}</span>
                            <span class="badge bg-warning">Payment: {{ $booking->payment_status ?? 'Pending' }}</span>
                        </div>
                    </div>

                    <!-- Cancellation Status Alert -->
                    @if (isset($booking->cancellation_requested_at) && $booking->cancellation_requested_at)
                        @if ($booking->cancellation_status === 'pending')
                            <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                                <h5 class="alert-heading">
                                    <i class="bi bi-clock-history me-2"></i>Cancellation Request Pending
                                </h5>
                                <p class="mb-2">Your cancellation request is being reviewed by our team. We'll notify you
                                    once a decision is made.</p>
                                <hr>
                                <p class="mb-0">
                                    <small><strong>Requested on:</strong>
                                        {{ \Carbon\Carbon::parse($booking->cancellation_requested_at)->format('M d, Y H:i A') }}</small>
                                </p>
                                @if ($booking->cancellation_reason)
                                    <p class="mb-0 mt-2">
                                        <small><strong>Reason:</strong> {{ $booking->cancellation_reason }}</small>
                                    </p>
                                @endif
                            </div>
                        @elseif($booking->cancellation_status === 'approved')
                            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                                <h5 class="alert-heading">
                                    <i class="bi bi-check-circle me-2"></i>Cancellation Approved
                                </h5>
                                <p class="mb-2">Your booking has been cancelled successfully.</p>
                                @if ($booking->refund_amount > 0)
                                    <hr>
                                    <p class="mb-0">
                                        <strong>Refund Amount:</strong>
                                        {{ currency_format(number_format($booking->refund_amount, 2)) }}
                                        <br>
                                        <strong>Refund Status:</strong> <span
                                            class="badge bg-info">{{ ucfirst($booking->refund_status ?? 'Pending') }}</span>
                                    </p>
                                @endif
                            </div>
                        @elseif($booking->cancellation_status === 'rejected')
                            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                <h5 class="alert-heading">
                                    <i class="bi bi-x-circle me-2"></i>Cancellation Request Declined
                                </h5>
                                <p class="mb-2">Your cancellation request has been reviewed and declined.</p>
                                @if ($booking->cancellation_reason)
                                    <hr>
                                    <p class="mb-0">
                                        <small>{{ $booking->cancellation_reason }}</small>
                                    </p>
                                @endif
                            </div>
                        @endif
                    @endif

                    <!-- Booking Details -->
                    <div class="confirmation-card mb-4">
                        <div class="card-header">
                            <h4><i class="bi bi-card-checklist me-2"></i>Booking Details</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    @if (isset($booking->property_image) && $booking->property_image)
                                        <img src="{{ $booking->property_image }}"
                                            alt="{{ $booking->property_name ?? 'Property' }}" class="confirmation-image"
                                            onerror="this.onerror=null; this.src='{{ asset('frontend/img/default-room.jpg') }}'">
                                    @else
                                        <img src="{{ asset('frontend/img/default-room.jpg') }}" alt="Property"
                                            class="confirmation-image">
                                    @endif
                                </div>
                                <div class="col-md-8">
                                    <h5>{{ $booking->property_name ?? 'Luxury Room' }}</h5>
                                    <p class="text-muted mb-3">
                                        <i class="bi bi-geo-alt me-2"></i>{{ $booking->location ?? 'Location' }}
                                    </p>

                                    <div class="detail-grid">
                                        <div class="detail-item">
                                            <div class="detail-icon">
                                                <i class="bi bi-calendar-check"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted">Check-in</small>
                                                <p class="mb-0">{{ $booking->check_in ?? 'N/A' }}</p>
                                            </div>
                                        </div>

                                        <div class="detail-item">
                                            <div class="detail-icon">
                                                <i class="bi bi-calendar-x"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted">Check-out</small>
                                                <p class="mb-0">{{ $booking->check_out ?? 'N/A' }}</p>
                                            </div>
                                        </div>

                                        <div class="detail-item">
                                            <div class="detail-icon">
                                                <i class="bi bi-moon"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted">Duration</small>
                                                <p class="mb-0">{{ $booking->nights ?? '1' }} Nights</p>
                                            </div>
                                        </div>

                                        <div class="detail-item">
                                            <div class="detail-icon">
                                                <i class="bi bi-people"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted">Guests</small>
                                                <p class="mb-0">{{ $booking->guests ?? '2' }} Adults
                                                    @if (($booking->children ?? 0) > 0)
                                                        , {{ $booking->children }} Children
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Discount/Coupon Info -->
                                    @if (isset($booking->discount_amount) && $booking->discount_amount > 0)
                                        <div class="alert alert-success mt-3 mb-3">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-tag-fill me-3" style="font-size: 24px;"></i>
                                                <div class="flex-grow-1">
                                                    @if (isset($booking->coupon_code))
                                                        <strong>Coupon Applied:
                                                            {{ strtoupper($booking->coupon_code) }}</strong>
                                                    @else
                                                        <strong>Discount Applied</strong>
                                                    @endif
                                                    <p class="mb-0 mt-1">You saved
                                                        {{ currency_format(number_format($booking->discount_amount, 2)) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if (isset($booking->wallet_amount_used) && $booking->wallet_amount_used > 0)
                                        <div class="alert alert-info mt-3 mb-3">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-wallet2 me-3" style="font-size: 24px;"></i>
                                                <div class="flex-grow-1">
                                                    <strong>Wallet Amount Used</strong>
                                                    <p class="mb-0 mt-1">
                                                        {{ currency_format(number_format($booking->wallet_amount_used, 2)) }}
                                                        deducted from your wallet</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Guest Names -->
                                    @if (isset($booking->adult_names) && count($booking->adult_names) > 0)
                                        <div class="mt-3">
                                            <h6 class="mb-2">Guest Names:</h6>
                                            <div class="guest-list">
                                                @foreach ($booking->adult_names as $index => $name)
                                                    <div class="guest-item">
                                                        <i class="bi bi-person-check me-2"></i>
                                                        <span>{{ $name }}</span>
                                                        <span class="badge bg-info ms-2">Adult</span>
                                                    </div>
                                                @endforeach
                                                @if (isset($booking->children_names))
                                                    @foreach ($booking->children_names as $index => $name)
                                                        <div class="guest-item">
                                                            <i class="bi bi-person-check me-2"></i>
                                                            <span>{{ $name }}</span>
                                                            <span class="badge bg-secondary ms-2">Child</span>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="confirmation-card mb-4">
                        <div class="card-header">
                            <h4><i class="bi bi-person-lines-fill me-2"></i>Customer Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="info-item">
                                        <i class="bi bi-person me-2"></i>
                                        <div>
                                            <small class="text-muted">Name</small>
                                            <p class="mb-0">{{ $booking->customer_name ?? 'Guest' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="info-item">
                                        <i class="bi bi-envelope me-2"></i>
                                        <div>
                                            <small class="text-muted">Email</small>
                                            <p class="mb-0">{{ $booking->customer_email ?? 'guest@example.com' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <i class="bi bi-telephone me-2"></i>
                                        <div>
                                            <small class="text-muted">Phone</small>
                                            <p class="mb-0">{{ $booking->customer_phone ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <i class="bi bi-credit-card me-2"></i>
                                        <div>
                                            <small class="text-muted">Payment Method</small>
                                            <p class="mb-0">{{ ucfirst($booking->payment_method ?? 'Card') }}</p>
                                        </div>
                                    </div>
                                </div>
                                @if (isset($booking->customer_address) && $booking->customer_address)
                                    <div class="col-12 mt-3">
                                        <div class="info-item">
                                            <i class="bi bi-geo-alt me-2"></i>
                                            <div>
                                                <small class="text-muted">Address</small>
                                                <p class="mb-0">{{ $booking->customer_address }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if (isset($booking->special_requests) && $booking->special_requests)
                                    <div class="col-12 mt-3">
                                        <div class="alert alert-info mb-0">
                                            <i class="bi bi-chat-left-text me-2"></i>
                                            <strong>Special Requests:</strong>
                                            <p class="mb-0 mt-2">{{ $booking->special_requests }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="confirmation-card mb-4">
                        <div class="card-header">
                            <h4><i class="bi bi-receipt me-2"></i>Payment Summary</h4>
                        </div>
                        <div class="card-body">
                            <div class="payment-breakdown">
                                <div class="payment-row">
                                    <span>Price per night</span>
                                    <span>{{ currency_format(number_format($booking->price_per_night ?? 0, 2)) }}</span>
                                </div>
                                <div class="payment-row">
                                    <span>× {{ $booking->nights ?? '1' }} nights</span>
                                    <span>{{ currency_format(number_format(($booking->price_per_night ?? 0) * ($booking->nights ?? 1), 2)) }}</span>
                                </div>
                                @if (($booking->service_fee ?? 0) > 0)
                                    <div class="payment-row">
                                        <span>Service fee</span>
                                        <span>{{ currency_format(number_format($booking->service_fee ?? 0, 2)) }}</span>
                                    </div>
                                @endif
                                @if (($booking->tax ?? 0) > 0)
                                    <div class="payment-row">
                                        <span>Taxes</span>
                                        <span>{{ currency_format(number_format($booking->tax ?? 0, 2)) }}</span>
                                    </div>
                                @endif

                                <!-- Subtotal before discount -->
                                @php
                                    $subtotal =
                                        ($booking->price_per_night ?? 0) * ($booking->nights ?? 1) +
                                        ($booking->service_fee ?? 0) +
                                        ($booking->tax ?? 0);
                                @endphp
                                @if (isset($booking->discount_amount) && $booking->discount_amount > 0)
                                    <div class="payment-row" style="color: #28a745;">
                                        <span>
                                            <i class="bi bi-tag-fill me-2"></i>Discount
                                            @if (isset($booking->coupon_code))
                                                <small class="ms-2">({{ strtoupper($booking->coupon_code) }})</small>
                                            @endif
                                        </span>
                                        <span>-
                                            {{ currency_format(number_format($booking->discount_amount ?? 0, 2)) }}</span>
                                    </div>
                                @endif

                                <!-- Wallet Amount Used -->
                                @if (isset($booking->wallet_amount_used) && $booking->wallet_amount_used > 0)
                                    <div class="payment-row" style="color: #0dcaf0;">
                                        <span><i class="bi bi-wallet2 me-2"></i>Wallet Amount Used</span>
                                        <span>-
                                            {{ currency_format(number_format($booking->wallet_amount_used ?? 0, 2)) }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="divider"></div>

                            <div class="total-amount">
                                <span>Total Paid</span>
                                <span>{{ currency_format(number_format($booking->total ?? 0, 2)) }}</span>
                            </div>

                            @if (
                                (isset($booking->discount_amount) && $booking->discount_amount > 0) ||
                                    (isset($booking->wallet_amount_used) && $booking->wallet_amount_used > 0))
                                <div class="text-center mt-3">
                                    @php
                                        $originalAmount = $subtotal ?? 0;
                                        $totalSavings =
                                            ($booking->discount_amount ?? 0) + ($booking->wallet_amount_used ?? 0);
                                    @endphp
                                    <p class="text-success mb-0">
                                        <i class="bi bi-check-circle-fill me-2"></i>
                                        <strong>You saved {{ currency_format(number_format($totalSavings, 2)) }} on this
                                            booking!</strong>
                                    </p>
                                </div>
                            @endif

                            <div class="payment-status">
                                @if (($booking->payment_status ?? 'pending') === 'paid')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-2"></i>Payment Successful
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="bi bi-clock me-2"></i>Payment Confirmation Pending
                                    </span>
                                    <p class="text-muted mt-2 mb-0">
                                        <small>Please complete the payment at the property.</small>
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="confirmation-actions">
                        <div class="row justify-content-center col-12 d-flex">
                            <div class="col-md-4 mb-3">
                                <a href="{{ route('customer.bookings') }}" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-list-ul me-2"></i>View All Bookings
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                @livewire('customer.booking-cancellation-request', ['bookingId' => $booking->id])
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="{{ url('/') }}" class="btn btn-primary w-100">
                                    <i class="bi bi-house me-2"></i>Back to Home
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Important Information -->
                    <div class="info-box">
                        <h5><i class="bi bi-info-circle me-2"></i>Important Information</h5>
                        <ul>
                            <li>Check-in time is from 2:00 PM</li>
                            <li>Check-out time is before 12:00 PM</li>
                            <li>Please bring a valid ID proof at the time of check-in</li>
                            <li>For any queries, contact us at <a href="mailto:support@ikarus.com">support@ikarus.com</a>
                            </li>
                            <li>Cancellation policy applies as per terms and conditions</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.8.11/dist/dotlottie-wc.js" type="module"></script>
@endsection
