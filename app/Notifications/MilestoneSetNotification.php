<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Milestone;
use App\Channels\LimsDatabaseChannel;

class MilestoneSetNotification extends Notification
{
    use Queueable;

    public $milestone;

    /**
     * Create a new notification instance.
     */
    public function __construct(Milestone $milestone)
    {
        $this->milestone = $milestone;
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
            ->subject('New Milestone Assigned: ' . $this->milestone->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your supervisor has assigned a new milestone for you.')
            ->line('**Title:** ' . $this->milestone->title)
            ->line('**Due Date:** ' . $this->milestone->due_date->format('d M Y, h:i A'))
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
            'title' => 'New Milestone: ' . $this->milestone->title,
            'message' => 'Due on ' . $this->milestone->due_date->format('d M Y, h:i A') . '.',
            'type' => 'info',
        ];
    }
}
