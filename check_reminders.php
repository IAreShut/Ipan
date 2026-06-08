<?php
// Mark ALL current pending as sent — we'll test with a brand new reminder
use App\Models\Task;

Task::where('type', 'personal_reminder')
    ->where('reminder_sent', false)
    ->update(['reminder_sent' => true]);

echo "All old reminders marked as sent. Create a new one from the UI to test.\n";
