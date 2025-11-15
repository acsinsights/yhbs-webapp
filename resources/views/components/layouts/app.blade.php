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
                        <img src="{{ asset('default/app_logo.png') }}" height="100" width="100" alt="logo"
                            class="light-logo" />
                        <img src="{{ asset('default/app_logo.png') }}" height="100" width="100" alt="logo"
                            class="dark-logo" />
                    </div>
                </div>
                <div class="display-when-collapsed hidden mx-5 mt-4 lg:mb-6 h-[28px]">
                    <img src="{{ asset('default/app_logo.png') }}" height="100" width="100" alt="logo"
                        class="light-logo" />
                    <img src="{{ asset('default/app_logo.png') }}" height="100" width="100" alt="logo"
                        class="dark-logo" />
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
                    <div class="flex items-center gap-2 justify-center">
                        <img src="{{ asset('default/app_logo.png') }}" height="100" width="100" alt="logo"
                            class="dark-logo" />
                        <img src="{{ asset('default/app_logo.png') }}" height="100" width="100" alt="logo"
                            class="light-logo" />
                    </div>
                </div>

                <div class="display-when-collapsed hidden mx-2 mt-4 lg:mb-3 h-[50px]">
                    <img src="{{ asset('default/app_logo.png') }}" height="100" width="100" alt="logo" />
                </div>
            </a>

            <x-menu activate-by-route>
                <x-menu-item title="Dashboard" icon="o-presentation-chart-bar" link="{{ route('admin.index') }}" />

                @role('reception|admin|superadmin')
                    <div class="divider divider-start my-1">
                        <small class="hidden-when-collapsed">Bookings</small>
                    </div>

                    <x-menu-item title="Yacht Bookings" icon="o-sparkles" link="{{ route('admin.index') }}" />
                    <x-menu-item title="Hotel Bookings" icon="o-building-office" link="{{ route('admin.index') }}" />
                    <x-menu-item title="Hotel Enquiries" icon="o-envelope" link="{{ route('admin.index') }}" />
                @endrole

                @role('admin|superadmin')
                    <div class="divider divider-start my-1">
                        <small class="hidden-when-collapsed">Management</small>
                    </div>

                    <x-menu-sub title="Yachts" icon="o-sparkles">
                        <x-menu-item title="All Yachts" icon="o-list-bullet" link="{{ route('admin.yatch.index') }}" />
                    </x-menu-sub>

                    <x-menu-item title="All Rooms" icon="o-home-modern" link="{{ route('admin.rooms.index') }}" />

                    <x-menu-sub title="Categories" icon="o-list-bullet">
                        <x-menu-item title="All Categories" icon="o-list-bullet" link="{{ route('admin.index') }}" />
                        {{-- <x-menu-item title="Add Category" icon="o-plus-circle" link="{{ route('admin.index') }}" /> --}}
                    </x-menu-sub>

                    <div class="divider divider-start my-1">
                        <small class="hidden-when-collapsed">Reports</small>
                    </div>

                    <x-menu-item title="Booking Reports" icon="o-chart-bar" link="{{ route('admin.index') }}" />
                    <x-menu-item title="Revenue Reports" icon="o-currency-dollar" link="{{ route('admin.index') }}" />
                @endrole

                @role('reception|superadmin|admin')
                    <div class="divider divider-start my-1">
                        <small class="hidden-when-collapsed">Quick Actions</small>
                    </div>

                    <x-menu-item title="New Yacht Booking" icon="o-plus-circle" link="{{ route('admin.index') }}" />
                    <x-menu-item title="New Hotel Booking" icon="o-plus-circle" link="{{ route('admin.index') }}" />
                @endrole

                @role('admin|superadmin')
                    <div class="divider divider-start my-1">
                        <small class="hidden-when-collapsed">Settings</small>
                    </div>
                @endrole

                <x-menu-item title="Profile" icon="o-user-circle" link="{{ route('admin.profile') }}" />
            </x-menu>
        </x-slot:sidebar>
        {{-- The `$slot` goes here --}}
        <x-slot:content class="lg:pt-0">
            <div role="navigation" aria-label="Navbar"
                class="navbar topbar-wrapper z-10 border-b border-base-200 px-3">
                <div class="gap-3 navbar-start">

                </div>
                <div class="navbar-center"></div>
                <div class="gap-1.5 navbar-end">
                    <div class="tooltip  tooltip-bottom" data-tip="Toggle Theme">
                        <x-theme-toggle class=" btn-sm w-12 h-12 btn-ghost" lightTheme="light" darkTheme="dark" />
                    </div>
                    @auth
                        <div class="dropdown dropdown-bottom dropdown-end">
                            <label tabindex="0" class="btn btn-ghost rounded-btn px-1.5 hover:bg-base-content/20">
                                <div class="flex items-center gap-2">
                                    <div aria-label="Avatar photo" class="avatar placeholder">
                                        @if (auth()->user()->image)
                                            <div class="w-8 h-8 rounded-md bg-base-content/10">
                                                <img src="{{ asset(auth()->user()->image) }}"
                                                    alt="{{ auth()->user()->name }}">
                                            </div>
                                        @else
                                            <div class="select-none avatar avatar-placeholder">
                                                <div class="w-8 rounded-full bg-primary text-primary-content">
                                                    <span class="text-md">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex flex-col items-start">
                                        <p class="text-sm/none">
                                            {{ auth()->user()->name }}
                                        </p>
                                    </div>
                                </div>
                            </label>
                            <ul tabindex="0"
                                class="z-50 p-2 mt-4 shadow dropdown-content menu bg-base-100 rounded-box w-52"
                                role="menu">
                                <li>
                                    <a href="{{ route('admin.profile') }}" wire:navigate>
                                        My Profile
                                    </a>
                                </li>
                                <hr class="my-1 -mx-2 border-base-content/10" />
                                <li>
                                    <a href="{{ route('admin.logout') }}" class="text-error">Logout</a>
                                </li>
                            </ul>
                        </div>
                    @endauth
                </div>
            </div>
            <div class="dashboard-content">
                {{ $slot }}
            </div>
            <div
                class="flex justify-between pt-3 px-3 mt-3 border-t text-sm/relaxed text-base-content border-base-200">
                <div>
                    © {{ date('Y') }} {{ config('app.name') }}. All Rights Reserved.
                </div>
            </div>
        </x-slot:content>
    </x-main>
    <x-toast />
    @stack('scripts')
</body>

</html>
