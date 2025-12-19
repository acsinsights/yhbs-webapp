@extends('frontend.layouts.app')

@section('title', $blog->title)
@section('meta_description', Str::limit($blog->description ?? strip_tags($blog->content), 160))

@section('content')
    <!-- Start Breadcrumb section -->
    <div class="breadcrumb-section"
        style="
        background-image: linear-gradient(
            rgba(0, 0, 0, 0.3),
            rgba(0, 0, 0, 0.3)
          ),
          url({{ $blog->image ?? asset('frontend/img/innerpages/breadcrumb-bg2.jpg') }});
      ">
        <div class="container">
            <div class="banner-content">
                <h1>{{ $blog->title }}</h1>
                <ul class="breadcrumb-list">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="{{ route('blogs.index') }}">Blogs</a></li>
                    <li>{{ Str::limit($blog->title, 30) }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- End Breadcrumb section -->

    <!-- Blog Detail Section Start-->
    <div class="blog-detail-section pt-100 mb-100">
        <div class="container">
            <div class="row">
                <div class="col-xl-8 col-lg-8">
                    <!-- Main Blog Content -->
                    <article class="blog-detail-card">
                        @if ($blog->image)
                            <div class="blog-detail-img mb-4">
                                <img src="{{ $blog->image }}" alt="{{ $blog->title }}" class="img-fluid w-100"
                                    style="max-height: 500px; object-fit: cover; border-radius: 10px;">
                            </div>
                        @endif

                        <div class="blog-meta mb-3">
                            @if ($blog->date)
                                <span class="blog-date">
                                    <i class="bi bi-calendar3"></i> {{ $blog->date->format('F d, Y') }}
                                </span>
                            @endif
                        </div>

                        <h1 class="blog-title mb-4">{{ $blog->title }}</h1>

                        @if ($blog->description)
                            <div class="blog-excerpt mb-4">
                                <p class="lead">{{ $blog->description }}</p>
                            </div>
                        @endif

                        @if ($blog->tags->count() > 0)
                            <div class="blog-tags mb-4">
                                @foreach ($blog->tags as $tag)
                                    <span class="badge bg-primary me-2">#{{ $tag->name }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="blog-content">
                            {!! $blog->content !!}
                        </div>

                        <!-- Social Share -->
                        <div class="blog-share mt-5 pt-4 border-top">
                            <h5 class="mb-3">Share this post:</h5>
                            <div class="social-share-buttons">
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('blogs.show', $blog->slug)) }}"
                                    target="_blank" class="btn btn-primary btn-sm me-2">
                                    <i class="bi bi-facebook"></i> Facebook
                                </a>
                                <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('blogs.show', $blog->slug)) }}&text={{ urlencode($blog->title) }}"
                                    target="_blank" class="btn btn-info btn-sm me-2">
                                    <i class="bi bi-twitter"></i> Twitter
                                </a>
                                <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(route('blogs.show', $blog->slug)) }}&title={{ urlencode($blog->title) }}"
                                    target="_blank" class="btn btn-primary btn-sm">
                                    <i class="bi bi-linkedin"></i> LinkedIn
                                </a>
                            </div>
                        </div>
                    </article>

                    <!-- Navigation to Previous/Next Blog -->
                    <div class="blog-navigation mt-5 pt-4 border-top">
                        <div class="row">
                            <div class="col-6">
                                @php
                                    $previousBlog = App\Models\Blog::published()
                                        ->where('id', '<', $blog->id)
                                        ->orderBy('id', 'desc')
                                        ->first();
                                @endphp
                                @if ($previousBlog)
                                    <a href="{{ route('blogs.show', $previousBlog->slug) }}" class="nav-link-prev">
                                        <i class="bi bi-arrow-left"></i> Previous Post
                                        <span class="d-block small">{{ Str::limit($previousBlog->title, 30) }}</span>
                                    </a>
                                @endif
                            </div>
                            <div class="col-6 text-end">
                                @php
                                    $nextBlog = App\Models\Blog::published()
                                        ->where('id', '>', $blog->id)
                                        ->orderBy('id', 'asc')
                                        ->first();
                                @endphp
                                @if ($nextBlog)
                                    <a href="{{ route('blogs.show', $nextBlog->slug) }}" class="nav-link-next">
                                        Next Post <i class="bi bi-arrow-right"></i>
                                        <span class="d-block small">{{ Str::limit($nextBlog->title, 30) }}</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-xl-4 col-lg-4">
                    <div class="blog-sidebar">
                        <!-- Related Posts -->
                        @if ($relatedBlogs->count() > 0)
                            <div class="sidebar-widget mb-4">
                                <h4 class="widget-title">Related Posts</h4>
                                <div class="related-posts">
                                    @foreach ($relatedBlogs as $relatedBlog)
                                        <div class="related-post-item mb-3">
                                            <div class="row g-2">
                                                @if ($relatedBlog->image)
                                                    <div class="col-4">
                                                        <img src="{{ $relatedBlog->image }}"
                                                            alt="{{ $relatedBlog->title }}" class="img-fluid"
                                                            style="height: 70px; width: 100%; object-fit: cover; border-radius: 5px;">
                                                    </div>
                                                @endif
                                                <div class="{{ $relatedBlog->image ? 'col-8' : 'col-12' }}">
                                                    <h6>
                                                        <a href="{{ route('blogs.show', $relatedBlog->slug) }}">
                                                            {{ Str::limit($relatedBlog->title, 50) }}
                                                        </a>
                                                    </h6>
                                                    @if ($relatedBlog->date)
                                                        <small class="text-muted">
                                                            <i class="bi bi-calendar3"></i>
                                                            {{ $relatedBlog->date->format('M d, Y') }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Latest Posts -->
                        <div class="sidebar-widget">
                            <h4 class="widget-title">Latest Posts</h4>
                            <div class="latest-posts">
                                @php
                                    $latestBlogs = App\Models\Blog::published()
                                        ->where('id', '!=', $blog->id)
                                        ->latest('date')
                                        ->take(5)
                                        ->get();
                                @endphp
                                @foreach ($latestBlogs as $latestBlog)
                                    <div class="latest-post-item mb-3 pb-3 border-bottom">
                                        <h6>
                                            <a href="{{ route('blogs.show', $latestBlog->slug) }}">
                                                {{ $latestBlog->title }}
                                            </a>
                                        </h6>
                                        @if ($latestBlog->date)
                                            <small class="text-muted">
                                                <i class="bi bi-calendar3"></i> {{ $latestBlog->date->format('M d, Y') }}
                                            </small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Blog Detail Section End -->
@endsection

@push('styles')
    <style>
        .blog-detail-card {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }

        .blog-meta {
            color: #666;
        }

        .blog-content {
            font-size: 16px;
            line-height: 1.8;
            color: #333;
        }

        .blog-content p {
            margin-bottom: 20px;
        }

        .sidebar-widget {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .widget-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .related-post-item h6 a,
        .latest-post-item h6 a {
            color: #333;
            text-decoration: none;
            transition: color 0.3s;
        }

        .related-post-item h6 a:hover,
        .latest-post-item h6 a:hover {
            color: #007bff;
        }

        .nav-link-prev,
        .nav-link-next {
            color: #333;
            text-decoration: none;
            transition: color 0.3s;
        }

        .nav-link-prev:hover,
        .nav-link-next:hover {
            color: #007bff;
        }
    </style>
@endpush
