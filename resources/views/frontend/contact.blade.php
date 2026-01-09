@extends('frontend.layouts.app')
@section('content')
    <!-- Start Breadcrumb section -->
    <div class="breadcrumb-section"
        style="background-image:linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url({{ asset('frontend/img/innerpages/breadcrumb-bg2.jpg') }})">
        <div class="container">
            <div class="banner-content">
                <h1>Contact Us</h1>
                <ul class="breadcrumb-list">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li>Contact Us</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- End Breadcrumb section -->

    <!-- Contact Page Start-->
    <div class="contact-page pt-100 mb-100">
        <div class="container">
            <div class="row g-xl-4 g-lg-3 g-4 mb-100">
                <div class="col-lg-4 col-md-6">
                    <div class="single-contact">
                        <div class="icon">
                            <svg width="36" height="36" viewbox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M17.9981 1.125C15.0037 1.12887 12.133 2.32012 10.0156 4.4375C7.89824 6.55489 6.70699 9.42557 6.70313 12.42C6.70312 16.2056 10.7587 22.2638 13.92 26.4037C9.99937 27.0562 7.51875 28.6087 7.51875 30.4706C7.51875 32.9794 12.0244 34.875 17.9981 34.875C23.9719 34.875 28.4831 32.9794 28.4831 30.4706C28.4831 28.6087 26.0025 27.0562 22.0762 26.4037C25.2375 22.2581 29.2931 16.2056 29.2931 12.42C29.2893 9.42557 28.098 6.55489 25.9806 4.4375C23.8632 2.32012 20.9926 1.12887 17.9981 1.125ZM17.9981 29.6663C16.0237 27.3488 7.82812 17.415 7.82812 12.42C7.82812 9.72275 8.8996 7.13597 10.8068 5.22872C12.7141 3.32148 15.3009 2.25 17.9981 2.25C20.6954 2.25 23.2822 3.32148 25.1894 5.22872C27.0966 7.13597 28.1681 9.72275 28.1681 12.42C28.1681 17.415 19.9725 27.3488 17.9981 29.6663Z">
                                </path>
                                <path
                                    d="M17.9966 18.1294C21.4853 18.1294 24.3134 15.3012 24.3134 11.8125C24.3134 8.3238 21.4853 5.49564 17.9966 5.49564C14.5078 5.49564 11.6797 8.3238 11.6797 11.8125C11.6797 15.3012 14.5078 18.1294 17.9966 18.1294Z">
                                </path>
                            </svg>
                        </div>
                        <h4>Kuwait Head Office </h4>
                        <h6><span>Contact :</span> <a href="#"> +965 1808080 </a></h6>
                        <p>Kuwait City, Kuwait</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="single-contact two">
                        <div class="icon">
                            <svg width="36" height="36" viewbox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M17.9981 1.125C15.0037 1.12887 12.133 2.32012 10.0156 4.4375C7.89824 6.55489 6.70699 9.42557 6.70313 12.42C6.70312 16.2056 10.7587 22.2638 13.92 26.4037C9.99937 27.0562 7.51875 28.6087 7.51875 30.4706C7.51875 32.9794 12.0244 34.875 17.9981 34.875C23.9719 34.875 28.4831 32.9794 28.4831 30.4706C28.4831 28.6087 26.0025 27.0562 22.0762 26.4037C25.2375 22.2581 29.2931 16.2056 29.2931 12.42C29.2893 9.42557 28.098 6.55489 25.9806 4.4375C23.8632 2.32012 20.9926 1.12887 17.9981 1.125ZM17.9981 29.6663C16.0237 27.3488 7.82812 17.415 7.82812 12.42C7.82812 9.72275 8.8996 7.13597 10.8068 5.22872C12.7141 3.32148 15.3009 2.25 17.9981 2.25C20.6954 2.25 23.2822 3.32148 25.1894 5.22872C27.0966 7.13597 28.1681 9.72275 28.1681 12.42C28.1681 17.415 19.9725 27.3488 17.9981 29.6663Z">
                                </path>
                                <path
                                    d="M17.9966 18.1294C21.4853 18.1294 24.3134 15.3012 24.3134 11.8125C24.3134 8.3238 21.4853 5.49564 17.9966 5.49564C14.5078 5.49564 11.6797 8.3238 11.6797 11.8125C11.6797 15.3012 14.5078 18.1294 17.9966 18.1294Z">
                                </path>
                            </svg>
                        </div>
                        <h4>Failaka Island Office </h4>
                        <h6><span>Contact :</span> <a href="#">+965 1808080 </a></h6>
                        <p>Heritage Village, Failaka Island, Kuwait</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="single-contact three">
                        <div class="icon">
                            <svg width="36" height="36" viewbox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M17.9981 1.125C15.0037 1.12887 12.133 2.32012 10.0156 4.4375C7.89824 6.55489 6.70699 9.42557 6.70313 12.42C6.70312 16.2056 10.7587 22.2638 13.92 26.4037C9.99937 27.0562 7.51875 28.6087 7.51875 30.4706C7.51875 32.9794 12.0244 34.875 17.9981 34.875C23.9719 34.875 28.4831 32.9794 28.4831 30.4706C28.4831 28.6087 26.0025 27.0562 22.0762 26.4037C25.2375 22.2581 29.2931 16.2056 29.2931 12.42C29.2893 9.42557 28.098 6.55489 25.9806 4.4375C23.8632 2.32012 20.9926 1.12887 17.9981 1.125ZM17.9981 29.6663C16.0237 27.3488 7.82812 17.415 7.82812 12.42C7.82812 9.72275 8.8996 7.13597 10.8068 5.22872C12.7141 3.32148 15.3009 2.25 17.9981 2.25C20.6954 2.25 23.2822 3.32148 25.1894 5.22872C27.0966 7.13597 28.1681 9.72275 28.1681 12.42C28.1681 17.415 19.9725 27.3488 17.9981 29.6663Z">
                                </path>
                                <path
                                    d="M17.9966 18.1294C21.4853 18.1294 24.3134 15.3012 24.3134 11.8125C24.3134 8.3238 21.4853 5.49564 17.9966 5.49564C14.5078 5.49564 11.6797 8.3238 11.6797 11.8125C11.6797 15.3012 14.5078 18.1294 17.9966 18.1294Z">
                                </path>
                            </svg>
                        </div>
                        <h4>Marine Operations Hub </h4>
                        <h6><span>Contact :</span> <a href="#"> +965 1808080 </a></h6>
                        <p>Docking & Maintenance Area, Kuwait</p>
                    </div>
                </div>
            </div>
            <div class="contact-form">
                <div class="row justify-content-center">
                    <div class="col-xl-8 col-lg-10">
                        <div class="contact-form-wrap">
                            <div class="section-title text-center mb-60">
                                <h2>Get in Touch!</h2>
                                <p>We’re excited to hear from you! Whether you have a question about our services, want to
                                    discuss a new project.</p>
                            </div>

                            @if (session('success'))
                                <div class="alert alert-success"
                                    style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        style="width: 24px; height: 24px; flex-shrink: 0;" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span style="font-size: 1rem; font-weight: 500;">{{ session('success') }}</span>
                                </div>
                            @endif

                            <form action="{{ route('contact.store') }}" method="POST">
                                @csrf
                                <div class="row g-4 mb-60">
                                    <div class="col-md-6">
                                        <div class="form-inner">
                                            <label>Full Name <span class="text-danger">*</span></label>
                                            <input type="text" name="full_name" placeholder="Wasington Mongla"
                                                value="{{ old('full_name') }}" required>
                                            @error('full_name')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-inner">
                                            <label>Phone Number <span class="text-danger">*</span></label>
                                            <input type="text" name="phone" placeholder="+965 1808080"
                                                value="{{ old('phone') }}" required>
                                            @error('phone')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-inner">
                                            <label>Email Address <span class="text-danger">*</span></label>
                                            <input type="email" name="email" placeholder="info@example.com"
                                                value="{{ old('email') }}" required>
                                            @error('email')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-inner">
                                            <label>Brief/Message <span class="text-danger">*</span></label>
                                            <textarea name="message" placeholder="Write somethings about inquiry" required>{{ old('message') }}</textarea>
                                            @error('message')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-inner2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value=""
                                                    id="contactCheck22" required>
                                                <label class="form-check-label" for="contactCheck22">
                                                    I will agree with yours privacy policy & terms & conditions.
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="primary-btn1">
                                        <span>
                                            Submit Now
                                            <svg width="10" height="10" viewbox="0 0 10 10"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M9.73535 1.14746C9.57033 1.97255 9.32924 3.26406 9.24902 4.66797C9.16817 6.08312 9.25559 7.5453 9.70214 8.73633C9.84754 9.12406 9.65129 9.55659 9.26367 9.70215C8.9001 9.83849 8.4969 9.67455 8.32812 9.33398L8.29785 9.26367L8.19921 8.98438C7.73487 7.5758 7.67054 5.98959 7.75097 4.58203C7.77875 4.09598 7.82525 3.62422 7.87988 3.17969L1.53027 9.53027C1.23738 9.82317 0.762615 9.82317 0.469722 9.53027C0.176829 9.23738 0.176829 8.76262 0.469722 8.46973L6.83593 2.10254C6.3319 2.16472 5.79596 2.21841 5.25 2.24902C3.8302 2.32862 2.2474 2.26906 0.958003 1.79102L0.704097 1.68945L0.635738 1.65527C0.303274 1.47099 0.157578 1.06102 0.310542 0.704102C0.463655 0.347333 0.860941 0.170391 1.22363 0.28418L1.29589 0.310547L1.48828 0.387695C2.47399 0.751207 3.79966 0.827571 5.16601 0.750977C6.60111 0.670504 7.97842 0.428235 8.86132 0.262695L9.95312 0.0585938L9.73535 1.14746Z">
                                                </path>
                                            </svg>
                                        </span>
                                        <span>
                                            Submit Now
                                            <svg width="10" height="10" viewbox="0 0 10 10"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M9.73535 1.14746C9.57033 1.97255 9.32924 3.26406 9.24902 4.66797C9.16817 6.08312 9.25559 7.5453 9.70214 8.73633C9.84754 9.12406 9.65129 9.55659 9.26367 9.70215C8.9001 9.83849 8.4969 9.67455 8.32812 9.33398L8.29785 9.26367L8.19921 8.98438C7.73487 7.5758 7.67054 5.98959 7.75097 4.58203C7.77875 4.09598 7.82525 3.62422 7.87988 3.17969L1.53027 9.53027C1.23738 9.82317 0.762615 9.82317 0.469722 9.53027C0.176829 9.23738 0.176829 8.76262 0.469722 8.46973L6.83593 2.10254C6.3319 2.16472 5.79596 2.21841 5.25 2.24902C3.8302 2.32862 2.2474 2.26906 0.958003 1.79102L0.704097 1.68945L0.635738 1.65527C0.303274 1.47099 0.157578 1.06102 0.310542 0.704102C0.463655 0.347333 0.860941 0.170391 1.22363 0.28418L1.29589 0.310547L1.48828 0.387695C2.47399 0.751207 3.79966 0.827571 5.16601 0.750977C6.60111 0.670504 7.97842 0.428235 8.86132 0.262695L9.95312 0.0585938L9.73535 1.14746Z">
                                                </path>
                                            </svg>
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <img src="assets/img/innerpages/vector/contact-page-vector1.svg" alt="" class="vector1">
        <img src="assets/img/innerpages/vector/contact-page-vector2.svg" alt="" class="vector2">
        <img src="assets/img/innerpages/vector/contact-page-vector3.svg" alt="" class="vector3">
    </div>
    <!--Contact Page End-->

    <!--Contact Map Section Start-->
    <div class="contact-map-section">
        <iframe src="https://www.google.com/maps?q=Dayia%20Tower,%20Sharq,%20Kuwait&output=embed" width="100%"
            height="100%" style="border:0;" allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>
    <!--Contact Map Section End-->
@endsection
