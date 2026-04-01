<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Channels\LimsDatabaseChannel;

class DailyLogReminderNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return [LimsDatabaseChannel::class, 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Daily Reminder: Submit Your Log Entry')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('This is your daily 5:00 PM reminder to submit your internship log entry for today.')
            ->line('Keeping your logbook up to date is important for tracking your internship progress.')
            ->action('Submit Log Entry', url('/student/log-entries'))
            ->line('Thank you for staying on top of your internship!');
    }

    public function toLimsDatabase(object $notifiable): array
    {
        return [
            'title' => 'Daily Reminder: Submit Your Log',
            'message' => 'Don\'t forget to submit your daily log entry. Keeping your logbook updated is crucial for your internship progress.',
            'type' => 'danger',
        ];
    }
}
