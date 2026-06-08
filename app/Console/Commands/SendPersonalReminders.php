<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\PersonalReminderNotification;
use Illuminate\Console\Command;

class SendPersonalReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders for personal tasks that are now due';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tasks = Task::where('type', 'personal_reminder')
            ->where('reminder_sent', false)
            ->whereNull('completed_at')
            ->where('due_date', '<=', now())
            ->with('user')
            ->get();

        if ($tasks->isEmpty()) {
            $this->info('No pending reminders.');

            return Command::SUCCESS;
        }

        $sent = 0;
        $failed = 0;

        foreach ($tasks as $task) {
            if (! $task->user) {
                continue;
            }

            try {
                // Send email immediately (bypassing queue)
                $task->user->notifyNow(
                    (new PersonalReminderNotification($task))->onlyMail()
                );

                $task->update(['reminder_sent' => true]);
                $sent++;
                $this->info("✓ Sent reminder: {$task->title} (Task #{$task->id})");
            } catch (\Exception $e) {
                $failed++;
                $this->warn("✗ Failed: {$task->title} — {$e->getMessage()}");
            }

            // Delay between emails to respect rate limits
            sleep(5);
        }

        $this->info("Done. Sent: {$sent}, Failed: {$failed} (will retry next run).");

        return Command::SUCCESS;
    }
}
