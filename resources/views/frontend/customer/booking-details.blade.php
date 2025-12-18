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
                            <span class="badge {{ $booking->status?->badgeColor() ?? 'badge-secondary' }}">
                                {{ $booking->status?->label() ?? 'Pending' }}
                            </span>
                        </div>
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
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <i class="bi bi-calendar-check"></i>
                                    </div>
                                    <div>
                                        <small>Check-in</small>
                                        <p>{{ $booking->check_in ? \Carbon\Carbon::parse($booking->check_in)->format('M d, Y') : 'N/A' }}
                                        </p>
                                    </div>
                                </div>

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

                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div>
                                        <small>Guests</small>
                                        <p>{{ ($booking->adults ?? 0) + ($booking->children ?? 0) }} Guests</p>
                                    </div>
                                </div>
                            </div>

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
                                <div class="payment-row">
                                    <span>Booking Amount</span>
                                    <span>{{ currency_format($booking->price ?? 0) }}</span>
                                </div>
                                @if ($booking->discount_price)
                                    <div class="payment-row">
                                        <span>Discount</span>
                                        <span class="text-success">-{{ currency_format($booking->discount_price) }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="divider"></div>

                            <div class="total-amount">
                                <span>Total Paid</span>
                                <span>{{ currency_format(($booking->price ?? 0) - ($booking->discount_price ?? 0)) }}</span>
                            </div>

                            <div class="payment-status">
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
                                @if (in_array($booking->status, ['pending', 'confirmed', 'booked']))
                                    <button class="btn btn-outline-danger" onclick="cancelBooking({{ $booking->id }})">
                                        <i class="bi bi-x-circle me-2"></i>Cancel Booking
                                    </button>
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

@section('scripts')
    <script>
        function cancelBooking(bookingId) {
            if (confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
                // Add your cancel booking AJAX logic here
                alert('Booking cancellation functionality will be implemented.');
            }
        }
    </script>
@endsection
