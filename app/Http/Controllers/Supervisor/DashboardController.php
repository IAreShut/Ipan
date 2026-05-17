<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\LogEntry;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show supervisor dashboard
     */
    public function dashboard()
    {
        $supervisor = Auth::user();

        // Get students under this supervisor with their log entries
        $students = User::where('supervisor_id', $supervisor->id)
            ->where('role', 'student')
            ->with('logEntries')
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

        // Identify At-Risk Students (has rejected logs or >2 pending logs)
        $atRiskStudents = $students->filter(function($student) {
            $rejectedCount = $student->logEntries->where('status', 'rejected')->count();
            $pendingCount = $student->logEntries->where('status', 'pending')->count();
            return $rejectedCount > 0 || $pendingCount >= 2;
        });

        return view('supervisor.dashboard', compact(
            'supervisor',
            'students',
            'totalStudents',
            'pendingReviews',
            'alerts',
            'recentActivity',
            'atRiskStudents'
        ));
    }
}
