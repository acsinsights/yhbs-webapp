<?php

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Enums\RolesEnum;
use App\Models\{Booking, House, Room, User, Yacht};

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

        // Current bookings for houses
        $currentHouseBookings = Booking::where('bookingable_type', House::class)
            ->whereIn('status', ['pending', 'booked', 'checked_in'])
            ->where(function ($query) use ($now) {
                $query->where('check_in', '<=', $now)->where('check_out', '>=', $now);
            })
            ->count();

        // Current bookings for rooms
        $currentRoomBookings = Booking::where('bookingable_type', Room::class)
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

        // Available houses
        $totalHouses = House::where('is_active', true)->count();
        $bookedHouseIds = Booking::where('bookingable_type', House::class)
            ->whereIn('status', ['pending', 'booked', 'checked_in'])
            ->where(function ($query) use ($now) {
                $query->where('check_in', '<=', $now)->where('check_out', '>=', $now);
            })
            ->pluck('bookingable_id')
            ->unique();
        $availableHouses = $totalHouses - $bookedHouseIds->count();

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

        // House Revenue
        $houseRevenue = Booking::where('payment_status', 'paid')->where('bookingable_type', House::class)->sum(DB::raw('COALESCE(discount_price, price)'));

        // Room Revenue
        $roomRevenue = Booking::where('payment_status', 'paid')->where('bookingable_type', Room::class)->sum(DB::raw('COALESCE(discount_price, price)'));

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
            'current_house_bookings' => $currentHouseBookings,
            'current_room_bookings' => $currentRoomBookings,
            'current_yacht_bookings' => $currentYachtBookings,
            'available_houses' => max(0, $availableHouses),
            'total_houses' => $totalHouses,
            'available_rooms' => max(0, $availableRooms),
            'total_rooms' => $totalRooms,
            'available_yachts' => max(0, $availableYachts),
            'total_yachts' => $totalYachts,
            'total_revenue' => $totalRevenue,
            'house_revenue' => $houseRevenue,
            'room_revenue' => $roomRevenue,
            'yacht_revenue' => $yachtRevenue,
            'active_customers' => $activeCustomers,
        ];
    }

    public function loadCharts(): void
    {
        $now = now();

        // Get current bookings for chart
        $currentHouseBookings = Booking::where('bookingable_type', House::class)
            ->whereIn('status', ['pending', 'booked', 'checked_in'])
            ->where(function ($query) use ($now) {
                $query->where('check_in', '<=', $now)->where('check_out', '>=', $now);
            })
            ->count();

        $currentRoomBookings = Booking::where('bookingable_type', Room::class)
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
                'labels' => ['House Bookings', 'Room Bookings', 'Yacht Bookings'],
                'datasets' => [
                    [
                        'label' => 'Current Bookings',
                        'data' => [$currentHouseBookings, $currentRoomBookings, $currentYachtBookings],
                        'backgroundColor' => ['rgb(245, 158, 11)', 'rgb(59, 130, 246)', 'rgb(16, 185, 129)'],
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
            ],
        ];

        // Revenue chart (line chart for monthly revenue - separated by House, Room, and Yacht)
        $monthlyHouseRevenue = [];
        $monthlyRoomRevenue = [];
        $monthlyYachtRevenue = [];
        $monthlyLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $houseRevenue = Booking::where('payment_status', 'paid')->where('bookingable_type', House::class)->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->sum(DB::raw('COALESCE(discount_price, price)'));

            $roomRevenue = Booking::where('payment_status', 'paid')->where('bookingable_type', Room::class)->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->sum(DB::raw('COALESCE(discount_price, price)'));

            $yachtRevenue = Booking::where('payment_status', 'paid')->where('bookingable_type', Yacht::class)->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->sum(DB::raw('COALESCE(discount_price, price)'));

            $monthlyHouseRevenue[] = $houseRevenue;
            $monthlyRoomRevenue[] = $roomRevenue;
            $monthlyYachtRevenue[] = $yachtRevenue;
            $monthlyLabels[] = $month->format('M Y');
        }

        $this->revenueChart = [
            'type' => 'line',
            'data' => [
                'labels' => $monthlyLabels,
                'datasets' => [
                    [
                        'label' => 'House Revenue',
                        'data' => $monthlyHouseRevenue,
                        'borderColor' => 'rgb(245, 158, 11)',
                        'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                    ],
                    [
                        'label' => 'Room Revenue',
                        'data' => $monthlyRoomRevenue,
                        'borderColor' => 'rgb(59, 130, 246)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                    ],
                    [
                        'label' => 'Yacht Revenue',
                        'data' => $monthlyYachtRevenue,
                        'borderColor' => 'rgb(16, 185, 129)',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
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

        // Active Customers chart (line chart for current month with 3-day grouping and previous month comparison)
        $currentMonth = now();
        $previousMonth = now()->subMonth();

        $customerLabels = [];
        $currentMonthActive = [];
        $previousMonthActive = [];

        // Get customers for current month grouped by 3-day intervals
        for ($i = 0; $i < 10; $i++) {
            $groupStart = $currentMonth
                ->copy()
                ->startOfMonth()
                ->addDays($i * 3);
            $groupEnd = $groupStart->copy()->addDays(2)->endOfDay();

            // Skip if we're beyond current month
            if ($groupStart->month != $currentMonth->month) {
                break;
            }

            // Count active customers for this 3-day group in current month
            $activeCount = Booking::whereIn('user_id', User::role(RolesEnum::CUSTOMER->value)->pluck('id'))
                ->whereBetween('created_at', [$groupStart, $groupEnd])
                ->pluck('user_id')
                ->unique()
                ->count();

            $currentMonthActive[] = $activeCount;

            // Count active customers for corresponding period in previous month
            $prevGroupStart = $groupStart->copy()->subMonth();
            $prevGroupEnd = $groupEnd->copy()->subMonth();

            $prevActiveCount = Booking::whereIn('user_id', User::role(RolesEnum::CUSTOMER->value)->pluck('id'))
                ->whereBetween('created_at', [$prevGroupStart, $prevGroupEnd])
                ->pluck('user_id')
                ->unique()
                ->count();

            $previousMonthActive[] = $prevActiveCount;

            // Format label as "Day X-Y"
            $customerLabels[] = 'Day ' . ($i * 3 + 1) . '-' . ($i * 3 + 3);
        }

        $this->customerChart = [
            'type' => 'line',
            'data' => [
                'labels' => $customerLabels,
                'datasets' => [
                    [
                        'label' => 'Current Month',
                        'data' => $currentMonthActive,
                        'borderColor' => 'rgb(59, 130, 246)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'borderWidth' => 2,
                        'fill' => true,
                        'tension' => 0.4,
                    ],
                    [
                        'label' => 'Previous Month',
                        'data' => $previousMonthActive,
                        'borderColor' => 'rgb(107, 114, 128)',
                        'backgroundColor' => 'rgba(107, 114, 128, 0.05)',
                        'borderWidth' => 2,
                        'fill' => true,
                        'tension' => 0.4,
                        'borderDash' => [5, 5],
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
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
        <!-- House Bookings Card -->
        <x-card shadow title="House Bookings" separator>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-warning/10 rounded-lg">
                    <div>
                        <div class="text-sm text-base-content/60 mb-1">Current Bookings</div>
                        <div class="text-4xl font-bold text-warning">{{ $stats['current_house_bookings'] }}</div>
                    </div>
                    <x-icon name="o-home-modern" class="w-16 h-16 text-warning/50" />
                </div>
                <div class="flex items-center justify-between p-4 bg-info/10 rounded-lg">
                    <div>
                        <div class="text-sm text-base-content/60 mb-1">Available Houses</div>
                        <div class="text-2xl font-bold text-info">
                            {{ $stats['available_houses'] }} / {{ $stats['total_houses'] }}
                        </div>
                    </div>
                    <x-icon name="o-home-modern" class="w-12 h-12 text-info/50" />
                </div>
            </div>
        </x-card>

        <!-- Room Bookings Card -->
        <x-card shadow title="Room Bookings" separator>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-primary/10 rounded-lg">
                    <div>
                        <div class="text-sm text-base-content/60 mb-1">Current Bookings</div>
                        <div class="text-4xl font-bold text-primary">{{ $stats['current_room_bookings'] }}</div>
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
    <div class="grid grid-cols-1 gap-6 md:grid-cols-4 mb-6">
        <!-- Total Revenue Card -->
        <x-card shadow title="Total Revenue" separator>
            <div class="flex items-center justify-between p-4 bg-secondary/10 rounded-lg">
                <div>
                    <div class="text-sm text-base-content/60 mb-1">All Time</div>
                    <div class="text-2xl font-bold text-secondary">
                        {{ currency_format($stats['total_revenue']) }}
                    </div>
                </div>
                <x-icon name="o-banknotes" class="w-12 h-12 text-secondary/50" />
            </div>
        </x-card>

        <!-- House Revenue Card -->
        <x-card shadow title="House Revenue" separator>
            <div class="flex items-center justify-between p-4 bg-warning/10 rounded-lg">
                <div>
                    <div class="text-sm text-base-content/60 mb-1">Houses</div>
                    <div class="text-2xl font-bold text-warning">
                        {{ currency_format($stats['house_revenue']) }}
                    </div>
                </div>
                <x-icon name="o-home-modern" class="w-12 h-12 text-warning/50" />
            </div>
        </x-card>

        <!-- Room Revenue Card -->
        <x-card shadow title="Room Revenue" separator>
            <div class="flex items-center justify-between p-4 bg-primary/10 rounded-lg">
                <div>
                    <div class="text-sm text-base-content/60 mb-1">Rooms</div>
                    <div class="text-2xl font-bold text-primary">
                        {{ currency_format($stats['room_revenue']) }}
                    </div>
                </div>
                <x-icon name="o-building-office" class="w-12 h-12 text-primary/50" />
            </div>
        </x-card>

        <!-- Yacht Revenue Card -->
        <x-card shadow title="Yacht Revenue" separator>
            <div class="flex items-center justify-between p-4 bg-success/10 rounded-lg">
                <div>
                    <div class="text-sm text-base-content/60 mb-1">Yachts</div>
                    <div class="text-2xl font-bold text-success">
                        {{ currency_format($stats['yacht_revenue']) }}
                    </div>
                </div>
                <x-icon name="o-sparkles" class="w-12 h-12 text-success/50" />
            </div>
        </x-card>
    </div>

    <!-- Charts Section -->
    <div class="space-y-6">
        <!-- Booking Distribution & Active Customers Charts Side by Side -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
            <!-- Booking Distribution Chart -->
            <x-card shadow title="Booking Distribution" separator>
                <div class="p-4">
                    <div class="h-80">
                        <x-chart wire:model="bookingChart" class="h-full" />
                    </div>
                </div>
            </x-card>

            <!-- Active Customers Chart -->
            <x-card shadow title="Active Customers" separator>
                <div class="p-4">
                    <div class="h-80">
                        <x-chart wire:model="customerChart" class="h-full" />
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Revenue Chart -->
        <x-card shadow title="Monthly Revenue Comparison" separator>
            <div class="p-4">
                <div class="h-96">
                    <x-chart wire:model="revenueChart" class="h-full" />
                </div>
            </div>
        </x-card>
    </div>
</div>
