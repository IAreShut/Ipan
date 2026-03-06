<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'company',
        'supervisor_id',
        'matrix_id',
        'phone',
        'faculty',
        'class',
        'programme_code',
        'location',
        'about',
        'avatar',
    ];

    /**
     * Get the supervisor for a student
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Get students under this supervisor
     */
    public function students()
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    /**
     * Get log entries for this user
     */
    public function logEntries()
    {
        return $this->hasMany(\App\Models\LogEntry::class, 'student_id');
    }

    /**
     * Get internships for this user
     */
    public function internships()
    {
        return $this->hasMany(\App\Models\Internship::class, 'student_id');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
