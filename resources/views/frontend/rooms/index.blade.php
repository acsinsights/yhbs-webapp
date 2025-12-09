@extends('frontend.layouts.app')
@section('content')
    <!-- Breadcrumb section Start-->
    <div class="breadcrumb-section three"
        style="background-image:linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url({{ asset('frontend/assets/img/innerpages/breadcrumb-bg6.jpg') }});">
        <div class="container">
            <div class="banner-content">
                <h1>Hotel Rooms</h1>
                <ul class="breadcrumb-list">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li>Hotel Rooms</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-wrapper hotel mb-100">
        <div class="container">
            <div class="filter-input-wrap">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Find Your Perfect Room</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="toggleAdvancedFilters">
                        <i class="bi bi-funnel"></i> Advanced Filters
                    </button>
                </div>

                <form method="GET" action="{{ route('rooms.index') }}" id="filterForm">
                    <!-- Basic Filters -->
                    <div class="filter-input two show">
                        <div class="single-search-box">
                            <input type="text" name="search" placeholder="Search by room name or number"
                                value="{{ request('search') }}">
                        </div>
                        <div class="single-search-box">
                            <select name="category" class="form-select">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="single-search-box">
                            <input type="number" name="min_price" placeholder="Min Price"
                                value="{{ request('min_price') }}" min="0">
                        </div>
                        <div class="single-search-box">
                            <input type="number" name="max_price" placeholder="Max Price"
                                value="{{ request('max_price') }}" min="0">
                        </div>
                        <div class="single-search-box">
                            <select name="sort_by" class="form-select">
                                <option value="latest" {{ request('sort_by') == 'latest' ? 'selected' : '' }}>Latest
                                </option>
                                <option value="price_low" {{ request('sort_by') == 'price_low' ? 'selected' : '' }}>Price:
                                    Low to High</option>
                                <option value="price_high" {{ request('sort_by') == 'price_high' ? 'selected' : '' }}>
                                    Price: High to Low</option>
                                <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name (A-Z)
                                </option>
                                <option value="capacity" {{ request('sort_by') == 'capacity' ? 'selected' : '' }}>Capacity
                                </option>
                            </select>
                        </div>
                        <button type="submit" class="primary-btn1 gap-2">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>

                    <!-- Advanced Filters (Collapsible) -->
                    <div id="advancedFilters" class="advanced-filters mt-4" style="display: none;">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label"><i class="bi bi-people"></i> Adults</label>
                                <input type="number" name="capacity" class="form-control" placeholder="Min adults"
                                    value="{{ request('capacity') }}" min="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><i class="bi bi-person"></i> Children</label>
                                <input type="number" name="children" class="form-control" placeholder="Min children"
                                    value="{{ request('children') }}" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-star"></i> Amenities</label>
                                <div class="amenities-filter-grid">
                                    @foreach ($amenities as $amenity)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="amenities[]"
                                                value="{{ $amenity->id }}" id="amenity{{ $amenity->id }}"
                                                {{ in_array($amenity->id, request('amenities', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="amenity{{ $amenity->id }}">
                                                {{ $amenity->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bi bi-check-circle"></i> Apply Filters
                                </button>
                                <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Clear All
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Active Filters Display -->
                @if (request()->hasAny(['search', 'category', 'min_price', 'max_price', 'capacity', 'children', 'amenities', 'sort_by']))
                    <div class="active-filters mt-3">
                        <span class="badge bg-secondary me-2">Active Filters:</span>
                        @if (request('search'))
                            <span class="badge bg-primary me-1">Search: {{ request('search') }}</span>
                        @endif
                        @if (request('category'))
                            <span class="badge bg-primary me-1">Category:
                                {{ $categories->find(request('category'))->name ?? 'N/A' }}</span>
                        @endif
                        @if (request('min_price'))
                            <span class="badge bg-primary me-1">Min Price:
                                {{ currency_format(request('min_price')) }}</span>
                        @endif
                        @if (request('max_price'))
                            <span class="badge bg-primary me-1">Max Price:
                                {{ currency_format(request('max_price')) }}</span>
                        @endif
                        @if (request('capacity'))
                            <span class="badge bg-primary me-1">Adults: {{ request('capacity') }}+</span>
                        @endif
                        @if (request('children'))
                            <span class="badge bg-primary me-1">Children: {{ request('children') }}+</span>
                        @endif
                        @if (request('amenities'))
                            <span class="badge bg-primary me-1">Amenities: {{ count(request('amenities')) }}
                                selected</span>
                        @endif
                        <a href="{{ route('rooms.index') }}" class="badge bg-danger text-decoration-none">
                            <i class="bi bi-x"></i> Clear All
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .advanced-filters {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
        }

        .amenities-filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            max-height: 150px;
            overflow-y: auto;
            padding: 10px;
            background: white;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }

        .form-check {
            margin-bottom: 0;
        }

        .active-filters {
            padding: 10px;
            background: #fff;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
        }

        .filter-input-wrap h6 {
            color: #333;
            font-size: 1.2rem;
        }

        .single-search-box input,
        .single-search-box select {
            border: 0px;
            border-radius: 5px;
        }

        .single-search-box input:focus,
        .single-search-box select:focus {
            border-color: #0066cc;
            box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.25);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('toggleAdvancedFilters');
            const advancedFilters = document.getElementById('advancedFilters');

            toggleBtn.addEventListener('click', function() {
                if (advancedFilters.style.display === 'none') {
                    advancedFilters.style.display = 'block';
                    toggleBtn.innerHTML = '<i class="bi bi-funnel-fill"></i> Hide Advanced Filters';
                } else {
                    advancedFilters.style.display = 'none';
                    toggleBtn.innerHTML = '<i class="bi bi-funnel"></i> Advanced Filters';
                }
            });

            // Auto-expand if advanced filters are active
            const hasAdvancedFilters =
                {{ request()->hasAny(['capacity', 'children', 'amenities']) ? 'true' : 'false' }};
            if (hasAdvancedFilters) {
                advancedFilters.style.display = 'block';
                toggleBtn.innerHTML = '<i class="bi bi-funnel-fill"></i> Hide Advanced Filters';
            }
        });
    </script>

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
                                                    <img src="{{ asset('frontend/assets/img/innerpages/hotel-img1.jpg') }}"
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
                                                    <span>{{ currency_format($room->price) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="pagination-area mt-60">
                            {{ $rooms->links() }}
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
