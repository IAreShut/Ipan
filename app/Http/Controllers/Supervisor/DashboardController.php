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

    /**
     * Show detailed profile and analytics for a specific student
     */
    public function showStudent(User $student)
    {
        $supervisor = Auth::user();

        // Security check
        if ($student->supervisor_id !== $supervisor->id || $student->role !== 'student') {
            abort(403, 'Unauthorized access to this student.');
        }

        // Load relationships
        $student->load(['internships', 'logEntries.attachments']);
        $internship = $student->internships->first();

        $tasks = \App\Models\Task::where('user_id', $student->id)
            ->where('created_by', $supervisor->id)
            ->orderBy('due_date', 'asc')
            ->get();

        $logEntries = $student->logEntries()
            ->where('status', '!=', 'draft') // Do not review drafts
            ->orderBy('entry_date', 'desc')
            ->with('attachments')
            ->get();

        $totalLogs = $logEntries->count();
        $approvedCount = $logEntries->where('status', 'approved')->count();
        $pendingCount = $logEntries->where('status', 'pending')->count();
        $rejectedCount = $logEntries->where('status', 'rejected')->count();

        // Calculate Weekly Progress Matrix
        $weeklyLogs = $logEntries->groupBy('week_number');
        $weeklyProgress = [];

        $totalWeeks = 12;
        if ($internship) {
            $totalWeeks = $internship->total_weeks ?? 12;
        }

        for ($w = 1; $w <= $totalWeeks; $w++) {
            if (!$weeklyLogs->has($w)) {
                $weeklyProgress[$w] = 'empty';
                continue;
            }

            $weekLogs = $weeklyLogs[$w];
            if ($weekLogs->where('status', 'rejected')->count() > 0) {
                $weeklyProgress[$w] = 'rejected';
            } elseif ($weekLogs->where('status', 'pending')->count() == $weekLogs->count()) {
                $weeklyProgress[$w] = 'pending';
            } elseif ($weekLogs->where('status', 'approved')->count() == $weekLogs->count()) {
                $weeklyProgress[$w] = 'approved';
            } else {
                $weeklyProgress[$w] = 'mixed';
            }
        }

        $progressPct = $totalWeeks > 0 ? min(100, round(($approvedCount / ($totalWeeks * 5)) * 100)) : 0;

        return view('supervisor.student-show', compact(
            'supervisor', 'student', 'internship', 'logEntries', 'tasks', 
            'totalLogs', 'approvedCount', 'pendingCount', 'rejectedCount',
            'weeklyProgress', 'totalWeeks', 'progressPct'
        ));
    }

}
