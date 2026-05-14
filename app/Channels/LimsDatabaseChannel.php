<?php

namespace App\Channels;

use App\Models\Notification as AppNotification;
use Illuminate\Notifications\Notification;

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
