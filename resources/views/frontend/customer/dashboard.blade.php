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
            <div class="welcome-banner mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="text-white">Welcome back, {{ auth()->user()->name ?? 'Guest' }}!</h2>
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
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-primary">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $totalBookings ?? 0 }}</h3>
                            <p>Total Bookings</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $confirmedBookings ?? 0 }}</h3>
                            <p>Confirmed</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-warning">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $pendingBookings ?? 0 }}</h3>
                            <p>Pending</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-info">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ currency_format($totalSpent ?? 0) }}</h3>
                            <p>Total Spent</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Recent Bookings -->
                <div class="col-lg-8 mb-4">
                    <div class="dashboard-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><i class="bi bi-list-ul me-2"></i>Recent Bookings</h4>
                            <a href="{{ route('customer.bookings') }}" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="card-body">
                            @forelse($recentBookings ?? [] as $booking)
                                <div class="booking-item">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <img src="{{ $booking->image ?? asset('frontend/img/default-room.jpg') }}"
                                                alt="Booking" class="booking-img">
                                        </div>
                                        <div class="col-md-6">
                                            <h5>{{ $booking->room_name ?? 'Room Name' }}</h5>
                                            <p class="text-muted mb-1">
                                                <i class="bi bi-calendar me-2"></i>{{ $booking->check_in ?? 'N/A' }} -
                                                {{ $booking->check_out ?? 'N/A' }}
                                            </p>
                                            <p class="text-muted mb-0">
                                                <i class="bi bi-hash me-2"></i>Booking ID: {{ $booking->id ?? 'N/A' }}
                                            </p>
                                        </div>
                                        <div class="col-md-2 text-center">
                                            <span class="badge badge-{{ $booking->status_color ?? 'secondary' }}">
                                                {{ ucfirst($booking->status ?? 'Pending') }}
                                            </span>
                                        </div>
                                        <div class="col-md-2 text-end">
                                            <h5 class="text-primary mb-0">${{ number_format($booking->total ?? 0, 2) }}
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                                    <p class="text-muted mt-3">No bookings yet</p>
                                    <a href="{{ url('/rooms') }}" class="btn btn-primary">Browse Rooms</a>
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
                                <i class="bi bi-person-circle"></i>
                            </div>
                            <h5>{{ auth()->user()->name ?? 'Guest User' }}</h5>
                            <p class="text-muted mb-3">{{ auth()->user()->email ?? 'guest@example.com' }}</p>
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
                                <a href="{{ url('/yachts') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-water me-2"></i>Browse Yachts
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
