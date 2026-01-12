<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $pageMeta = getPageMeta();
    @endphp

    <meta name="description" content="@yield('meta_description', $pageMeta->description)">
    <meta name="keywords" content="@yield('meta_keywords', $pageMeta->keywords)">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="@yield('title', $pageMeta->title)">
    <meta property="og:description" content="@yield('meta_description', $pageMeta->description)">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', $pageMeta->title)">
    <meta name="twitter:description" content="@yield('meta_description', $pageMeta->description)">

    <link rel="icon" href="{{ asset('frontend/img/fav-icon.svg') }}" type="image/gif" sizes="20x20">
    <title>
        @yield('title', $pageMeta->title)
    </title>

    <link href="{{ asset('frontend/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('frontend/css/jquery-ui.css') }}" rel="stylesheet">
    <link href="{{ asset('frontend/css/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('frontend/css/animate.min.css') }}" rel="stylesheet">
    <link href="{{ asset('frontend/css/jquery.fancybox.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('frontend/css/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/slick-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/daterangepicker.css') }}">
    <link href="{{ asset('frontend/css/boxicons.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}">

    <style>
        /* Hide Alpine elements until Alpine is loaded */
        [x-cloak] {
            display: none !important;
        }

        /* Aggressively hide notification drawer from appearing on page load */
        .notification-bell-wrapper>div[style*="position: fixed"] {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
        }

        .notification-bell-wrapper template {
            display: none !important;
        }
    </style>

    <!-- Google Translate Widget Script -->
    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                    pageLanguage: 'en',
                    autoDisplay: 'true',
                    includedLanguages: 'en,hi,ar,es,fr,de,ja,zh-CN,ru,pt,it,ko,tr,nl,pl,sv,id,th,vi',
                    layout: google.translate.TranslateElement.InlineLayout.HORIZONTAL
                },
                'google_translate_element'
            );
        }
    </script>
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit">
    </script>

    <!-- Google Translate Custom Styles (CodePen Style) -->
    <style>
        /* Remove top spacing and hide unwanted elements */
        body {
            top: 0 !important;
        }

        body>.skiptranslate,
        .goog-logo-link,
        .gskiptranslate,
        .goog-te-gadget span,
        .goog-te-banner-frame,
        #goog-gt-tt,
        .goog-te-balloon-frame,
        div#goog-gt-tt {
            display: none !important;
        }

        .goog-te-gadget {
            color: transparent !important;
            font-size: 0px;
        }

        .goog-text-highlight {
            background: transparent !important;
            box-shadow: transparent !important;
        }

        /* Custom dropdown styling for footer */
        #google_translate_element select {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-weight: 600;
            border-radius: 5px;
            padding: 8px 12px;
            cursor: pointer;
            outline: none;
            transition: all 0.3s ease;
        }

        #google_translate_element select:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
        }

        #google_translate_element select option {
            background: #1a1a1a;
            color: #fff;
            padding: 8px;
        }

        /* Ensure clean container */
        #google_translate_element {
            display: inline-block;
        }
    </style>

    @livewireStyles
    @yield('styles')
</head>

<body class="tt-magic-cursor">

    {{-- <div id="magic-cursor">
        <div id="ball"></div>
    </div> --}}

    <!-- Back To Top -->
    <div class="progress-wrap">
        <svg class="progress-circle svg-content" width="100%" height="100%" viewbox="-1 -1 102 102">
            <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98"></path>
        </svg>
        <svg class="arrow" width="22" height="25" viewbox="0 0 24 23" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M0.556131 11.4439L11.8139 0.186067L13.9214 2.29352L13.9422 20.6852L9.70638 20.7061L9.76793 8.22168L3.6064 14.4941L0.556131 11.4439Z">
            </path>
            <path d="M23.1276 11.4999L16.0288 4.40105L15.9991 10.4203L20.1031 14.5243L23.1276 11.4999Z"></path>
        </svg>
    </div>

    @include('frontend.layouts.nav')

    @yield('content')

    @include('frontend.layouts.footer')

    @include('frontend.layouts.footer-scripts')

    @livewireScripts
    @yield('scripts')
</body>

</html>
