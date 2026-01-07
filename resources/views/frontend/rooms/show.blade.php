@extends('frontend.layouts.app')
@section('title', $room->name)
@section('meta_description', $room->meta_description ?? $room->name)
@section('meta_keywords', $room->meta_keywords ?? $room->name)
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
                        @php
                            if ($room->image) {
                                if (str_starts_with($room->image, '/default')) {
                                    $mainImageUrl = asset($room->image);
                                } else {
                                    $mainImageUrl = asset('storage/' . $room->image);
                                }
                            } else {
                                $mainImageUrl = asset('frontend/img/home2/houses/5.jpg');
                            }
                        @endphp
                        <img id="mainRoomImage" src="{{ $mainImageUrl }}" alt="{{ $room->name }}"
                            class="img-fluid rounded"
                            style="width: 100%; height: 450px; object-fit: cover; cursor: pointer;">
                    </div>

                    <!-- Additional Images / Thumbnails -->
                    @if ($room->library && $room->library->count() > 0)
                        <div class="row g-3 mb-4">
                            @foreach ($room->library as $imageData)
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
                                    <img src="{{ $libraryImage }}" alt="{{ $room->name }}"
                                        class="img-fluid rounded room-thumbnail"
                                        style="width: 100%; height: 200px; object-fit: cover; cursor: pointer; transition: transform 0.3s, box-shadow 0.3s;"
                                        onclick="changeMainImage('{{ $libraryImage }}')"
                                        onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)'"
                                        onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Room Information -->
                    <div class="package-details-content">
                        <h2>{{ $room->name }}</h2>

                        @if ($room->categories->first())
                            <div class="mb-3">
                                <span class="badge bg-primary">{{ $room->categories->first()->name }}</span>
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
                                    <strong><i class="bi bi-hash me-2"></i>Room Number:</strong> {{ $room->room_number }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="bi bi-building me-2"></i>Property:</strong>
                                    {{ $room->house->name ?? 'N/A' }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="bi bi-people me-2"></i>Adults:</strong> {{ $room->adults ?? 0 }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="bi bi-person me-2"></i>Children:</strong> {{ $room->children ?? 0 }}
                                </div>
                                @if ($room->price_per_night)
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="bi bi-cash me-2"></i>Price per Night:</strong>
                                        {{ currency_format($room->price_per_night) }}
                                    </div>
                                @endif
                                @if ($room->discount_price)
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="bi bi-tag-fill me-2"></i>Discount Price:</strong>
                                        <span class="text-success">{{ currency_format($room->discount_price) }}</span>
                                    </div>
                                @endif
                                <div class="col-md-6 mb-3">
                                    <strong><i class="bi bi-check-circle me-2"></i>Status:</strong>
                                    @if ($room->is_under_maintenance)
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-tools me-1"></i>Under Maintenance
                                        </span>
                                        @if ($room->maintenance_note)
                                            <div class="text-muted small mt-1">{{ $room->maintenance_note }}</div>
                                        @endif
                                    @else
                                        <span class="badge {{ $room->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $room->is_active ? 'Available' : 'Not Available' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if ($room->description)
                            <div class="mb-4">
                                <p>{!! $room->description !!}</p>
                            </div>
                        @endif

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
                                                        <img src="{{ asset('frontend/img/home2/houses/5.jpg') }}"
                                                            alt="{{ $similar->name }}">
                                                    @endif
                                                </a>
                                            </div>
                                            <div class="hotel-content">
                                                <h6><a
                                                        href="{{ route('rooms.show', $similar->id) }}">{{ $similar->name }}</a>
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
                        @livewire('frontend.booking-card', ['bookable' => $room, 'type' => 'room'])

                        <!-- Quick Info -->
                        <div class="quick-info-wrap p-4 border rounded">
                            <h5 class="mb-3">Quick Info</h5>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="bi bi-hash me-2"></i>
                                    <strong>Room:</strong> {{ $room->room_number }}
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-building me-2"></i>
                                    <strong>Property:</strong> {{ $room->house->name ?? 'N/A' }}
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-people me-2"></i>
                                    <strong>Adults:</strong> {{ $room->adults ?? 0 }}
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-person me-2"></i>
                                    <strong>Children:</strong> {{ $room->children ?? 0 }}
                                </li>
                                @if ($room->categories->first())
                                    <li class="mb-2">
                                        <i class="bi bi-tag me-2"></i>
                                        <strong>Category:</strong> {{ $room->categories->first()->name }}
                                    </li>
                                @endif
                                @if ($room->discount_price)
                                    <li class="mb-2">
                                        <i class="bi bi-tag-fill me-2"></i>
                                        <strong>Discount:</strong> <span
                                            class="text-success">{{ currency_format($room->discount_price) }}</span>
                                    </li>
                                @endif
                                <li class="mb-2">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <strong>Status:</strong>
                                    @if ($room->is_under_maintenance)
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-tools"></i> Maintenance
                                        </span>
                                    @else
                                        {{ $room->is_active ? 'Available' : 'Not Available' }}
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
            const mainImage = document.getElementById('mainRoomImage');
            if (mainImage) {
                // Add fade effect
                mainImage.style.opacity = '0.5';

                setTimeout(() => {
                    mainImage.src = imageUrl;
                    mainImage.style.opacity = '1';
                }, 200);

                // Smooth scroll to main image
                mainImage.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        }
    </script>
@endsection
