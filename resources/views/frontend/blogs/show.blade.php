@extends('frontend.layouts.app')

@section('title', $blog->title)
@section('meta_description', Str::limit($blog->description ?? strip_tags($blog->content), 160))

@section('content')
    <!-- Inspiration Details Page Start-->
    <div class="inspiration-details-page pt-100 mb-100">
        <div class="container">
            <div class="row g-lg-4 gy-5 justify-content-between">
                <div class="col-xl-7 col-lg-8">
                    <div class="inspiration-details">
                        @if ($blog->image)
                            <div class="inspiration-image mb-50">
                                <img src="{{ $blog->image ? (str_starts_with($blog->image, '/storage/') ? asset($blog->image) : asset('storage/' . $blog->image)) : asset('frontend/img/home2/blog-img1.jpg') }}"
                                    alt="{{ $blog->title }}">
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
                                            <img src="{{ $recentBlog->image ? (str_starts_with($recentBlog->image, '/storage/') ? asset($recentBlog->image) : asset('storage/' . $recentBlog->image)) : asset('frontend/img/innerpages/blog-img1.jpg') }}"
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
