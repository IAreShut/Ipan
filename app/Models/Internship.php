<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Internship extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'company_name',
        'company_address',
        'start_date',
        'end_date',
        'total_weeks',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the student for this internship
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get log entries for this internship
     */
    public function logEntries()
    {
        return $this->hasMany(LogEntry::class);
    }

    public function getInternshipWeek($date): int
    {
        if (!$date || !$this->start_date) {
            return 1;
        }

        $startMonday = $this->start_date->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $targetMonday = \Carbon\Carbon::parse($date)->startOfWeek(\Carbon\Carbon::MONDAY);
        $week = (int) $startMonday->diffInWeeks($targetMonday, false) + 1;

        if ($this->total_weeks) {
            $week = min($week, $this->total_weeks);
        }

        return max(1, $week);
    }

    public function getWeekTargetLogs(int $week): int
    {
        if (!$this->start_date) {
            return 5;
        }

        $startMonday = $this->start_date->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $weekMonday = $startMonday->copy()->addWeeks($week - 1);
        $weekFriday = $weekMonday->copy()->endOfWeek(\Carbon\Carbon::FRIDAY);

        $effectiveStart = max($weekMonday, $this->start_date);
        $effectiveEnd = min($weekFriday, $this->end_date ?? $weekFriday);

        $count = 0;
        if ($effectiveStart <= $effectiveEnd) {
            $curr = $effectiveStart->copy();
            while ($curr <= $effectiveEnd) {
                if (!$curr->isWeekend()) {
                    $count++;
                }
                $curr->addDay();
            }
        }

        return max(1, $count);
    }

    public function calculateTotalWeeks(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return 1;
        }

        $startMonday = $this->start_date->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $endMonday = $this->end_date->copy()->startOfWeek(\Carbon\Carbon::MONDAY);

        return max(1, (int) $startMonday->diffInWeeks($endMonday) + 1);
    }
}
