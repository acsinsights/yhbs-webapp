@extends('frontend.layouts.app')

@section('title', 'Our Blogs - IKARUS United Marine Services')
@section('meta_description',
    'Explore our latest blogs about marine tourism, yacht services, and coastal adventures in
    Kuwait.')

@section('content')
    <!-- Start Breadcrumb section -->
    <div class="breadcrumb-section"
        style="
        background-image: linear-gradient(
            rgba(0, 0, 0, 0.3),
            rgba(0, 0, 0, 0.3)
          ),
          url({{ asset('frontend/img/innerpages/breadcrumb-bg2.jpg') }});
      ">
        <div class="container">
            <div class="banner-content">
                <h1>Our Blogs</h1>
                <ul class="breadcrumb-list">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li>Blogs</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- End Breadcrumb section -->

    <!-- Blog Section Start-->
    <div class="blog-section pt-100 mb-100">
        <div class="container">
            @if ($blogs->count() > 0)
                <div class="row g-4">
                    @foreach ($blogs as $blog)
                        <div class="col-xl-4 col-lg-6 col-md-6">
                            <div class="blog-card h-100">
                                <div class="blog-img">
                                    <a href="{{ route('blogs.show', $blog->slug) }}">
                                        <img src="{{ $blog->image ?? asset('frontend/img/innerpages/blog-placeholder.jpg') }}"
                                            alt="{{ $blog->title }}" class="img-fluid"
                                            style="width: 100%; height: 250px; object-fit: cover;">
                                    </a>
                                    @if ($blog->date)
                                        <div class="blog-date">
                                            <i class="bi bi-calendar3"></i>
                                            {{ $blog->date->format('M d, Y') }}
                                        </div>
                                    @endif
                                </div>
                                <div class="blog-content">
                                    <h3>
                                        <a href="{{ route('blogs.show', $blog->slug) }}">{{ $blog->title }}</a>
                                    </h3>
                                    <p>{{ Str::limit($blog->description ?? strip_tags($blog->content), 120) }}</p>
                                    <a href="{{ route('blogs.show', $blog->slug) }}" class="read-more-btn">
                                        Read More <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="pagination-wrapper d-flex justify-content-center">
                            {{ $blogs->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-12">
                        <div class="text-center py-5">
                            <h3>No blogs published yet</h3>
                            <p>Check back soon for updates!</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <!-- Blog Section End -->
@endsection
