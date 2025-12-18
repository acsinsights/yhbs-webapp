@extends('frontend.layouts.app')
@section('title', 'Rooms')
@section('content')

    <!-- Breadcrumb section Start-->
    <div class="breadcrumb-section three"
        style="background-image:linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)),  url({{ asset('frontend/img/Rooms/luxury-bedroom-interior-with-rich-furniture-scenic-view-from-walkout-deck.jpg') }});">
        <div class="container">
            <div class="banner-content">
                <h1>Rooms</h1>
                <ul class="breadcrumb-list">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li>Rooms</li>
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
                        <h5 class="mb-0 fw-bold">Find Your Perfect Room</h5>
                    </div>

                    <form method="GET" action="{{ route('rooms.index') }}" id="filterForm">
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

                            <!-- Category -->
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-semibold text-muted small mb-2">
                                    <i class="bi bi-tag text-primary"></i> Category
                                </label>
                                <select name="category"
                                    class="form-select form-select-lg rounded-3 shadow-sm border-0 bg-light">
                                    <option value="">All Categories</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ request('category') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Adults and Children -->
                            <div class="col-md-6 col-lg-3">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold text-muted small mb-2">
                                            <i class="bi bi-people text-primary"></i> Adults
                                        </label>
                                        <input type="number" name="adults"
                                            class="form-control rounded-3 shadow-sm border-0 bg-light" placeholder="Adults"
                                            value="{{ request('adults', 1) }}" min="0" max="20">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold text-muted small mb-2">
                                            <i class="bi bi-person text-primary"></i> Children
                                        </label>
                                        <input type="number" name="children"
                                            class="form-control rounded-3 shadow-sm border-0 bg-light"
                                            placeholder="Children" value="{{ request('children', 0) }}" min="0"
                                            max="20">
                                    </div>
                                </div>
                            </div>

                            <!-- Search Button -->
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary btn-lg rounded-3 shadow-sm px-5 py-3">
                                    <i class="bi bi-search me-2"></i> Search Available Rooms
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Active Filters Display -->
                    @if (request()->hasAny(['check_in', 'check_out', 'category', 'adults', 'children']))
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <span class="badge bg-secondary py-2 px-3 rounded-pill">
                                    <i class="bi bi-funnel me-1"></i> Active Filters:
                                </span>
                                @if (request('check_in'))
                                    <span class="badge bg-primary py-2 px-3 rounded-pill">
                                        <i class="bi bi-calendar-check me-1"></i> Check-in:
                                        {{ \Carbon\Carbon::parse(request('check_in'))->format('M d, Y') }}
                                    </span>
                                @endif
                                @if (request('check_out'))
                                    <span class="badge bg-primary py-2 px-3 rounded-pill">
                                        <i class="bi bi-calendar-x me-1"></i> Check-out:
                                        {{ \Carbon\Carbon::parse(request('check_out'))->format('M d, Y') }}
                                    </span>
                                @endif
                                @if (request('category'))
                                    <span class="badge bg-primary py-2 px-3 rounded-pill">
                                        <i class="bi bi-tag me-1"></i>
                                        {{ $categories->find(request('category'))->name ?? 'N/A' }}
                                    </span>
                                @endif
                                @if (request('adults'))
                                    <span class="badge bg-primary py-2 px-3 rounded-pill">
                                        <i class="bi bi-people me-1"></i> {{ request('adults') }} Adult(s)
                                    </span>
                                @endif
                                @if (request('children'))
                                    <span class="badge bg-primary py-2 px-3 rounded-pill">
                                        <i class="bi bi-person me-1"></i> {{ request('children') }} Child(ren)
                                    </span>
                                @endif
                                <a href="{{ route('rooms.index') }}"
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

    <!-- Rooms Grid Section -->
    <div class="package-grid-section pt-50 pb-100">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="hotel-grid-top-area mb-4">
                        <span><strong>{{ $rooms->total() }}</strong> Rooms Available</span>
                    </div>

                    @if ($rooms->count() > 0)
                        <div class="row gy-4">
                            @foreach ($rooms as $room)
                                <div class="col-lg-4 col-md-6">
                                    <div class="hotel-card">
                                        <div class="hotel-img-wrap">
                                            <a href="{{ route('rooms.show', $room->slug) }}" class="hotel-img">
                                                @if ($room->image)
                                                    @if (str_starts_with($room->image, '/default'))
                                                        <img src="{{ asset($room->image) }}" alt="{{ $room->name }}">
                                                    @else
                                                        <img src="{{ asset('storage/' . $room->image) }}"
                                                            alt="{{ $room->name }}">
                                                    @endif
                                                @else
                                                    <img src="{{ asset('frontend/img/home2/hoses rooms/5.jpg') }}"
                                                        alt="{{ $room->name }}">
                                                @endif
                                            </a>
                                            @if ($room->is_active)
                                                <div class="batch batch-success">
                                                    <span>Available</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="hotel-content">
                                            <div class="rating-area mb-2">
                                                @if ($room->categories->first())
                                                    <span
                                                        class="badge bg-primary text-white">{{ $room->categories->first()->name }}</span>
                                                @endif
                                            </div>
                                            <h5>
                                                <a href="{{ route('rooms.show', $room->slug) }}">
                                                    {{ $room->name }}
                                                </a>
                                            </h5>
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
                                                    <span>Capacity: {{ $room->adults ?? 0 }} guests</span>
                                                </div>
                                            </div>

                                            @if ($room->amenities && $room->amenities->count() > 0)
                                                <ul class="hotel-feature-list mb-3">
                                                    @foreach ($room->amenities->take(4) as $amenity)
                                                        <li>
                                                            <svg width="14" height="14" viewBox="0 0 14 14"
                                                                xmlns="http://www.w3.org/2000/svg">
                                                                <rect x="0.5" y="0.5" width="13" height="13"
                                                                    rx="6.5"></rect>
                                                                <path
                                                                    d="M10.6947 5.45777L6.24644 9.90841C6.17556 9.97689 6.08572 10.0124 5.99596 10.0124C5.9494 10.0125 5.90328 10.0033 5.86027 9.98548C5.81727 9.96763 5.77822 9.94144 5.7454 9.90841L3.3038 7.46681C3.16436 7.32969 3.16436 7.10521 3.3038 6.96577L4.16652 6.10065C4.29892 5.96833 4.53524 5.96833 4.66764 6.10065L5.99596 7.42897L9.33092 4.09161C9.36377 4.05868 9.40278 4.03255 9.44573 4.01471C9.48868 3.99686 9.53473 3.98766 9.58124 3.98761C9.67572 3.98761 9.76556 4.02545 9.83172 4.09161L10.6944 4.95681C10.8341 5.09625 10.8341 5.32073 10.6947 5.45777Z">
                                                                </path>
                                                            </svg>
                                                            {{ $amenity->name }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif

                                            @if ($room->description)
                                                <p class="text-muted small mb-3">{{ Str::limit($room->description, 100) }}
                                                </p>
                                            @endif

                                            <div class="btn-and-price-area">
                                                <a href="{{ route('rooms.show', $room->slug) }}" class="primary-btn1">
                                                    <span>View Details</span>
                                                </a>

                                                <div class="price-area">
                                                    <h6>Per Night</h6>
                                                    <span>{{ currency_format($room->price_per_night) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-60">
                            {{ $rooms->links('pagination::bootstrap-5') }}
                        </div>
                    @else
                        <div class="alert alert-info">
                            <h4>No rooms found</h4>
                            <p>Try adjusting your search filters to find more results.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
