@extends('frontend.layouts.app')
@section('title', $boat->name)
@section('meta_description', $boat->meta_description ?? $boat->name)
@section('meta_keywords', $boat->meta_keywords ?? $boat->name)
@section('styles')
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .pricing-card {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }

        .pricing-card:hover {
            border-color: #007bff;
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.1);
        }

        .pricing-card.active {
            border-color: #007bff;
            background-color: #f8f9ff;
        }

        .service-badge {
            font-size: 0.9rem;
            padding: 8px 16px;
        }
    </style>
@endsection

@section('content')
    <!-- Breadcrumb section Start-->
    <div class="breadcrumb-section three"
        style="background-image:linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url({{ asset('frontend/assets/img/innerpages/breadcrumb-bg6.jpg') }});">
        <div class="container">
            <div class="banner-content">
                <h1>{{ $boat->name }}</h1>
                <ul class="breadcrumb-list">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="{{ route('boats.index') }}">Boats</a></li>
                    <li>{{ $boat->name }}</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Boat Details Section -->
    <div class="package-details-section pt-120 pb-120">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Boat Image Gallery -->
                    <div class="package-img-area mb-4">
                        @php
                            $boatMainImage = null;
                            if ($boat->image) {
                                if (
                                    str_starts_with($boat->image, 'http://') ||
                                    str_starts_with($boat->image, 'https://')
                                ) {
                                    $boatMainImage = $boat->image;
                                } elseif (
                                    str_starts_with($boat->image, '/default') ||
                                    str_starts_with($boat->image, 'default/') ||
                                    str_starts_with($boat->image, '/frontend') ||
                                    str_starts_with($boat->image, 'frontend/')
                                ) {
                                    $boatMainImage = asset($boat->image);
                                } elseif (str_starts_with($boat->image, 'storage/')) {
                                    $boatMainImage = asset($boat->image);
                                } else {
                                    $boatMainImage = asset('storage/' . $boat->image);
                                }
                            } else {
                                $boatMainImage = asset('frontend/img/Boats/yacht-default.jpg');
                            }
                        @endphp
                        <img src="{{ $boatMainImage }}" alt="{{ $boat->name }}" class="img-fluid rounded"
                            style="width: 100%; height: 450px; object-fit: cover;">
                    </div>

                    <!-- Additional Images -->
                    @if ($boat->images && is_array($boat->images) && count($boat->images) > 0)
                        <div class="row g-3 mb-4">
                            @foreach ($boat->images as $image)
                                <div class="col-md-4">
                                    @php
                                        $additionalImage = str_starts_with($image, 'http')
                                            ? $image
                                            : asset('storage/' . $image);
                                    @endphp
                                    <img src="{{ $additionalImage }}" alt="{{ $boat->name }}" class="img-fluid rounded"
                                        style="width: 100%; height: 200px; object-fit: cover;">
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Boat Information -->
                    <div class="package-details-content">
                        <h2>{{ $boat->name }}</h2>

                        <div class="mb-3">
                            <span class="badge bg-info service-badge">
                                {{ ucfirst(str_replace('_', ' ', $boat->service_type)) }}
                            </span>
                            @if ($boat->is_featured)
                                <span class="badge bg-warning service-badge">Featured</span>
                            @endif
                        </div>

                        @if ($boat->description)
                            <div class="mb-4">
                                <h4>About This Boat</h4>
                                <p>{!! nl2br(e($boat->description)) !!}</p>
                            </div>
                        @endif

                        <!-- Boat Amenities -->
                        @if ($boat->amenities && $boat->amenities->count() > 0)
                            <div class="mb-4">
                                <h4>Amenities & Features</h4>
                                <div class="row">
                                    @foreach ($boat->amenities as $amenity)
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

                        @if ($boat->features)
                            <div class="mb-4">
                                <h4>Special Features</h4>
                                <p>{!! nl2br(e($boat->features)) !!}</p>
                            </div>
                        @endif

                        <!-- Boat Specifications -->
                        <div class="mb-4">
                            <h4>Boat Specifications</h4>
                            <div class="row">
                                @if ($boat->location)
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="bi bi-geo-alt me-2"></i>Location:</strong> {{ $boat->location }}
                                    </div>
                                @endif
                                <div class="col-md-6 mb-3">
                                    <strong><i class="bi bi-people me-2"></i>Capacity:</strong>
                                    {{ $boat->min_passengers ?? 1 }} - {{ $boat->max_passengers ?? 10 }} Passengers
                                </div>
                                @if ($boat->buffer_time)
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="bi bi-clock me-2"></i>Buffer Time:</strong>
                                        {{ $boat->buffer_time }} minutes
                                    </div>
                                @endif
                                <div class="col-md-6 mb-3">
                                    <strong><i class="bi bi-check-circle me-2"></i>Status:</strong>
                                    <span class="badge {{ $boat->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $boat->is_active ? 'Available' : 'Not Available' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing Details -->
                        <div class="mb-4">
                            <h4>Pricing Details</h4>

                            @if ($boat->service_type == 'hourly')
                                <div class="row">
                                    @if ($boat->price_1hour)
                                        <div class="col-md-6 mb-3">
                                            <div class="pricing-card">
                                                <h6 class="mb-2">1 Hour</h6>
                                                <h4 class="text-primary mb-0">{{ currency_format($boat->price_1hour) }}
                                                </h4>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($boat->price_2hours)
                                        <div class="col-md-6 mb-3">
                                            <div class="pricing-card">
                                                <h6 class="mb-2">2 Hours</h6>
                                                <h4 class="text-primary mb-0">{{ currency_format($boat->price_2hours) }}
                                                </h4>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($boat->price_3hours)
                                        <div class="col-md-6 mb-3">
                                            <div class="pricing-card">
                                                <h6 class="mb-2">3 Hours</h6>
                                                <h4 class="text-primary mb-0">{{ currency_format($boat->price_3hours) }}
                                                </h4>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($boat->additional_hour_price)
                                        <div class="col-md-6 mb-3">
                                            <div class="pricing-card">
                                                <h6 class="mb-2">Additional Hour</h6>
                                                <h4 class="text-primary mb-0">
                                                    {{ currency_format($boat->additional_hour_price) }}</h4>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @elseif ($boat->service_type == 'ferry_service')
                                <div class="row">
                                    @if ($boat->ferry_private_weekday)
                                        <div class="col-md-6 mb-3">
                                            <div class="pricing-card">
                                                <h6 class="mb-2">Private - Weekday</h6>
                                                <h4 class="text-primary mb-0">
                                                    {{ currency_format($boat->ferry_private_weekday) }}</h4>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($boat->ferry_private_weekend)
                                        <div class="col-md-6 mb-3">
                                            <div class="pricing-card">
                                                <h6 class="mb-2">Private - Weekend</h6>
                                                <h4 class="text-primary mb-0">
                                                    {{ currency_format($boat->ferry_private_weekend) }}</h4>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($boat->ferry_public_weekday)
                                        <div class="col-md-6 mb-3">
                                            <div class="pricing-card">
                                                <h6 class="mb-2">Public - Weekday</h6>
                                                <h4 class="text-primary mb-0">
                                                    {{ currency_format($boat->ferry_public_weekday) }}</h4>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($boat->ferry_public_weekend)
                                        <div class="col-md-6 mb-3">
                                            <div class="pricing-card">
                                                <h6 class="mb-2">Public - Weekend</h6>
                                                <h4 class="text-primary mb-0">
                                                    {{ currency_format($boat->ferry_public_weekend) }}</h4>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @elseif ($boat->service_type == 'experience')
                                <div class="row">
                                    @if ($boat->price_15min)
                                        <div class="col-md-6 mb-3">
                                            <div class="pricing-card">
                                                <h6 class="mb-2">15 Minutes</h6>
                                                <h4 class="text-primary mb-0">{{ currency_format($boat->price_15min) }}
                                                </h4>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($boat->price_30min)
                                        <div class="col-md-6 mb-3">
                                            <div class="pricing-card">
                                                <h6 class="mb-2">30 Minutes</h6>
                                                <h4 class="text-primary mb-0">{{ currency_format($boat->price_30min) }}
                                                </h4>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($boat->price_full_boat)
                                        <div class="col-md-6 mb-3">
                                            <div class="pricing-card">
                                                <h6 class="mb-2">Full Boat Experience</h6>
                                                <h4 class="text-primary mb-0">{{ currency_format($boat->price_full_boat) }}
                                                </h4>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Booking Policy -->
                        @if ($boat->booking_policy)
                            <div class="mb-4">
                                <h4>Booking Policy</h4>
                                <p>{!! nl2br(e($boat->booking_policy)) !!}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="package-sidebar sticky-top" style="top: 100px;">
                        <!-- Booking Card -->
                        @livewire('frontend.boat-booking-card', ['boat' => $boat])

                        <!-- Quick Info -->
                        <div class="quick-info-wrap p-4 border rounded shadow-sm">
                            <h5 class="mb-3">Quick Info</h5>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="bi bi-diagram-3 me-2"></i>
                                    <strong>Service:</strong> {{ ucfirst(str_replace('_', ' ', $boat->service_type)) }}
                                </li>
                                @if ($boat->location)
                                    <li class="mb-2">
                                        <i class="bi bi-geo-alt me-2"></i>
                                        <strong>Location:</strong> {{ $boat->location }}
                                    </li>
                                @endif
                                <li class="mb-2">
                                    <i class="bi bi-people me-2"></i>
                                    <strong>Capacity:</strong> {{ $boat->min_passengers ?? 1 }} -
                                    {{ $boat->max_passengers ?? 10 }}
                                </li>
                                @if ($boat->buffer_time)
                                    <li class="mb-2">
                                        <i class="bi bi-clock me-2"></i>
                                        <strong>Buffer:</strong> {{ $boat->buffer_time }} min
                                    </li>
                                @endif
                                <li class="mb-2">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <strong>Status:</strong>
                                    <span class="badge {{ $boat->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $boat->is_active ? 'Available' : 'Not Available' }}
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
