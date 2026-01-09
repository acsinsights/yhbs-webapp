<div wire:poll.15s="loadNotifications" class="dropdown dropdown-bottom dropdown-end">
    <label tabindex="0" class="btn btn-ghost btn-circle relative">
        <x-icon name="o-bell" class="w-6 h-6" />
        @if ($unreadCount > 0)
            <span class="badge badge-error badge-sm absolute -top-1 -right-1 animate-pulse">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </label>
    <div tabindex="0" class="dropdown-content card card-compact z-[1] w-96 p-2 shadow bg-base-100 rounded-box mt-3">
        <div class="card-body">
            <div class="flex items-center justify-between mb-2">
                <h3 class="card-title text-sm">Notifications</h3>
                @if ($unreadCount > 0)
                    <button wire:click="markAllAsRead" class="text-xs link link-primary hover:link-hover">
                        Mark all as read
                    </button>
                @endif
            </div>
            <div class="divider my-0"></div>
            <div class="max-h-96 overflow-y-auto space-y-2">
                @forelse($notifications as $notification)
                    <div wire:key="notification-{{ $notification->id }}"
                        class="block p-3 rounded-lg hover:bg-base-200 transition cursor-pointer {{ is_null($notification->read_at) ? 'bg-primary/5' : '' }}"
                        wire:click="$dispatch('navigateToNotification', { id: '{{ $notification->id }}', url: '{{ $notification->data['url'] ?? '#' }}' })">
                        <div class="flex gap-3">
                            <div class="flex-shrink-0">
                                <x-icon name="{{ $notification->data['icon'] ?? 'o-bell' }}"
                                    class="w-5 h-5 text-primary" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p
                                    class="text-sm font-medium {{ is_null($notification->read_at) ? 'text-base-content' : 'text-base-content/70' }}">
                                    {{ $notification->data['message'] ?? 'New notification' }}
                                </p>
                                <p class="text-xs text-base-content/50 mt-1">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                            @if (is_null($notification->read_at))
                                <div class="flex-shrink-0">
                                    <span class="w-2 h-2 rounded-full bg-primary block animate-pulse"></span>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-base-content/50">
                        <x-icon name="o-bell-slash" class="w-12 h-12 mx-auto mb-2 opacity-30" />
                        <p class="text-sm">No notifications yet</p>
                    </div>
                @endforelse
            </div>
            @if ($notifications->count() >= 10)
                <div class="divider my-2"></div>
                <a href="{{ route('admin.notifications') }}" class="btn btn-sm btn-ghost w-full">
                    View all notifications
                </a>
            @endif
        </div>
    </div>

    @script
        <script>
            $wire.on('navigateToNotification', (data) => {
                // Mark as read first
                $wire.markAsRead(data.id).then((url) => {
                    if (url && url !== '#') {
                        window.location.href = url;
                    }
                });
            });
        </script>
    @endscript
</div>
