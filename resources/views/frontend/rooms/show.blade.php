@extends('frontend.layouts.app')
@section('content')
    <!-- Breadcrumb section Start-->
    <div class="breadcrumb-section three"
        style="background-image:linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url({{ asset('frontend/assets/img/innerpages/breadcrumb-bg6.jpg') }});">
        <div class="container">
            <div class="banner-content">
                <h1>{{ $room->name }}</h1>
                <ul class="breadcrumb-list">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="{{ route('rooms.index') }}">Rooms</a></li>
                    <li>{{ $room->name }}</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Room Details Section -->
    <div class="package-details-section pt-120 pb-120">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Room Image -->
                    <div class="package-img-area mb-4">
                        @if ($room->image)
                            @if (str_starts_with($room->image, '/default'))
                                <img src="{{ asset($room->image) }}" alt="{{ $room->name }}" class="img-fluid rounded">
                            @else
                                <img src="{{ asset('storage/' . $room->image) }}" alt="{{ $room->name }}"
                                    class="img-fluid rounded">
                            @endif
                        @else
                            <img src="{{ asset('frontend/assets/img/innerpages/hotel-img1.jpg') }}"
                                alt="{{ $room->name }}" class="img-fluid rounded">
                        @endif
                    </div>

                    <!-- Room Information -->
                    <div class="package-details-content">
                        <h2>{{ $room->name }}</h2>

                        @if ($room->categories->first())
                            <div class="mb-3">
                                <span class="badge bg-primary">{{ $room->categories->first()->name }}</span>
                            </div>
                        @endif

                        @if ($room->description)
                            <div class="mb-4">
                                <h4>About This Room</h4>
                                <p>{{ $room->description }}</p>
                            </div>
                        @endif

                        <!-- Room Features -->
                        @if ($room->amenities && $room->amenities->count() > 0)
                            <div class="mb-4">
                                <h4>Amenities</h4>
                                <div class="row">
                                    @foreach ($room->amenities as $amenity)
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

                        <!-- Room Details -->
                        <div class="mb-4">
                            <h4>Room Details</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong>Capacity:</strong> {{ $room->adults ?? 0 }} guests
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Status:</strong>
                                    <span class="badge {{ $room->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $room->is_active ? 'Available' : 'Not Available' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Similar Rooms -->
                    @if ($similarRooms->count() > 0)
                        <div class="similar-rooms-section mt-5">
                            <h4 class="mb-4">Similar Rooms</h4>
                            <div class="row">
                                @foreach ($similarRooms as $similar)
                                    <div class="col-md-4 mb-4">
                                        <div class="hotel-card">
                                            <div class="hotel-img-wrap">
                                                <a href="{{ route('rooms.show', $similar->id) }}" class="hotel-img">
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
                                                        href="{{ route('rooms.show', $similar->id) }}">{{ $similar->name }}</a>
                                                </h6>
                                                <div class="price-area">
                                                    <span>{{ currency_format($similar->price) }}</span>
                                                    <small>/ night</small>
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
                            <h4 class="mb-3">Book This Room</h4>
                            <div class="price-display mb-3 text-center">
                                <h2 class="text-primary">{{ currency_format($room->price) }}</h2>
                                <p class="text-muted">per night</p>
                            </div>

                            @if ($room->is_active)
                                <a href="{{ route('checkout') }}?type=room&id={{ $room->id }}"
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
                                    <strong>Capacity:</strong> {{ $room->adults ?? 0 }} guests
                                </li>
                                @if ($room->categories->first())
                                    <li class="mb-2">
                                        <i class="bi bi-tag me-2"></i>
                                        <strong>Category:</strong> {{ $room->categories->first()->name }}
                                    </li>
                                @endif
                                <li class="mb-2">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <strong>Status:</strong> {{ $room->is_active ? 'Available' : 'Not Available' }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
