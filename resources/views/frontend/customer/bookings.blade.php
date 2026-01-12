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
                        @forelse($bookings as $booking)
                            <div class="col-lg-12 mb-4">
                                <div class="booking-card">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            @php
                                                $propertyImage = asset(
                                                    'frontend/img/innerpages/hotel-dt-room-img1.jpg',
                                                );
                                                if ($booking->bookingable?->image) {
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
                                            <img src="{{ $propertyImage }}"
                                                alt="{{ $booking->bookingable?->name ?? 'Booking' }}" class="booking-image"
                                                onerror="this.src='{{ asset('frontend/img/innerpages/hotel-dt-room-img1.jpg') }}'">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="booking-details">
                                                <div class="mb-2">
                                                    <span
                                                        class="badge {{ $booking->status?->badgeColor() ?? 'badge-secondary' }} me-2">
                                                        {{ $booking->status?->label() ?? 'Pending' }}
                                                    </span>

                                                    @if ($booking->status !== App\Enums\BookingStatusEnum::CANCELLED)
                                                        @if ($booking->cancellation_status === 'pending')
                                                            <span class="badge bg-warning">
                                                                <i class="bi bi-clock me-1"></i>Cancellation Pending
                                                            </span>
                                                        @elseif($booking->cancellation_status === 'rejected')
                                                            <span class="badge bg-info">
                                                                <i class="bi bi-info-circle me-1"></i>Cancellation Declined
                                                            </span>
                                                        @endif

                                                        @if ($booking->reschedule_status === 'pending')
                                                            <span class="badge bg-warning">
                                                                <i class="bi bi-calendar-event me-1"></i>Reschedule Pending
                                                            </span>
                                                        @elseif($booking->reschedule_status === 'approved')
                                                            <span class="badge bg-success">
                                                                <i class="bi bi-check-circle me-1"></i>Rescheduled
                                                            </span>
                                                        @elseif($booking->reschedule_status === 'rejected')
                                                            <span class="badge bg-danger">
                                                                <i class="bi bi-x-circle me-1"></i>Reschedule Declined
                                                            </span>
                                                        @endif
                                                    @endif
                                                </div>
                                                <h4>{{ $booking->bookingable?->name ?? 'Property Name' }}</h4>
                                                @if ($booking->bookingable?->house)
                                                    <p class="text-muted mb-2">
                                                        <i
                                                            class="bi bi-house me-2"></i>{{ $booking->bookingable->house->name }}
                                                    </p>
                                                @endif
                                                <div class="booking-info">
                                                    @php
                                                        $isBoat =
                                                            $booking->bookingable_type === \App\Models\Boat::class;
                                                    @endphp

                                                    @if ($isBoat)
                                                        {{-- For Boat bookings, show date and time slot --}}
                                                        <div class="info-item">
                                                            <i class="bi bi-calendar-check"></i>
                                                            <div>
                                                                <small>Date</small>
                                                                <p>{{ $booking->check_in?->format('M d, Y') ?? 'N/A' }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="info-item">
                                                            <i class="bi bi-clock"></i>
                                                            <div>
                                                                <small>Time Slot</small>
                                                                <p>
                                                                    @if ($booking->check_in && $booking->check_out)
                                                                        {{ $booking->check_in->format('h:i A') }} -
                                                                        {{ $booking->check_out->format('h:i A') }}
                                                                    @else
                                                                        N/A
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </div>
                                                    @else
                                                        {{-- For House/Room bookings, show check-in and check-out --}}
                                                        <div class="info-item">
                                                            <i class="bi bi-calendar-check"></i>
                                                            <div>
                                                                <small>Check-in</small>
                                                                <p>{{ $booking->check_in?->format('M d, Y') ?? 'N/A' }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="info-item">
                                                            <i class="bi bi-calendar-x"></i>
                                                            <div>
                                                                <small>Check-out</small>
                                                                <p>{{ $booking->check_out?->format('M d, Y') ?? 'N/A' }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <div class="info-item">
                                                        <i class="bi bi-people"></i>
                                                        <div>
                                                            <small>{{ $isBoat ? 'Passengers' : 'Guests' }}</small>
                                                            <p>{{ $booking->adults ?? '0' }}
                                                                {{ $isBoat ? 'Passenger(s)' : 'Adults' }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <p class="booking-id mb-0">
                                                    <i class="bi bi-hash me-1"></i>Booking ID:
                                                    <strong>{{ $booking->booking_id }}</strong>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <div class="booking-price mb-3">
                                                <small class="text-muted">Total Amount</small>
                                                <h3 class="text-primary">{{ currency_format($booking->price ?? 0) }}</h3>
                                            </div>
                                            <div class="booking-actions">
                                                <a href="{{ route('customer.booking.details', $booking->id) }}"
                                                    class="btn btn-outline-primary btn-sm mb-2 w-100">
                                                    <i class="bi bi-eye me-2"></i>View Details
                                                </a>
                                                @if ($booking->status === App\Enums\BookingStatusEnum::PENDING)
                                                    <button class="btn btn-outline-danger btn-sm w-100"
                                                        onclick="cancelBooking({{ $booking->id }})">
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
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    @if ($bookings->hasPages())
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="pagination-area">
                                    @if ($bookings->onFirstPage())
                                        <div class="paginations-button disabled">
                                            <span>
                                                <svg width="10" height="10" viewBox="0 0 10 10"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <g>
                                                        <path
                                                            d="M7.86133 9.28516C7.14704 7.49944 3.57561 5.71373 1.43276 4.99944C3.57561 4.28516 6.7899 3.21373 7.86133 0.713728"
                                                            stroke-width="1.5" stroke-linecap="round"></path>
                                                    </g>
                                                </svg>
                                                Prev
                                            </span>
                                        </div>
                                    @else
                                        <div class="paginations-button">
                                            <a href="{{ $bookings->previousPageUrl() }}">
                                                <svg width="10" height="10" viewBox="0 0 10 10"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <g>
                                                        <path
                                                            d="M7.86133 9.28516C7.14704 7.49944 3.57561 5.71373 1.43276 4.99944C3.57561 4.28516 6.7899 3.21373 7.86133 0.713728"
                                                            stroke-width="1.5" stroke-linecap="round"></path>
                                                    </g>
                                                </svg>
                                                Prev
                                            </a>
                                        </div>
                                    @endif

                                    <ul class="paginations">
                                        @foreach ($bookings->getUrlRange(1, $bookings->lastPage()) as $page => $url)
                                            <li class="page-item {{ $page == $bookings->currentPage() ? 'active' : '' }}">
                                                <a href="{{ $url }}">
                                                    {{ str_pad($page, 2, '0', STR_PAD_LEFT) }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>

                                    @if ($bookings->hasMorePages())
                                        <div class="paginations-button">
                                            <a href="{{ $bookings->nextPageUrl() }}">
                                                Next
                                                <svg width="10" height="10" viewBox="0 0 10 10"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <g>
                                                        <path
                                                            d="M1.42969 9.28613C2.14397 7.50042 5.7154 5.7147 7.85826 5.00042C5.7154 4.28613 2.50112 3.21471 1.42969 0.714705"
                                                            stroke-width="1.5" stroke-linecap="round"></path>
                                                    </g>
                                                </svg>
                                            </a>
                                        </div>
                                    @else
                                        <div class="paginations-button disabled">
                                            <span>
                                                Next
                                                <svg width="10" height="10" viewBox="0 0 10 10"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <g>
                                                        <path
                                                            d="M1.42969 9.28613C2.14397 7.50042 5.7154 5.7147 7.85826 5.00042C5.7154 4.28613 2.50112 3.21471 1.42969 0.714705"
                                                            stroke-width="1.5" stroke-linecap="round"></path>
                                                    </g>
                                                </svg>
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Confirmed Bookings -->
                <div class="tab-pane fade" id="confirmed" role="tabpanel">
                    <div class="row">
                        @php
                            $confirmedBookings = $bookings->filter(function ($booking) {
                                return in_array($booking->status, [
                                    App\Enums\BookingStatusEnum::BOOKED,
                                    App\Enums\BookingStatusEnum::CHECKED_IN,
                                ]);
                            });
                        @endphp

                        @forelse($confirmedBookings as $booking)
                            <div class="col-lg-12 mb-4">
                                <div class="booking-card">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            @php
                                                $propertyImage = asset(
                                                    'frontend/img/innerpages/hotel-dt-room-img1.jpg',
                                                );
                                                if ($booking->bookingable?->image) {
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
                                            <img src="{{ $propertyImage }}"
                                                alt="{{ $booking->bookingable?->name ?? 'Booking' }}"
                                                class="booking-image"
                                                onerror="this.src='{{ asset('frontend/img/innerpages/hotel-dt-room-img1.jpg') }}'">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="booking-details">
                                                <div class="mb-2">
                                                    <span
                                                        class="badge {{ $booking->status?->badgeColor() ?? 'badge-secondary' }} me-2">
                                                        {{ $booking->status?->label() ?? 'Confirmed' }}
                                                    </span>

                                                    @if ($booking->status !== App\Enums\BookingStatusEnum::CANCELLED)
                                                        @if ($booking->cancellation_status === 'pending')
                                                            <span class="badge bg-warning">
                                                                <i class="bi bi-clock me-1"></i>Cancellation Pending
                                                            </span>
                                                        @elseif($booking->cancellation_status === 'rejected')
                                                            <span class="badge bg-info">
                                                                <i class="bi bi-info-circle me-1"></i>Cancellation Declined
                                                            </span>
                                                        @endif

                                                        @if ($booking->reschedule_status === 'pending')
                                                            <span class="badge bg-warning">
                                                                <i class="bi bi-calendar-event me-1"></i>Reschedule Pending
                                                            </span>
                                                        @elseif($booking->reschedule_status === 'approved')
                                                            <span class="badge bg-success">
                                                                <i class="bi bi-check-circle me-1"></i>Rescheduled
                                                            </span>
                                                        @elseif($booking->reschedule_status === 'rejected')
                                                            <span class="badge bg-danger">
                                                                <i class="bi bi-x-circle me-1"></i>Reschedule Declined
                                                            </span>
                                                        @endif
                                                    @endif
                                                </div>
                                                <h4>{{ $booking->bookingable?->name ?? 'Property Name' }}</h4>
                                                @if ($booking->bookingable?->house)
                                                    <p class="text-muted mb-2">
                                                        <i
                                                            class="bi bi-house me-2"></i>{{ $booking->bookingable->house->name }}
                                                    </p>
                                                @endif
                                                <div class="booking-info">
                                                    @php
                                                        $isBoat =
                                                            $booking->bookingable_type === \App\Models\Boat::class;
                                                    @endphp

                                                    @if ($isBoat)
                                                        {{-- For Boat bookings, show date and time slot --}}
                                                        <div class="info-item">
                                                            <i class="bi bi-calendar-check"></i>
                                                            <div>
                                                                <small>Date</small>
                                                                <p>{{ $booking->check_in?->format('M d, Y') ?? 'N/A' }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="info-item">
                                                            <i class="bi bi-clock"></i>
                                                            <div>
                                                                <small>Time Slot</small>
                                                                <p>
                                                                    @if ($booking->check_in && $booking->check_out)
                                                                        {{ $booking->check_in->format('h:i A') }} -
                                                                        {{ $booking->check_out->format('h:i A') }}
                                                                    @else
                                                                        N/A
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </div>
                                                    @else
                                                        {{-- For House/Room bookings, show check-in and check-out --}}
                                                        <div class="info-item">
                                                            <i class="bi bi-calendar-check"></i>
                                                            <div>
                                                                <small>Check-in</small>
                                                                <p>{{ $booking->check_in?->format('M d, Y') ?? 'N/A' }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="info-item">
                                                            <i class="bi bi-calendar-x"></i>
                                                            <div>
                                                                <small>Check-out</small>
                                                                <p>{{ $booking->check_out?->format('M d, Y') ?? 'N/A' }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <div class="info-item">
                                                        <i class="bi bi-people"></i>
                                                        <div>
                                                            <small>{{ $isBoat ? 'Passengers' : 'Guests' }}</small>
                                                            <p>{{ $booking->adults ?? '0' }}
                                                                {{ $isBoat ? 'Passenger(s)' : 'Adults' }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <p class="booking-id mb-0">
                                                    <i class="bi bi-hash me-1"></i>Booking ID:
                                                    <strong>{{ $booking->booking_id }}</strong>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <div class="booking-price mb-3">
                                                <small class="text-muted">Total Amount</small>
                                                <h3 class="text-primary">{{ currency_format($booking->price ?? 0) }}</h3>
                                            </div>
                                            <div class="booking-actions">
                                                <a href="{{ route('customer.booking.details', $booking->id) }}"
                                                    class="btn btn-outline-primary btn-sm mb-2 w-100">
                                                    <i class="bi bi-eye me-2"></i>View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="empty-state">
                                    <i class="bi bi-check-circle"></i>
                                    <h4>No Confirmed Bookings</h4>
                                    <p class="text-muted">Your confirmed bookings will appear here</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Pending Bookings -->
                <div class="tab-pane fade" id="pending" role="tabpanel">
                    <div class="row">
                        @php
                            $pendingBookings = $bookings->filter(function ($booking) {
                                return $booking->status === App\Enums\BookingStatusEnum::PENDING;
                            });
                        @endphp

                        @forelse($pendingBookings as $booking)
                            <div class="col-lg-12 mb-4">
                                <div class="booking-card">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            @php
                                                $propertyImage = asset(
                                                    'frontend/img/innerpages/hotel-dt-room-img1.jpg',
                                                );
                                                if ($booking->bookingable?->image) {
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
                                            <img src="{{ $propertyImage }}"
                                                alt="{{ $booking->bookingable?->name ?? 'Booking' }}"
                                                class="booking-image"
                                                onerror="this.src='{{ asset('frontend/img/innerpages/hotel-dt-room-img1.jpg') }}'">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="booking-details">
                                                <div class="mb-2">
                                                    <span
                                                        class="badge {{ $booking->status?->badgeColor() ?? 'badge-secondary' }} me-2">
                                                        {{ $booking->status?->label() ?? 'Pending' }}
                                                    </span>

                                                    @if ($booking->status !== App\Enums\BookingStatusEnum::CANCELLED)
                                                        @if ($booking->cancellation_status === 'pending')
                                                            <span class="badge bg-warning">
                                                                <i class="bi bi-clock me-1"></i>Cancellation Pending
                                                            </span>
                                                        @elseif($booking->cancellation_status === 'rejected')
                                                            <span class="badge bg-info">
                                                                <i class="bi bi-info-circle me-1"></i>Cancellation Declined
                                                            </span>
                                                        @endif

                                                        @if ($booking->reschedule_status === 'pending')
                                                            <span class="badge bg-warning">
                                                                <i class="bi bi-calendar-event me-1"></i>Reschedule Pending
                                                            </span>
                                                        @elseif($booking->reschedule_status === 'approved')
                                                            <span class="badge bg-success">
                                                                <i class="bi bi-check-circle me-1"></i>Rescheduled
                                                            </span>
                                                        @elseif($booking->reschedule_status === 'rejected')
                                                            <span class="badge bg-danger">
                                                                <i class="bi bi-x-circle me-1"></i>Reschedule Declined
                                                            </span>
                                                        @endif
                                                    @endif
                                                </div>
                                                <h4>{{ $booking->bookingable?->name ?? 'Property Name' }}</h4>
                                                @if ($booking->bookingable?->house)
                                                    <p class="text-muted mb-2">
                                                        <i
                                                            class="bi bi-house me-2"></i>{{ $booking->bookingable->house->name }}
                                                    </p>
                                                @endif
                                                <div class="booking-info">
                                                    @php
                                                        $isBoat =
                                                            $booking->bookingable_type === \App\Models\Boat::class;
                                                    @endphp

                                                    @if ($isBoat)
                                                        {{-- For Boat bookings, show date and time slot --}}
                                                        <div class="info-item">
                                                            <i class="bi bi-calendar-check"></i>
                                                            <div>
                                                                <small>Date</small>
                                                                <p>{{ $booking->check_in?->format('M d, Y') ?? 'N/A' }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="info-item">
                                                            <i class="bi bi-clock"></i>
                                                            <div>
                                                                <small>Time Slot</small>
                                                                <p>
                                                                    @if ($booking->check_in && $booking->check_out)
                                                                        {{ $booking->check_in->format('h:i A') }} -
                                                                        {{ $booking->check_out->format('h:i A') }}
                                                                    @else
                                                                        N/A
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </div>
                                                    @else
                                                        {{-- For House/Room bookings, show check-in and check-out --}}
                                                        <div class="info-item">
                                                            <i class="bi bi-calendar-check"></i>
                                                            <div>
                                                                <small>Check-in</small>
                                                                <p>{{ $booking->check_in?->format('M d, Y') ?? 'N/A' }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="info-item">
                                                            <i class="bi bi-calendar-x"></i>
                                                            <div>
                                                                <small>Check-out</small>
                                                                <p>{{ $booking->check_out?->format('M d, Y') ?? 'N/A' }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <div class="info-item">
                                                        <i class="bi bi-people"></i>
                                                        <div>
                                                            <small>{{ $isBoat ? 'Passengers' : 'Guests' }}</small>
                                                            <p>{{ $booking->adults ?? '0' }}
                                                                {{ $isBoat ? 'Passenger(s)' : 'Adults' }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <p class="booking-id mb-0">
                                                    <i class="bi bi-hash me-1"></i>Booking ID:
                                                    <strong>{{ $booking->booking_id }}</strong>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <div class="booking-price mb-3">
                                                <small class="text-muted">Total Amount</small>
                                                <h3 class="text-primary">{{ currency_format($booking->price ?? 0) }}</h3>
                                            </div>
                                            <div class="booking-actions">
                                                <a href="{{ route('customer.booking.details', $booking->id) }}"
                                                    class="btn btn-outline-primary btn-sm mb-2 w-100">
                                                    <i class="bi bi-eye me-2"></i>View Details
                                                </a>
                                                <button class="btn btn-outline-danger btn-sm w-100"
                                                    onclick="cancelBooking({{ $booking->id }})">
                                                    <i class="bi bi-x-circle me-2"></i>Cancel
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="empty-state">
                                    <i class="bi bi-clock-history"></i>
                                    <h4>No Pending Bookings</h4>
                                    <p class="text-muted">Your pending bookings will appear here</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Cancelled Bookings -->
                <div class="tab-pane fade" id="cancelled" role="tabpanel">
                    <div class="row">
                        @php
                            $cancelledBookings = $bookings->filter(function ($booking) {
                                return $booking->status === App\Enums\BookingStatusEnum::CANCELLED;
                            });
                        @endphp

                        @forelse($cancelledBookings as $booking)
                            <div class="col-lg-12 mb-4">
                                <div class="booking-card">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            @php
                                                $propertyImage = asset(
                                                    'frontend/img/innerpages/hotel-dt-room-img1.jpg',
                                                );
                                                if ($booking->bookingable?->image) {
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
                                            <img src="{{ $propertyImage }}"
                                                alt="{{ $booking->bookingable?->name ?? 'Booking' }}"
                                                class="booking-image"
                                                onerror="this.src='{{ asset('frontend/img/innerpages/hotel-dt-room-img1.jpg') }}'">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="booking-details">
                                                <div class="mb-2">
                                                    <span
                                                        class="badge {{ $booking->status?->badgeColor() ?? 'badge-secondary' }} me-2">
                                                        {{ $booking->status?->label() ?? 'Cancelled' }}
                                                    </span>

                                                    @if ($booking->refund_amount > 0)
                                                        <span class="badge bg-info">
                                                            <i class="bi bi-cash-coin me-1"></i>Refunded:
                                                            {{ currency_format($booking->refund_amount) }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <h4>{{ $booking->bookingable?->name ?? 'Property Name' }}</h4>
                                                @if ($booking->bookingable?->house)
                                                    <p class="text-muted mb-2">
                                                        <i
                                                            class="bi bi-house me-2"></i>{{ $booking->bookingable->house->name }}
                                                    </p>
                                                @endif
                                                <div class="booking-info">
                                                    @php
                                                        $isBoat =
                                                            $booking->bookingable_type === \App\Models\Boat::class;
                                                    @endphp

                                                    @if ($isBoat)
                                                        {{-- For Boat bookings, show date and time slot --}}
                                                        <div class="info-item">
                                                            <i class="bi bi-calendar-check"></i>
                                                            <div>
                                                                <small>Date</small>
                                                                <p>{{ $booking->check_in?->format('M d, Y') ?? 'N/A' }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="info-item">
                                                            <i class="bi bi-clock"></i>
                                                            <div>
                                                                <small>Time Slot</small>
                                                                <p>
                                                                    @if ($booking->check_in && $booking->check_out)
                                                                        {{ $booking->check_in->format('h:i A') }} -
                                                                        {{ $booking->check_out->format('h:i A') }}
                                                                    @else
                                                                        N/A
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </div>
                                                    @else
                                                        {{-- For House/Room bookings, show check-in and check-out --}}
                                                        <div class="info-item">
                                                            <i class="bi bi-calendar-check"></i>
                                                            <div>
                                                                <small>Check-in</small>
                                                                <p>{{ $booking->check_in?->format('M d, Y') ?? 'N/A' }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="info-item">
                                                            <i class="bi bi-calendar-x"></i>
                                                            <div>
                                                                <small>Check-out</small>
                                                                <p>{{ $booking->check_out?->format('M d, Y') ?? 'N/A' }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <div class="info-item">
                                                        <i class="bi bi-people"></i>
                                                        <div>
                                                            <small>{{ $isBoat ? 'Passengers' : 'Guests' }}</small>
                                                            <p>{{ $booking->adults ?? '0' }}
                                                                {{ $isBoat ? 'Passenger(s)' : 'Adults' }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <p class="booking-id mb-0">
                                                    <i class="bi bi-hash me-1"></i>Booking ID:
                                                    <strong>{{ $booking->booking_id }}</strong>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <div class="booking-price mb-3">
                                                <small class="text-muted">Total Amount</small>
                                                <h3 class="text-muted">{{ currency_format($booking->price ?? 0) }}</h3>
                                            </div>
                                            <div class="booking-actions">
                                                <a href="{{ route('customer.booking.details', $booking->id) }}"
                                                    class="btn btn-outline-primary btn-sm w-100">
                                                    <i class="bi bi-eye me-2"></i>View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="empty-state">
                                    <i class="bi bi-x-circle"></i>
                                    <h4>No Cancelled Bookings</h4>
                                    <p class="text-muted">Your cancelled bookings will appear here</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function cancelBooking(bookingId) {
            if (confirm('Are you sure you want to cancel this booking?')) {
                fetch(`/customer/bookings/${bookingId}/cancel`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Booking cancelled successfully!');
                            location.reload();
                        } else {
                            alert(data.message || 'Failed to cancel booking');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while cancelling the booking');
                    });
            }
        }
    </script>
@endsection
