@extends('frontend.layouts.app')
@section('title', $yacht->name)
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
                                @if ($yacht->sku)
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="bi bi-hash me-2"></i>SKU:</strong> {{ $yacht->sku }}
                                    </div>
                                @endif
                                <div class="col-md-6 mb-3">
                                    <strong><i class="bi bi-people me-2"></i>Max Guests:</strong>
                                    {{ $yacht->max_guests ?? 0 }}
                                </div>
                                @if ($yacht->max_crew)
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="bi bi-person-badge me-2"></i>Max Crew:</strong>
                                        {{ $yacht->max_crew }}
                                    </div>
                                @endif
                                @if ($yacht->max_capacity)
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="bi bi-person-fill me-2"></i>Total Capacity:</strong>
                                        {{ $yacht->max_capacity }}
                                    </div>
                                @endif
                                @if ($yacht->length)
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="bi bi-arrows-expand me-2"></i>Length:</strong>
                                        {{ $yacht->length }} ft
                                    </div>
                                @endif
                                @if ($yacht->width)
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="bi bi-arrows me-2"></i>Width:</strong> {{ $yacht->width }} ft
                                    </div>
                                @endif
                                @if ($yacht->max_fuel_capacity)
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="bi bi-fuel-pump me-2"></i>Fuel Capacity:</strong>
                                        {{ $yacht->max_fuel_capacity }} L
                                    </div>
                                @endif
                                @if ($yacht->price_per_hour)
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="bi bi-tag-fill me-2"></i>Price Per Hour:</strong>
                                        <span
                                            class="text-decoration-line-through text-muted">{{ currency_format($yacht->price) }}</span>
                                        <span
                                            class="text-success fw-bold">{{ currency_format($yacht->price_per_hour) }}</span>
                                    </div>
                                @endif
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
                        @livewire('frontend.booking-card', ['bookable' => $yacht, 'type' => 'yacht'])

                        <!-- Quick Info -->
                        <div class="quick-info-wrap p-4 border rounded">
                            <h5 class="mb-3">Quick Info</h5>
                            <ul class="list-unstyled">
                                @if ($yacht->sku)
                                    <li class="mb-2">
                                        <i class="bi bi-hash me-2"></i>
                                        <strong>SKU:</strong> {{ $yacht->sku }}
                                    </li>
                                @endif
                                <li class="mb-2">
                                    <i class="bi bi-people me-2"></i>
                                    <strong>Guests:</strong> {{ $yacht->max_guests ?? 0 }}
                                </li>
                                @if ($yacht->max_crew)
                                    <li class="mb-2">
                                        <i class="bi bi-person-badge me-2"></i>
                                        <strong>Crew:</strong> {{ $yacht->max_crew }}
                                    </li>
                                @endif
                                @if ($yacht->length)
                                    <li class="mb-2">
                                        <i class="bi bi-arrows-expand me-2"></i>
                                        <strong>Length:</strong> {{ $yacht->length }} ft
                                    </li>
                                @endif
                                @if ($yacht->width)
                                    <li class="mb-2">
                                        <i class="bi bi-arrows me-2"></i>
                                        <strong>Width:</strong> {{ $yacht->width }} ft
                                    </li>
                                @endif
                                @if ($yacht->categories->first())
                                    <li class="mb-2">
                                        <i class="bi bi-tag me-2"></i>
                                        <strong>Category:</strong> {{ $yacht->categories->first()->name }}
                                    </li>
                                @endif
                                @if ($yacht->discount_price)
                                    <li class="mb-2">
                                        <i class="bi bi-tag-fill me-2"></i>
                                        <strong>Discount:</strong> <span
                                            class="text-success">{{ currency_format($yacht->discount_price) }}</span>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script src="{{ asset('frontend/js/yacht-booking.js') }}"></script>
@endsection
