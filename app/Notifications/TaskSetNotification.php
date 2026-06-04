<?php

namespace App\Notifications;

use App\Channels\LimsDatabaseChannel;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class TaskSetNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $task;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [LimsDatabaseChannel::class, 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Task Assigned: '.$this->task->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your supervisor has assigned a new task for you.')
            ->line('**Title:** '.$this->task->title)
            ->line('**Due Date:** '.$this->task->due_date->format('d M Y, h:i A'))
            ->action('View Details', url('/student/notifications'))
            ->line('Please ensure you complete the required tasks before the deadline.');
    }

    /**
     * Get the LIMS database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toLimsDatabase(object $notifiable): array
    {
        return [
            'title' => 'New Task: '.$this->task->title,
            'message' => 'Due on '.$this->task->due_date->format('d M Y, h:i A').'.',
            'type' => 'info',
        ];
    }
}
