<?php

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Enums\RolesEnum;
use App\Models\{Booking, House, Room, User, Boat};

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

        // Current bookings for boats
        $currentBoatBookings = Booking::where('bookingable_type', Boat::class)
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

        // Available boats
        $totalBoats = Boat::where('is_active', true)->count();
        $bookedBoatIds = Booking::where('bookingable_type', Boat::class)
            ->whereIn('status', ['pending', 'booked', 'checked_in'])
            ->where(function ($query) use ($now) {
                $query->where('check_in', '<=', $now)->where('check_out', '>=', $now);
            })
            ->pluck('bookingable_id')
            ->unique();
        $availableBoats = $totalBoats - $bookedBoatIds->count();

        // Total revenue (from paid bookings) - use discount_price if it exists and is less than price, otherwise use price
        $totalRevenue = Booking::where('payment_status', 'paid')->sum(DB::raw('CASE WHEN discount_price IS NOT NULL AND discount_price < price THEN discount_price ELSE price END'));

        // House Revenue
        $houseRevenue = Booking::where('payment_status', 'paid')->where('bookingable_type', House::class)->sum(DB::raw('CASE WHEN discount_price IS NOT NULL AND discount_price < price THEN discount_price ELSE price END'));

        // Room Revenue
        $roomRevenue = Booking::where('payment_status', 'paid')->where('bookingable_type', Room::class)->sum(DB::raw('CASE WHEN discount_price IS NOT NULL AND discount_price < price THEN discount_price ELSE price END'));

        // Boat revenue
        $boatRevenue = Booking::where('payment_status', 'paid')->where('bookingable_type', Boat::class)->sum(DB::raw('CASE WHEN discount_price IS NOT NULL AND discount_price < price THEN discount_price ELSE price END'));

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

        // Total bookings count
        $totalBookings = Booking::count();
        $totalHouseBookings = Booking::where('bookingable_type', House::class)->count();
        $totalRoomBookings = Booking::where('bookingable_type', Room::class)->count();
        $totalBoatBookings = Booking::where('bookingable_type', Boat::class)->count();

        // Pending payments
        $pendingPayments = Booking::where('payment_status', 'pending')->count();
        $pendingPaymentAmount = Booking::where('payment_status', 'pending')->sum(DB::raw('CASE WHEN discount_price IS NOT NULL AND discount_price < price THEN discount_price ELSE price END'));

        // Today's check-ins and check-outs
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        $todayCheckIns = Booking::whereBetween('check_in', [$todayStart, $todayEnd])
            ->whereIn('status', ['pending', 'booked'])
            ->count();
        $todayCheckOuts = Booking::whereBetween('check_out', [$todayStart, $todayEnd])
            ->where('status', 'checked_in')
            ->count();

        $this->stats = [
            'current_house_bookings' => $currentHouseBookings,
            'current_room_bookings' => $currentRoomBookings,
            'current_boat_bookings' => $currentBoatBookings,
            'total_bookings' => $totalBookings,
            'total_house_bookings' => $totalHouseBookings,
            'total_room_bookings' => $totalRoomBookings,
            'total_boat_bookings' => $totalBoatBookings,
            'available_houses' => max(0, $availableHouses),
            'total_houses' => $totalHouses,
            'available_rooms' => max(0, $availableRooms),
            'total_rooms' => $totalRooms,
            'available_boats' => max(0, $availableBoats),
            'total_boats' => $totalBoats,
            'total_revenue' => $totalRevenue,
            'house_revenue' => $houseRevenue,
            'room_revenue' => $roomRevenue,
            'boat_revenue' => $boatRevenue,
            'active_customers' => $activeCustomers,
            'pending_payments' => $pendingPayments,
            'pending_payment_amount' => $pendingPaymentAmount,
            'today_check_ins' => $todayCheckIns,
            'today_check_outs' => $todayCheckOuts,
        ];
    }

    public function loadCharts(): void
    {
        $now = now();

        // Get total bookings for chart (all time)
        $totalHouseBookings = Booking::where('bookingable_type', House::class)->count();
        $totalRoomBookings = Booking::where('bookingable_type', Room::class)->count();
        $totalBoatBookings = Booking::where('bookingable_type', Boat::class)->count();

        // Booking distribution chart - show total bookings instead of current
        $this->bookingChart = [
            'type' => 'pie',
            'data' => [
                'labels' => ['House Bookings', 'Room Bookings', 'Boat Bookings'],
                'datasets' => [
                    [
                        'label' => 'Total Bookings',
                        'data' => [$totalHouseBookings, $totalRoomBookings, $totalBoatBookings],
                        'backgroundColor' => ['rgb(245, 158, 11)', 'rgb(59, 130, 246)', 'rgb(16, 185, 129)'],
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => [
                        'display' => true,
                        'position' => 'bottom',
                    ],
                    'tooltip' => [
                        'callbacks' => [
                            'label' => 'function(context) { return context.label + ": " + context.parsed + " bookings"; }',
                        ],
                    ],
                ],
            ],
        ];

        // Revenue chart (line chart for monthly revenue - separated by House, Room, and Boat)
        $monthlyHouseRevenue = [];
        $monthlyRoomRevenue = [];
        $monthlyBoatRevenue = [];
        $monthlyLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $houseRevenue = Booking::where('payment_status', 'paid')->where('bookingable_type', House::class)->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->sum(DB::raw('CASE WHEN discount_price IS NOT NULL AND discount_price < price THEN discount_price ELSE price END'));

            $roomRevenue = Booking::where('payment_status', 'paid')->where('bookingable_type', Room::class)->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->sum(DB::raw('CASE WHEN discount_price IS NOT NULL AND discount_price < price THEN discount_price ELSE price END'));

            $boatRevenue = Booking::where('payment_status', 'paid')->where('bookingable_type', Boat::class)->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->sum(DB::raw('CASE WHEN discount_price IS NOT NULL AND discount_price < price THEN discount_price ELSE price END'));

            $monthlyHouseRevenue[] = $houseRevenue;
            $monthlyRoomRevenue[] = $roomRevenue;
            $monthlyBoatRevenue[] = $boatRevenue;
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
                        'label' => 'Boat Revenue',
                        'data' => $monthlyBoatRevenue,
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

            // Skip if we're beyond current month or beyond today
            if ($groupStart->month != $currentMonth->month || $groupStart->isAfter($now)) {
                break;
            }

            // Adjust end date if it goes beyond today
            if ($groupEnd->isAfter($now)) {
                $groupEnd = $now->copy()->endOfDay();
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

    <!-- Today's Summary Section -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4 mb-6">
        <!-- Today's Check-ins -->
        <x-card shadow title="Today's Check-ins" separator>
            <div class="flex items-center justify-between p-4 bg-info/10 rounded-lg">
                <div>
                    <div class="text-sm text-base-content/60 mb-1">Expected</div>
                    <div class="text-3xl font-bold text-info">{{ $stats['today_check_ins'] }}</div>
                </div>
                <x-icon name="o-arrow-right-on-rectangle" class="w-12 h-12 text-info/50" />
            </div>
        </x-card>

        <!-- Today's Check-outs -->
        <x-card shadow title="Today's Check-outs" separator>
            <div class="flex items-center justify-between p-4 bg-warning/10 rounded-lg">
                <div>
                    <div class="text-sm text-base-content/60 mb-1">Expected</div>
                    <div class="text-3xl font-bold text-warning">{{ $stats['today_check_outs'] }}</div>
                </div>
                <x-icon name="o-arrow-left-on-rectangle" class="w-12 h-12 text-warning/50" />
            </div>
        </x-card>

        <!-- Pending Payments -->
        <x-card shadow title="Pending Payments" separator>
            <div class="flex items-center justify-between p-4 bg-error/10 rounded-lg">
                <div>
                    <div class="text-sm text-base-content/60 mb-1">Count</div>
                    <div class="text-3xl font-bold text-error">{{ $stats['pending_payments'] }}</div>
                </div>
                <x-icon name="o-exclamation-triangle" class="w-12 h-12 text-error/50" />
            </div>
        </x-card>

        <!-- Pending Payment Amount -->
        <x-card shadow title="Pending Amount" separator>
            <div class="flex items-center justify-between p-4 bg-error/10 rounded-lg">
                <div>
                    <div class="text-sm text-base-content/60 mb-1">Total</div>
                    <div class="text-xl font-bold text-error">{{ currency_format($stats['pending_payment_amount']) }}
                    </div>
                </div>
                <x-icon name="o-currency-dollar" class="w-12 h-12 text-error/50" />
            </div>
        </x-card>
    </div>

    <!-- Current Bookings Section -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
        <!-- House Bookings Card -->
        <x-card shadow title="House Bookings" separator>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-info/10 rounded-lg">
                    <div>
                        <div class="text-sm text-base-content/60 mb-1">Available Houses</div>
                        <div class="text-xl font-bold text-info">
                            {{ $stats['available_houses'] }} / {{ $stats['total_houses'] }}
                        </div>
                    </div>
                    <x-icon name="o-home-modern" class="w-10 h-10 text-info/50" />
                </div>
                <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                    <div>
                        <div class="text-sm text-base-content/60 mb-1">Total Bookings</div>
                        <div class="text-xl font-bold">{{ $stats['total_house_bookings'] }}</div>
                    </div>
                    <x-icon name="o-clipboard-document-list" class="w-10 h-10 text-base-content/30" />
                </div>
            </div>
        </x-card>

        <!-- Room Bookings Card -->
        <x-card shadow title="Room Bookings" separator>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-success/10 rounded-lg">
                    <div>
                        <div class="text-sm text-base-content/60 mb-1">Available Rooms</div>
                        <div class="text-xl font-bold text-success">
                            {{ $stats['available_rooms'] }} / {{ $stats['total_rooms'] }}
                        </div>
                    </div>
                    <x-icon name="o-home" class="w-10 h-10 text-success/50" />
                </div>
                <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                    <div>
                        <div class="text-sm text-base-content/60 mb-1">Total Bookings</div>
                        <div class="text-xl font-bold">{{ $stats['total_room_bookings'] }}</div>
                    </div>
                    <x-icon name="o-clipboard-document-list" class="w-10 h-10 text-base-content/30" />
                </div>
            </div>
        </x-card>

        <!-- Boat Bookings Card -->
        <x-card shadow title="Boat Bookings" separator>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-info/10 rounded-lg">
                    <div>
                        <div class="text-sm text-base-content/60 mb-1">Available Boats</div>
                        <div class="text-xl font-bold text-info">
                            {{ $stats['available_boats'] }} / {{ $stats['total_boats'] }}
                        </div>
                    </div>
                    <x-icon name="o-sparkles" class="w-10 h-10 text-info/50" />
                </div>
                <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                    <div>
                        <div class="text-sm text-base-content/60 mb-1">Total Bookings</div>
                        <div class="text-xl font-bold">{{ $stats['total_boat_bookings'] }}</div>
                    </div>
                    <x-icon name="o-clipboard-document-list" class="w-10 h-10 text-base-content/30" />
                </div>
            </div>
        </x-card>
    </div>

    <!-- Revenue & Customer Section -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5 mb-6">
        <!-- Total Revenue Card -->
        <x-card shadow title="Total Revenue" separator>
            <div class="flex items-center justify-between p-4 bg-secondary/10 rounded-lg">
                <div>
                    <div class="text-sm text-base-content/60 mb-1">All Time</div>
                    <div class="text-xl font-bold text-secondary">
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
                    <div class="text-xl font-bold text-warning">
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
                    <div class="text-xl font-bold text-primary">
                        {{ currency_format($stats['room_revenue']) }}
                    </div>
                </div>
                <x-icon name="o-building-office" class="w-12 h-12 text-primary/50" />
            </div>
        </x-card>

        <!-- Boat Revenue Card -->
        <x-card shadow title="Boat Revenue" separator>
            <div class="flex items-center justify-between p-4 bg-success/10 rounded-lg">
                <div>
                    <div class="text-sm text-base-content/60 mb-1">Boats</div>
                    <div class="text-xl font-bold text-success">
                        {{ currency_format($stats['boat_revenue']) }}
                    </div>
                </div>
                <x-icon name="o-sparkles" class="w-12 h-12 text-success/50" />
            </div>
        </x-card>

        <!-- Active Customers Card -->
        <x-card shadow title="Active Customers" separator>
            <div class="flex items-center justify-between p-4 bg-accent/10 rounded-lg">
                <div>
                    <div class="text-sm text-base-content/60 mb-1">Total</div>
                    <div class="text-3xl font-bold text-accent">
                        {{ $stats['active_customers'] }}
                    </div>
                </div>
                <x-icon name="o-users" class="w-12 h-12 text-accent/50" />
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
