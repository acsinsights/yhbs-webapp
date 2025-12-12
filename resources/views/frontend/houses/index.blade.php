@extends('frontend.layouts.app')
@section('title', 'Houses')
@section('content')
    <style>
        .card {
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .form-control,
        .form-select {
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: #fff !important;
            border-color: #0066cc !important;
            box-shadow: 0 0 0 0.25rem rgba(0, 102, 204, 0.15) !important;
            transform: translateY(-1px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
            border: none;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0052a3 0%, #004080 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 102, 204, 0.3);
        }

        .badge {
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        .rounded-4 {
            border-radius: 1rem !important;
        }

        .bg-light {
            background-color: #f8f9fa !important;
        }
    </style>

    <!-- Breadcrumb section Start-->
    <div class="breadcrumb-section three"
        style="background-image:linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url({{ asset('frontend/assets/img/innerpages/breadcrumb-bg6.jpg') }});">
        <div class="container">
            <div class="banner-content">
                <h1>Houses</h1>
                <ul class="breadcrumb-list">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li>Houses</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-wrapper hotel mb-5">
        <div class="container">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <i class="bi bi-search text-primary fs-4 me-3"></i>
                        <h5 class="mb-0 fw-bold">Find Your Perfect House</h5>
                    </div>

                    <form method="GET" action="{{ route('houses.index') }}" id="filterForm">
                        <div class="row g-3 align-items-end">
                            <!-- Check-in Date -->
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-semibold text-muted small mb-2">
                                    <i class="bi bi-calendar-check text-primary"></i> Check-in
                                </label>
                                <input type="date" name="check_in"
                                    class="form-control rounded-3 shadow-sm border-0 bg-light"
                                    value="{{ request('check_in') }}" required min="{{ date('Y-m-d') }}">
                            </div>

                            <!-- Check-out Date -->
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-semibold text-muted small mb-2">
                                    <i class="bi bi-calendar-x text-primary"></i> Check-out
                                </label>
                                <input type="date" name="check_out"
                                    class="form-control rounded-3 shadow-sm border-0 bg-light"
                                    value="{{ request('check_out') }}" required
                                    min="{{ request('check_in', date('Y-m-d')) }}">
                            </div>

                            <!-- Capacity -->
                            <div class="col-md-6 col-lg-2">
                                <label class="form-label fw-semibold text-muted small mb-2">
                                    <i class="bi bi-people text-primary"></i> Guests
                                </label>
                                <input type="number" name="capacity"
                                    class="form-control rounded-3 shadow-sm border-0 bg-light" min="1"
                                    value="{{ request('capacity', 1) }}" placeholder="No. of guests">
                            </div>

                            <!-- Search Button -->
                            <div class="col-md-6 col-lg-2">
                                <button type="submit" class="btn btn-primary w-100 rounded-3 shadow-sm py-2">
                                    <i class="bi bi-search me-2"></i>Search
                                </button>
                            </div>

                            <!-- Clear Filters -->
                            <div class="col-md-6 col-lg-2">
                                <a href="{{ route('houses.index') }}"
                                    class="btn btn-outline-secondary w-100 rounded-3 py-2">
                                    <i class="bi bi-x-circle me-2"></i>Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- House List Section -->
    <div class="hotel-section pt-5 pb-120">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="hotel-grid-top-area mb-4">
                        <span><strong>{{ $houses->total() }}</strong> Houses Available</span>
                    </div>

                    @if ($houses->count() > 0)
                        <div class="row gy-4">
                            @foreach ($houses as $house)
                                <div class="col-lg-4 col-md-6">
                                    <div class="hotel-card">
                                        <div class="hotel-img-wrap">
                                            <a href="{{ route('houses.show', $house->slug) }}" class="hotel-img">
                                                @if ($house->image)
                                                    @if (str_starts_with($house->image, '/default'))
                                                        <img src="{{ asset($house->image) }}" alt="{{ $house->name }}">
                                                    @else
                                                        <img src="{{ asset('storage/' . $house->image) }}"
                                                            alt="{{ $house->name }}">
                                                    @endif
                                                @else
                                                    <img src="{{ asset('frontend/assets/img/innerpages/hotel-img1.jpg') }}"
                                                        alt="{{ $house->name }}">
                                                @endif
                                            </a>
                                            @if ($house->is_active)
                                                <div class="batch batch-success">
                                                    <span>Available</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="hotel-content">
                                            <h5>
                                                <a href="{{ route('houses.show', $house->slug) }}">
                                                    {{ $house->name }}
                                                </a>
                                            </h5>
                                            @if ($house->house_number)
                                                <p class="text-muted small mb-2">House #{{ $house->house_number }}</p>
                                            @endif
                                            <div class="location-area mb-3">
                                                <div class="location">
                                                    <svg width="14" height="14" viewBox="0 0 14 14"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M6.83615 0C3.77766 0 1.28891 2.48879 1.28891 5.54892C1.28891 7.93837 4.6241 11.8351 6.05811 13.3994C6.25669 13.6175 6.54154 13.7411 6.83615 13.7411C7.13076 13.7411 7.41561 13.6175 7.6142 13.3994C9.04821 11.8351 12.3834 7.93833 12.3834 5.54892C12.3834 2.48879 9.89464 0 6.83615 0ZM7.31469 13.1243C7.18936 13.2594 7.02008 13.3342 6.83615 13.3342C6.65222 13.3342 6.48295 13.2594 6.35761 13.1243C4.95614 11.5959 1.69584 7.79515 1.69584 5.54896C1.69584 2.7134 4.00067 0.406933 6.83615 0.406933C9.67164 0.406933 11.9765 2.7134 11.9765 5.54896C11.9765 7.79515 8.71617 11.5959 7.31469 13.1243Z">
                                                        </path>
                                                        <path
                                                            d="M6.83618 8.54554C8.4624 8.54554 9.7807 7.22723 9.7807 5.60102C9.7807 3.9748 8.4624 2.65649 6.83618 2.65649C5.20997 2.65649 3.89166 3.9748 3.89166 5.60102C3.89166 7.22723 5.20997 8.54554 6.83618 8.54554Z">
                                                        </path>
                                                    </svg>
                                                    <span>Capacity: {{ $house->adults ?? 0 }} guests |
                                                        {{ $house->rooms->count() }} rooms</span>
                                                </div>
                                            </div>

                                            @if ($house->description)
                                                <p class="text-muted small mb-3">{{ Str::limit($house->description, 100) }}
                                                </p>
                                            @endif

                                            <div class="btn-and-price-area">
                                                <a href="{{ route('houses.show', $house->slug) }}" class="primary-btn1">
                                                    <span>View Details</span>
                                                </a>

                                                <div class="price-area">
                                                    <h6>Per Night</h6>
                                                    <span>{{ currency_format($house->price_per_night) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-60">
                            {{ $houses->links('pagination::bootstrap-5') }}
                        </div>
                    @else
                        <div class="alert alert-info">
                            <h4>No houses found</h4>
                            <p>Try adjusting your search filters to find more results.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
