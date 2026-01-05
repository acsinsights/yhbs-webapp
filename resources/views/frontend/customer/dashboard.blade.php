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
                    <div class="wallet-featured-banner-enhanced">
                        <div class="wallet-sparkle-animated">✨</div>
                        <div class="row align-items-center g-0">
                            <div class="col-lg-6">
                                <div class="wallet-balance-section">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="wallet-icon-modern">
                                            <i class="bi bi-wallet2"></i>
                                        </div>
                                        <div>
                                            <p class="wallet-label-text mb-1">Your Wallet Balance</p>
                                            <h1 class="wallet-amount-text mb-0">
                                                {{ currency_format(auth()->user()->wallet_balance ?? 0) }}
                                            </h1>
                                        </div>
                                    </div>
                                    @if ((auth()->user()->wallet_balance ?? 0) > 0)
                                        <div class="wallet-badge-active">
                                            <i class="bi bi-check-circle-fill me-2"></i> Available for bookings
                                        </div>
                                        @php
                                            $nearestExpiry = auth()->user()->getNearestWalletExpiry();
                                        @endphp
                                        @if ($nearestExpiry)
                                            <div class="wallet-expiry-info">
                                                <i class="bi bi-clock-history me-1"></i>
                                                Expires: {{ $nearestExpiry->expires_at->format('d M Y') }}
                                                <small
                                                    class="text-white-50">({{ $nearestExpiry->expires_at->diffForHumans() }})</small>
                                            </div>
                                        @endif
                                    @else
                                        <div class="wallet-badge-inactive">
                                            <i class="bi bi-info-circle me-2"></i> Refunds appear here
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="wallet-info-card">
                                    <div class="wallet-info-header">
                                        <i class="bi bi-info-circle-fill me-2"></i>
                                        <h6 class="mb-0">How to use?</h6>
                                    </div>
                                    <p class="wallet-info-description">
                                        Your wallet balance can be used during checkout. Simply toggle the wallet option
                                        when making a new booking.
                                    </p>
                                    <div class="wallet-policy-badge">
                                        <i class="bi bi-shield-check me-2"></i>
                                        <strong>Wallet Policy:</strong> Credits expire after 90 days
                                    </div>
                                    <button type="button" class="btn-wallet-transactions" data-bs-toggle="offcanvas"
                                        data-bs-target="#walletTransactionsOffcanvas">
                                        <i class="bi bi-clock-history me-2"></i>View Transaction History
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                .wallet-featured-banner-enhanced {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border-radius: 20px;
                    padding: 40px;
                    position: relative;
                    overflow: hidden;
                    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
                }

                .wallet-sparkle-animated {
                    position: absolute;
                    top: 20px;
                    right: 20px;
                    font-size: 40px;
                    animation: sparkle 2s ease-in-out infinite;
                }

                @keyframes sparkle {

                    0%,
                    100% {
                        transform: scale(1) rotate(0deg);
                        opacity: 0.8;
                    }

                    50% {
                        transform: scale(1.2) rotate(180deg);
                        opacity: 1;
                    }
                }

                .wallet-balance-section {
                    color: white;
                }

                .wallet-icon-modern {
                    background: rgba(255, 255, 255, 0.2);
                    width: 70px;
                    height: 70px;
                    border-radius: 15px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    backdrop-filter: blur(10px);
                }

                .wallet-icon-modern i {
                    font-size: 35px;
                    color: white;
                }

                .wallet-label-text {
                    color: rgba(255, 255, 255, 0.9);
                    font-size: 14px;
                    font-weight: 500;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                }

                .wallet-amount-text {
                    color: white;
                    font-size: 42px;
                    font-weight: 800;
                    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
                }

                .wallet-badge-active {
                    display: inline-block;
                    background: rgba(40, 167, 69, 0.9);
                    color: white;
                    padding: 8px 16px;
                    border-radius: 25px;
                    font-size: 13px;
                    font-weight: 600;
                    margin-top: 10px;
                }

                .wallet-badge-inactive {
                    display: inline-block;
                    background: rgba(255, 255, 255, 0.25);
                    color: white;
                    padding: 8px 16px;
                    border-radius: 25px;
                    font-size: 13px;
                    font-weight: 600;
                    margin-top: 10px;
                }

                .wallet-expiry-info {
                    color: #fff3cd;
                    font-size: 13px;
                    margin-top: 12px;
                    font-weight: 500;
                }

                .wallet-info-card {
                    background: rgba(255, 255, 255, 0.15);
                    backdrop-filter: blur(20px);
                    border-radius: 15px;
                    padding: 25px;
                    color: white;
                    border: 1px solid rgba(255, 255, 255, 0.2);
                }

                .wallet-info-header {
                    display: flex;
                    align-items: center;
                    margin-bottom: 12px;
                }

                .wallet-info-header h6 {
                    color: white;
                    font-weight: 700;
                }

                .wallet-info-description {
                    color: rgba(255, 255, 255, 0.9);
                    font-size: 14px;
                    margin-bottom: 15px;
                    line-height: 1.6;
                }

                .wallet-policy-badge {
                    background: rgba(255, 255, 255, 0.1);
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    padding: 10px 15px;
                    border-radius: 10px;
                    font-size: 12px;
                    color: rgba(255, 255, 255, 0.95);
                    margin-bottom: 20px;
                }

                .btn-wallet-transactions {
                    width: 100%;
                    background: white;
                    color: #667eea;
                    border: none;
                    padding: 12px 20px;
                    border-radius: 10px;
                    font-weight: 600;
                    font-size: 14px;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                }

                .btn-wallet-transactions:hover {
                    background: #f8f9fa;
                    transform: translateY(-2px);
                    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
                }

                @media (max-width: 991px) {
                    .wallet-featured-banner-enhanced {
                        padding: 30px 20px;
                    }

                    .wallet-amount-text {
                        font-size: 32px;
                    }

                    .wallet-info-card {
                        margin-top: 20px;
                    }
                }
            </style>

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
                                                <i class="bi bi-hash me-2"></i>Booking ID: {{ $booking['booking_id'] }}
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

    <!-- Wallet Transactions Offcanvas (Slides from Left) -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="walletTransactionsOffcanvas"
        aria-labelledby="walletTransactionsLabel" style="width: 600px;">
        <div class="offcanvas-header"
            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h5 class="offcanvas-title text-white" id="walletTransactionsLabel">
                <i class="bi bi-wallet2 me-2"></i>Wallet Transactions
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            @livewire('frontend.wallet-transactions-offcanvas')
        </div>
    </div>
@endsection
