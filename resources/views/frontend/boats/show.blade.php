@extends('frontend.layouts.app')
@section('title', $boat->name)
@section('meta_description', $boat->meta_description ?? $boat->name)
@section('meta_keywords', $boat->meta_keywords ?? $boat->name)

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
                        <div class="mb-4" style="overflow-x: auto; white-space: nowrap;">
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
                                        onclick="changeMainImage('{{ $libraryImage }}', this)"
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
    <!-- Image Gallery Script -->
    <script>
        function changeMainImage(imageUrl, thumbnail) {
            const mainImage = document.getElementById('mainBoatImage');
            if (mainImage) {
                // Add fade effect
                mainImage.style.opacity = '0.5';

                setTimeout(() => {
                    mainImage.src = imageUrl;
                    mainImage.style.opacity = '1';
                }, 200);

                // Remove border from all thumbnails
                document.querySelectorAll('.boat-thumbnail').forEach(thumb => {
                    thumb.style.border = '2px solid transparent';
                });

                // Add border to clicked thumbnail
                if (thumbnail) {
                    thumbnail.style.border = '2px solid #007bff';
                }

                // Smooth scroll to main image
                mainImage.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        }
    </script>
@endsection
