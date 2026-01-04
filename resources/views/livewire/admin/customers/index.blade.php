<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\User;
use Mary\Traits\Toast;

new class extends Component {
    use WithPagination, Toast;

    #[Title('All Customers')]
    public string $search = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    // Headers for the table
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#', 'sortable' => true], ['key' => 'name', 'label' => 'Name', 'sortable' => true], ['key' => 'email', 'label' => 'Email', 'sortable' => true], ['key' => 'phone', 'label' => 'Phone'], ['key' => 'bookings_count', 'label' => 'Total Bookings', 'sortable' => true], ['key' => 'created_at', 'label' => 'Registered On', 'sortable' => true], ['key' => 'actions', 'label' => 'Actions']];
    }

    public function customers()
    {
        return User::role('customer')
            ->withCount('bookings')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%");
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function sortByColumn(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function clear(): void
    {
        $this->reset();
        $this->success('Filters cleared successfully!');
    }

    public function with(): array
    {
        return [
            'customers' => $this->customers(),
            'headers' => $this->headers(),
        ];
    }
}; ?>

<div>
    {{-- Header Section --}}
    <x-header title="All Customers" separator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search customers..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Clear Filters" icon="o-x-mark" wire:click="clear" spinner />
        </x-slot:actions>
    </x-header>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 gap-4 mb-6 lg:grid-cols-4">
        <x-stat title="Total Customers" value="{{ User::role('customer')->count() }}" icon="o-users"
            color="text-primary" />

        <x-stat title="Active This Month"
            value="{{ User::role('customer')->whereHas('bookings', function ($q) {
                    $q->whereMonth('created_at', now()->month);
                })->count() }}"
            icon="o-user-group" color="text-success" />

        <x-stat title="New This Month"
            value="{{ User::role('customer')->whereMonth('created_at', now()->month)->count() }}" icon="o-user-plus"
            color="text-info" />

        <x-stat title="With Bookings" value="{{ User::role('customer')->has('bookings')->count() }}"
            icon="o-calendar-days" color="text-warning" />
    </div>

    {{-- Customers Table --}}
    <x-card>
        <x-table :headers="$headers" :rows="$customers" with-pagination per-page="perPage" :per-page-values="[10, 25, 50, 100]">

            @scope('cell_name', $customer)
                <div class="flex items-center gap-3">
                    @if ($customer->avatar)
                        <div class="avatar">
                            <div class="w-10 rounded-md">
                                <img src="{{ asset('storage/' . $customer->avatar) }}" alt="{{ $customer->name }}" />
                            </div>
                        </div>
                    @else
                        <div class="avatar placeholder">
                            <div class="bg-primary text-primary-content rounded-md w-10 flex items-center justify-center">
                                <span class="text-sm font-bold">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                            </div>
                        </div>
                    @endif
                    <div>
                        <div class="font-semibold">{{ $customer->name }}</div>
                        @if ($customer->email_verified_at)
                            <x-badge value="Verified" class="badge-xs badge-success" />
                        @else
                            <x-badge value="Not Verified" class="badge-xs badge-warning" />
                        @endif
                    </div>
                </div>
            @endscope

            @scope('cell_email', $customer)
                <div class="flex items-center gap-2">
                    <x-icon name="o-envelope" class="w-4 h-4 text-gray-400" />
                    <span>{{ $customer->email }}</span>
                </div>
            @endscope

            @scope('cell_phone', $customer)
                @if ($customer->phone)
                    <div class="flex items-center gap-2">
                        <x-icon name="o-phone" class="w-4 h-4 text-gray-400" />
                        <span>{{ $customer->phone }}</span>
                    </div>
                @else
                    <span class="text-gray-400">-</span>
                @endif
            @endscope

            @scope('cell_bookings_count', $customer)
                <x-badge value="{{ $customer->bookings_count }}"
                    class="{{ $customer->bookings_count > 0 ? 'badge-primary' : 'badge-ghost' }}" />
            @endscope

            @scope('cell_created_at', $customer)
                <div class="text-sm">
                    {{ $customer->created_at->format('d M, Y') }}
                    <div class="text-xs text-gray-400">{{ $customer->created_at->diffForHumans() }}</div>
                </div>
            @endscope

            @scope('cell_actions', $customer)
                <div class="flex gap-2">
                    <x-button icon="o-eye" link="{{ route('admin.customers.show', $customer->id) }}"
                        class="btn-sm btn-ghost" tooltip="View Details" />
                </div>
            @endscope
        </x-table>
    </x-card>
</div>
