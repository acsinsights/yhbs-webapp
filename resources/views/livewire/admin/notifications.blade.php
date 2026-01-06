<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $filter = 'all'; // all, unread, read

    public function mount()
    {
        //
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

        return [
            'notifications' => $query->paginate(20),
            'unreadCount' => auth()->user()->unreadNotifications->count(),
        ];
    }
}; ?>

<div>
    <x-header title="Notifications" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Filter..." wire:model.live="filter" icon="o-funnel" />
        </x-slot:middle>

        <x-slot:actions>
            @if ($unreadCount > 0)
                <x-button label="Mark All as Read ({{ $unreadCount }})" icon="o-check-circle" class="btn-primary btn-sm"
                    wire:click="markAllAsRead" />
            @endif
        </x-slot:actions>
    </x-header>

    {{-- Filter Tabs --}}
    <div class="mb-4">
        <div class="tabs tabs-boxed inline-flex">
            <button wire:click="$set('filter', 'all')" class="tab {{ $filter === 'all' ? 'tab-active' : '' }}">
                All Notifications
            </button>
            <button wire:click="$set('filter', 'unread')" class="tab {{ $filter === 'unread' ? 'tab-active' : '' }}">
                Unread
                @if ($unreadCount > 0)
                    <span class="badge badge-error badge-sm ml-1">{{ $unreadCount }}</span>
                @endif
            </button>
            <button wire:click="$set('filter', 'read')" class="tab {{ $filter === 'read' ? 'tab-active' : '' }}">
                Read
            </button>
        </div>
    </div>

    {{-- Notifications List --}}
    <div class="space-y-3">
        @forelse($notifications as $notification)
            <div
                class="card bg-base-100 shadow-sm {{ $notification->read_at ? 'opacity-75' : 'border-l-4 border-primary' }}">
                <div class="card-body p-4">
                    <div class="flex items-start gap-4">
                        {{-- Icon --}}
                        <div class="flex-shrink-0">
                            @php
                                $iconName = $notification->data['icon'] ?? 'o-bell';
                                $iconClass = $notification->read_at ? 'text-gray-400' : 'text-primary';
                            @endphp
                            <div class="w-12 h-12 rounded-full bg-base-200 flex items-center justify-center">
                                <x-icon :name="$iconName" class="w-6 h-6 {{ $iconClass }}" />
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <h3
                                    class="font-semibold text-base {{ $notification->read_at ? 'text-gray-600' : 'text-gray-900' }}">
                                    {{ $notification->data['title'] ?? 'New Notification' }}
                                </h3>

                                {{-- Unread Badge --}}
                                @if (!$notification->read_at)
                                    <span class="badge badge-primary badge-sm">New</span>
                                @endif
                            </div>

                            <p class="text-sm text-gray-600 mb-2">
                                {{ $notification->data['message'] ?? '' }}
                            </p>

                            {{-- Meta Info --}}
                            @if (isset($notification->data['booking_number']) ||
                                    isset($notification->data['customer_name']) ||
                                    isset($notification->data['total_amount']))
                                <div class="flex flex-wrap gap-3 text-xs text-gray-500 mb-3">
                                    @if (isset($notification->data['booking_number']))
                                        <span>
                                            <x-icon name="o-document-text" class="w-3 h-3 inline" />
                                            {{ $notification->data['booking_number'] }}
                                        </span>
                                    @endif

                                    @if (isset($notification->data['customer_name']))
                                        <span>
                                            <x-icon name="o-user" class="w-3 h-3 inline" />
                                            {{ $notification->data['customer_name'] }}
                                        </span>
                                    @endif

                                    @if (isset($notification->data['total_amount']))
                                        <span>
                                            <x-icon name="o-currency-dollar" class="w-3 h-3 inline" />
                                            ${{ number_format($notification->data['total_amount'], 2) }}
                                        </span>
                                    @endif

                                    @if (isset($notification->data['check_in']))
                                        <span>
                                            <x-icon name="o-calendar" class="w-3 h-3 inline" />
                                            {{ \Carbon\Carbon::parse($notification->data['check_in'])->format('M d, Y') }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            {{-- Timestamp --}}
                            <div class="text-xs text-gray-400">
                                {{ $notification->created_at->diffForHumans() }}
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex-shrink-0 flex gap-2">
                            @if (isset($notification->data['url']))
                                <a href="{{ $notification->data['url'] }}"
                                    wire:click="markAsRead('{{ $notification->id }}')" class="btn btn-sm btn-primary">
                                    <x-icon name="o-eye" class="w-4 h-4" />
                                    View
                                </a>
                            @endif

                            <div class="dropdown dropdown-end">
                                <label tabindex="0" class="btn btn-sm btn-ghost btn-circle">
                                    <x-icon name="o-ellipsis-vertical" class="w-4 h-4" />
                                </label>
                                <ul tabindex="0"
                                    class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                                    @if (!$notification->read_at)
                                        <li>
                                            <a wire:click="markAsRead('{{ $notification->id }}')">
                                                <x-icon name="o-check" class="w-4 h-4" />
                                                Mark as Read
                                            </a>
                                        </li>
                                    @else
                                        <li>
                                            <a wire:click="markAsUnread('{{ $notification->id }}')">
                                                <x-icon name="o-arrow-uturn-left" class="w-4 h-4" />
                                                Mark as Unread
                                            </a>
                                        </li>
                                    @endif
                                    <li>
                                        <a wire:click="deleteNotification('{{ $notification->id }}')"
                                            wire:confirm="Are you sure you want to delete this notification?"
                                            class="text-error">
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
        @empty
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body text-center py-12">
                    <x-icon name="o-bell-slash" class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                    <h3 class="text-lg font-semibold text-gray-600 mb-2">No Notifications</h3>
                    <p class="text-sm text-gray-500">
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
