<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Milestone;
use App\Channels\LimsDatabaseChannel;

class PersonalReminderNotification extends Notification
{
    use Queueable;

    public $milestone;

    public function __construct(Milestone $milestone)
    {
        $this->milestone = $milestone;
    }

    public function via(object $notifiable): array
    {
        return [LimsDatabaseChannel::class, 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reminder: ' . $this->milestone->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You have set a personal reminder.')
            ->line('**Title:** ' . $this->milestone->title)
            ->line('**Due Date:** ' . $this->milestone->due_date->format('d M Y, h:i A'))
            ->action('View Notifications', url('/student/notifications'))
            ->line('Make sure to complete it before the deadline!');
    }

    public function toLimsDatabase(object $notifiable): array
    {
        return [
            'title' => 'Reminder: ' . $this->milestone->title,
            'message' => 'Due on ' . $this->milestone->due_date->format('d M Y, h:i A') . '.',
            'type' => 'warning',
        ];
    }
}
