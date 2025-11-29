<?php

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Enums\RolesEnum;
use App\Models\User;

new class extends Component {
    #[Title('Reception Dashboard')]
    public $user;
    public $stats = [];

    public function mount(): void
    {
        $this->user = auth()->user();
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $this->stats = [
            'today_entries' => 0, // You can add actual logic here
            'pending_tasks' => 0, // You can add actual logic here
            'total_customers' => User::role(RolesEnum::CUSTOMER->value)->count(),
            'recent_activity' => 0, // You can add actual logic here
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

<div>
    <x-header title="Reception Dashboard" subtitle="Welcome, {{ $user->name }}" separator progress-indicator>
        <x-slot:middle class="justify-end">
            <x-badge value="{{ date('M d, Y') }}" class="badge-lg" />
        </x-slot:middle>
    </x-header>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4 mb-6">
        <x-card class="text-primary-content shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-primary-content/70 text-sm font-medium">Today's Entries</div>
                    <div class="text-3xl font-bold mt-1">{{ $stats['today_entries'] }}</div>
                </div>
                <x-icon name="o-clipboard-document-check" class="w-12 h-12 text-primary-content/50" />
            </div>
        </x-card>

        <x-card class="text-warning-content shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-warning-content/70 text-sm font-medium">Pending Tasks</div>
                    <div class="text-3xl font-bold mt-1">{{ $stats['pending_tasks'] }}</div>
                </div>
                <x-icon name="o-clock" class="w-12 h-12 text-warning-content/50" />
            </div>
        </x-card>

        <x-card class="text-success-content shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-success-content/70 text-sm font-medium">Total Customers</div>
                    <div class="text-3xl font-bold mt-1">{{ $stats['total_customers'] }}</div>
                </div>
                <x-icon name="o-users" class="w-12 h-12 text-success-content/50" />
            </div>
        </x-card>

        <x-card class="text-info-content shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-info-content/70 text-sm font-medium">Recent Activity</div>
                    <div class="text-3xl font-bold mt-1">{{ $stats['recent_activity'] }}</div>
                </div>
                <x-icon name="o-bolt" class="w-12 h-12 text-info-content/50" />
            </div>
        </x-card>
    </div>

    <!-- Today's Overview -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 mb-6">
        <x-card shadow title="Today's Schedule" separator>
            <div class="space-y-3">
                <div class="flex items-center gap-3 p-3 rounded-lg bg-base-200">
                    <x-icon name="o-calendar" class="w-6 h-6 text-primary" />
                    <div class="flex-1">
                        <div class="font-semibold">No appointments scheduled</div>
                        <div class="text-sm text-base-content/60">Check back later for updates</div>
                    </div>
                </div>
            </div>
        </x-card>

        <x-card shadow title="Quick Tasks" separator>
            <div class="space-y-2">
                <x-button label="New Entry" icon="o-plus-circle" class="btn-primary w-full justify-start" />
                <x-button label="View Customers" icon="o-user-group" class="btn-outline w-full justify-start" />
                <x-button label="Reports" icon="o-document-chart-bar" class="btn-outline w-full justify-start" />
            </div>
        </x-card>
    </div>

    <!-- Recent Activity -->
    <x-card shadow title="Recent Activity" separator>
        <div class="space-y-3">
            <div class="flex items-center gap-3 p-3 rounded-lg bg-base-200">
                <div class="avatar placeholder">
                    <div class="bg-primary text-primary-content rounded-full w-10">
                        <x-icon name="o-bell" class="w-5 h-5" />
                    </div>
                </div>
                <div class="flex-1">
                    <div class="font-medium">System Ready</div>
                    <div class="text-sm text-base-content/60">All systems operational</div>
                </div>
                <div class="text-xs text-base-content/50">{{ now()->format('H:i') }}</div>
            </div>
        </div>
    </x-card>
</div>
