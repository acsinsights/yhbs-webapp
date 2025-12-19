@extends('frontend.layouts.app')

@section('title', $blog->title)
@section('meta_description', Str::limit($blog->description ?? strip_tags($blog->content), 160))

@section('content')
    <!-- Breadcrumb section Start-->
    <div class="breadcrumb-section"
        style="background-image:linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url({{ $blog->image ? asset($blog->image) : asset('frontend/img/innerpages/breadcrumb-bg2.jpg') }});">
        <div class="container">
            <div class="banner-content">
                <h1>{{ $blog->title }}</h1>
                <ul class="breadcrumb-list">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li>{{ Str::limit($blog->title, 50) }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Breadcrumb section End-->

    <!-- Inspiration Details Page Start-->
    <div class="inspiration-details-page pt-100 mb-100">
        <div class="container">
            <div class="row g-lg-4 gy-5 justify-content-between">
                <div class="col-xl-7 col-lg-8">
                    <div class="inspiration-details">
                        @if ($blog->image)
                            <div class="inspiration-image mb-50">
                                <img src="{{ asset($blog->image) }}" alt="{{ $blog->title }}">
                            </div>
                        @endif

                        <h2>{{ $blog->title }}</h2>
                        <span class="line-break"></span>

                        @if ($blog->description)
                            <p>{{ $blog->description }}</p>
                            <span class="line-break"></span>
                            <span class="line-break"></span>
                        @endif

                        <div class="blog-content">
                            {!! $blog->content !!}
                        </div>

                        <div class="tag-and-social-area">
                            @if ($blog->tags->count() > 0)
                                <div class="tag-area">
                                    <h6>Tag:</h6>
                                    <ul class="tag-list">
                                        @foreach ($blog->tags as $tag)
                                            <li><a href="{{ route('blogs.index') }}">{{ $tag->name }}</a></li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <div class="social-area">
                                <h6>Share:</h6>
                                <ul class="social-list">
                                    <li><a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('blogs.show', $blog->slug)) }}"
                                            target="_blank"><i class="bx bxl-facebook"></i></a></li>
                                    <li><a href="https://twitter.com/intent/tweet?url={{ urlencode(route('blogs.show', $blog->slug)) }}&text={{ urlencode($blog->title) }}"
                                            target="_blank"><i class="bi bi-twitter-x"></i></a></li>
                                    <li><a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(route('blogs.show', $blog->slug)) }}"
                                            target="_blank"><i class="bx bxl-linkedin"></i></a></li>
                                    <li><a href="https://wa.me/?text={{ urlencode($blog->title . ' ' . route('blogs.show', $blog->slug)) }}"
                                            target="_blank"><i class="bx bxl-whatsapp"></i></a></li>
                                    <li><a href="javascript:void(0);"
                                            onclick="copyToClipboard('{{ route('blogs.show', $blog->slug) }}')"
                                            title="Copy URL"><i class="bx bx-link"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="blog-sidebar-area">
                        @if ($blog->tags->count() > 0)
                            <div class="single-widget mb-40">
                                <h5 class="widget-title">
                                    <svg width="22" height="22" viewbox="0 0 22 22"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M20.1667 8.24996L13.75 1.83329C13.4781 1.56103 13.1128 1.40845 12.7317 1.40329H4.58333C3.80833 1.40329 3.16667 2.04496 3.16667 2.81996V12.7316C3.17183 13.1128 3.32441 13.478 3.59667 13.75L10.0133 20.1666C10.285 20.4385 10.6498 20.5909 11.0308 20.5966C11.4119 20.6023 11.7809 20.4608 12.0608 20.1975L20.1667 12.0916C20.4299 11.8118 20.5714 11.4428 20.5657 11.0617C20.56 10.6807 20.4076 10.3159 20.1358 10.0441L20.1667 8.24996ZM11.0308 18.1941L4.58333 11.7383V2.81996H12.7317L19.1792 9.26746L11.0308 18.1941Z">
                                        </path>
                                        <path
                                            d="M7.79163 8.25C8.70372 8.25 9.44163 7.51209 9.44163 6.6C9.44163 5.68791 8.70372 4.95 7.79163 4.95C6.87954 4.95 6.14163 5.68791 6.14163 6.6C6.14163 7.51209 6.87954 8.25 7.79163 8.25Z">
                                        </path>
                                    </svg>
                                    Tags
                                </h5>
                                <div class="tag-list-sidebar" style="display: flex; flex-wrap: wrap; gap: 8px;">
                                    @foreach ($blog->tags as $tag)
                                        <a href="{{ route('blogs.index') }}"
                                            style="display: inline-block; padding: 8px 16px; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 20px; font-size: 14px; color: #333; text-decoration: none; transition: all 0.3s ease;"
                                            onmouseover="this.style.background='#136497'; this.style.color='#fff'; this.style.borderColor='#136497';"
                                            onmouseout="this.style.background='#f8f9fa'; this.style.color='#333'; this.style.borderColor='#e0e0e0';">
                                            {{ $tag->name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="single-widget mb-40">
                            <h5 class="widget-title">
                                <svg width="22" height="22" viewbox="0 0 22 22" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M20.9688 11C20.9726 12.1773 20.9019 13.3538 20.7569 14.5221C20.1691 19.1327 18.5625 20.9688 18.5625 20.9688H1.03125V18.9062C14.1316 17.7671 11.6596 7.89809 10.4264 4.54609C10.1607 4.50672 9.89326 4.48091 9.625 4.46875C9.625 4.46875 4.46875 4.125 4.8125 8.25C4.8125 8.25 2.0625 7.5625 2.75 4.8125C3.4375 2.0625 6.1875 2.75 6.1875 2.75C6.1875 2.75 6.875 1.03125 9.28125 1.03125C11.6875 1.03125 12.7188 3.09375 12.7188 3.09375C14.4375 1.03125 20.9688 3.78125 20.9688 11Z">
                                    </path>
                                    <path
                                        d="M10.9981 20.9687C10.9981 20.9687 15.4669 18.5625 15.4669 14.4375C15.4669 10.3125 13.0606 11.3437 13.0606 11.3437C13.0606 11.3437 13.4044 9.625 11.6856 8.9375C9.96688 8.25 8.93563 9.625 8.93563 9.625C8.93563 9.625 5.84188 8.25 5.15437 11C4.46688 13.75 7.56063 13.75 7.56063 13.75C7.56063 13.75 7.25692 11.7483 9.27938 11.3437C10.9981 11 15.8106 13.75 10.9981 20.9687Z">
                                    </path>
                                    <path
                                        d="M20.9697 11C20.9736 12.1773 20.9028 13.3538 20.7579 14.5222C18.5635 8.9375 15.9196 5.35777 10.4273 4.54609C10.1617 4.50672 9.89423 4.48091 9.62596 4.46875C9.62596 4.46875 4.46971 4.125 4.81346 8.25C4.81346 8.25 2.06346 7.5625 2.75096 4.8125C3.43846 2.0625 6.18846 2.75 6.18846 2.75C6.18846 2.75 6.87596 1.03125 9.28221 1.03125C11.6885 1.03125 12.7197 3.09375 12.7197 3.09375C14.4385 1.03125 20.9697 3.78125 20.9697 11Z">
                                    </path>
                                </svg>
                                Recent Posts
                            </h5>
                            @php
                                $recentBlogs = App\Models\Blog::published()
                                    ->where('id', '!=', $blog->id)
                                    ->with('tags')
                                    ->latest('date')
                                    ->take(4)
                                    ->get();
                            @endphp
                            @foreach ($recentBlogs as $recentBlog)
                                <div class="recent-post-widget mb-30">
                                    <div class="recent-post-img">
                                        <a href="{{ route('blogs.show', $recentBlog->slug) }}">
                                            <img src="{{ $recentBlog->image ? asset($recentBlog->image) : asset('frontend/img/innerpages/blog-img1.jpg') }}"
                                                alt="{{ $recentBlog->title }}">
                                        </a>
                                    </div>
                                    <div class="recent-post-content">
                                        @if ($recentBlog->date)
                                            <a
                                                href="{{ route('blogs.show', $recentBlog->slug) }}">{{ $recentBlog->date->format('d F, Y') }}</a>
                                        @endif
                                        <h6><a
                                                href="{{ route('blogs.show', $recentBlog->slug) }}">{{ Str::limit($recentBlog->title, 50) }}</a>
                                        </h6>
                                        @if ($recentBlog->tags->count() > 0)
                                            <div class="recent-post-tags"
                                                style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 5px;">
                                                @foreach ($recentBlog->tags as $tag)
                                                    <span
                                                        style="display: inline-block; padding: 2px 8px; background: #f0f0f0; border-radius: 3px; font-size: 11px; color: #666;">{{ $tag->name }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Inspiration Details Page End-->

    <!-- Related Inspiration Section Start-->
    @if ($relatedBlogs->count() > 0)
        <div class="related-inspiration-section pt-100 mb-100">
            <div class="container">
                <div class="row justify-content-center mb-50 wow animate fadeInDown" data-wow-delay="200ms"
                    data-wow-duration="1500ms">
                    <div class="col-xl-6 col-lg-8">
                        <div class="section-title text-center">
                            <h2>You May Also Like</h2>
                            <p>More articles you might be interested in reading.</p>
                        </div>
                    </div>
                </div>
                <div class="row gy-md-5 gy-4">
                    @foreach ($relatedBlogs as $relatedBlog)
                        <div class="col-lg-4 col-md-6">
                            <div class="blog-card2 two">
                                <div class="blog-img-wrap">
                                    <a href="{{ route('blogs.show', $relatedBlog->slug) }}" class="blog-img">
                                        <img src="{{ $relatedBlog->image ? asset($relatedBlog->image) : asset('frontend/img/innerpages/blog-img1.jpg') }}"
                                            alt="{{ $relatedBlog->title }}">
                                    </a>
                                    @if ($relatedBlog->location)
                                        <a href="{{ route('blogs.show', $relatedBlog->slug) }}" class="location">
                                            <svg width="14" height="14" viewbox="0 0 14 14"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M6.83615 0C3.77766 0 1.28891 2.48879 1.28891 5.54892C1.28891 7.93837 4.6241 11.8351 6.05811 13.3994C6.25669 13.6175 6.54154 13.7411 6.83615 13.7411C7.13076 13.7411 7.41561 13.6175 7.6142 13.3994C9.04821 11.8351 12.3834 7.93833 12.3834 5.54892C12.3834 2.48879 9.89464 0 6.83615 0ZM7.31469 13.1243C7.18936 13.2594 7.02008 13.3342 6.83615 13.3342C6.65222 13.3342 6.48295 13.2594 6.35761 13.1243C4.95614 11.5959 1.69584 7.79515 1.69584 5.54896C1.69584 2.7134 4.00067 0.406933 6.83615 0.406933C9.67164 0.406933 11.9765 2.7134 11.9765 5.54896C11.9765 7.79515 8.71617 11.5959 7.31469 13.1243Z">
                                                </path>
                                                <path
                                                    d="M6.83618 8.54529C8.4624 8.54529 9.7807 7.22698 9.7807 5.60077C9.7807 3.97456 8.4624 2.65625 6.83618 2.65625C5.20997 2.65625 3.89166 3.97456 3.89166 5.60077C3.89166 7.22698 5.20997 8.54529 6.83618 8.54529Z">
                                                </path>
                                            </svg>
                                            {{ $relatedBlog->location }}
                                        </a>
                                    @endif
                                </div>
                                <div class="blog-content">
                                    @if ($relatedBlog->date)
                                        <a href="{{ route('blogs.show', $relatedBlog->slug) }}"
                                            class="blog-date">{{ $relatedBlog->date->format('d F, Y') }}</a>
                                    @endif
                                    <h4><a
                                            href="{{ route('blogs.show', $relatedBlog->slug) }}">{{ $relatedBlog->title }}</a>
                                    </h4>
                                    <p>{{ Str::limit($relatedBlog->description ?? strip_tags($relatedBlog->content), 100) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    <!-- Related Inspiration Section End-->

    <script>
        function copyToClipboard(url) {
            navigator.clipboard.writeText(url).then(function() {
                alert('URL copied to clipboard!');
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }
    </script>
@endsection
