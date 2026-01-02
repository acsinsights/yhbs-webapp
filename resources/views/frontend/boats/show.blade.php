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
                        <div class="booking-form-wrap border rounded p-4 shadow-sm mb-4">
                            <h5 class="mb-4">Book This Boat</h5>

                            @auth
                                <form action="{{ route('checkout') }}" method="GET" id="boatBookingForm">
                                    @csrf
                                    <input type="hidden" name="type" value="boat">
                                    <input type="hidden" name="id" value="{{ $boat->id }}">
                                    <input type="hidden" name="service_type" id="selected_service_type"
                                        value="{{ $boat->service_type }}">

                                    <!-- Service Type Selection (if boat has multiple services) -->
                                    @php
                                        $availableServices = [];
                                        // Yacht or Taxi - Hourly pricing
                                        if ($boat->service_type === 'yacht' || $boat->service_type === 'taxi') {
                                            if ($boat->price_1hour || $boat->price_2hours || $boat->price_3hours) {
                                                $availableServices[] = [
                                                    'value' => $boat->service_type,
                                                    'label' => ucfirst($boat->service_type) . ' Rental',
                                                ];
                                            }
                                        }
                                        // Ferry - Ferry service pricing
                                        if ($boat->service_type === 'ferry') {
                                            if ($boat->ferry_private_weekday || $boat->ferry_public_weekday) {
                                                $availableServices[] = ['value' => 'ferry', 'label' => 'Ferry Service'];
                                            }
                                        }
                                        // Limousine - Experience pricing
                                        if ($boat->service_type === 'limousine') {
                                            if ($boat->price_15min || $boat->price_30min || $boat->price_full_boat) {
                                                $availableServices[] = [
                                                    'value' => 'limousine',
                                                    'label' => 'Limousine Experience',
                                                ];
                                            }
                                        }
                                    @endphp

                                    <!-- Date Selection - FIRST -->
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-calendar-event text-primary"></i> Select Date
                                        </label>
                                        <input type="date" name="check_in" id="booking_date" class="form-control"
                                            required min="{{ date('Y-m-d') }}"
                                            @if (!$boat->allows_same_day_booking) min="{{ date('Y-m-d', strtotime('+1 day')) }}" @endif
                                            placeholder="Select date">
                                    </div>

                                    @if (count($availableServices) > 1)
                                        <!-- Multiple service types available -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-grid text-primary"></i> Select Trip Type
                                            </label>
                                            <select name="trip_type" id="trip_type" class="form-select" required>
                                                <option value="">Choose service type...</option>
                                                @foreach ($availableServices as $service)
                                                    <option value="{{ $service['value'] }}"
                                                        {{ $boat->service_type == $service['value'] ? 'selected' : '' }}>
                                                        {{ $service['label'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    <!-- Duration/Option Selection (shown based on service type) -->
                                    @if (in_array($boat->service_type, ['yacht', 'taxi']))
                                        <div class="mb-3 service-option" id="hourly_options"
                                            style="{{ in_array($boat->service_type, ['yacht', 'taxi']) ? '' : 'display:none;' }}">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-hourglass-split text-primary"></i> Select Duration
                                            </label>
                                            <select name="duration" class="form-select">
                                                <option value="">Choose duration...</option>
                                                @if ($boat->price_1hour)
                                                    <option value="1">1 Hour - {{ currency_format($boat->price_1hour) }}
                                                    </option>
                                                @endif
                                                @if ($boat->price_2hours)
                                                    <option value="2">2 Hours -
                                                        {{ currency_format($boat->price_2hours) }}</option>
                                                @endif
                                                @if ($boat->price_3hours)
                                                    <option value="3">3 Hours -
                                                        {{ currency_format($boat->price_3hours) }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    @endif

                                    @if ($boat->service_type == 'ferry')
                                        <div class="mb-3 service-option" id="ferry_options"
                                            style="{{ $boat->service_type == 'ferry' ? '' : 'display:none;' }}">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-ticket text-primary"></i> Select Ferry Type
                                            </label>
                                            <select name="ferry_type" class="form-select">
                                                <option value="">Choose ferry type...</option>
                                                @if ($boat->ferry_private_weekday)
                                                    <option value="private_weekday" data-day-type="weekday">Private - Weekday
                                                        ({{ currency_format($boat->ferry_private_weekday) }})</option>
                                                @endif
                                                @if ($boat->ferry_private_weekend)
                                                    <option value="private_weekend" data-day-type="weekend">Private - Weekend
                                                        ({{ currency_format($boat->ferry_private_weekend) }})</option>
                                                @endif
                                                @if ($boat->ferry_public_weekday)
                                                    <option value="public_weekday" data-day-type="weekday">Public - Weekday
                                                        ({{ currency_format($boat->ferry_public_weekday) }})</option>
                                                @endif
                                                @if ($boat->ferry_public_weekend)
                                                    <option value="public_weekend" data-day-type="weekend">Public - Weekend
                                                        ({{ currency_format($boat->ferry_public_weekend) }})</option>
                                                @endif
                                            </select>
                                        </div>
                                    @endif

                                    @if ($boat->service_type == 'limousine')
                                        <div class="mb-3 service-option" id="experience_options"
                                            style="{{ $boat->service_type == 'limousine' ? '' : 'display:none;' }}">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-star text-primary"></i> Select Experience
                                            </label>
                                            <select name="experience_duration" class="form-select">
                                                <option value="">Choose experience...</option>
                                                @if ($boat->price_15min)
                                                    <option value="15">15 Minutes -
                                                        {{ currency_format($boat->price_15min) }}</option>
                                                @endif
                                                @if ($boat->price_30min)
                                                    <option value="30">30 Minutes -
                                                        {{ currency_format($boat->price_30min) }}</option>
                                                @endif
                                                @if ($boat->price_full_boat)
                                                    <option value="full">Full Experience -
                                                        {{ currency_format($boat->price_full_boat) }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    @endif

                                    <!-- Time Slot Selection (only for yacht/taxi service) -->
                                    <div class="mb-3 time-slot-container" id="time_slot_section"
                                        style="{{ in_array($boat->service_type, ['yacht', 'taxi']) ? '' : 'display:none;' }}">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-clock text-primary"></i> Select Time Slot
                                        </label>
                                        <div id="time_slots_loading" class="text-center py-3" style="display:none;">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <small class="d-block mt-2 text-muted">Loading available time slots...</small>
                                        </div>
                                        <div id="time_slots_container" class="border rounded"
                                            style="max-height: 300px; overflow-y: auto; display:none;">
                                            <!-- Time slots will be loaded here -->
                                        </div>
                                        <div id="no_slots_message" class="alert alert-warning py-2" style="display:none;">
                                            <small><i class="bi bi-exclamation-triangle me-1"></i> No time slots available for
                                                selected date and duration.</small>
                                        </div>
                                        <input type="hidden" name="start_time" id="selected_time_slot" required>
                                    </div>

                                    <!-- Passengers -->
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-people text-primary"></i> Number of Passengers
                                        </label>
                                        <input type="number" name="adults" id="passenger_count" class="form-control"
                                            min="{{ $boat->min_passengers ?? 1 }}" max="{{ $boat->max_passengers ?? 10 }}"
                                            value="{{ $boat->min_passengers ?? 1 }}" required>
                                        <small class="text-muted">
                                            Min: {{ $boat->min_passengers ?? 1 }}, Max: {{ $boat->max_passengers ?? 10 }}
                                        </small>
                                    </div>

                                    <!-- Passenger Names -->
                                    <div class="mb-3" id="passenger_names_section">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-person-badge text-primary"></i> Passenger Names
                                        </label>
                                        <div id="passenger_names_container">
                                            <input type="text" name="passenger_names[]" class="form-control mb-2"
                                                placeholder="Passenger 1 Name" required>
                                        </div>
                                        <small class="text-muted">Enter full name of each passenger</small>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100 py-3">
                                        <i class="bi bi-calendar-check me-2"></i> Proceed to Booking
                                    </button>
                                </form>
                            @else
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Please <a href="{{ route('customer.login') }}" class="alert-link">login</a> to book this
                                    boat.
                                </div>
                            @endauth
                        </div>

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

@section('scripts')
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const boatId = {{ $boat->id }};
            const csrfToken = '{{ csrf_token() }}';
            let currentServiceType = '{{ $boat->service_type }}';
            let selectedDuration = null;

            // Handle passenger count change to add/remove name fields
            const passengerCountInput = document.getElementById('passenger_count');
            const passengerNamesContainer = document.getElementById('passenger_names_container');

            function updatePassengerNameFields() {
                const count = parseInt(passengerCountInput.value) || 1;
                passengerNamesContainer.innerHTML = '';

                for (let i = 1; i <= count; i++) {
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.name = 'passenger_names[]';
                    input.className = 'form-control mb-2';
                    input.placeholder = `Passenger ${i} Name`;
                    input.required = true;
                    passengerNamesContainer.appendChild(input);
                }
            }

            passengerCountInput.addEventListener('change', updatePassengerNameFields);
            passengerCountInput.addEventListener('input', updatePassengerNameFields);

            // Initialize date picker
            flatpickr("#booking_date", {
                minDate: "{{ $boat->allows_same_day_booking ? 'today' : '+1 day' }}",
                dateFormat: "Y-m-d",
                onChange: function(selectedDates, dateStr, instance) {
                    filterFerryOptions(); // Filter ferry options based on weekday/weekend
                    loadTimeSlots();
                }
            });

            // Function to check if date is weekend (Friday-Saturday) or weekday (Sunday-Thursday)
            function isWeekend(dateStr) {
                const date = new Date(dateStr);
                const day = date.getDay(); // 0 = Sunday, 5 = Friday, 6 = Saturday
                // Weekend = Friday (5) to Saturday (6)
                return day === 5 || day === 6;
            }

            // Function to filter ferry options based on selected date
            function filterFerryOptions() {
                const dateInput = document.getElementById('booking_date');
                const ferrySelect = document.querySelector('#ferry_options select');

                if (!ferrySelect || !dateInput || !dateInput.value) {
                    return;
                }

                const selectedDate = dateInput.value;
                const weekend = isWeekend(selectedDate);
                const dayType = weekend ? 'weekend' : 'weekday';

                // Show/hide ferry options based on day type
                const ferryOptions = ferrySelect.querySelectorAll('option[data-day-type]');
                ferryOptions.forEach(option => {
                    if (option.dataset.dayType === dayType) {
                        option.style.display = 'block';
                        option.disabled = false;
                    } else {
                        option.style.display = 'none';
                        option.disabled = true;
                    }
                });

                // Reset selection if current selection is now hidden
                const currentValue = ferrySelect.value;
                if (currentValue) {
                    const currentOption = ferrySelect.querySelector(`option[value="${currentValue}"]`);
                    if (currentOption && currentOption.disabled) {
                        ferrySelect.value = '';
                    }
                }
            }

            // Handle trip type change (if multiple services available)
            const tripTypeSelect = document.getElementById('trip_type');
            if (tripTypeSelect) {
                tripTypeSelect.addEventListener('change', function() {
                    const selectedType = this.value;
                    currentServiceType = selectedType;

                    // Hide all service options
                    document.querySelectorAll('.service-option').forEach(function(el) {
                        el.style.display = 'none';
                        // Clear and disable inputs in hidden sections
                        el.querySelectorAll('select, input').forEach(function(input) {
                            input.value = '';
                            input.removeAttribute('required');
                        });
                    });

                    // Show/hide time slot section based on service type
                    const timeSlotSection = document.getElementById('time_slot_section');
                    const selectedTimeSlotInput = document.getElementById('selected_time_slot');

                    if (selectedType === 'yacht' || selectedType === 'taxi') {
                        if (timeSlotSection) {
                            timeSlotSection.style.display = 'block';
                            selectedTimeSlotInput.setAttribute('required', 'required');
                        }
                    } else {
                        if (timeSlotSection) {
                            timeSlotSection.style.display = 'none';
                            selectedTimeSlotInput.removeAttribute('required');
                            selectedTimeSlotInput.value = '';
                        }
                    }

                    // Show selected service options and make them required
                    if (selectedType === 'yacht' || selectedType === 'taxi') {
                        const hourlyOptions = document.getElementById('hourly_options');
                        if (hourlyOptions) {
                            hourlyOptions.style.display = 'block';
                            hourlyOptions.querySelector('select').setAttribute('required', 'required');
                        }
                    } else if (selectedType === 'ferry') {
                        const ferryOptions = document.getElementById('ferry_options');
                        if (ferryOptions) {
                            ferryOptions.style.display = 'block';
                            ferryOptions.querySelector('select').setAttribute('required', 'required');
                            filterFerryOptions(); // Apply filter when ferry service is selected
                        }
                    } else if (selectedType === 'limousine') {
                        const experienceOptions = document.getElementById('experience_options');
                        if (experienceOptions) {
                            experienceOptions.style.display = 'block';
                            experienceOptions.querySelector('select').setAttribute('required', 'required');
                        }
                    }

                    // Update hidden service type field
                    document.getElementById('selected_service_type').value = selectedType;

                    // Load time slots if needed
                    loadTimeSlots();
                });
            }

            // Handle duration change
            const durationSelect = document.querySelector('#hourly_options select');
            if (durationSelect) {
                durationSelect.addEventListener('change', function() {
                    selectedDuration = this.value;
                    loadTimeSlots();
                });
            }

            // Function to load time slots
            function loadTimeSlots() {
                // Only load for yacht/taxi service
                if (currentServiceType !== 'yacht' && currentServiceType !== 'taxi') {
                    return;
                }

                const date = document.getElementById('booking_date').value;
                const duration = selectedDuration || (durationSelect ? durationSelect.value : null);

                if (!date || !duration) {
                    return;
                }

                // Show loading
                document.getElementById('time_slots_loading').style.display = 'block';
                document.getElementById('time_slots_container').style.display = 'none';
                document.getElementById('no_slots_message').style.display = 'none';

                // Fetch time slots
                fetch('{{ route('boats.timeslots') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            boat_id: boatId,
                            date: date,
                            duration: parseFloat(duration)
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('time_slots_loading').style.display = 'none';

                        if (data.success && data.slots && data.slots.length > 0) {
                            renderTimeSlots(data.slots);
                            document.getElementById('time_slots_container').style.display = 'block';
                        } else {
                            document.getElementById('no_slots_message').style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading time slots:', error);
                        document.getElementById('time_slots_loading').style.display = 'none';
                        document.getElementById('no_slots_message').style.display = 'block';
                    });
            }

            // Function to render time slots
            function renderTimeSlots(slots) {
                const container = document.getElementById('time_slots_container');
                container.innerHTML = '';

                slots.forEach(slot => {
                    const slotDiv = document.createElement('div');
                    slotDiv.className = 'p-3 border-bottom ' + (slot.is_available ? 'slot-available' :
                        'slot-unavailable');
                    slotDiv.style.cursor = slot.is_available ? 'pointer' : 'not-allowed';
                    slotDiv.style.opacity = slot.is_available ? '1' : '0.5';
                    slotDiv.style.transition = 'background-color 0.2s';

                    slotDiv.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi ${slot.is_available ? 'bi-clock' : 'bi-lock'} text-${slot.is_available ? 'primary' : 'secondary'}"></i>
                                <span class="fw-medium">${slot.display}</span>
                            </div>
                            <span class="badge bg-${slot.is_available ? 'success' : 'secondary'}">
                                ${slot.is_available ? 'Available' : 'Booked'}
                            </span>
                        </div>
                    `;

                    if (slot.is_available) {
                        slotDiv.addEventListener('mouseenter', () => {
                            slotDiv.style.backgroundColor = '#f8f9fa';
                        });
                        slotDiv.addEventListener('mouseleave', () => {
                            if (document.getElementById('selected_time_slot').value !== slot
                                .value) {
                                slotDiv.style.backgroundColor = '';
                            }
                        });
                        slotDiv.addEventListener('click', () => {
                            selectTimeSlot(slot.value, slot.display, slotDiv);
                        });
                    }

                    container.appendChild(slotDiv);
                });
            }

            // Function to select a time slot
            function selectTimeSlot(value, display, element) {
                // Remove selection from all slots
                document.querySelectorAll('#time_slots_container > div').forEach(div => {
                    div.style.backgroundColor = '';
                    div.classList.remove('border-primary');
                });

                // Highlight selected slot
                element.style.backgroundColor = '#e7f3ff';
                element.classList.add('border-primary');

                // Set hidden input value
                document.getElementById('selected_time_slot').value = value;
            }

            // Set initial required state based on default service type
            const currentServiceTypeInitial = '{{ $boat->service_type }}';
            if (currentServiceTypeInitial === 'yacht' || currentServiceTypeInitial === 'taxi') {
                const durationSelectInit = document.querySelector('#hourly_options select');
                const startTimeInput = document.getElementById('selected_time_slot');
                if (durationSelectInit) durationSelectInit.setAttribute('required', 'required');
                if (startTimeInput) startTimeInput.setAttribute('required', 'required');
            } else if (currentServiceTypeInitial === 'ferry') {
                const ferrySelect = document.querySelector('#ferry_options select');
                if (ferrySelect) {
                    ferrySelect.setAttribute('required', 'required');
                    // Initial filter for ferry options
                    setTimeout(() => filterFerryOptions(), 100);
                }
            } else if (currentServiceTypeInitial === 'limousine') {
                const experienceSelect = document.querySelector('#experience_options select');
                if (experienceSelect) experienceSelect.setAttribute('required', 'required');
            }
        });
    </script>
@endsection
