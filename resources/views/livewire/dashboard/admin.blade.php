<?php

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Enums\RolesEnum;
use App\Models\{Booking, Room, User, Yacht};

new class extends Component {
    #[Title('Admin Dashboard')]
    public $user;
    public $stats = [];

    public array $bookingChart = [];
    public array $revenueChart = [];
    public array $customerChart = [];

    public function mount(): void
    {
        $this->user = auth()->user();
        $this->loadStats();
        $this->loadCharts();
    }

    public function loadStats(): void
    {
        $now = now();

        // Current bookings for hotel/rooms
        $currentHotelBookings = Booking::where('bookingable_type', Room::class)
            ->whereIn('status', ['pending', 'booked', 'checked_in'])
            ->where(function ($query) use ($now) {
                $query->where('check_in', '<=', $now)->where('check_out', '>=', $now);
            })
            ->count();

        // Current bookings for yacht
        $currentYachtBookings = Booking::where('bookingable_type', Yacht::class)
            ->whereIn('status', ['pending', 'booked', 'checked_in'])
            ->where(function ($query) use ($now) {
                $query->where('check_in', '<=', $now)->where('check_out', '>=', $now);
            })
            ->count();

        // Available rooms (rooms without active bookings for current date)
        $totalRooms = Room::where('is_active', true)->count();
        $bookedRoomIds = Booking::where('bookingable_type', Room::class)
            ->whereIn('status', ['pending', 'booked', 'checked_in'])
            ->where(function ($query) use ($now) {
                $query->where('check_in', '<=', $now)->where('check_out', '>=', $now);
            })
            ->pluck('bookingable_id')
            ->unique();
        $availableRooms = $totalRooms - $bookedRoomIds->count();

        // Available yachts
        $totalYachts = Yacht::count();
        $bookedYachtIds = Booking::where('bookingable_type', Yacht::class)
            ->whereIn('status', ['pending', 'booked', 'checked_in'])
            ->where(function ($query) use ($now) {
                $query->where('check_in', '<=', $now)->where('check_out', '>=', $now);
            })
            ->pluck('bookingable_id')
            ->unique();
        $availableYachts = $totalYachts - $bookedYachtIds->count();

        // Total revenue (from paid bookings)
        $totalRevenue = Booking::where('payment_status', 'paid')->sum(DB::raw('COALESCE(discount_price, price)'));

        // Hotel revenue
        $hotelRevenue = Booking::where('payment_status', 'paid')->where('bookingable_type', Room::class)->sum(DB::raw('COALESCE(discount_price, price)'));

        // Yacht revenue
        $yachtRevenue = Booking::where('payment_status', 'paid')->where('bookingable_type', Yacht::class)->sum(DB::raw('COALESCE(discount_price, price)'));

        // Active customers (users with bookings in last 30 days or with active bookings)
        $customerUserIds = User::role(RolesEnum::CUSTOMER->value)->pluck('id');

        $activeCustomerIds = Booking::whereIn('user_id', $customerUserIds)
            ->where(function ($query) {
                $query->where('created_at', '>=', now()->subDays(30))->orWhereIn('status', ['pending', 'booked', 'checked_in']);
            })
            ->pluck('user_id')
            ->unique();

        $activeCustomers = $activeCustomerIds->count();

        // Fallback: if no bookings, count all customer users
        if ($activeCustomers == 0) {
            $activeCustomers = $customerUserIds->count();
        }

        $this->stats = [
            'current_hotel_bookings' => $currentHotelBookings,
            'current_yacht_bookings' => $currentYachtBookings,
            'available_rooms' => max(0, $availableRooms),
            'total_rooms' => $totalRooms,
            'available_yachts' => max(0, $availableYachts),
            'total_yachts' => $totalYachts,
            'total_revenue' => $totalRevenue,
            'hotel_revenue' => $hotelRevenue,
            'yacht_revenue' => $yachtRevenue,
            'active_customers' => $activeCustomers,
        ];
    }

    public function loadCharts(): void
    {
        $now = now();

        // Get current bookings for chart
        $currentHotelBookings = Booking::where('bookingable_type', Room::class)
            ->whereIn('status', ['pending', 'booked', 'checked_in'])
            ->where(function ($query) use ($now) {
                $query->where('check_in', '<=', $now)->where('check_out', '>=', $now);
            })
            ->count();

        $currentYachtBookings = Booking::where('bookingable_type', Yacht::class)
            ->whereIn('status', ['pending', 'booked', 'checked_in'])
            ->where(function ($query) use ($now) {
                $query->where('check_in', '<=', $now)->where('check_out', '>=', $now);
            })
            ->count();

        // Booking distribution chart
        $this->bookingChart = [
            'type' => 'pie',
            'data' => [
                'labels' => ['Hotel Bookings', 'Yacht Bookings'],
                'datasets' => [
                    [
                        'label' => 'Current Bookings',
                        'data' => [$currentHotelBookings, $currentYachtBookings],
                        'backgroundColor' => ['rgb(59, 130, 246)', 'rgb(16, 185, 129)'],
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
            ],
        ];

        // Revenue chart (bar chart for monthly revenue - separated by hotel and yacht)
        $monthlyHotelRevenue = [];
        $monthlyYachtRevenue = [];
        $monthlyLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $hotelRevenue = Booking::where('payment_status', 'paid')->where('bookingable_type', Room::class)->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->sum(DB::raw('COALESCE(discount_price, price)'));

            $yachtRevenue = Booking::where('payment_status', 'paid')->where('bookingable_type', Yacht::class)->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->sum(DB::raw('COALESCE(discount_price, price)'));

            $monthlyHotelRevenue[] = $hotelRevenue;
            $monthlyYachtRevenue[] = $yachtRevenue;
            $monthlyLabels[] = $month->format('M Y');
        }

        $this->revenueChart = [
            'type' => 'bar',
            'data' => [
                'labels' => $monthlyLabels,
                'datasets' => [
                    [
                        'label' => 'Hotel Revenue',
                        'data' => $monthlyHotelRevenue,
                        'backgroundColor' => 'rgb(59, 130, 246)',
                    ],
                    [
                        'label' => 'Yacht Revenue',
                        'data' => $monthlyYachtRevenue,
                        'backgroundColor' => 'rgb(16, 185, 129)',
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                    ],
                ],
                'plugins' => [
                    'legend' => [
                        'display' => true,
                        'position' => 'top',
                    ],
                ],
            ],
        ];

        // Customer chart (line chart for customer growth)
        $customerGrowth = [];
        $customerLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            // Count customers created up to and including this month (cumulative)
            $customers = User::role(RolesEnum::CUSTOMER->value)->where('created_at', '<=', $monthEnd)->count();
            $customerGrowth[] = $customers;
            $customerLabels[] = $month->format('M Y');
        }

        $this->customerChart = [
            'type' => 'line',
            'data' => [
                'labels' => $customerLabels,
                'datasets' => [
                    [
                        'label' => 'Total Customers',
                        'data' => $customerGrowth,
                        'borderColor' => 'rgb(16, 185, 129)',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'fill' => true,
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                    ],
                ],
            ],
        ];
    }

    public function with(): array
    {
        return [
            'stats' => $this->stats,
        ];
    }
};
?>
@section('cdn')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endsection
<div>
    <x-header title="Admin Dashboard" subtitle="Welcome back, {{ $user->name }}" separator progress-indicator>
        <x-slot:middle class="justify-end">
            <x-badge value="{{ date('M d, Y') }}" class="badge-lg" />
        </x-slot:middle>
    </x-header>

    <!-- Current Bookings Section -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
        <!-- Hotel Bookings Card -->
        <x-card shadow title="Hotel Bookings" separator>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-primary/10 rounded-lg">
                    <div>
                        <div class="text-sm text-base-content/60 mb-1">Current Bookings</div>
                        <div class="text-4xl font-bold text-primary">{{ $stats['current_hotel_bookings'] }}</div>
                    </div>
                    <x-icon name="o-building-office" class="w-16 h-16 text-primary/50" />
                </div>
                <div class="flex items-center justify-between p-4 bg-success/10 rounded-lg">
                    <div>
                        <div class="text-sm text-base-content/60 mb-1">Available Rooms</div>
                        <div class="text-2xl font-bold text-success">
                            {{ $stats['available_rooms'] }} / {{ $stats['total_rooms'] }}
                        </div>
                    </div>
                    <x-icon name="o-home" class="w-12 h-12 text-success/50" />
                </div>
            </div>
        </x-card>

        <!-- Yacht Bookings Card -->
        <x-card shadow title="Yacht Bookings" separator>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-success/10 rounded-lg">
                    <div>
                        <div class="text-sm text-base-content/60 mb-1">Current Bookings</div>
                        <div class="text-4xl font-bold text-success">{{ $stats['current_yacht_bookings'] }}</div>
                    </div>
                    <x-icon name="o-sparkles" class="w-16 h-16 text-success/50" />
                </div>
                <div class="flex items-center justify-between p-4 bg-info/10 rounded-lg">
                    <div>
                        <div class="text-sm text-base-content/60 mb-1">Available Yachts</div>
                        <div class="text-2xl font-bold text-info">
                            {{ $stats['available_yachts'] }} / {{ $stats['total_yachts'] }}
                        </div>
                    </div>
                    <x-icon name="o-sparkles" class="w-12 h-12 text-info/50" />
                </div>
            </div>
        </x-card>
    </div>

    <!-- Revenue Section -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3 mb-6">
        <!-- Total Revenue Card -->
        <x-card shadow title="Total Revenue" separator>
            <div class="flex items-center justify-between p-4 bg-warning/10 rounded-lg">
                <div>
                    <div class="text-sm text-base-content/60 mb-1">All Time Revenue</div>
                    <div class="text-3xl font-bold text-warning">
                        {{ currency_format($stats['total_revenue']) }}
                    </div>
                </div>
                <x-icon name="o-banknotes" class="w-14 h-14 text-warning/50" />
            </div>
        </x-card>

        <!-- Hotel Revenue Card -->
        <x-card shadow title="Hotel Revenue" separator>
            <div class="flex items-center justify-between p-4 bg-primary/10 rounded-lg">
                <div>
                    <div class="text-sm text-base-content/60 mb-1">Hotel Revenue</div>
                    <div class="text-3xl font-bold text-primary">
                        {{ currency_format($stats['hotel_revenue']) }}
                    </div>
                </div>
                <x-icon name="o-building-office" class="w-14 h-14 text-primary/50" />
            </div>
        </x-card>

        <!-- Yacht Revenue Card -->
        <x-card shadow title="Yacht Revenue" separator>
            <div class="flex items-center justify-between p-4 bg-success/10 rounded-lg">
                <div>
                    <div class="text-sm text-base-content/60 mb-1">Yacht Revenue</div>
                    <div class="text-3xl font-bold text-success">
                        {{ currency_format($stats['yacht_revenue']) }}
                    </div>
                </div>
                <x-icon name="o-sparkles" class="w-14 h-14 text-success/50" />
            </div>
        </x-card>
    </div>

    <!-- Active Customers Section -->
    <div class="mb-6">
        <x-card shadow title="Active Customers" separator>
            <div class="flex items-center justify-between p-4 bg-info/10 rounded-lg">
                <div>
                    <div class="text-sm text-base-content/60 mb-1">Active in Last 30 Days</div>
                    <div class="text-4xl font-bold text-info">{{ $stats['active_customers'] }}</div>
                </div>
                <x-icon name="o-users" class="w-16 h-16 text-info/50" />
            </div>
        </x-card>
    </div>

    <!-- Charts Section -->
    <div class="space-y-6">
        <!-- Booking Distribution Chart -->
        <x-card shadow title="Booking Distribution" separator>
            <div class="p-4">
                <div class="h-96">
                    <x-chart wire:model="bookingChart" class="h-full" />
                </div>
            </div>
        </x-card>

        <!-- Revenue Chart -->
        <x-card shadow title="Monthly Revenue Comparison" separator>
            <div class="p-4">
                <div class="h-96">
                    <x-chart wire:model="revenueChart" class="h-full" />
                </div>
            </div>
        </x-card>

        <!-- Customer Growth Chart -->
        <x-card shadow title="Customer Growth" separator>
            <div class="p-4">
                <div class="h-96">
                    <x-chart wire:model="customerChart" class="h-full" />
                </div>
            </div>
        </x-card>
    </div>
</div>
