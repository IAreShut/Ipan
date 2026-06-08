<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'internship_id',
        'entry_date',
        'week_number',
        'log_type',
        'task_description',
        'ai_summary',
        'status',
        'supervisor_comment',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    /**
     * Get the student for this log entry
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the internship for this log entry
     */
    public function internship()
    {
        return $this->belongsTo(Internship::class);
    }

    /**
     * Get attachments for this log entry
     */
    public function attachments()
    {
        return $this->hasMany(LogAttachment::class);
    }

    /**
     * Determine the overall status of a week based on its log entries.
     * Enforces the '5 logs per week' rule.
     * 
     * @param \Illuminate\Support\Collection $weekLogs
     * @return string (completed, rejected, mixed, pending, empty)
     */
    public static function getWeeklyStatus($weekLogs)
    {
        if ($weekLogs->isEmpty()) {
            return 'empty';
        }

        $approvedLogs = $weekLogs->where('status', 'approved');

        $approvedUniqueDaysCount = $approvedLogs->pluck('entry_date')
            ->map(function ($date) {
                return $date instanceof \Carbon\Carbon ? $date->format('Y-m-d') : $date;
            })
            ->unique()
            ->count();

        $rejectedCount = $weekLogs->where('status', 'rejected')->count();
        $pendingCount = $weekLogs->where('status', 'pending')->count();

        if ($approvedUniqueDaysCount >= 5) {
            return 'completed';
        } elseif ($rejectedCount > 0) {
            return 'rejected';
        } elseif ($approvedLogs->count() > 0) {
            return 'mixed';
        } elseif ($pendingCount > 0) {
            return 'pending';
        } else {
            return 'empty';
        }
    }
}
