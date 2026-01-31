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
}
