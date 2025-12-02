@extends('frontend.layouts.app')
@section('content')
    <!-- Breadcrumb section Start-->
    <div class="breadcrumb-section three"
        style="background-image:linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url({{ asset('frontend/assets/img/innerpages/breadcrumb-bg6.jpg') }});">
        <div class="container">
            <div class="banner-content">
                <h1>{{ $yacht->name }}</h1>
                <ul class="breadcrumb-list">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="{{ route('yachts.index') }}">Yachts</a></li>
                    <li>{{ $yacht->name }}</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Yacht Details Section -->
    <div class="package-details-section pt-120 pb-120">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Yacht Image -->
                    <div class="package-img-area mb-4">
                        @if ($yacht->image)
                            @if (str_starts_with($yacht->image, '/default'))
                                <img src="{{ asset($yacht->image) }}" alt="{{ $yacht->name }}" class="img-fluid rounded">
                            @else
                                <img src="{{ asset('storage/' . $yacht->image) }}" alt="{{ $yacht->name }}"
                                    class="img-fluid rounded">
                            @endif
                        @else
                            <img src="{{ asset('frontend/assets/img/innerpages/hotel-img1.jpg') }}"
                                alt="{{ $yacht->name }}" class="img-fluid rounded">
                        @endif
                    </div>

                    <!-- Yacht Information -->
                    <div class="package-details-content">
                        <h2>{{ $yacht->name }}</h2>

                        @if ($yacht->categories->first())
                            <div class="mb-3">
                                <span class="badge bg-primary">{{ $yacht->categories->first()->name }}</span>
                            </div>
                        @endif

                        @if ($yacht->description)
                            <div class="mb-4">
                                <h4>About This Yacht</h4>
                                <p>{{ $yacht->description }}</p>
                            </div>
                        @endif

                        <!-- Yacht Features -->
                        @if ($yacht->amenities && $yacht->amenities->count() > 0)
                            <div class="mb-4">
                                <h4>Amenities</h4>
                                <div class="row">
                                    @foreach ($yacht->amenities as $amenity)
                                        <div class="col-md-6 mb-3">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-check-circle text-success me-2"></i>
                                                <span>{{ $amenity->name }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Yacht Details -->
                        <div class="mb-4">
                            <h4>Yacht Details</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong>Capacity:</strong> {{ $yacht->max_guests ?? 0 }} guests
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Status:</strong>
                                    <span class="badge {{ $yacht->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $yacht->is_active ? 'Available' : 'Not Available' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Similar Yachts -->
                    @if ($similarYachts->count() > 0)
                        <div class="similar-yachts-section mt-5">
                            <h4 class="mb-4">Similar Yachts</h4>
                            <div class="row">
                                @foreach ($similarYachts as $similar)
                                    <div class="col-md-4 mb-4">
                                        <div class="hotel-card">
                                            <div class="hotel-img-wrap">
                                                <a href="{{ route('yachts.show', $similar->id) }}" class="hotel-img">
                                                    @if ($similar->image)
                                                        @if (str_starts_with($similar->image, '/default'))
                                                            <img src="{{ asset($similar->image) }}"
                                                                alt="{{ $similar->name }}">
                                                        @else
                                                            <img src="{{ asset('storage/' . $similar->image) }}"
                                                                alt="{{ $similar->name }}">
                                                        @endif
                                                    @else
                                                        <img src="{{ asset('frontend/assets/img/innerpages/hotel-img1.jpg') }}"
                                                            alt="{{ $similar->name }}">
                                                    @endif
                                                </a>
                                            </div>
                                            <div class="hotel-content">
                                                <h6><a
                                                        href="{{ route('yachts.show', $similar->id) }}">{{ $similar->name }}</a>
                                                </h6>
                                                <div class="price-area">
                                                    <span>{{ currency_format($similar->price) }}</span>
                                                    <small>/ day</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="package-sidebar">
                        <!-- Booking Card -->
                        <div class="booking-form-wrap mb-4 p-4 border rounded">
                            <h4 class="mb-3">Book This Yacht</h4>
                            <div class="price-display mb-3 text-center">
                                <h2 class="text-primary">{{ currency_format($yacht->price) }}</h2>
                                <p class="text-muted">per day</p>
                            </div>

                            @if ($yacht->is_active)
                                <a href="{{ route('checkout') }}?type=yacht&id={{ $yacht->id }}"
                                    class="primary-btn1 w-100">
                                    <span>Book Now</span>
                                </a>
                            @else
                                <button class="btn btn-secondary w-100" disabled>Not Available</button>
                            @endif
                        </div>

                        <!-- Quick Info -->
                        <div class="quick-info-wrap p-4 border rounded">
                            <h5 class="mb-3">Quick Info</h5>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="bi bi-people me-2"></i>
                                    <strong>Capacity:</strong> {{ $yacht->max_guests ?? 0 }} guests
                                </li>
                                @if ($yacht->categories->first())
                                    <li class="mb-2">
                                        <i class="bi bi-tag me-2"></i>
                                        <strong>Category:</strong> {{ $yacht->categories->first()->name }}
                                    </li>
                                @endif
                                <li class="mb-2">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <strong>Status:</strong> {{ $yacht->is_active ? 'Available' : 'Not Available' }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
