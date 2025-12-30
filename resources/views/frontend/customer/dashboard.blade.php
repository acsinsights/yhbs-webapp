@extends('frontend.layouts.app')
@section('title', 'Dashboard - YHBS')
@section('content')
    <!-- Breadcrumb section Start-->
    <div class="breadcrumb-section"
        style="background-image:linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url({{ asset('frontend/img/innerpages/breadcrumb-bg6.jpg') }});">
        <div class="container">
            <div class="banner-content">
                <h1>My Dashboard</h1>
                <ul class="breadcrumb-list">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li>Dashboard</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Dashboard Section Start -->
    <div class="customer-dashboard-section pt-100 pb-100">
        <div class="container">
            <!-- Welcome Section -->
            <div class="welcome-banner mb-5">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="text-white">Welcome back, {{ auth()->user()->name }}!</h2>
                        <p class="mb-0">Manage your bookings and profile from your dashboard</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="{{ route('customer.profile') }}" class="btn btn-outline-primary">
                            <i class="bi bi-person-circle me-2"></i>View Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <!-- Total Bookings -->
                <div class="col-lg-4 col-md-4">
                    <div class="stat-card stat-card-primary">
                        <div class="stat-icon-modern">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div class="stat-content-modern">
                            <p class="stat-label">Total Bookings</p>
                            <h3 class="stat-number">{{ $totalBookings }}</h3>
                        </div>
                    </div>
                </div>

                <!-- Confirmed -->
                <div class="col-lg-4 col-md-4">
                    <div class="stat-card stat-card-success">
                        <div class="stat-icon-modern">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="stat-content-modern">
                            <p class="stat-label">Confirmed</p>
                            <h3 class="stat-number">{{ $confirmedBookings }}</h3>
                        </div>
                    </div>
                </div>

                <!-- Pending -->
                <div class="col-lg-4 col-md-4">
                    <div class="stat-card stat-card-warning">
                        <div class="stat-icon-modern">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="stat-content-modern">
                            <p class="stat-label">Pending</p>
                            <h3 class="stat-number">{{ $pendingBookings }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wallet Balance - Featured Banner -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="wallet-featured-banner">
                        <div class="wallet-sparkle">✨</div>
                        <div class="row align-items-center">
                            <div class="col-lg-7">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="wallet-icon-banner">
                                        <i class="bi bi-wallet2"></i>
                                    </div>
                                    <div>
                                        <p class="wallet-label-banner">Your Wallet Balance</p>
                                        <h2 class="wallet-amount-banner">
                                            {{ currency_format(number_format(auth()->user()->wallet_balance ?? 0, 2)) }}
                                        </h2>
                                        @if ((auth()->user()->wallet_balance ?? 0) > 0)
                                            <div class="wallet-status active">
                                                <i class="bi bi-check-circle-fill"></i> Available for bookings
                                            </div>
                                        @else
                                            <div class="wallet-status inactive">
                                                <i class="bi bi-info-circle"></i> Refunds appear here
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5 mt-3 mt-lg-0">
                                <div class="wallet-info-note">
                                    <div class="wallet-info-icon">
                                        <i class="bi bi-info-circle-fill"></i>
                                    </div>
                                    <div class="wallet-info-text">
                                        <h6>How to use?</h6>
                                        <p>Your wallet balance can only be used during the booking checkout process. Simply
                                            toggle the wallet option when making a new booking.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Recent Bookings -->
                <div class="col-lg-8">
                    <div class="dashboard-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><i class="bi bi-list-ul me-2"></i>Recent Bookings</h4>
                            <a href="{{ route('customer.bookings') }}" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="card-body">
                            @forelse($recentBookings as $booking)
                                <div class="booking-item">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <img src="{{ $booking['image'] }}" alt="Booking" class="booking-img">
                                        </div>
                                        <div class="col-md-6">
                                            <h5>{{ $booking['room_name'] }}</h5>
                                            <p class="text-muted mb-1">
                                                <i class="bi bi-calendar me-2"></i>{{ $booking['check_in'] }} -
                                                {{ $booking['check_out'] }}
                                            </p>
                                            <p class="text-muted mb-0">
                                                <i class="bi bi-hash me-2"></i>Booking ID: {{ $booking['id'] }}
                                            </p>
                                        </div>
                                        <div class="col-md-2 text-center">
                                            <span
                                                class="badge {{ $booking['status']?->badgeColor() ?? 'badge-secondary' }}">
                                                {{ $booking['status']?->label() ?? 'Pending' }}
                                            </span>
                                        </div>
                                        <div class="col-md-2 text-end">
                                            <h5 class="text-primary mb-0">{{ currency_format($booking['total']) }}
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                                    <p class="text-muted mt-3">No bookings yet</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Quick Actions & Profile -->
                <div class="col-lg-4">
                    <!-- Profile Card -->
                    <div class="dashboard-card mb-4">
                        <div class="card-header">
                            <h4><i class="bi bi-person me-2"></i>Profile</h4>
                        </div>
                        <div class="card-body text-center">
                            <div class="profile-avatar mb-3">
                                @if (auth()->user()->avatar)
                                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Profile Avatar"
                                        class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                                @else
                                    <i class="bi bi-person-circle"></i>
                                @endif
                            </div>
                            <h5>{{ auth()->user()->name }}</h5>
                            <p class="text-muted mb-3">{{ auth()->user()->email }}</p>
                            <a href="{{ route('customer.profile') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil me-2"></i>Edit Profile
                            </a>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h4><i class="bi bi-lightning me-2"></i>Quick Actions</h4>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ url('/rooms') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-search me-2"></i>Browse Rooms
                                </a>
                                <a href="{{ url('/houses') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-house me-2"></i>Browse Houses
                                </a>
                                <a href="{{ route('customer.bookings') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-calendar-check me-2"></i>My Bookings
                                </a>
                                <a href="{{ route('customer.profile') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-gear me-2"></i>Account Settings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
