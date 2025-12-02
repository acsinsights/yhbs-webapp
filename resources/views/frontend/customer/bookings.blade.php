@extends('frontend.layouts.app') 
@section('content')
    <!-- Breadcrumb section Start-->
    <div class="breadcrumb-section"
        style="background-image:linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url({{ asset('frontend/img/innerpages/breadcrumb-bg6.jpg') }});">
        <div class="container">
            <div class="banner-content">
                <h1>My Bookings</h1>
                <ul class="breadcrumb-list">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li><a href="{{ route('customer.dashboard') }}">Dashboard</a></li>
                    <li>Bookings</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Bookings Section Start -->
    <div class="customer-bookings-section pt-100 pb-100">
        <div class="container">
            <!-- Filter Tabs -->
            <div class="booking-filters mb-4">
                <ul class="nav nav-pills" id="bookingTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="all-tab" data-bs-toggle="pill" data-bs-target="#all"
                            type="button">
                            <i class="bi bi-list-ul me-2"></i>All Bookings
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="confirmed-tab" data-bs-toggle="pill" data-bs-target="#confirmed"
                            type="button">
                            <i class="bi bi-check-circle me-2"></i>Confirmed
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pending-tab" data-bs-toggle="pill" data-bs-target="#pending"
                            type="button">
                            <i class="bi bi-clock me-2"></i>Pending
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="cancelled-tab" data-bs-toggle="pill" data-bs-target="#cancelled"
                            type="button">
                            <i class="bi bi-x-circle me-2"></i>Cancelled
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Bookings Content -->
            <div class="tab-content" id="bookingTabsContent">
                <!-- All Bookings -->
                <div class="tab-pane fade show active" id="all" role="tabpanel">
                    <div class="row">
                        @forelse($bookings ?? [] as $booking)
                            <div class="col-lg-12 mb-4">
                                <div class="booking-card">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <img src="{{ $booking->image ?? asset('frontend/img/default-room.jpg') }}"
                                                alt="Booking" class="booking-image">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="booking-details">
                                                <span class="badge badge-{{ $booking->status_color ?? 'secondary' }} mb-2">
                                                    {{ ucfirst($booking->status ?? 'Pending') }}
                                                </span>
                                                <h4>{{ $booking->property_name ?? 'Property Name' }}</h4>
                                                <p class="text-muted mb-2">
                                                    <i class="bi bi-geo-alt me-2"></i>{{ $booking->location ?? 'Location' }}
                                                </p>
                                                <div class="booking-info">
                                                    <div class="info-item">
                                                        <i class="bi bi-calendar-check"></i>
                                                        <div>
                                                            <small>Check-in</small>
                                                            <p>{{ $booking->check_in ?? 'N/A' }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="info-item">
                                                        <i class="bi bi-calendar-x"></i>
                                                        <div>
                                                            <small>Check-out</small>
                                                            <p>{{ $booking->check_out ?? 'N/A' }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="info-item">
                                                        <i class="bi bi-people"></i>
                                                        <div>
                                                            <small>Guests</small>
                                                            <p>{{ $booking->guests ?? '2' }} Adults</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <p class="booking-id mb-0">
                                                    <i class="bi bi-hash me-1"></i>Booking ID:
                                                    <strong>{{ $booking->id ?? 'N/A' }}</strong>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <div class="booking-price mb-3">
                                                <small class="text-muted">Total Amount</small>
                                                <h3 class="text-primary">${{ number_format($booking->total ?? 0, 2) }}</h3>
                                            </div>
                                            <div class="booking-actions">
                                                <a href="{{ route('customer.booking.details', $booking->id ?? '#') }}"
                                                    class="btn btn-outline-primary btn-sm mb-2 w-100">
                                                    <i class="bi bi-eye me-2"></i>View Details
                                                </a>
                                                @if (($booking->status ?? '') == 'pending')
                                                    <button class="btn btn-outline-danger btn-sm w-100"
                                                        onclick="cancelBooking({{ $booking->id ?? 0 }})">
                                                        <i class="bi bi-x-circle me-2"></i>Cancel
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <h4>No Bookings Found</h4>
                                    <p class="text-muted">You haven't made any bookings yet</p>
                                    <a href="{{ url('/rooms') }}" class="btn btn-primary">
                                        <i class="bi bi-search me-2"></i>Browse Properties
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Confirmed Bookings -->
                <div class="tab-pane fade" id="confirmed" role="tabpanel">
                    <div class="row">
                        <div class="col-12">
                            <div class="empty-state">
                                <i class="bi bi-check-circle"></i>
                                <h4>No Confirmed Bookings</h4>
                                <p class="text-muted">Your confirmed bookings will appear here</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Bookings -->
                <div class="tab-pane fade" id="pending" role="tabpanel">
                    <div class="row">
                        <div class="col-12">
                            <div class="empty-state">
                                <i class="bi bi-clock-history"></i>
                                <h4>No Pending Bookings</h4>
                                <p class="text-muted">Your pending bookings will appear here</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cancelled Bookings -->
                <div class="tab-pane fade" id="cancelled" role="tabpanel">
                    <div class="row">
                        <div class="col-12">
                            <div class="empty-state">
                                <i class="bi bi-x-circle"></i>
                                <h4>No Cancelled Bookings</h4>
                                <p class="text-muted">Your cancelled bookings will appear here</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

