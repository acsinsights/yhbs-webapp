<?php

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new class extends Component {
    #[Title('Admin Dashboard')]
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
            'total_users' => User::count(),
            'admin_users' => User::role(RolesEnum::ADMIN->value)->count(),
            'reception_users' => User::role(RolesEnum::RECEPTION->value)->count(),
            'customer_users' => User::role(RolesEnum::CUSTOMER->value)->count(),
            'active_sessions' => DB::table('sessions')
                ->where('last_activity', '>', now()->subHours(24)->timestamp)
                ->count(),
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
    <x-header title="Admin Dashboard" subtitle="Welcome back, {{ $user->name }}" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-badge value="{{ date('M d, Y') }}" class="badge-lg" />
        </x-slot:middle>
    </x-header>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4 mb-6">
        <x-card class="bg-gradient-to-br from-primary to-primary/80 text-primary-content shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-primary-content/70 text-sm font-medium">Total Users</div>
                    <div class="text-3xl font-bold mt-1">{{ $stats['total_users'] }}</div>
                </div>
                <x-icon name="o-users" class="w-12 h-12 text-primary-content/50" />
            </div>
        </x-card>

        <x-card class="bg-gradient-to-br from-success to-success/80 text-success-content shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-success-content/70 text-sm font-medium">Admin Users</div>
                    <div class="text-3xl font-bold mt-1">{{ $stats['admin_users'] }}</div>
                </div>
                <x-icon name="o-shield-check" class="w-12 h-12 text-success-content/50" />
            </div>
        </x-card>

        <x-card class="bg-gradient-to-br from-warning to-warning/80 text-warning-content shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-warning-content/70 text-sm font-medium">Reception Users</div>
                    <div class="text-3xl font-bold mt-1">{{ $stats['reception_users'] }}</div>
                </div>
                <x-icon name="o-user-group" class="w-12 h-12 text-warning-content/50" />
            </div>
        </x-card>

        <x-card class="bg-gradient-to-br from-info to-info/80 text-info-content shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-info-content/70 text-sm font-medium">Active Sessions</div>
                    <div class="text-3xl font-bold mt-1">{{ $stats['active_sessions'] }}</div>
                </div>
                <x-icon name="o-signal" class="w-12 h-12 text-info-content/50" />
            </div>
        </x-card>
    </div>

    <!-- Additional Stats Row -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3 mb-6">
        <x-card shadow class="border-l-4 border-l-primary">
            <div class="flex items-center gap-4">
                <div class="avatar placeholder">
                    <div class="bg-primary text-primary-content rounded-full w-16">
                        <x-icon name="o-user" class="w-8 h-8" />
                    </div>
                </div>
                <div>
                    <div class="text-sm text-base-content/60">Customer Users</div>
                    <div class="text-2xl font-bold">{{ $stats['customer_users'] }}</div>
                </div>
            </div>
        </x-card>

        <x-card shadow class="border-l-4 border-l-success">
            <div class="flex items-center gap-4">
                <div class="avatar placeholder">
                    <div class="bg-success text-success-content rounded-full w-16">
                        <x-icon name="o-chart-bar" class="w-8 h-8" />
                    </div>
                </div>
                <div>
                    <div class="text-sm text-base-content/60">System Status</div>
                    <div class="text-2xl font-bold text-success">Active</div>
                </div>
            </div>
        </x-card>

        <x-card shadow class="border-l-4 border-l-warning">
            <div class="flex items-center gap-4">
                <div class="avatar placeholder">
                    <div class="bg-warning text-warning-content rounded-full w-16">
                        <x-icon name="o-clock" class="w-8 h-8" />
                    </div>
                </div>
                <div>
                    <div class="text-sm text-base-content/60">Last Updated</div>
                    <div class="text-2xl font-bold">{{ now()->format('H:i') }}</div>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Quick Actions -->
    <x-card shadow title="Quick Actions" separator>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <x-button label="Dashboard" icon="o-presentation-chart-bar" link="{{ route('admin.index') }}"
                class="btn-outline" />
            <x-button label="Users" icon="o-users" link="{{ route('users.index') }}" class="btn-outline" />
            <x-button label="Profile" icon="o-user-circle" link="{{ route('admin.index') }}" class="btn-outline" />
            <x-button label="Settings" icon="o-cog-6-tooth" link="{{ route('admin.index') }}" class="btn-outline" />
        </div>
    </x-card>
</div>
