<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>

    {{-- add favicon --}}
    <link rel="icon" href="{{ asset('default/app_logo.png') }}" type="image/x-icon" />

    {{-- Flatpickr CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    {{-- Fonts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('cdn')
</head>

<body class="min-h-screen font-sans antialiased bg-base-200/50 dark:bg-base-200">
    <x-nav sticky class="lg:hidden">
        <x-slot:brand>
            <a href="{{ route('admin.index') }}" wire:navigate="">
                {{-- Logo section - commented out temporarily --}}
                <div class="hidden-when-collapsed ">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('frontend/img/header-logo2.svg') }}" width="500" alt="logo"
                            class="light-logo" />
                        <img src="{{ asset('frontend/img/header-logo2.svg') }}" width="500" alt="logo"
                            class="dark-logo" />
                    </div>
                </div>
                <div class="display-when-collapsed hidden mx-5 mt-4 lg:mb-6 h-[28px]">
                    <img src="{{ asset('frontend/img/header-logo2.svg') }}" width="500" alt="logo"
                        class="light-logo" />
                    <img src="{{ asset('frontend/img/header-logo2.svg') }}" width="500" alt="logo"
                        class="dark-logo" />
                </div>
                {{-- Text Logo --}}
                {{-- <div class="hidden-when-collapsed">
                    <div class="flex items gap-2">
                        <span class="text-3xl font-bold text-primary">YHBS</span>
                    </div>
                </div>
                <div class="display-when-collapsed hidden mx-5 mt-4 lg:mb-6">
                    <span class="text-2xl font-bold text-primary">YHBS</span>
                </div> --}}
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
                <div class="p-5 pt-3 hidden-when-collapsed ">
                    <div class="flex items-center gap-2 mt-2">
                        <img src="{{ asset('frontend/img/admin-logo.svg') }}" width="250" alt="logo"
                            class="light-logo" />
                        <img src="{{ asset('frontend/img/admin-logo-light.svg') }}" width="250" alt="logo"
                            class="dark-logo" />
                    </div>
                </div>

                <div class="display-when-collapsed hidden mx-2 mt-4 lg:mb-3">
                    <img src="{{ asset('frontend/img/fav-icon.svg') }}" width="50" alt="IKARUS Logo" />
                </div>
            </a>

            <x-menu activate-by-route>
                {{-- Dashboard --}}
                <x-menu-item title="Dashboard" icon="o-presentation-chart-bar" link="{{ route('admin.index') }}" />

                @role('reception|admin|superadmin')
                    {{-- Bookings Section --}}
                    <x-menu-separator title="Bookings" />

                    <x-menu-sub title="All Bookings" icon="o-calendar-days">
                        <x-menu-item title="Room Bookings" icon="o-home-modern"
                            link="{{ route('admin.bookings.room.index') }}" />
                        <x-menu-item title="House Bookings" icon="o-building-office"
                            link="{{ route('admin.bookings.house.index') }}" />
                        <x-menu-item title="Boat Bookings" icon="o-circle-stack"
                            link="{{ route('admin.bookings.boat.index') }}" />
                    </x-menu-sub>

                    <x-menu-item title="Coupons" icon="o-ticket" link="{{ route('admin.coupons.index') }}" />

                    @php
                        $pendingCancellations = \App\Models\Booking::where('cancellation_status', 'pending')->count();
                    @endphp
                    <x-menu-item title="Cancellation Requests" icon="o-x-circle"
                        link="{{ route('admin.cancellation-requests') }}">
                        @if ($pendingCancellations > 0)
                            <x-slot:actions>
                                <x-badge value="{{ $pendingCancellations }}" class="badge-error" />
                            </x-slot:actions>
                        @endif
                    </x-menu-item>

                    @php
                        $pendingReschedules = \App\Models\Booking::where('reschedule_status', 'pending')->count();
                    @endphp
                    <x-menu-item title="Reschedule Requests" icon="o-calendar"
                        link="{{ route('admin.reschedule-requests') }}">
                        @if ($pendingReschedules > 0)
                            <x-slot:actions>
                                <x-badge value="{{ $pendingReschedules }}" class="badge-warning" />
                            </x-slot:actions>
                        @endif
                    </x-menu-item>
                @endrole

                @role('admin|superadmin')
                    {{-- Property Management Section --}}
                    <x-menu-separator title="Property Management" />

                    <x-menu-sub title="Accommodations" icon="o-building-office-2">
                        <x-menu-item title="Houses" icon="o-building-office" link="{{ route('admin.houses.index') }}" />
                        <x-menu-item title="Rooms" icon="o-home-modern" link="{{ route('admin.rooms.index') }}" />
                        <x-menu-item title="Categories" icon="o-tag" link="{{ route('admin.category.index') }}" />
                    </x-menu-sub>

                    <x-menu-sub title="Marine Services" icon="o-circle-stack">
                        <x-menu-item title="Boats" icon="o-circle-stack" link="{{ route('admin.boats.index') }}" />
                        <x-menu-item title="Service Types" icon="o-squares-2x2"
                            link="{{ route('admin.boats.service-types.index') }}" />
                    </x-menu-sub>

                    <x-menu-item title="Amenities" icon="o-star" link="{{ route('admin.amenity.index') }}" />

                    {{-- Customers Section --}}
                    <x-menu-separator title="Users" />

                    <x-menu-item title="All Customers" icon="o-users" link="{{ route('admin.customers.index') }}" />

                    {{-- Content Section --}}
                    <x-menu-separator title="Content" />

                    <x-menu-item title="Blogs" icon="o-document-text" link="{{ route('admin.blogs.index') }}" />
                    <x-menu-item title="Contact Submissions" icon="o-envelope"
                        link="{{ route('admin.contacts.index') }}" />
                    {{-- CMS Section --}}
                    <x-menu-separator title="CMS" />

                    <x-menu-item title="Hero Sliders" icon="o-photo" link="{{ route('admin.sliders.index') }}" />
                    <x-menu-item title="Testimonials" icon="o-chat-bubble-left-right"
                        link="{{ route('admin.testimonials.index') }}" />
                    <x-menu-item title="Statistics" icon="o-chart-bar" link="{{ route('admin.statistics.index') }}" />

                    <x-menu-sub title="Policy Pages" icon="o-document-text">
                        @php
                            $policyPages = \App\Models\PolicyPage::orderBy('id')->get();
                        @endphp
                        @foreach ($policyPages as $page)
                            <x-menu-item title="{{ $page->title }}" icon="o-document"
                                link="{{ route('admin.policy-pages.edit', $page->id) }}" />
                        @endforeach
                    </x-menu-sub>
                @endrole

                {{-- Settings Section --}}
                <x-menu-separator title="Settings" />

                <x-menu-item title="Website Settings" icon="o-cog-6-tooth"
                    link="{{ route('admin.website-settings.index') }}" />
                <x-menu-item title="Page Meta" icon="o-document-magnifying-glass"
                    link="{{ route('admin.page-meta.index') }}" />
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
                    @auth
                        {{-- Notifications Bell --}}
                        @php
                            $unreadCount = auth()->user()->unreadNotifications->count();
                        @endphp
                        <div class="dropdown dropdown-bottom dropdown-end">
                            <label tabindex="0" class="btn btn-ghost btn-circle relative">
                                <x-icon name="o-bell" class="w-6 h-6" />
                                @if ($unreadCount > 0)
                                    <span
                                        class="badge badge-error badge-sm absolute -top-1 -right-1">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                                @endif
                            </label>
                            <div tabindex="0"
                                class="dropdown-content card card-compact z-[1] w-96 p-2 shadow bg-base-100 rounded-box mt-3">
                                <div class="card-body">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="card-title text-sm">Notifications</h3>
                                        @if ($unreadCount > 0)
                                            <a href="{{ route('admin.notifications.mark-all-read') }}"
                                                class="text-xs link link-primary">
                                                Mark all as read
                                            </a>
                                        @endif
                                    </div>
                                    <div class="divider my-0"></div>
                                    <div class="max-h-96 overflow-y-auto space-y-2">
                                        @forelse(auth()->user()->notifications->take(10) as $notification)
                                            <a href="{{ $notification->data['url'] ?? '#' }}"
                                                class="block p-3 rounded-lg hover:bg-base-200 transition {{ is_null($notification->read_at) ? 'bg-primary/5' : '' }}"
                                                onclick="event.preventDefault(); markAsRead('{{ $notification->id }}', '{{ $notification->data['url'] ?? '#' }}')">
                                                <div class="flex gap-3">
                                                    <div class="flex-shrink-0">
                                                        <x-icon name="{{ $notification->data['icon'] ?? 'o-bell' }}"
                                                            class="w-5 h-5 text-primary" />
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p
                                                            class="text-sm font-medium {{ is_null($notification->read_at) ? 'text-base-content' : 'text-base-content/70' }}">
                                                            {{ $notification->data['message'] ?? 'New notification' }}
                                                        </p>
                                                        <p class="text-xs text-base-content/50 mt-1">
                                                            {{ $notification->created_at->diffForHumans() }}
                                                        </p>
                                                    </div>
                                                    @if (is_null($notification->read_at))
                                                        <div class="flex-shrink-0">
                                                            <span class="w-2 h-2 rounded-full bg-primary block"></span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </a>
                                        @empty
                                            <div class="text-center py-8 text-base-content/50">
                                                <x-icon name="o-bell-slash" class="w-12 h-12 mx-auto mb-2 opacity-30" />
                                                <p class="text-sm">No notifications yet</p>
                                            </div>
                                        @endforelse
                                    </div>
                                    @if (auth()->user()->notifications->count() > 10)
                                        <div class="divider my-2"></div>
                                        <a href="{{ route('admin.notifications') }}" class="btn btn-sm btn-ghost w-full">
                                            View all notifications
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endauth

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
                                    <button onclick="logout_modal.showModal()" class="text-error w-full text-left">
                                        Logout
                                    </button>
                                </li>
                            </ul>
                        </div>
                    @endauth

                    <!-- Logout Confirmation Modal -->
                    <x-modal id="logout_modal" title="Confirm Logout" subtitle="Are you sure you want to logout?"
                        separator>
                        <div class="flex justify-end gap-2 mt-4">
                            <x-button label="Cancel" onclick="logout_modal.close()" />
                            <x-button label="Yes, Logout" class="btn-error" link="{{ route('admin.logout') }}" />
                        </div>
                    </x-modal>
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
                <div>
                    <span class="text-primary">v{{ config('app.version') }}</span>
                </div>
            </div>
        </x-slot:content>
    </x-main>
    <x-toast />

    {{-- Flatpickr JS --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    {{-- Notification mark as read script --}}
    <script>
        function markAsRead(notificationId, url) {
            fetch(`/admin/notifications/mark-read/${notificationId}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                }).then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = url;
                    }
                });
        }
    </script>

    @yield('scripts')
</body>

</html>
