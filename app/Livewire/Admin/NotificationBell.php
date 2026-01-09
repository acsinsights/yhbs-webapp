<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class NotificationBell extends Component
{
    public $unreadCount = 0;
    public $notifications = [];

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        if (auth()->check()) {
            $this->unreadCount = auth()->user()->unreadNotifications->count();
            $this->notifications = auth()->user()->notifications()->take(10)->get();
        }
    }

    public function markAsRead($notificationId)
    {
        $notification = auth()->user()->notifications()->find($notificationId);
        if ($notification) {
            $notification->markAsRead();
            $this->loadNotifications();

            // Return URL to redirect
            return $notification->data['url'] ?? '#';
        }
        return '#';
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->loadNotifications();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'All notifications marked as read'
        ]);
    }

    public function render()
    {
        return view('livewire.admin.notification-bell');
    }
}
