@extends('frontend.layouts.app')
@section('title', $boat->name)
@section('meta_description', $boat->meta_description ?? $boat->name)
@section('meta_keywords', $boat->meta_keywords ?? $boat->name)
@section('styles')
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
                    <div class="package-img-area mb-3">
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
                        <img id="mainBoatImage" src="{{ $boatMainImage }}" alt="{{ $boat->name }}"
                            class="img-fluid rounded" style="width: 100%; height: 500px; object-fit: cover;">
                    </div>

                    <!-- Additional Images / Thumbnails -->
                    @if ($boat->library && $boat->library->count() > 0)
                        <div class="mb-4 custom-scroll-container"
                            style="overflow-x: auto; white-space: nowrap; padding-bottom: 10px;">
                            <div style="display: inline-flex; gap: 8px;">
                                @foreach ($boat->library as $imageData)
                                    @php
                                        // Skip if imageData is not an array or object
                                        if (!is_array($imageData) && !is_object($imageData)) {
                                            continue;
                                        }
                                        // Convert to array if it's an object
$imageArray = is_object($imageData) ? (array) $imageData : $imageData;
// Get the URL or path
$libraryImage = $imageArray['url'] ?? ($imageArray['path'] ?? null);
                                        if (!$libraryImage) {
                                            continue;
                                        }
                                    @endphp
                                    <img src="{{ $libraryImage }}" alt="{{ $boat->name }}" class="rounded boat-thumbnail"
                                        style="width: 100px; height: 80px; object-fit: cover; cursor: pointer; transition: transform 0.3s, box-shadow 0.3s; border: 2px solid transparent;"
                                        onclick="changeMainImageBoat('{{ $libraryImage }}', this)"
                                        onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.3)'"
                                        onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
                                @endforeach
                            </div>
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
                                <div class="col-md-6 mb-3">
                                    <strong><i class="bi bi-check-circle me-2"></i>Status:</strong>
                                    @if ($boat->is_under_maintenance)
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-tools me-1"></i>Under Maintenance
                                        </span>
                                        @if ($boat->maintenance_note)
                                            <div class="text-muted small mt-1">{{ $boat->maintenance_note }}</div>
                                        @endif
                                    @else
                                        <span class="badge {{ $boat->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $boat->is_active ? 'Available' : 'Not Available' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Pricing Details -->
                        <div class="mb-4">
                            <h4>Pricing Information</h4>
                            <div class="pricing-table">
                                @if (in_array($boat->service_type, ['yacht', 'taxi']))
                                    @if ($boat->price_1hour)
                                        <div class="pricing-row d-flex justify-content-between border-bottom py-2">
                                            <strong><i class="bi bi-clock me-2"></i>1 Hour Price:</strong>
                                            <span class="text-primary fw-bold">{{ $boat->price_1hour }} KD</span>
                                        </div>
                                    @endif
                                    @if ($boat->price_2hours)
                                        <div class="pricing-row d-flex justify-content-between border-bottom py-2">
                                            <strong><i class="bi bi-clock me-2"></i>2 Hours Price:</strong>
                                            <span class="text-primary fw-bold">{{ $boat->price_2hours }} KD</span>
                                        </div>
                                    @endif
                                    @if ($boat->price_3hours)
                                        <div class="pricing-row d-flex justify-content-between border-bottom py-2">
                                            <strong><i class="bi bi-clock me-2"></i>3 Hours Price:</strong>
                                            <span class="text-primary fw-bold">{{ $boat->price_3hours }} KD</span>
                                        </div>
                                    @endif
                                    @if ($boat->additional_hour_price)
                                        <div class="pricing-row d-flex justify-content-between border-bottom py-2">
                                            <strong><i class="bi bi-plus-circle me-2"></i>Additional Hour:</strong>
                                            <span class="text-primary fw-bold">{{ $boat->additional_hour_price }} KD</span>
                                        </div>
                                    @endif
                                @elseif ($boat->service_type === 'ferry')
                                    @if ($boat->ferry_private_weekday)
                                        <div class="pricing-row d-flex justify-content-between border-bottom py-2">
                                            <strong><i class="bi bi-calendar-week me-2"></i>Private (Weekday):</strong>
                                            <span class="text-primary fw-bold">{{ $boat->ferry_private_weekday }} KD</span>
                                        </div>
                                    @endif
                                    @if ($boat->ferry_private_weekend)
                                        <div class="pricing-row d-flex justify-content-between border-bottom py-2">
                                            <strong><i class="bi bi-calendar-event me-2"></i>Private (Weekend):</strong>
                                            <span class="text-primary fw-bold">{{ $boat->ferry_private_weekend }} KD</span>
                                        </div>
                                    @endif
                                    @if ($boat->ferry_public_weekday)
                                        <div class="pricing-row d-flex justify-content-between border-bottom py-2">
                                            <strong><i class="bi bi-calendar-week me-2"></i>Public (Weekday):</strong>
                                            <span class="text-primary fw-bold">{{ $boat->ferry_public_weekday }} KD</span>
                                        </div>
                                    @endif
                                    @if ($boat->ferry_public_weekend)
                                        <div class="pricing-row d-flex justify-content-between border-bottom py-2">
                                            <strong><i class="bi bi-calendar-event me-2"></i>Public (Weekend):</strong>
                                            <span class="text-primary fw-bold">{{ $boat->ferry_public_weekend }} KD</span>
                                        </div>
                                    @endif
                                @elseif (in_array($boat->service_type, ['water_activities', 'limousine']))
                                    @if ($boat->price_15min)
                                        <div class="pricing-row d-flex justify-content-between border-bottom py-2">
                                            <strong><i class="bi bi-clock me-2"></i>15 Minutes:</strong>
                                            <span class="text-primary fw-bold">{{ $boat->price_15min }} KD</span>
                                        </div>
                                    @endif
                                    @if ($boat->price_30min)
                                        <div class="pricing-row d-flex justify-content-between border-bottom py-2">
                                            <strong><i class="bi bi-clock me-2"></i>30 Minutes:</strong>
                                            <span class="text-primary fw-bold">{{ $boat->price_30min }} KD</span>
                                        </div>
                                    @endif
                                    @if ($boat->price_full_boat)
                                        <div class="pricing-row d-flex justify-content-between border-bottom py-2">
                                            <strong><i class="bi bi-tag me-2"></i>Full Boat:</strong>
                                            <span class="text-primary fw-bold">{{ $boat->price_full_boat }} KD</span>
                                        </div>
                                    @endif
                                @endif
                                @if ($boat->price_per_hour)
                                    <div class="pricing-row d-flex justify-content-between border-bottom py-2">
                                        <strong><i class="bi bi-clock-history me-2"></i>Price Per Hour:</strong>
                                        <span class="text-primary fw-bold">{{ $boat->price_per_hour }} KD</span>
                                    </div>
                                @endif
                            </div>
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
                                    @if ($boat->is_under_maintenance)
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-tools"></i> Maintenance
                                        </span>
                                    @else
                                        <span class="badge {{ $boat->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $boat->is_active ? 'Available' : 'Not Available' }}
                                        </span>
                                    @endif
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@endsection
