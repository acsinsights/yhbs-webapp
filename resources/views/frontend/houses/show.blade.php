@extends('frontend.layouts.app')
@section('title', $house->name)
@section('meta_description', $house->meta_description ?? $house->name)
@section('meta_keywords', $house->meta_keywords ?? $house->name)
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
                <h1>{{ $house->name }}</h1>
                <ul class="breadcrumb-list">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="{{ route('houses.index') }}">Houses</a></li>
                    <li>{{ $house->name }}</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- House Details Section -->
    <div class="package-details-section pt-120 pb-120">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <!-- House Image -->
                    <div class="package-img-area mb-4">
                        @php
                            $mainImageUrl = $house->image ? asset($house->image) : asset('frontend/img/home2/houses/5.jpg');
                        @endphp
                        <img id="mainHouseImage" src="{{ $mainImageUrl }}" alt="{{ $house->name }}" class="img-fluid rounded"
                            style="width: 100%; height: 450px; object-fit: cover; cursor: pointer;">
                    </div>

                    <!-- Additional Images / Thumbnails -->
                    @if ($house->library && $house->library->count() > 0)
                        <div class="row g-3 mb-4">
                            @foreach ($house->library as $imageData)
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
                                <div class="col-md-4">
                                    <img src="{{ $libraryImage }}" alt="{{ $house->name }}"
                                        class="img-fluid rounded house-thumbnail"
                                        style="width: 100%; height: 200px; object-fit: cover; cursor: pointer; transition: transform 0.3s, box-shadow 0.3s;"
                                        onclick="changeMainImage('{{ $libraryImage }}')"
                                        onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)'"
                                        onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- House Information -->
                    <div class="package-details-content">
                        <h2>{{ $house->name }}</h2>

                        @if ($house->house_number)
                            <div class="mb-3">
                                <span class="badge bg-primary">House #{{ $house->house_number }}</span>
                            </div>
                        @endif

                        <!-- House Details -->
                        <div class="mb-4">
                            <h4>House Details</h4>
                            <div class="row">
                                @if ($house->house_number)
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="bi bi-hash me-2"></i>House Number:</strong>
                                        {{ $house->house_number }}
                                    </div>
                                @endif
                                <div class="col-md-6 mb-3">
                                    <strong><i class="bi bi-door-open me-2"></i>Rooms:</strong>
                                    {{ $house->number_of_rooms ?? 0 }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="bi bi-people me-2"></i>Adults:</strong> {{ $house->adults ?? 0 }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="bi bi-person me-2"></i>Children:</strong> {{ $house->children ?? 0 }}
                                </div>
                                @if ($house->price_per_night)
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="bi bi-cash me-2"></i>Price per Night:</strong>
                                        {{ currency_format($house->price_per_night) }}
                                    </div>
                                @endif
                                @if ($house->price_per_2night)
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="bi bi-cash me-2"></i>Price for 2 Nights:</strong>
                                        {{ currency_format($house->price_per_2night) }}
                                    </div>
                                @endif
                                @if ($house->price_per_3night)
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="bi bi-cash me-2"></i>Price for 3 Nights:</strong>
                                        {{ currency_format($house->price_per_3night) }}
                                    </div>
                                @endif
                                @if ($house->additional_night_price)
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="bi bi-cash me-2"></i>Additional Night Price:</strong>
                                        {{ currency_format($house->additional_night_price) }}
                                    </div>
                                @endif
                                <div class="col-md-6 mb-3">
                                    <strong><i class="bi bi-check-circle me-2"></i>Status:</strong>
                                    @if ($house->is_under_maintenance)
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-tools me-1"></i>Under Maintenance
                                        </span>
                                        @if ($house->maintenance_note)
                                            <div class="text-muted small mt-1">{{ $house->maintenance_note }}</div>
                                        @endif
                                    @else
                                        <span class="badge {{ $house->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $house->is_active ? 'Available' : 'Not Available' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if ($house->description)
                            <div class="mb-4">
                                <p>{!! $house->description !!}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Similar Houses -->
                    @if ($similarHouses->count() > 0)
                        <div class="similar-rooms-section mt-5">
                            <h4 class="mb-4">Similar Houses</h4>
                            <div class="row">
                                @foreach ($similarHouses as $similar)
                                    <div class="col-md-4 mb-4">
                                        <div class="hotel-card">
                                            <div class="hotel-img-wrap">
                                                <a href="{{ route('houses.show', $similar->slug) }}" class="hotel-img">
                                                    @if ($similar->image)
                                                        <img src="{{ asset($similar->image) }}"
                                                            alt="{{ $similar->name }}">
                                                    @else
                                                        <img src="{{ asset('frontend/img/home2/houses/5.jpg') }}"
                                                            alt="{{ $similar->name }}">
                                                    @endif
                                                </a>
                                            </div>
                                            <div class="hotel-content">
                                                <h6><a
                                                        href="{{ route('houses.show', $similar->slug) }}">{{ $similar->name }}</a>
                                                </h6>
                                                <div class="price-area">
                                                    <span>{{ currency_format($similar->price_per_night) }}</span>
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
                        @livewire('frontend.booking-card', ['bookable' => $house, 'type' => 'house'])

                        <!-- Quick Info -->
                        <div class="quick-info-wrap p-4 border rounded">
                            <h5 class="mb-3">Quick Info</h5>
                            <ul class="list-unstyled">
                                @if ($house->house_number)
                                    <li class="mb-2">
                                        <i class="bi bi-hash me-2"></i>
                                        <strong>House:</strong> #{{ $house->house_number }}
                                    </li>
                                @endif
                                <li class="mb-2">
                                    <i class="bi bi-door-open me-2"></i>
                                    <strong>Rooms:</strong> {{ $house->number_of_rooms ?? 0 }}
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-people me-2"></i>
                                    <strong>Adults:</strong> {{ $house->adults ?? 0 }}
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-person me-2"></i>
                                    <strong>Children:</strong> {{ $house->children ?? 0 }}
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <strong>Status:</strong>
                                    @if ($house->is_under_maintenance)
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-tools"></i> Maintenance
                                        </span>
                                    @else
                                        {{ $house->is_active ? 'Available' : 'Not Available' }}
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

    <!-- Image Gallery Script -->
    <script>
        function changeMainImage(imageUrl) {
            const mainImage = document.getElementById('mainHouseImage');
            if (mainImage) {
                // Add fade effect
                mainImage.style.opacity = '0.5';

                setTimeout(() => {
                    mainImage.src = imageUrl;
                    mainImage.style.opacity = '1';
                }, 200);

                // Smooth scroll to main image
                mainImage.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    </script>
@endsection
