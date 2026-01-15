<?php

use Livewire\Volt\Component;
use App\Models\UserNotification;

new class extends Component {
    public array $notifications = [];
    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        if (auth()->check()) {
            // Get custom UserNotifications
            $userNotifications = UserNotification::where('user_id', auth()->id())
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'type' => $notification->type ?? 'info',
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'data' => $notification->data ?? [],
                        'is_read' => $notification->is_read,
                        'created_at' => $notification->created_at,
                        'source' => 'user_notifications',
                    ];
                });

            // Get Laravel notifications (booking status, etc.)
            $laravelNotifications = auth()
                ->user()
                ->notifications()
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($notification) {
                    $data = $notification->data;
                    return [
                        'id' => $notification->id,
                        'type' => $data['icon'] ?? 'info',
                        'title' => $this->getNotificationTitle($notification->type, $data),
                        'message' => $data['message'] ?? 'Notification',
                        'data' => $data,
                        'is_read' => $notification->read_at !== null,
                        'created_at' => $notification->created_at,
                        'source' => 'notifications',
                    ];
                });

            // Merge and sort by created_at
            $this->notifications = collect($userNotifications)->merge($laravelNotifications)->sortByDesc('created_at')->take(10)->values()->toArray();

            // Count unread from both sources
            $userUnreadCount = UserNotification::where('user_id', auth()->id())
                ->unread()
                ->count();

            $laravelUnreadCount = auth()->user()->unreadNotifications()->count();

            $this->unreadCount = $userUnreadCount + $laravelUnreadCount;
        }
    }

    private function getNotificationTitle(string $type, array $data): string
    {
        // Extract notification type from class name
        if (str_contains($type, 'BookingStatusNotification')) {
            $bookingType = $data['booking_type'] ?? 'Booking';
            $bookingNumber = $data['booking_number'] ?? '';

            if (isset($data['refund_amount'])) {
                return "Cancellation Approved - {$bookingType} #{$bookingNumber}";
            } elseif (isset($data['rejection_reason']) && str_contains($data['message'], 'cancellation')) {
                return "Cancellation Rejected - {$bookingType} #{$bookingNumber}";
            } elseif (str_contains($data['message'], 'reschedule') && str_contains($data['message'], 'approved')) {
                return "Reschedule Approved - {$bookingType} #{$bookingNumber}";
            } elseif (str_contains($data['message'], 'reschedule') && str_contains($data['message'], 'declined')) {
                return "Reschedule Rejected - {$bookingType} #{$bookingNumber}";
            }

            return "{$bookingType} Booking #{$bookingNumber}";
        }

        return 'Notification';
    }

    public function markAsRead(string $notificationId, string $source = 'user_notifications'): void
    {
        if ($source === 'user_notifications') {
            $notification = UserNotification::find($notificationId);
            if ($notification && $notification->user_id === auth()->id()) {
                $notification->markAsRead();
            }
        } else {
            // Laravel notifications
            $notification = auth()->user()->notifications()->where('id', $notificationId)->first();
            if ($notification) {
                $notification->markAsRead();
            }
        }
        $this->loadNotifications();
    }

    public function markAllAsRead(): void
    {
        // Mark custom UserNotifications as read
        UserNotification::where('user_id', auth()->id())
            ->unread()
            ->get()
            ->each->markAsRead();

        // Mark Laravel notifications as read
        auth()
            ->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        $this->loadNotifications();
    }
}; ?>

<div x-data="{ open: false }" class="notification-bell-wrapper">
    <!-- Bell Icon -->
    <div style="position: relative; cursor: pointer;" @click="open = !open">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M18 8C18 6.4087 17.3679 4.88258 16.2426 3.75736C15.1174 2.63214 13.5913 2 12 2C10.4087 2 8.88258 2.63214 7.75736 3.75736C6.63214 4.88258 6 6.4087 6 8C6 15 3 17 3 17H21C21 17 18 15 18 8Z"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            <path
                d="M13.73 21C13.5542 21.3031 13.3019 21.5547 12.9982 21.7295C12.6946 21.9044 12.3504 21.9965 12 21.9965C11.6496 21.9965 11.3054 21.9044 11.0018 21.7295C10.6982 21.5547 10.4458 21.3031 10.27 21"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        @if ($unreadCount > 0)
            <span
                style="position: absolute; top: -8px; right: -8px; background: #dc3545; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold;">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </div>

    <!-- Notification Drawer -->
    <div x-cloak x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        style="position: fixed; top: 0; right: 0; width: 400px; max-width: 90vw; height: 100vh; background: white; box-shadow: -5px 0 20px rgba(0,0,0,0.1); z-index: 9999; overflow-y: auto;">

        <!-- Header -->
        <div
            style="padding: 20px; border-bottom: 2px solid #f0f0f0; position: sticky; top: 0; background: white; z-index: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 20px; font-weight: 700;">Notifications</h3>
                <button @click="open = false"
                    style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">
                    &times;
                </button>
            </div>
            @if ($unreadCount > 0)
                <button wire:click="markAllAsRead"
                    style="margin-top: 10px; padding: 6px 12px; background: #667eea; color: white; border: none; border-radius: 5px; font-size: 13px; cursor: pointer;">
                    Mark All as Read
                </button>
            @endif
        </div>

        <!-- Notifications List -->
        <div style="padding: 15px;">
            @forelse ($notifications as $notification)
                <div wire:key="notification-{{ $notification['id'] }}"
                    style="padding: 15px; margin-bottom: 10px; background: {{ $notification['is_read'] ? '#f9f9f9' : '#fff3cd' }}; border-radius: 8px; border-left: 4px solid {{ $notification['is_read'] ? '#e0e0e0' : '#667eea' }};">
                    <div style="display: flex; justify-content: between; align-items: start;">
                        <div style="flex: 1;">
                            <h4 style="margin: 0 0 8px 0; font-size: 15px; font-weight: 600; color: #1a1a1a;">
                                {{ $notification['title'] }}
                            </h4>
                            <p style="margin: 0 0 8px 0; font-size: 13px; color: #555; line-height: 1.5;">
                                {{ $notification['message'] }}
                            </p>
                            @if (isset($notification['data']['rejection_reason']) && $notification['data']['rejection_reason'])
                                <p
                                    style="margin: 0 0 8px 0; font-size: 12px; color: #666; font-style: italic; padding-left: 10px; border-left: 2px solid #ddd;">
                                    <strong>Reason:</strong> {{ $notification['data']['rejection_reason'] }}
                                </p>
                            @endif
                            @if (isset($notification['data']['refund_amount']) && $notification['data']['refund_amount'] > 0)
                                <p style="margin: 0 0 8px 0; font-size: 12px; color: #28a745; font-weight: 600;">
                                    <strong>Refund:</strong>
                                    {{ currency_format($notification['data']['refund_amount']) }}
                                </p>
                            @endif
                            @if (isset($notification['data']['notes']) && $notification['data']['notes'])
                                <p
                                    style="margin: 0 0 8px 0; font-size: 12px; color: #666; font-style: italic; padding-left: 10px; border-left: 2px solid #ddd;">
                                    <strong>Note:</strong> {{ $notification['data']['notes'] }}
                                </p>
                            @endif
                            <p style="margin: 0; font-size: 11px; color: #999;">
                                {{ \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() }}
                            </p>
                        </div>
                        @if (!$notification['is_read'])
                            <button wire:key="mark-read-{{ $notification['id'] }}"
                                wire:click="markAsRead('{{ $notification['id'] }}', '{{ $notification['source'] }}')"
                                style="background: #667eea; color: white; border: none; padding: 5px 10px; border-radius: 4px; font-size: 11px; cursor: pointer; white-space: nowrap; margin-left: 10px;">
                                Mark Read
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 40px 20px; color: #999;">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        style="margin: 0 auto 15px;">
                        <path
                            d="M18 8C18 6.4087 17.3679 4.88258 16.2426 3.75736C15.1174 2.63214 13.5913 2 12 2C10.4087 2 8.88258 2.63214 7.75736 3.75736C6.63214 4.88258 6 6.4087 6 8C6 15 3 17 3 17H21C21 17 18 15 18 8Z"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <p style="font-size: 15px; font-weight: 500;">No notifications yet</p>
                    <p style="font-size: 13px;">We'll notify you when something important happens</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
