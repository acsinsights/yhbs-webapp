@extends('frontend.layouts.app')
@section('title', 'Booking Confirmed - YHBS')
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
                        <div class="success-icon mb-4">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <h2>Booking Confirmed!</h2>
                        <p class="text-muted">Thank you for your booking. A confirmation email has been sent to your email
                            address.</p>
                        <div class="booking-reference">
                            <span>Booking Reference</span>
                            <h3>#{{ $booking->reference ?? 'YHBS' . str_pad($booking->id ?? rand(1000, 9999), 4, '0', STR_PAD_LEFT) }}
                            </h3>
                        </div>
                    </div>

                    <!-- Booking Details -->
                    <div class="confirmation-card mb-4">
                        <div class="card-header">
                            <h4><i class="bi bi-card-checklist me-2"></i>Booking Details</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <img src="{{ $booking->property_image ?? asset('frontend/img/default-room.jpg') }}"
                                        alt="Property" class="confirmation-image">
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
                                                <p class="mb-0">{{ $booking->guests ?? '2' }} Adults</p>
                                            </div>
                                        </div>
                                    </div>
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
                                    <span>${{ number_format($booking->price_per_night ?? 0, 2) }}</span>
                                </div>
                                <div class="payment-row">
                                    <span>× {{ $booking->nights ?? '1' }} nights</span>
                                    <span>${{ number_format(($booking->price_per_night ?? 0) * ($booking->nights ?? 1), 2) }}</span>
                                </div>
                                @if (($booking->service_fee ?? 0) > 0)
                                    <div class="payment-row">
                                        <span>Service fee</span>
                                        <span>${{ number_format($booking->service_fee ?? 0, 2) }}</span>
                                    </div>
                                @endif
                                @if (($booking->tax ?? 0) > 0)
                                    <div class="payment-row">
                                        <span>Taxes</span>
                                        <span>${{ number_format($booking->tax ?? 0, 2) }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="divider"></div>

                            <div class="total-amount">
                                <span>Total Paid</span>
                                <span>${{ number_format($booking->total ?? 0, 2) }}</span>
                            </div>

                            <div class="payment-status">
                                <span class="badge badge-success">
                                    <i class="bi bi-check-circle me-2"></i>Payment Successful
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="confirmation-actions">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <a href="{{ route('customer.bookings') }}" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-list-ul me-2"></i>View All Bookings
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <button onclick="window.print()" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-printer me-2"></i>Print Confirmation
                                </button>
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
                            <li>For any queries, contact us at <a href="mailto:support@yhbs.com">support@yhbs.com</a></li>
                            <li>Cancellation policy applies as per terms and conditions</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
