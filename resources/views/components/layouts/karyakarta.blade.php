<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>

    {{-- add favicon --}}
    <link rel="icon" href="{{ asset('default/app_logo.png') }}" type="image/x-icon" />

    {{-- Fonts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('cdn')
</head>

<body class="min-h-screen font-sans antialiased bg-base-200/50 dark:bg-base-200">
    <x-nav sticky class="lg:hidden">
        <x-slot:brand>
            <a href="{{ route('admin.index') }}" wire:navigate="">
                <div class="hidden-when-collapsed ">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('default/app_logo.png') }}" width="200" alt="logo"
                            class="light-logo" />
                        <img src="{{ asset('default/app_logo.png') }}" width="200" alt="logo"
                            class="dark-logo" />
                    </div>
                </div>
                <div class="display-when-collapsed hidden mx-5 mt-4 lg:mb-6 h-[28px]">
                    <img src="{{ asset('default/app_logo.png') }}" width="200" alt="logo" class="light-logo" />
                    <img src="{{ asset('default/app_logo.png') }}" width="200" alt="logo" class="dark-logo" />
                </div>
            </a>
        </x-slot:brand>
        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden me-3">
                <x-icon name="fas.bars" class="cursor-pointer" />
            </label>
        </x-slot:actions>
    </x-nav>

    <x-main full-width>
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">
            <a href="{{ route('admin.index') }}" wire:navigate="">
                <div class="hidden-when-collapsed p-5 pt-3 ">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('default/app_logo.png') }}" width="200" alt="logo"
                            class="dark-logo" />
                        <img src="{{ asset('default/app_logo.png') }}" width="200" alt="logo"
                            class="light-logo" />
                    </div>
                </div>

                <div class="display-when-collapsed hidden mx-5 mt-4 lg:mb-6 h-[28px]">
                    <img src="{{ asset('default/app_logo.png') }}" width="200" alt="logo" />
                </div>
            </a>

            <x-menu activate-by-route>
                <x-menu-item title="Dashboard" icon="o-presentation-chart-bar"
                    link="{{ route('karyakarta.dashboard') }}" />
                <x-menu-item title="Voters" icon="o-identification" link="{{ route('karyakarta.voters') }}" />
                <x-menu-item title="Advanced Filters" icon="o-funnel" link="{{ route('karyakarta.filters') }}" />

                <!-- Mobile Logout -->
                <div class="md:hidden mt-4 px-4">
                    <livewire:karyakarta.logout />
                </div>
            </x-menu>
        </x-slot:sidebar>
        {{-- The `$slot` goes here --}}
        <x-slot:content class="lg:pt-0">
            <div role="navigation" aria-label="Navbar" class="navbar topbar-wrapper z-10 border-b border-base-200 px-3">
                <div class="gap-3 navbar-start">

                </div>
                <div class="navbar-center"></div>
                <div class="gap-1.5 navbar-end">
                    <div class="tooltip  tooltip-bottom" data-tip="Toggle Theme">
                        <x-theme-toggle class=" btn-sm w-12 h-12 btn-ghost" lightTheme="light" darkTheme="dark" />
                    </div>

                    <div class="hidden md:flex gap-3">
                        <livewire:karyakarta.logout />
                    </div>
                </div>
            </div>
            <div class="dashboard-content">
                {{ $slot }}
            </div>
            <div class="flex justify-between pt-3 px-3 mt-3 border-t text-sm/relaxed text-base-content border-base-200">
                <div>
                    © {{ date('Y') }} {{ config('app.name') }}. All Rights Reserved.
                </div>
            </div>
        </x-slot:content>
    </x-main>
    <x-toast />
</body>

</html>
