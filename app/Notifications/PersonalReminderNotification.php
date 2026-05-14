<?php

namespace App\Notifications;

use App\Channels\LimsDatabaseChannel;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PersonalReminderNotification extends Notification
{
    use Queueable;

    public $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function via(object $notifiable): array
    {
        return [LimsDatabaseChannel::class, 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reminder: '.$this->task->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('You have set a personal reminder.')
            ->line('**Title:** '.$this->task->title)
            ->line('**Due Date:** '.$this->task->due_date->format('d M Y, h:i A'))
            ->action('View Notifications', url('/student/notifications'))
            ->line('Make sure to complete it before the deadline!');
    }

    public function toLimsDatabase(object $notifiable): array
    {
        return [
            'title' => 'Reminder: '.$this->task->title,
            'message' => 'Due on '.$this->task->due_date->format('d M Y, h:i A').'.',
            'type' => 'warning',
        ];
    }
}
