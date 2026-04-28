<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LogEntry;
use App\Models\Internship;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show student dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        $internship = Internship::where('student_id', $user->id)->first();
        
        // Get log entry statistics
        $totalLogs = LogEntry::where('student_id', $user->id)->count();
        $approvedLogs = LogEntry::where('student_id', $user->id)->where('status', 'approved')->count();
        $pendingLogs = LogEntry::where('student_id', $user->id)->where('status', 'pending')->count();
        $rejectedLogs = LogEntry::where('student_id', $user->id)->where('status', 'rejected')->count();
        
        // Get recent log entries — drafts first so user can prioritize
        $recentLogs = LogEntry::where('student_id', $user->id)
            ->orderByRaw("FIELD(status, 'draft') DESC")
            ->orderBy('entry_date', 'desc')
            ->take(10)
            ->get();
        
        // Calculate progress
        $progress = 0;
        if ($internship && $internship->total_weeks > 0) {
            $currentWeek = now()->diffInWeeks($internship->start_date) + 1;
            $progress = min(100, ($currentWeek / $internship->total_weeks) * 100);
        }

        // Get recent alerts (notifications)
        $recentAlerts = \App\Models\Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        return view('student.dashboard', compact(
            'user', 
            'internship', 
            'totalLogs', 
            'approvedLogs', 
            'pendingLogs', 
            'rejectedLogs',
            'recentLogs',
            'progress',
            'recentAlerts'
        ));
    }
}
