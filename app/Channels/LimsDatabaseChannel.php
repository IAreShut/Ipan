<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use App\Models\Notification as AppNotification;

class LimsDatabaseChannel
{
    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification)
    {
        if (method_exists($notification, 'toLimsDatabase')) {
            $data = $notification->toLimsDatabase($notifiable);
            
            AppNotification::create([
                'user_id' => $notifiable->id,
                'title' => $data['title'],
                'message' => $data['message'],
                'type' => $data['type'] ?? 'info',
                'is_read' => false,
            ]);
        }
    }
}
