<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use App\Models\UserNotification;

class NotificationBell extends Component
{
    public $notifications = [];
    public $unreadCount = 0;
    public $showDrawer = false;

    protected $listeners = ['refreshNotifications' => '$refresh'];

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        if (auth()->check()) {
            $this->notifications = UserNotification::where('user_id', auth()->id())
                ->latest()
                ->take(10)
                ->get();
            $this->unreadCount = UserNotification::where('user_id', auth()->id())
                ->unread()
                ->count();
        }
    }

    public function markAsRead($notificationId)
    {
        $notification = UserNotification::find($notificationId);
        if ($notification && $notification->user_id === auth()->id()) {
            $notification->markAsRead();
            $this->loadNotifications();
        }
    }

    public function markAllAsRead()
    {
        UserNotification::where('user_id', auth()->id())
            ->unread()
            ->get()
            ->each->markAsRead();
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.customer.notification-bell');
    }
}
