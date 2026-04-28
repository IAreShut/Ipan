<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LogEntry;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show supervisor dashboard
     */
    public function dashboard()
    {
        $supervisor = Auth::user();
        
        // Get students under this supervisor
        $students = User::where('supervisor_id', $supervisor->id)
            ->where('role', 'student')
            ->get();
        
        $totalStudents = $students->count();
        
        // Get pending reviews count
        $pendingReviews = LogEntry::whereIn('student_id', $students->pluck('id'))
            ->where('status', 'pending')
            ->count();
        
        // Get flagged/alerts (rejected logs in last week)
        $alerts = LogEntry::whereIn('student_id', $students->pluck('id'))
            ->where('status', 'rejected')
            ->where('updated_at', '>=', now()->subWeek())
            ->count();
        
        // Get recent activity
        $recentActivity = LogEntry::whereIn('student_id', $students->pluck('id'))
            ->whereIn('status', ['approved', 'rejected'])
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->with('student')
            ->get();

        return view('supervisor.dashboard', compact(
            'supervisor',
            'students',
            'totalStudents',
            'pendingReviews',
            'alerts',
            'recentActivity'
        ));
    }
}
