<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupervisorAssignment extends Model
{
    protected $fillable = [
        'student_matrix_id',
        'student_name',
        'supervisor_matrix_id',
        'faculty',
        'programme_code',
        'class',
    ];
}
