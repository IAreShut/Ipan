<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'log_entry_id',
        'file_path',
        'file_name',
        'file_type',
    ];

    /**
     * Get the log entry for this attachment
     */
    public function logEntry()
    {
        return $this->belongsTo(LogEntry::class);
    }
}
