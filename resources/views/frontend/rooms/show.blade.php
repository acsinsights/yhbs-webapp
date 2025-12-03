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
                                <form action="{{ route('checkout') }}" method="GET" id="roomBookingForm">
                                    <input type="hidden" name="type" value="room">
                                    <input type="hidden" name="id" value="{{ $room->id }}">

                                    <!-- Check-in Date -->
                                    <div class="mb-3">
                                        <label class="form-label"><i class="bi bi-calendar-check me-2"></i>Check-in</label>
                                        <input type="date" name="check_in" class="form-control" required
                                            min="{{ date('Y-m-d') }}" id="checkInDate">
                                    </div>

                                    <!-- Check-out Date -->
                                    <div class="mb-3">
                                        <label class="form-label"><i class="bi bi-calendar-x me-2"></i>Check-out</label>
                                        <input type="date" name="check_out" class="form-control" required
                                            min="{{ date('Y-m-d', strtotime('+1 day')) }}" id="checkOutDate">
                                    </div>

                                    <!-- Adults -->
                                    <div class="mb-3">
                                        <label class="form-label"><i class="bi bi-people me-2"></i>Adults</label>
                                        <input type="number" name="adults" class="form-control" min="1"
                                            max="{{ $room->adults ?? 1 }}" value="1" required id="adultsInput">
                                        <small class="text-muted">Max: {{ $room->adults ?? 1 }} adults</small>
                                    </div>

                                    <!-- Children -->
                                    <div class="mb-3">
                                        <label class="form-label"><i class="bi bi-person me-2"></i>Children</label>
                                        <input type="number" name="children" class="form-control" min="0"
                                            max="{{ $room->children ?? 0 }}" value="0" id="childrenInput">
                                        <small class="text-muted">Max: {{ $room->children ?? 0 }} children</small>
                                    </div>

                                    <!-- Validation Alert -->
                                    <div class="alert alert-warning d-none" id="capacityAlert">
                                        <small><i class="bi bi-exclamation-triangle me-2"></i>Guest capacity
                                            exceeded!</small>
                                    </div>

                                    <button type="submit" class="primary-btn1 w-100">
                                        <span>Book Now</span>
                                    </button>
                                </form>

                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const checkInDate = document.getElementById('checkInDate');
                                        const checkOutDate = document.getElementById('checkOutDate');
                                        const adultsInput = document.getElementById('adultsInput');
                                        const childrenInput = document.getElementById('childrenInput');
                                        const capacityAlert = document.getElementById('capacityAlert');
                                        const form = document.getElementById('roomBookingForm');

                                        const maxAdults = {{ $room->adults ?? 1 }};
                                        const maxChildren = {{ $room->children ?? 0 }};

                                        // Update check-out min date when check-in changes
                                        checkInDate.addEventListener('change', function() {
                                            const minCheckOut = new Date(this.value);
                                            minCheckOut.setDate(minCheckOut.getDate() + 1);
                                            checkOutDate.min = minCheckOut.toISOString().split('T')[0];
                                            if (checkOutDate.value && checkOutDate.value <= this.value) {
                                                checkOutDate.value = '';
                                            }
                                        });

                                        // Validate guest capacity
                                        function validateCapacity() {
                                            const adults = parseInt(adultsInput.value) || 0;
                                            const children = parseInt(childrenInput.value) || 0;

                                            if (adults > maxAdults || children > maxChildren) {
                                                capacityAlert.classList.remove('d-none');
                                                return false;
                                            } else {
                                                capacityAlert.classList.add('d-none');
                                                return true;
                                            }
                                        }

                                        adultsInput.addEventListener('input', validateCapacity);
                                        childrenInput.addEventListener('input', validateCapacity);

                                        form.addEventListener('submit', function(e) {
                                            if (!validateCapacity()) {
                                                e.preventDefault();
                                                alert('Please ensure guest numbers are within the allowed capacity.');
                                            }
                                        });
                                    });
                                </script>
                            @else
                                <button class="btn btn-secondary w-100" disabled>Not Available</button>
                            @endif
                        </div>

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
