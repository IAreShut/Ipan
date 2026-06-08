<?php

namespace App\Notifications;

use App\Channels\LimsDatabaseChannel;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class PersonalReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $task;
    protected $mailOnly = false;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Set this notification to only send via email (used by scheduler).
     */
    public function onlyMail(): self
    {
        $this->mailOnly = true;

        return $this;
    }

    public function via(object $notifiable): array
    {
        if ($this->mailOnly) {
            return ['mail'];
        }

        return [LimsDatabaseChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⏰ Reminder: '.$this->task->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('This is your scheduled reminder!')
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
