@extends('frontend.layouts.app')

@section('title', 'Our Blogs - IKARUS United Marine Services')
@section('meta_description',
    'Explore our latest blogs about marine tourism, yacht services, and coastal adventures in
    Kuwait.')

@section('content')
    <!-- Start Breadcrumb section -->
    <div class="breadcrumb-section"
        style="background-image:linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url({{ asset('frontend/img/innerpages/breadcrumb-bg2.jpg') }});">
        <div class="container">
            <div class="banner-content">
                <h1>Travel Inspiration</h1>
                <ul class="breadcrumb-list">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li>Travel Inspiration</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- End Breadcrumb section -->

    <!-- Travel Inspiration Page Start-->
    <div class="travel-inspiration-page pt-100 mb-100">
        <div class="container">
            <!-- Search Bar -->
            <div class="row justify-content-center mb-60">
                <div class="col-lg-8 col-md-10">
                    <div class="search-widget">
                        <form action="{{ route('blogs.index') }}" method="GET">
                            <div class="search-box"
                                style="display: flex; align-items: center; background: #fff; border: 2px solid #e0e0e0; border-radius: 50px; padding: 8px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: all 0.3s ease;">
                                <svg width="20" height="20" viewbox="0 0 16 16" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" style="margin-right: 12px; flex-shrink: 0;">
                                    <path
                                        d="M15.8044 14.8845L13.0544 12.197L12.9901 12.0992C12.8689 11.9797 12.7055 11.9127 12.5354 11.9127C12.3652 11.9127 12.2018 11.9797 12.0807 12.0992C9.74349 14.2433 6.14318 14.3595 3.66568 12.3714C1.18818 10.3833 0.604738 6.90545 2.30068 4.24732C3.99661 1.5892 7.44661 0.572634 10.3632 1.87232C13.2797 3.17201 14.7551 6.38638 13.8126 9.38138C13.7793 9.48804 13.7754 9.60167 13.8012 9.71037C13.827 9.81907 13.8815 9.91883 13.9591 9.9992C14.0375 10.081 14.1358 10.1411 14.2444 10.1736C14.3529 10.2061 14.468 10.21 14.5785 10.1848C14.6884 10.1606 14.79 10.108 14.8732 10.0322C14.9564 9.95643 15.0183 9.86013 15.0526 9.75295C16.1776 6.19888 14.4782 2.37388 11.0526 0.752946C7.62693 -0.867991 3.50474 0.199821 1.3513 3.26763C-0.802137 6.33545 -0.339949 10.4808 2.43911 13.0229C5.21818 15.5651 9.47974 15.7398 12.4688 13.4367L14.9038 15.8173C15.026 15.9348 15.189 16.0004 15.3585 16.0004C15.528 16.0004 15.6909 15.9348 15.8132 15.8173C15.8728 15.7589 15.9202 15.6892 15.9525 15.6123C15.9849 15.5353 16.0016 15.4527 16.0016 15.3692C16.0016 15.2857 15.9849 15.2031 15.9525 15.1261C15.9202 15.0492 15.8728 14.9795 15.8132 14.9211L15.8044 14.8845Z"
                                        fill="#666"></path>
                                </svg>
                                <input type="text" name="search" value="{{ request('search') }}"
                                    placeholder="Search for blogs, destinations, experiences..."
                                    style="flex: 1; border: none; outline: none; font-size: 16px; padding: 10px 0; background: transparent; color: #333;">
                                <button type="submit"
                                    style="background: #136497; color: #fff; border: none; padding: 12px 30px; border-radius: 50px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; margin-left: 10px;"
                                    onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 5px 15px rgba(19, 100, 151, 0.4)';"
                                    onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">Search</button>
                                <a href="{{ route('blogs.index') }}"
                                    style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: #fff; border: none; padding: 12px 30px; border-radius: 50px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; margin-left: 10px; text-decoration: none; display: inline-block;"
                                    onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 5px 15px rgba(239, 68, 68, 0.4)';"
                                    onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @if ($blogs->count() > 0)
                <div class="row gy-md-5 gy-4 mb-60">
                    @foreach ($blogs as $index => $blog)
                        <div class="col-lg-4 col-md-6 wow animate fadeInDown"
                            data-wow-delay="{{ 200 + ($index % 3) * 200 }}ms" data-wow-duration="1500ms">
                            <div class="blog-card2 two">
                                <div class="blog-img-wrap">
                                    <a href="{{ route('blogs.show', $blog->slug) }}" class="blog-img">
                                        <img src="{{ $blog->image ? (str_starts_with($blog->image, '/storage/') ? asset($blog->image) : asset('storage/' . $blog->image)) : asset('frontend/img/home2/blog-img1.jpg') }}"
                                            alt="{{ $blog->title }}">
                                    </a>
                                    @if ($blog->location)
                                        <a href="{{ route('blogs.show', $blog->slug) }}" class="location">
                                            <svg width="14" height="14" viewbox="0 0 14 14"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M6.83615 0C3.77766 0 1.28891 2.48879 1.28891 5.54892C1.28891 7.93837 4.6241 11.8351 6.05811 13.3994C6.25669 13.6175 6.54154 13.7411 6.83615 13.7411C7.13076 13.7411 7.41561 13.6175 7.6142 13.3994C9.04821 11.8351 12.3834 7.93833 12.3834 5.54892C12.3834 2.48879 9.89464 0 6.83615 0ZM7.31469 13.1243C7.18936 13.2594 7.02008 13.3342 6.83615 13.3342C6.65222 13.3342 6.48295 13.2594 6.35761 13.1243C4.95614 11.5959 1.69584 7.79515 1.69584 5.54896C1.69584 2.7134 4.00067 0.406933 6.83615 0.406933C9.67164 0.406933 11.9765 2.7134 11.9765 5.54896C11.9765 7.79515 8.71617 11.5959 7.31469 13.1243Z">
                                                </path>
                                                <path
                                                    d="M6.83618 8.54529C8.4624 8.54529 9.7807 7.22698 9.7807 5.60077C9.7807 3.97456 8.4624 2.65625 6.83618 2.65625C5.20997 2.65625 3.89166 3.97456 3.89166 5.60077C3.89166 7.22698 5.20997 8.54529 6.83618 8.54529Z">
                                                </path>
                                            </svg>
                                            {{ $blog->location }}
                                        </a>
                                    @endif
                                </div>
                                <div class="blog-content">
                                    @if ($blog->date)
                                        <a href="{{ route('blogs.show', $blog->slug) }}"
                                            class="blog-date">{{ $blog->date->format('d F, Y') }}</a>
                                    @endif
                                    <h4><a href="{{ route('blogs.show', $blog->slug) }}">{{ $blog->title }}</a></h4>
                                    <p>{{ Str::limit($blog->description ?? strip_tags($blog->content), 100) }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pagination-area wow animate fadeInUp" data-wow-delay="200ms" data-wow-duration="1500ms">
                    <div class="paginations-button">
                        @if ($blogs->onFirstPage())
                            <span style="opacity: 0.5; cursor: not-allowed;">
                                <svg width="10" height="10" viewbox="0 0 10 10" xmlns="http://www.w3.org/2000/svg">
                                    <g>
                                        <path
                                            d="M7.86133 9.28516C7.14704 7.49944 3.57561 5.71373 1.43276 4.99944C3.57561 4.28516 6.7899 3.21373 7.86133 0.713728"
                                            stroke-width="1.5" stroke-linecap="round"></path>
                                    </g>
                                </svg>
                                Prev
                            </span>
                        @else
                            <a href="{{ $blogs->previousPageUrl() }}">
                                <svg width="10" height="10" viewbox="0 0 10 10" xmlns="http://www.w3.org/2000/svg">
                                    <g>
                                        <path
                                            d="M7.86133 9.28516C7.14704 7.49944 3.57561 5.71373 1.43276 4.99944C3.57561 4.28516 6.7899 3.21373 7.86133 0.713728"
                                            stroke-width="1.5" stroke-linecap="round"></path>
                                    </g>
                                </svg>
                                Prev
                            </a>
                        @endif
                    </div>
                    <ul class="paginations">
                        @foreach ($blogs->getUrlRange(1, $blogs->lastPage()) as $page => $url)
                            <li class="page-item {{ $page == $blogs->currentPage() ? 'active' : '' }}">
                                <a href="{{ $url }}">{{ str_pad($page, 2, '0', STR_PAD_LEFT) }}</a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="paginations-button">
                        @if ($blogs->hasMorePages())
                            <a href="{{ $blogs->nextPageUrl() }}">
                                Next
                                <svg width="10" height="10" viewbox="0 0 10 10" xmlns="http://www.w3.org/2000/svg">
                                    <g>
                                        <path
                                            d="M1.42969 9.28613C2.14397 7.50042 5.7154 5.7147 7.85826 5.00042C5.7154 4.28613 2.50112 3.21471 1.42969 0.714705"
                                            stroke-width="1.5" stroke-linecap="round"></path>
                                    </g>
                                </svg>
                            </a>
                        @else
                            <span style="opacity: 0.5; cursor: not-allowed;">
                                Next
                                <svg width="10" height="10" viewbox="0 0 10 10" xmlns="http://www.w3.org/2000/svg">
                                    <g>
                                        <path
                                            d="M1.42969 9.28613C2.14397 7.50042 5.7154 5.7147 7.85826 5.00042C5.7154 4.28613 2.50112 3.21471 1.42969 0.714705"
                                            stroke-width="1.5" stroke-linecap="round"></path>
                                    </g>
                                </svg>
                            </span>
                        @endif
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
    <!--Travel Inspiration Page End-->
@endsection
