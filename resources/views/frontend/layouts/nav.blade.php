    <!-- header Section Start-->
    <header class="header-area style-2">
        <div class="container d-flex flex-nowrap align-items-center justify-content-between">
            <div class="logo-and-menu-area" style="gap: 170px">
                <a href="{{ route('home') }}" class="header-logo">
                    <img src="{{ asset('frontend/img/header-logo2.svg') }}" alt="">
                </a>
                <div class="main-menu">
                    <div class="mobile-logo-area d-xl-none d-flex align-items-center justify-content-between">
                        <a href="{{ route('home') }}" class="mobile-logo-wrap">
                            <img src="{{ asset('frontend/img/header-logo2.svg') }}" alt="">
                        </a>
                        <div class="menu-close-btn">
                            <i class="bi bi-x"></i>
                        </div>
                    </div>
                    <ul class="menu-list">
                        <li class="{{ request()->routeIs('home') ? 'active' : '' }}">
                            <a href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="{{ request()->is('about*') ? 'active' : '' }}">
                            <a href="{{ route('about') }}">About Us</a>
                        </li>
                        <li
                            class="menu-item-has-children {{ request()->is('rooms*') || request()->is('houses*') || request()->is('boats*') ? 'active' : '' }}">
                            <a href="#" class="drop-down">
                                Our Services
                                <i class="bi bi-caret-down-fill"></i>
                            </a>
                            <i class="bi bi-plus dropdown-icon"></i>
                            <ul class="sub-menu">
                                <li class="{{ request()->is('houses*') ? 'active' : '' }}">
                                    <a href="{{ route('houses.index') }}">Houses</a>
                                </li>
                                <li class="{{ request()->is('rooms*') ? 'active' : '' }}">
                                    <a href="{{ route('rooms.index') }}">Rooms</a>
                                </li>
                                <li class="{{ request()->is('boats*') ? 'active' : '' }}">
                                    <a href="{{ route('boats.index') }}">Boats & Marine Services</a>
                                </li>
                            </ul>
                        </li>
                        <li class="{{ request()->is('contact*') ? 'active' : '' }}">
                            <a href="{{ url('/contact') }}">Contact Us</a>
                        </li>
                    </ul>
                    {{-- <div class="contact-area d-xl-none d-flex">
                        <div class="icon">
                            <svg width="16" height="16" viewbox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                                <g>
                                    <path
                                        d="M15.5646 11.7424L13.3317 9.50954C12.5343 8.7121 11.1786 9.03111 10.8596 10.0678C10.6204 10.7855 9.82296 11.1842 9.10526 11.0247C7.51037 10.626 5.35726 8.55261 4.95854 6.87797C4.71931 6.16024 5.19778 5.36279 5.91548 5.12359C6.95216 4.80461 7.27113 3.44895 6.47369 2.65151L4.24084 0.418659C3.60288 -0.139553 2.64595 -0.139553 2.08774 0.418659L0.572591 1.93381C-0.942555 3.5287 0.73208 7.75516 4.48007 11.5032C8.22807 15.2512 12.4545 17.0056 14.0494 15.4106L15.5646 13.8955C16.1228 13.2575 16.1228 12.3006 15.5646 11.7424Z">
                                    </path>
                                </g>
                            </svg>
                        </div>
                        <div class="content">
                            <span>Need Help?</span>
                            <a href="tel:91345533865">+965 1808080</a>
                        </div>
                    </div> --}}
                    <a href="#" class="primary-btn1 black-bg d-xl-none d-flex">
                        <span>
                            <svg width="15" height="15" viewbox="0 0 15 15" xmlns="http://www.w3.org/2000/svg">
                                <g>
                                    <path
                                        d="M7.50105 7.78913C9.64392 7.78913 11.3956 6.03744 11.3956 3.89456C11.3956 1.75169 9.64392 0 7.50105 0C5.35818 0 3.60652 1.75169 3.60652 3.89456C3.60652 6.03744 5.35821 7.78913 7.50105 7.78913ZM14.1847 10.9014C14.0827 10.6463 13.9467 10.4082 13.7936 10.1871C13.0113 9.0306 11.8038 8.2653 10.4433 8.07822C10.2732 8.06123 10.0861 8.09522 9.95007 8.19727C9.23578 8.72448 8.38546 8.99658 7.50108 8.99658C6.61671 8.99658 5.76638 8.72448 5.05209 8.19727C4.91603 8.09522 4.72895 8.04421 4.5589 8.07822C3.19835 8.2653 1.97387 9.0306 1.20857 10.1871C1.05551 10.4082 0.919443 10.6633 0.817424 10.9014C0.766415 11.0034 0.783407 11.1225 0.834416 11.2245C0.970484 11.4626 1.14054 11.7007 1.2936 11.9048C1.53168 12.2279 1.78679 12.517 2.07592 12.7891C2.31401 13.0272 2.58611 13.2483 2.85824 13.4694C4.20177 14.4728 5.81742 15 7.48409 15C9.15076 15 10.7664 14.4728 12.1099 13.4694C12.382 13.2653 12.6541 13.0272 12.8923 12.7891C13.1644 12.517 13.4365 12.2279 13.6746 11.9048C13.8446 11.6837 13.9977 11.4626 14.1338 11.2245C14.2188 11.1225 14.2358 11.0034 14.1847 10.9014Z">
                                    </path>
                                </g>
                            </svg>
                            Login
                        </span>
                        <span>
                            <svg width="15" height="15" viewbox="0 0 15 15" xmlns="http://www.w3.org/2000/svg">
                                <g>
                                    <path
                                        d="M7.50105 7.78913C9.64392 7.78913 11.3956 6.03744 11.3956 3.89456C11.3956 1.75169 9.64392 0 7.50105 0C5.35818 0 3.60652 1.75169 3.60652 3.89456C3.60652 6.03744 5.35821 7.78913 7.50105 7.78913ZM14.1847 10.9014C14.0827 10.6463 13.9467 10.4082 13.7936 10.1871C13.0113 9.0306 11.8038 8.2653 10.4433 8.07822C10.2732 8.06123 10.0861 8.09522 9.95007 8.19727C9.23578 8.72448 8.38546 8.99658 7.50108 8.99658C6.61671 8.99658 5.76638 8.72448 5.05209 8.19727C4.91603 8.09522 4.72895 8.04421 4.5589 8.07822C3.19835 8.2653 1.97387 9.0306 1.20857 10.1871C1.05551 10.4082 0.919443 10.6633 0.817424 10.9014C0.766415 11.0034 0.783407 11.1225 0.834416 11.2245C0.970484 11.4626 1.14054 11.7007 1.2936 11.9048C1.53168 12.2279 1.78679 12.517 2.07592 12.7891C2.31401 13.0272 2.58611 13.2483 2.85824 13.4694C4.20177 14.4728 5.81742 15 7.48409 15C9.15076 15 10.7664 14.4728 12.1099 13.4694C12.382 13.2653 12.6541 13.0272 12.8923 12.7891C13.1644 12.517 13.4365 12.2279 13.6746 11.9048C13.8446 11.6837 13.9977 11.4626 14.1338 11.2245C14.2188 11.1225 14.2358 11.0034 14.1847 10.9014Z">
                                    </path>
                                </g>
                            </svg>
                            Login
                        </span>
                    </a>
                </div>
            </div>
            <div class="nav-right">
                {{-- <div class="contact-and-search-area">
                    <div class="contact-area d-xl-flex d-none">
                        <div class="icon">
                            <svg width="16" height="16" viewbox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                                <g>
                                    <path
                                        d="M15.5646 11.7424L13.3317 9.50954C12.5343 8.7121 11.1786 9.03111 10.8596 10.0678C10.6204 10.7855 9.82296 11.1842 9.10526 11.0247C7.51037 10.626 5.35726 8.55261 4.95854 6.87797C4.71931 6.16024 5.19778 5.36279 5.91548 5.12359C6.95216 4.80461 7.27113 3.44895 6.47369 2.65151L4.24084 0.418659C3.60288 -0.139553 2.64595 -0.139553 2.08774 0.418659L0.572591 1.93381C-0.942555 3.5287 0.73208 7.75516 4.48007 11.5032C8.22807 15.2512 12.4545 17.0056 14.0494 15.4106L15.5646 13.8955C16.1228 13.2575 16.1228 12.3006 15.5646 11.7424Z">
                                    </path>
                                </g>
                            </svg>
                        </div>
                        <div class="content">
                            <span>Need Help?</span>
                            <a href="tel:91345533865">+91 345 533 865</a>
                        </div>
                    </div>
                    <div class="search-bar">
                        <div class="search-btn">
                            <svg width="16" height="16" viewbox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                                <g>
                                    <path
                                        d="M15.7417 14.6098L13.486 12.3621C14.7088 10.8514 15.3054 8.9291 15.1526 6.99153C14.9998 5.05396 14.1093 3.24888 12.6648 1.94851C11.2203 0.648146 9.33193 -0.0483622 7.38901 0.00261294C5.44609 0.0535881 3.59681 0.84816 2.22248 2.22248C0.84816 3.59681 0.0535881 5.44609 0.00261294 7.38901C-0.0483622 9.33193 0.648146 11.2203 1.94851 12.6648C3.24888 14.1093 5.05396 14.9998 6.99153 15.1526C8.9291 15.3054 10.8514 14.7088 12.3621 13.486L14.6098 15.7417C14.6839 15.8164 14.7721 15.8757 14.8692 15.9161C14.9664 15.9566 15.0705 15.9774 15.1758 15.9774C15.281 15.9774 15.3852 15.9566 15.4823 15.9161C15.5794 15.8757 15.6676 15.8164 15.7417 15.7417C15.8164 15.6676 15.8757 15.5794 15.9161 15.4823C15.9566 15.3852 15.9774 15.281 15.9774 15.1758C15.9774 15.0705 15.9566 14.9664 15.9161 14.8692C15.8757 14.7721 15.8164 14.6839 15.7417 14.6098ZM1.62572 7.60368C1.62572 6.42135 1.97632 5.26557 2.63319 4.2825C3.29005 3.29943 4.22368 2.53322 5.31601 2.08076C6.40834 1.62831 7.61031 1.50992 8.76992 1.74058C9.92953 1.97124 10.9947 2.54059 11.8307 3.37662C12.6668 4.21266 13.2361 5.27783 13.4668 6.43744C13.6974 7.59705 13.579 8.79902 13.1266 9.89134C12.6741 10.9837 11.9079 11.9173 10.9249 12.5742C9.94178 13.231 8.78601 13.5816 7.60368 13.5816C6.01822 13.5816 4.49771 12.9518 3.37662 11.8307C2.25554 10.7096 1.62572 9.18913 1.62572 7.60368Z">
                                    </path>
                                </g>
                            </svg>
                        </div>
                        <div class="search-input">
                            <div class="search-close"></div>
                            <form>
                                <div class="search-group">
                                    <div class="form-inner2">
                                        <input type="text" placeholder="Find Your Perfect Tour Package">
                                        <button type="submit"><i class="bi bi-search"></i></button>
                                    </div>
                                </div>
                                <div class="quick-search">
                                    <ul>
                                        <li>Quick Search :</li>
                                        <li><a href="travel-package-01.html">Thailand Tour,</a></li>
                                        <li><a href="travel-package-01.html">Philippines Tour,</a></li>
                                        <li><a href="travel-package-01.html">Bali Tour,</a></li>
                                        <li><a href="travel-package-01.html">Hawaii, USA Tour,</a></li>
                                        <li><a href="travel-package-01.html">Switzerland Tour,</a></li>
                                        <li><a href="travel-package-01.html">Maldives Tour,</a></li>
                                        <li><a href="travel-package-01.html">Paris Tour,</a></li>
                                    </ul>
                                </div>
                            </form>
                        </div>
                    </div>
                </div> --}}

                @auth
                    <!-- Logged In User Menu -->
                    <div class="user-menu-dropdown d-xl-flex d-none"
                        style="position: relative; display: flex; align-items: center; gap: 20px;">

                        <!-- Notification Bell -->
                        @role('customer')
                            <livewire:frontend.notification-bell />
                        @endrole

                        <a href="#" class="primary-btn1 black-bg user-dropdown-toggle" style="position: relative;">
                            <span>
                                <svg width="15" height="15" viewbox="0 0 15 15" xmlns="http://www.w3.org/2000/svg">
                                    <g>
                                        <path
                                            d="M7.50105 7.78913C9.64392 7.78913 11.3956 6.03744 11.3956 3.89456C11.3956 1.75169 9.64392 0 7.50105 0C5.35818 0 3.60652 1.75169 3.60652 3.89456C3.60652 6.03744 5.35821 7.78913 7.50105 7.78913ZM14.1847 10.9014C14.0827 10.6463 13.9467 10.4082 13.7936 10.1871C13.0113 9.0306 11.8038 8.2653 10.4433 8.07822C10.2732 8.06123 10.0861 8.09522 9.95007 8.19727C9.23578 8.72448 8.38546 8.99658 7.50108 8.99658C6.61671 8.99658 5.76638 8.72448 5.05209 8.19727C4.91603 8.09522 4.72895 8.04421 4.5589 8.07822C3.19835 8.2653 1.97387 9.0306 1.20857 10.1871C1.05551 10.4082 0.919443 10.6633 0.817424 10.9014C0.766415 11.0034 0.783407 11.1225 0.834416 11.2245C0.970484 11.4626 1.14054 11.7007 1.2936 11.9048C1.53168 12.2279 1.78679 12.517 2.07592 12.7891C2.31401 13.0272 2.58611 13.2483 2.85824 13.4694C4.20177 14.4728 5.81742 15 7.48409 15C9.15076 15 10.7664 14.4728 12.1099 13.4694C12.382 13.2653 12.6541 13.0272 12.8923 12.7891C13.1644 12.517 13.4365 12.2279 13.6746 11.9048C13.8446 11.6837 13.9977 11.4626 14.1338 11.2245C14.2188 11.1225 14.2358 11.0034 14.1847 10.9014Z">
                                        </path>
                                    </g>
                                </svg>
                                {{ trim((auth()->user()->first_name ?? '') . ' ' . (auth()->user()->last_name ?? '')) ?: auth()->user()->name ?? 'My Account' }}
                                <i class="bi bi-chevron-down ms-1"></i>
                            </span>
                        </a>

                        <div class="user-dropdown-menu"
                            style="position: absolute; top: 100%; right: 0; background: white; min-width: 250px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-radius: 10px; margin-top: 10px; padding: 10px 0; display: none; z-index: 1000;">
                            <div style="padding: 15px 20px; border-bottom: 2px solid #f0f0f0;">
                                <p style="margin: 0; font-weight: 700; color: #1a1a1a;">
                                    {{ trim((auth()->user()->first_name ?? '') . ' ' . (auth()->user()->last_name ?? '')) ?: auth()->user()->name ?? 'Guest' }}
                                </p>
                                <p style="margin: 0; font-size: 13px; color: #6c757d;">{{ auth()->user()->email ?? '' }}
                                </p>
                            </div>
                            @role('admin|superadmin|reception')
                                <a href="{{ route('admin.index') }}"
                                    style="display: flex; align-items: center; padding: 12px 20px; color: #333; text-decoration: none; transition: all 0.3s;">
                                    <i class="bi bi-speedometer2"
                                        style="font-size: 18px; margin-right: 10px; color: #667eea;"></i>
                                    Admin Dashboard
                                </a>
                            @endrole
                            @role('customer')
                                <a href="{{ route('customer.dashboard') }}"
                                    style="display: flex; align-items: center; padding: 12px 20px; color: #333; text-decoration: none; transition: all 0.3s;">
                                    <i class="bi bi-speedometer2"
                                        style="font-size: 18px; margin-right: 10px; color: #667eea;"></i>
                                    Dashboard
                                </a>
                                <a href="{{ route('customer.bookings') }}"
                                    style="display: flex; align-items: center; padding: 12px 20px; color: #333; text-decoration: none; transition: all 0.3s;">
                                    <i class="bi bi-calendar-check"
                                        style="font-size: 18px; margin-right: 10px; color: #667eea;"></i>
                                    My Bookings
                                </a>
                                <a href="{{ route('customer.profile') }}"
                                    style="display: flex; align-items: center; padding: 12px 20px; color: #333; text-decoration: none; transition: all 0.3s;">
                                    <i class="bi bi-person-circle"
                                        style="font-size: 18px; margin-right: 10px; color: #667eea;"></i>
                                    My Profile
                                </a>
                            @endrole
                            <div style="border-top: 2px solid #f0f0f0; margin: 10px 0;"></div>
                            <a href="{{ route('customer.logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                style="display: flex; align-items: center; padding: 12px 20px; color: #dc3545; text-decoration: none; transition: all 0.3s;">
                                <i class="bi bi-box-arrow-right" style="font-size: 18px; margin-right: 10px;"></i>
                                Logout
                            </a>
                            <form id="logout-form" action="{{ route('customer.logout') }}" method="POST"
                                style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </div>
                @else
                    <!-- Guest User - Show Single Auth Button -->
                    <div class="auth-buttons d-xl-flex d-none align-items-center" style="gap: 10px;">
                        <button onclick="openUnifiedAuthModal()"
                            style="display: flex; align-items: center; gap: 8px; background: transparent; border: none; cursor: pointer; padding: 8px 12px; border-radius: 6px; transition: background 0.3s;">
                            <div
                                style="width: 40px; height: 40px; background: #136497; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <img src="{{ asset('default/user.svg') }}" width="100" class="p-2" alt="">
                            </div>
                            <div style="text-align: left;">
                                <div style="font-size: 14px; font-weight: 600; color: #333; line-height: 1.2;">Login or
                                </div>
                                <div style="font-size: 14px; font-weight: 600; color: #333; line-height: 1.2;">Create
                                    Account</div>
                            </div>
                        </button>
                    </div>
                @endauth

                <div class="sidebar-button mobile-menu-btn">
                    <svg width="20" height="18" viewbox="0 0 20 18" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M1.29445 2.8421H10.5237C11.2389 2.8421 11.8182 2.2062 11.8182 1.42105C11.8182 0.635903 11.2389 0 10.5237 0H1.29445C0.579249 0 0 0.635903 0 1.42105C0 2.2062 0.579249 2.8421 1.29445 2.8421Z">
                        </path>
                        <path
                            d="M1.23002 10.421H18.77C19.4496 10.421 20 9.78506 20 8.99991C20 8.21476 19.4496 7.57886 18.77 7.57886H1.23002C0.550421 7.57886 0 8.21476 0 8.99991C0 9.78506 0.550421 10.421 1.23002 10.421Z">
                        </path>
                        <path
                            d="M18.8052 15.1579H10.2858C9.62563 15.1579 9.09094 15.7938 9.09094 16.5789C9.09094 17.3641 9.62563 18 10.2858 18H18.8052C19.4653 18 20 17.3641 20 16.5789C20 15.7938 19.4653 15.1579 18.8052 15.1579Z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>
    </header>
    <div class="top-offer-text-slider-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="top-offer-text-slider-wrap">
                        <div class="slider-btn top-offer-text-slider-prev">
                            <svg width="11" height="12" viewbox="0 0 11 12"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M9.42865 10.4085C8.69396 8.57179 5.02049 6.73505 2.81641 6.00036C5.02049 5.26567 8.32661 4.16363 9.42865 1.5922"
                                    stroke-width="2" stroke-linecap="round"></path>
                            </svg>
                        </div>
                        <div class="swiper top-offer-text-slider">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <a href="travel-package-01.html">One-Click Booking, Upto <strong>FLAT 30%</strong>
                                        Discount of Haneymoon Tours</a>
                                </div>
                                <div class="swiper-slide">
                                    <a href="travel-package-01.html">Customize Your Trip Plan and Get <strong>Special
                                            Discounts</strong> Instantly</a>
                                </div>
                                <div class="swiper-slide">
                                    <a href="travel-package-01.html">Enjoy Family Holiday Packages with
                                        <strong>Flexible Payment Options</strong></a>
                                </div>
                            </div>
                        </div>
                        <div class="slider-btn top-offer-text-slider-next">
                            <svg width="11" height="12" viewbox="0 0 11 12"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M1.57141 10.4085C2.3061 8.57179 5.97957 6.73505 8.18366 6.00036C5.97957 5.26567 2.67345 4.16363 1.57141 1.5922"
                                    stroke-width="2" stroke-linecap="round"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- header Section End-->

    <!-- Unified Auth Modal -->
    <div id="unifiedAuthModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div
            style="background: white; border-radius: 15px; padding: 40px; max-width: 450px; width: 90%; position: relative; box-shadow: 0 10px 50px rgba(0,0,0,0.3);">
            <button onclick="closeUnifiedAuthModal()"
                style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">&times;</button>

            <div id="emailStep">
                <h3 style="margin: 0 0 10px 0; color: #1a1a1a; font-size: 24px;">Welcome!</h3>
                <p style="margin: 0 0 25px 0; color: #666;">Enter your email to continue</p>

                <form id="emailForm" onsubmit="checkEmailAndProceed(event)">
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; color: #333; font-weight: 500;">Email
                            Address</label>
                        <input type="email" id="authEmail" name="email" required
                            style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; transition: border-color 0.3s;"
                            onfocus="this.style.borderColor='#136497'" onblur="this.style.borderColor='#e0e0e0'"
                            placeholder="Enter your email">
                    </div>
                    <div id="emailError" style="display: none; color: #dc3545; margin-bottom: 15px; font-size: 14px;">
                    </div>
                    <button type="submit" id="emailSubmitBtn"
                        style="width: 100%; padding: 14px; background: #136497; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.3s;"
                        onmouseover="this.style.background='#0d4d75'" onmouseout="this.style.background='#136497'">
                        Continue
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- User Dropdown Menu Script -->
    <script>
        // Unified Auth Modal Functions
        function openUnifiedAuthModal() {
            document.getElementById('unifiedAuthModal').style.display = 'flex';
            document.getElementById('authEmail').focus();
        }

        function closeUnifiedAuthModal() {
            document.getElementById('unifiedAuthModal').style.display = 'none';
            document.getElementById('emailForm').reset();
            document.getElementById('emailError').style.display = 'none';
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('unifiedAuthModal');
            if (event.target === modal) {
                closeUnifiedAuthModal();
            }
        });

        // Check email and redirect
        async function checkEmailAndProceed(event) {
            event.preventDefault();

            const email = document.getElementById('authEmail').value;
            const submitBtn = document.getElementById('emailSubmitBtn');
            const errorDiv = document.getElementById('emailError');

            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Checking...';
            errorDiv.style.display = 'none';

            try {
                const response = await fetch('{{ route('customer.check-email') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        email: email
                    })
                });

                const data = await response.json();

                if (data.exists) {
                    // User exists - redirect to login with email pre-filled
                    window.location.href = '{{ route('customer.login') }}?email=' + encodeURIComponent(email);
                } else {
                    // User doesn't exist - redirect to register with email pre-filled
                    window.location.href = '{{ route('customer.register') }}?email=' + encodeURIComponent(email);
                }
            } catch (error) {
                errorDiv.textContent = 'An error occurred. Please try again.';
                errorDiv.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Continue';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const dropdownContainer = document.querySelector('.user-menu-dropdown');
            const dropdownMenu = document.querySelector('.user-dropdown-menu');
            let hideTimeout;

            if (dropdownContainer && dropdownMenu) {
                // Show dropdown on hover
                dropdownContainer.addEventListener('mouseenter', function() {
                    clearTimeout(hideTimeout);
                    dropdownMenu.style.display = 'block';
                });

                // Hide dropdown with delay when mouse leaves
                dropdownContainer.addEventListener('mouseleave', function() {
                    hideTimeout = setTimeout(function() {
                        dropdownMenu.style.display = 'none';
                    }, 300); // 300ms delay before hiding
                });

                // Keep dropdown visible when hovering over the menu itself
                dropdownMenu.addEventListener('mouseenter', function() {
                    clearTimeout(hideTimeout);
                    dropdownMenu.style.display = 'block';
                });

                dropdownMenu.addEventListener('mouseleave', function() {
                    hideTimeout = setTimeout(function() {
                        dropdownMenu.style.display = 'none';
                    }, 300);
                });
            }
        });
    </script>
