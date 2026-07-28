<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Models\LogEntry;
use Illuminate\Support\Facades\Log;

class TaskVerificationService
{
    /**
     * Evaluate pending tasks for a student to see if they meet the target_week criteria.
     *
     * @param User $student
     * @return void
     */
    public static function evaluateStudentTasks(User $student)
    {
        // 1. Fetch all pending tasks for $student where target_week IS NOT NULL
        $pendingTasks = Task::where('user_id', $student->id)
            ->whereNull('completed_at')
            ->whereNotNull('target_week')
            ->get();

        // 2. If no pending week-based tasks exist, exit early
        if ($pendingTasks->isEmpty()) {
            return;
        }

        // 3. Fetch student's log entries (pending or approved) in 1 single optimized query, grouped by week_number
        $validStatuses = ['pending', 'approved'];
        
        $logEntries = LogEntry::where('student_id', $student->id)
            ->whereIn('status', $validStatuses)
            ->get(['entry_date', 'week_number']);

        // Group by week_number, and for each week, count unique entry_dates
        $uniqueDaysPerWeek = $logEntries->groupBy('week_number')->map(function ($entries) {
            return $entries->pluck('entry_date')->map(function ($date) {
                return $date instanceof \Carbon\Carbon ? $date->format('Y-m-d') : substr((string)$date, 0, 10);
            })->unique()->count();
        });

        // Maximum total weeks for the internship, defaulting to 12 if not set or relation is null
        $totalWeeks = optional($student->internship)->total_weeks ?? 12;

        foreach ($pendingTasks as $task) {
            // 4. Determine effective target week
            $effectiveTargetWeek = min($task->target_week, $totalWeeks);
            
            $meetsCriteria = true;
            
            // 5. Check every week W in [1 .. effectiveTargetWeek]
            for ($w = 1; $w <= $effectiveTargetWeek; $w++) {
                $uniqueDaysCount = $uniqueDaysPerWeek->get($w, 0);
                
                if ($uniqueDaysCount < 5) {
                    $meetsCriteria = false;
                    break;
                }
            }

            // 6. If all cumulative weeks meet the >= 5 unique days threshold
            if ($meetsCriteria) {
                $task->update(['completed_at' => now()]);
                
                // Log completion
                Log::info("Task [{$task->id}] auto-completed for student [{$student->id}] based on logbook entries (Target Week: {$effectiveTargetWeek}).");
            }
        }
    }
}
