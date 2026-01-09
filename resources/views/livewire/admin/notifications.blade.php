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
    <div class="mb-6">
        <div class="tabs tabs-boxed bg-base-200 p-1 inline-flex gap-1">
            <button wire:click="$set('filter', 'all')"
                class="tab transition-all {{ $filter === 'all' ? 'tab-active bg-primary text-primary-content' : 'hover:bg-base-300' }}">
                <x-icon name="o-inbox" class="w-4 h-4 mr-1.5" />
                All Notifications
            </button>
            <button wire:click="$set('filter', 'unread')"
                class="tab transition-all {{ $filter === 'unread' ? 'tab-active bg-primary text-primary-content' : 'hover:bg-base-300' }}">
                <x-icon name="o-bell-alert" class="w-4 h-4 mr-1.5" />
                Unread
                @if ($unreadCount > 0)
                    <span class="badge badge-error badge-sm ml-2">{{ $unreadCount }}</span>
                @endif
            </button>
            <button wire:click="$set('filter', 'read')"
                class="tab transition-all {{ $filter === 'read' ? 'tab-active bg-primary text-primary-content' : 'hover:bg-base-300' }}">
                <x-icon name="o-check-circle" class="w-4 h-4 mr-1.5" />
                Read
            </button>
        </div>
    </div>

    {{-- Notifications List --}}
    <div class="space-y-4">
        @forelse($notifications as $notification)
            <div
                class="group relative {{ $notification->read_at ? 'opacity-80 hover:opacity-100' : '' }} transition-all">
                <div
                    class="card bg-base-100 border border-base-300 shadow-sm hover:shadow-md transition-all {{ !$notification->read_at ? 'border-l-4 border-l-primary' : '' }}">
                    <div class="card-body p-5">
                        <div class="flex items-start gap-4">
                            {{-- Icon --}}
                            <div class="flex-shrink-0">
                                @php
                                    $iconName = $notification->data['icon'] ?? 'o-bell';
                                    $bgColor = !$notification->read_at ? 'bg-primary/10' : 'bg-base-200';
                                    $iconColor = !$notification->read_at ? 'text-primary' : 'text-base-content/50';
                                @endphp
                                <div
                                    class="w-14 h-14 rounded-2xl {{ $bgColor }} flex items-center justify-center transition-colors">
                                    <x-icon :name="$iconName" class="w-7 h-7 {{ $iconColor }}" />
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="mb-2">
                                    <h3
                                        class="font-bold text-lg {{ $notification->read_at ? 'text-base-content/70' : 'text-base-content' }}">
                                        {{ $notification->data['title'] ?? 'New Notification' }}
                                    </h3>
                                </div>

                                <p class="text-sm text-base-content/80 mb-3 leading-relaxed">
                                    {{ $notification->data['message'] ?? '' }}
                                </p>

                                {{-- Meta Info --}}
                                @if (isset($notification->data['booking_number']) ||
                                        isset($notification->data['customer_name']) ||
                                        isset($notification->data['total_amount']))
                                    <div class="flex flex-wrap gap-3 mb-3">
                                        @if (isset($notification->data['booking_number']))
                                            <div class="badge badge-lg bg-base-200 text-base-content border-0 gap-1.5">
                                                <x-icon name="o-document-text" class="w-3.5 h-3.5" />
                                                {{ $notification->data['booking_number'] }}
                                            </div>
                                        @endif

                                        @if (isset($notification->data['customer_name']))
                                            <div class="badge badge-lg bg-base-200 text-base-content border-0 gap-1.5">
                                                <x-icon name="o-user" class="w-3.5 h-3.5" />
                                                {{ $notification->data['customer_name'] }}
                                            </div>
                                        @endif

                                        @if (isset($notification->data['total_amount']))
                                            <div class="badge badge-lg bg-success/10 text-success border-0 gap-1.5">
                                                <x-icon name="o-currency-dollar" class="w-3.5 h-3.5" />
                                                ${{ number_format($notification->data['total_amount'], 2) }}
                                            </div>
                                        @endif

                                        @if (isset($notification->data['check_in']))
                                            <div class="badge badge-lg bg-base-200 text-base-content border-0 gap-1.5">
                                                <x-icon name="o-calendar" class="w-3.5 h-3.5" />
                                                {{ \Carbon\Carbon::parse($notification->data['check_in'])->format('M d, Y') }}
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                {{-- Timestamp --}}
                                <div class="flex items-center gap-1.5 text-xs text-base-content/50">
                                    <x-icon name="o-clock" class="w-3.5 h-3.5" />
                                    {{ $notification->created_at->diffForHumans() }}
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex-shrink-0 flex items-start gap-2">
                                {{-- Unread Badge (if no View button) --}}
                                @if (!$notification->read_at && !isset($notification->data['url']))
                                    <div class="badge badge-primary gap-1.5 px-3 py-3 font-semibold">
                                        <x-icon name="o-sparkles" class="w-3.5 h-3.5" />
                                        New
                                    </div>
                                @endif

                                @if (isset($notification->data['url']))
                                    <a href="{{ $notification->data['url'] }}"
                                        wire:click="markAsRead('{{ $notification->id }}')"
                                        class="btn btn-sm btn-primary gap-1.5 min-w-[80px] hover:shadow-lg transition-all">
                                        <x-icon name="o-eye" class="w-4 h-4" />
                                        View
                                    </a>
                                @endif

                                <div class="dropdown dropdown-end">
                                    <label tabindex="0"
                                        class="btn btn-sm btn-square btn-ghost border border-base-300 hover:bg-base-200 hover:border-base-400 transition-all">
                                        <x-icon name="o-ellipsis-vertical" class="w-4 h-4" />
                                    </label>
                                    <ul tabindex="0"
                                        class="dropdown-content z-[1] menu p-2 shadow-xl bg-base-100 border border-base-300 rounded-xl w-56 gap-1">
                                        @if (!$notification->read_at)
                                            <li>
                                                <a wire:click="markAsRead('{{ $notification->id }}')"
                                                    class="hover:bg-primary/10 hover:text-primary rounded-lg gap-2 py-2.5">
                                                    <x-icon name="o-check" class="w-4 h-4" />
                                                    Mark as Read
                                                </a>
                                            </li>
                                        @else
                                            <li>
                                                <a wire:click="markAsUnread('{{ $notification->id }}')"
                                                    class="hover:bg-warning/10 hover:text-warning rounded-lg gap-2 py-2.5">
                                                    <x-icon name="o-arrow-uturn-left" class="w-4 h-4" />
                                                    Mark as Unread
                                                </a>
                                            </li>
                                        @endif
                                        <li>
                                            <a wire:click="deleteNotification('{{ $notification->id }}')"
                                                wire:confirm="Are you sure you want to delete this notification?"
                                                class="hover:bg-error/10 hover:text-error rounded-lg gap-2 py-2.5">
                                                <x-icon name="o-trash" class="w-4 h-4" />
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
