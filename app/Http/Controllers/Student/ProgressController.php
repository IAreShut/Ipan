<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LogEntry;
use App\Models\Internship;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    /**
     * Show progress page
     */
    public function index()
    {
        $user = Auth::user();
        $internship = Internship::where('student_id', $user->id)->first();
        $logs = LogEntry::where('student_id', $user->id)->get();
        
        return view('student.progress', compact('user', 'internship', 'logs'));
    }

    /**
     * Show detailed weekly logs progress page
     */
    public function week($week)
    {
        $user = Auth::user();
        $internship = Internship::where('student_id', $user->id)->first();

        if (!$internship || $week < 1 || $week > $internship->total_weeks) {
            return redirect()->route('student.progress')->with('error', 'Invalid week selected.');
        }

        $weekLogs = LogEntry::where('student_id', $user->id)
            ->where('week_number', $week)
            ->orderBy('entry_date', 'desc')
            ->get();

        return view('student.progress-week', compact('user', 'internship', 'week', 'weekLogs'));
    }
}
