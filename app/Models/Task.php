<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'created_by',
        'title',
        'due_date',
        'type',
        'completed_at',
        'reminder_sent',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'reminder_sent' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isCompleted()
    {
        return ! is_null($this->completed_at);
    }

    public function markAsCompleted()
    {
        $this->update(['completed_at' => now()]);
    }
}
