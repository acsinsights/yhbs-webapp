@extends('frontend.layouts.app')
@section('title', 'Boats & Marine Services')
@section('content')

    <!-- Breadcrumb section Start-->
    <div class="breadcrumb-section three"
        style="background-image:linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url({{ asset('frontend/img/Boats/yacht-sailing.jpg') }});">
        <div class="container">
            <div class="banner-content">
                <h1>Boats & Marine Services</h1>
                <ul class="breadcrumb-list">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li>Boats & Marine Services</li>
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
                        <h5 class="mb-0 fw-bold">Find Your Perfect Boat</h5>
                    </div>

                    <form method="GET" action="{{ route('boats.index') }}" id="filterForm">
                        <div class="row g-3 align-items-end">
                            <!-- Service Type -->
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label fw-semibold text-muted small mb-2">
                                    <i class="bi bi-diagram-3 text-primary"></i> Service Type
                                </label>
                                <select name="service_type"
                                    class="form-select boats-filter-select form-select-lg rounded-3 shadow-sm border-0 bg-light">
                                    <option value="">All Types</option>
                                    <option value="hourly" {{ request('service_type') == 'hourly' ? 'selected' : '' }}>
                                        Hourly Rental
                                    </option>
                                    <option value="ferry_service"
                                        {{ request('service_type') == 'ferry_service' ? 'selected' : '' }}>
                                        Ferry Service
                                    </option>
                                    <option value="experience"
                                        {{ request('service_type') == 'experience' ? 'selected' : '' }}>
                                        Experience
                                    </option>
                                </select>
                            </div>

                            <!-- Search -->
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label fw-semibold text-muted small mb-2">
                                    <i class="bi bi-search text-primary"></i> Search
                                </label>
                                <input type="text" name="search"
                                    class="form-control rounded-3 shadow-sm border-0 bg-light" placeholder="Search boats..."
                                    value="{{ request('search') }}">
                            </div>

                            <!-- Search Button -->
                            <div class="col-md-12 col-lg-4 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary btn-lg rounded-3 shadow-sm px-5 py-3 w-100">
                                    <i class="bi bi-search me-2"></i> Search Boats
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Active Filters Display -->
                    @if (request()->hasAny(['service_type', 'search']))
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <span class="badge bg-secondary py-2 px-3 rounded-pill">
                                    <i class="bi bi-funnel me-1"></i> Active Filters:
                                </span>
                                @if (request('service_type'))
                                    <span class="badge bg-primary py-2 px-3 rounded-pill">
                                        <i class="bi bi-diagram-3 me-1"></i>
                                        {{ ucfirst(str_replace('_', ' ', request('service_type'))) }}
                                    </span>
                                @endif
                                @if (request('search'))
                                    <span class="badge bg-primary py-2 px-3 rounded-pill">
                                        <i class="bi bi-search me-1"></i> "{{ request('search') }}"
                                    </span>
                                @endif
                                <a href="{{ route('boats.index') }}"
                                    class="badge bg-danger py-2 px-3 rounded-pill text-decoration-none">
                                    <i class="bi bi-x-circle me-1"></i> Clear All
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Boats Grid Section -->
    <div class="package-grid-section pt-50 pb-100">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="hotel-grid-top-area mb-4">
                        <span><strong>{{ $boats->total() }}</strong> Boats Available</span>
                    </div>

                    @if ($boats->count() > 0)
                        <div class="row gy-4">
                            @foreach ($boats as $boat)
                                <div class="col-lg-4 col-md-6">
                                    <div class="hotel-card h-100">
                                        <div class="hotel-img-wrap">
                                            <a href="{{ route('boats.show', $boat->slug) }}" class="hotel-img">
                                                @php
                                                    $boatImage = null;
                                                    if ($boat->image) {
                                                        if (
                                                            str_starts_with($boat->image, 'http://') ||
                                                            str_starts_with($boat->image, 'https://')
                                                        ) {
                                                            $boatImage = $boat->image;
                                                        } elseif (
                                                            str_starts_with($boat->image, '/default') ||
                                                            str_starts_with($boat->image, 'default/') ||
                                                            str_starts_with($boat->image, '/frontend') ||
                                                            str_starts_with($boat->image, 'frontend/')
                                                        ) {
                                                            $boatImage = asset($boat->image);
                                                        } elseif (str_starts_with($boat->image, 'storage/')) {
                                                            $boatImage = asset($boat->image);
                                                        } else {
                                                            $boatImage = asset('storage/' . $boat->image);
                                                        }
                                                    } else {
                                                        $boatImage = asset('frontend/img/Boats/yacht-default.jpg');
                                                    }
                                                @endphp
                                                <img src="{{ $boatImage }}" alt="{{ $boat->name }}"
                                                    style="width: 100%; height: 250px; object-fit: cover;">
                                            </a>

                                            @if ($boat->is_featured)
                                                <div class="batch batch-primary" style="top: 10px; left: 10px;">
                                                    <span>Featured</span>
                                                </div>
                                            @endif
                                            @if ($boat->is_active)
                                                <div class="batch batch-success" style="top: 10px; right: 10px;">
                                                    <span>Available</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="hotel-content">
                                            <div class="rating-area mb-2">
                                                <span class="badge bg-info text-white">
                                                    {{ ucfirst(str_replace('_', ' ', $boat->service_type)) }}
                                                </span>
                                            </div>
                                            <h5>
                                                <a href="{{ route('boats.show', $boat->slug) }}">
                                                    {{ $boat->name }}
                                                </a>
                                            </h5>

                                            @if ($boat->location)
                                                <div class="location-area mb-3">
                                                    <div class="location">
                                                        <svg width="14" height="14" viewBox="0 0 14 14"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <path
                                                                d="M6.83615 0C3.77766 0 1.28891 2.48879 1.28891 5.54892C1.28891 7.93837 4.6241 11.8351 6.05811 13.3994C6.25669 13.6175 6.54154 13.7411 6.83615 13.7411C7.13076 13.7411 7.41561 13.6175 7.6142 13.3994C9.04821 11.8351 12.3834 7.93833 12.3834 5.54892C12.3834 2.48879 9.89464 0 6.83615 0ZM7.31469 13.1243C7.18936 13.2594 7.02008 13.3342 6.83615 13.3342C6.65222 13.3342 6.48295 13.2594 6.35761 13.1243C4.95614 11.5959 1.69584 7.79515 1.69584 5.54896C1.69584 2.7134 4.00067 0.406933 6.83615 0.406933C9.67164 0.406933 11.9765 2.7134 11.9765 5.54896C11.9765 7.79515 8.71617 11.5959 7.31469 13.1243Z">
                                                            </path>
                                                        </svg>
                                                        <span>{{ $boat->location }}</span>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Capacity Info -->
                                            <div class="mb-3">
                                                <small class="text-muted">
                                                    <i class="bi bi-people me-1"></i>
                                                    {{ $boat->min_passengers ?? 1 }} - {{ $boat->max_passengers ?? 10 }}
                                                    Passengers
                                                </small>
                                            </div>

                                            <!-- Pricing Info -->
                                            <div class="price-area">
                                                @if ($boat->service_type == 'hourly')
                                                    @if ($boat->price_1hour)
                                                        <span>{{ currency_format($boat->price_1hour) }}</span>
                                                        <small>/ hour</small>
                                                    @elseif ($boat->price_per_hour)
                                                        <span>{{ currency_format($boat->price_per_hour) }}</span>
                                                        <small>/ hour</small>
                                                    @endif
                                                @elseif ($boat->service_type == 'ferry_service')
                                                    @if ($boat->ferry_private_weekday)
                                                        <span>{{ currency_format($boat->ferry_private_weekday) }}</span>
                                                        <small>/ trip</small>
                                                    @endif
                                                @elseif ($boat->service_type == 'experience')
                                                    @if ($boat->price_15min)
                                                        <span>{{ currency_format($boat->price_15min) }}</span>
                                                        <small>/ 15 min</small>
                                                    @elseif ($boat->price_30min)
                                                        <span>{{ currency_format($boat->price_30min) }}</span>
                                                        <small>/ 30 min</small>
                                                    @endif
                                                @endif
                                            </div>

                                            <div class="view-btn-area mt-3">
                                                <a href="{{ route('boats.show', $boat->slug) }}" class="primary-btn3">
                                                    View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="row mt-5">
                            <div class="col-lg-12">
                                <div class="pagination-area">
                                    {{ $boats->links() }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info text-center py-5">
                            <i class="bi bi-info-circle fs-1 mb-3 d-block"></i>
                            <h5>No boats found matching your criteria.</h5>
                            <p>Try adjusting your search filters.</p>
                            <a href="{{ route('boats.index') }}" class="btn btn-primary mt-3">
                                <i class="bi bi-arrow-clockwise me-2"></i> View All Boats
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
