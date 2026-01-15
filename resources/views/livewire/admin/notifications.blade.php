<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $filter = 'all'; // all, unread, read
    public $search = '';
    public $perPage = 10;

    public function mount()
    {
        //
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
            $this->dispatch('notification-updated');
        }
    }

    public function markAsUnread($id)
    {
        $notification = auth()->user()->notifications()->find($id);
        if ($notification) {
            $notification->update(['read_at' => null]);
            $this->dispatch('notification-updated');
        }
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->dispatch('notification-updated');
    }

    public function deleteNotification($id)
    {
        $notification = auth()->user()->notifications()->find($id);
        if ($notification) {
            $notification->delete();
            $this->dispatch('notification-updated');
        }
    }

    public function with(): array
    {
        $query = auth()->user()->notifications();

        if ($this->filter === 'unread') {
            $query = auth()->user()->unreadNotifications();
        } elseif ($this->filter === 'read') {
            $query = auth()->user()->readNotifications();
        }

        // Search by booking ID or booking number
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('data->booking_number', 'like', '%' . $this->search . '%')
                    ->orWhere('data->booking_id', 'like', '%' . $this->search . '%')
                    ->orWhere('data->customer_name', 'like', '%' . $this->search . '%')
                    ->orWhere('data->message', 'like', '%' . $this->search . '%');
            });
        }

        return [
            'notifications' => $query->paginate($this->perPage),
            'unreadCount' => auth()->user()->unreadNotifications->count(),
        ];
    }
}; ?>

<div>
    <x-header title="Notifications" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search by booking ID, customer..." wire:model.live.debounce.300ms="search"
                icon="o-magnifying-glass" clearable />
        </x-slot:middle>

        <x-slot:actions>
            <x-select wire:model.live="perPage" :options="[
                ['id' => 10, 'name' => '10 per page'],
                ['id' => 25, 'name' => '25 per page'],
                ['id' => 50, 'name' => '50 per page'],
                ['id' => 100, 'name' => '100 per page'],
            ]" class="select-sm" />
            @if ($unreadCount > 0)
                <x-button label="Mark All as Read ({{ $unreadCount }})" icon="o-check-circle" class="btn-primary btn-sm"
                    wire:click="markAllAsRead" />
            @endif
        </x-slot:actions>
    </x-header>

    {{-- Filter Tabs --}}
    <div class="mb-4 sm:mb-6">
        <div class="tabs tabs-boxed bg-base-200 p-1 inline-flex gap-1 flex-wrap sm:flex-nowrap w-full sm:w-auto">
            <button wire:click="$set('filter', 'all')"
                class="tab transition-all text-xs sm:text-sm {{ $filter === 'all' ? 'tab-active bg-primary text-primary-content' : 'hover:bg-base-300' }} flex-1 sm:flex-initial">
                <x-icon name="o-inbox" class="w-3 h-3 sm:w-4 sm:h-4 mr-1" />
                All
            </button>
            <button wire:click="$set('filter', 'unread')"
                class="tab transition-all text-xs sm:text-sm {{ $filter === 'unread' ? 'tab-active bg-primary text-primary-content' : 'hover:bg-base-300' }} flex-1 sm:flex-initial">
                <x-icon name="o-bell-alert" class="w-3 h-3 sm:w-4 sm:h-4 mr-1" />
                Unread
                @if ($unreadCount > 0)
                    <span class="badge badge-error badge-xs sm:badge-sm ml-1 sm:ml-2">{{ $unreadCount }}</span>
                @endif
            </button>
            <button wire:click="$set('filter', 'read')"
                class="tab transition-all text-xs sm:text-sm {{ $filter === 'read' ? 'tab-active bg-primary text-primary-content' : 'hover:bg-base-300' }} flex-1 sm:flex-initial">
                <x-icon name="o-check-circle" class="w-3 h-3 sm:w-4 sm:h-4 mr-1" />
                Read
            </button>
        </div>
    </div>

    {{-- Notifications List --}}
    <div class="space-y-2 sm:space-y-4">
        @forelse($notifications as $notification)
            <div
                class="group relative {{ $notification->read_at ? 'opacity-80 hover:opacity-100' : '' }} transition-all">
                <div
                    class="card bg-base-100 border border-base-300 shadow-sm hover:shadow-md transition-all {{ !$notification->read_at ? 'border-l-4 border-l-primary' : '' }}">
                    <div class="card-body p-3 sm:p-5">
                        <div class="flex items-start gap-2 sm:gap-4">
                            {{-- Icon --}}
                            <div class="flex-shrink-0">
                                @php
                                    $iconName = $notification->data['icon'] ?? 'o-bell';
                                    $bgColor = !$notification->read_at ? 'bg-primary/10' : 'bg-base-200';
                                    $iconColor = !$notification->read_at ? 'text-primary' : 'text-base-content/50';
                                @endphp
                                <div
                                    class="w-10 h-10 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl {{ $bgColor }} flex items-center justify-center transition-colors">
                                    <x-icon :name="$iconName" class="w-5 h-5 sm:w-7 sm:h-7 {{ $iconColor }}" />
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="mb-1 sm:mb-2">
                                    <h3
                                        class="font-bold text-sm sm:text-lg {{ $notification->read_at ? 'text-base-content/70' : 'text-base-content' }} leading-tight">
                                        {{ $notification->data['title'] ?? 'New Notification' }}
                                    </h3>
                                </div>

                                <p class="text-xs sm:text-sm text-base-content/80 mb-2 sm:mb-3 leading-relaxed">
                                    {{ $notification->data['message'] ?? '' }}
                                </p>

                                {{-- Meta Info --}}
                                @if (isset($notification->data['booking_number']) ||
                                        isset($notification->data['customer_name']) ||
                                        isset($notification->data['total_amount']))
                                    <div class="flex flex-wrap gap-1.5 sm:gap-3 mb-2 sm:mb-3">
                                        @if (isset($notification->data['booking_number']))
                                            <div
                                                class="badge badge-sm sm:badge-lg bg-base-200 text-base-content border-0 gap-1">
                                                <x-icon name="o-document-text" class="w-2.5 h-2.5 sm:w-3.5 sm:h-3.5" />
                                                <span
                                                    class="text-[10px] sm:text-xs">{{ $notification->data['booking_number'] }}</span>
                                            </div>
                                        @endif

                                        @if (isset($notification->data['customer_name']))
                                            <div
                                                class="badge badge-sm sm:badge-lg bg-base-200 text-base-content border-0 gap-1">
                                                <x-icon name="o-user" class="w-2.5 h-2.5 sm:w-3.5 sm:h-3.5" />
                                                <span
                                                    class="text-[10px] sm:text-xs">{{ $notification->data['customer_name'] }}</span>
                                            </div>
                                        @endif

                                        @if (isset($notification->data['total_amount']))
                                            <div
                                                class="badge badge-sm sm:badge-lg bg-success/10 text-success border-0 gap-1">
                                                <x-icon name="o-currency-dollar"
                                                    class="w-2.5 h-2.5 sm:w-3.5 sm:h-3.5" />
                                                <span
                                                    class="text-[10px] sm:text-xs">${{ number_format($notification->data['total_amount'], 2) }}</span>
                                            </div>
                                        @endif

                                        @if (isset($notification->data['check_in']))
                                            <div
                                                class="badge badge-sm sm:badge-lg bg-base-200 text-base-content border-0 gap-1">
                                                <x-icon name="o-calendar" class="w-2.5 h-2.5 sm:w-3.5 sm:h-3.5" />
                                                <span
                                                    class="text-[10px] sm:text-xs">{{ \Carbon\Carbon::parse($notification->data['check_in'])->format('M d, Y') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                {{-- Timestamp --}}
                                <div class="flex items-center gap-1 text-[10px] sm:text-xs text-base-content/50">
                                    <x-icon name="o-clock" class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                                    {{ $notification->created_at->diffForHumans() }}
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div
                                class="flex-shrink-0 flex flex-col sm:flex-row items-end sm:items-start gap-1.5 sm:gap-2">
                                {{-- Unread Badge (if no View button) --}}
                                @if (!$notification->read_at && !isset($notification->data['url']))
                                    <div
                                        class="badge badge-primary gap-1 px-2 py-2 sm:px-3 sm:py-3 font-semibold text-[10px] sm:text-xs">
                                        <x-icon name="o-sparkles" class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                                        New
                                    </div>
                                @endif

                                @if (isset($notification->data['url']))
                                    <a href="{{ $notification->data['url'] }}"
                                        wire:click="markAsRead('{{ $notification->id }}')"
                                        class="btn btn-xs sm:btn-sm btn-primary gap-1 min-w-[60px] sm:min-w-[80px] hover:shadow-lg transition-all text-[10px] sm:text-xs">
                                        <x-icon name="o-eye" class="w-3 h-3 sm:w-4 sm:h-4" />
                                        View
                                    </a>
                                @endif

                                <div class="dropdown dropdown-end">
                                    <label tabindex="0"
                                        class="btn btn-xs sm:btn-sm btn-square btn-ghost border border-base-300 hover:bg-base-200 hover:border-base-400 transition-all">
                                        <x-icon name="o-ellipsis-vertical" class="w-3 h-3 sm:w-4 sm:h-4" />
                                    </label>
                                    <ul tabindex="0"
                                        class="dropdown-content z-[1] menu p-2 shadow-xl bg-base-100 border border-base-300 rounded-xl w-48 sm:w-56 gap-1">
                                        @if (!$notification->read_at)
                                            <li>
                                                <a wire:click="markAsRead('{{ $notification->id }}')"
                                                    class="hover:bg-primary/10 hover:text-primary rounded-lg gap-2 py-2 text-xs sm:text-sm">
                                                    <x-icon name="o-check" class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                                    Mark as Read
                                                </a>
                                            </li>
                                        @else
                                            <li>
                                                <a wire:click="markAsUnread('{{ $notification->id }}')"
                                                    class="hover:bg-warning/10 hover:text-warning rounded-lg gap-2 py-2 text-xs sm:text-sm">
                                                    <x-icon name="o-arrow-uturn-left"
                                                        class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                                    Mark as Unread
                                                </a>
                                            </li>
                                        @endif
                                        <li>
                                            <a wire:click="deleteNotification('{{ $notification->id }}')"
                                                wire:confirm="Are you sure you want to delete this notification?"
                                                class="hover:bg-error/10 hover:text-error rounded-lg gap-2 py-2 text-xs sm:text-sm">
                                                <x-icon name="o-trash" class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                                Delete
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card bg-base-100 border border-base-300 shadow-sm">
                <div class="card-body text-center py-16">
                    <div class="w-24 h-24 rounded-full bg-base-200 flex items-center justify-center mx-auto mb-4">
                        <x-icon name="o-bell-slash" class="w-12 h-12 text-base-content/30" />
                    </div>
                    <h3 class="text-xl font-bold text-base-content mb-2">No Notifications</h3>
                    <p class="text-sm text-base-content/60">
                        @if ($filter === 'unread')
                            You don't have any unread notifications.
                        @elseif($filter === 'read')
                            You don't have any read notifications.
                        @else
                            You don't have any notifications yet.
                        @endif
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($notifications->hasPages())
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
