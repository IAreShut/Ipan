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
}
