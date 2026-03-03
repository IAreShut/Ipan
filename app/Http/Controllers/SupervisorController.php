<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LogEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupervisorController extends Controller
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

    /**
     * Show review logbook page
     */
    public function reviewLogbook(Request $request)
    {
        $supervisor = Auth::user();
        $students = User::where('supervisor_id', $supervisor->id)->pluck('id');
        
        $logs = LogEntry::whereIn('student_id', $students)
            ->where('status', 'pending')
            ->with(['student', 'attachments'])
            ->orderBy('entry_date', 'desc')
            ->paginate(10);

        return view('supervisor.review-logbook', compact('logs'));
    }

    /**
     * Approve a log entry
     */
    public function approveLog(Request $request, $id)
    {
        $log = LogEntry::findOrFail($id);
        $log->update([
            'status' => 'approved',
            'supervisor_comment' => $request->comment,
        ]);

        return back()->with('success', 'Log entry approved successfully!');
    }

    /**
     * Reject a log entry
     */
    public function rejectLog(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:500',
        ]);

        $log = LogEntry::findOrFail($id);
        $log->update([
            'status' => 'rejected',
            'supervisor_comment' => $request->comment,
        ]);

        return back()->with('success', 'Log entry rejected with feedback.');
    }

    /**
     * Show analytics page
     */
    public function analytics()
    {
        $supervisor = Auth::user();
        $students = User::where('supervisor_id', $supervisor->id)->get();
        
        return view('supervisor.analytics', compact('supervisor', 'students'));
    }

    /**
     * Show milestones management page
     */
    public function milestones()
    {
        $supervisor = Auth::user();
        
        $students = User::where('supervisor_id', $supervisor->id)
            ->where('role', 'student')
            ->get();
            
        // Get all milestones assigned by this supervisor
        $milestones = \App\Models\Milestone::where('created_by', $supervisor->id)
            ->where('type', 'sv_milestone')
            ->with('user')
            ->orderBy('due_date', 'asc')
            ->get();
            
        return view('supervisor.milestones', compact('supervisor', 'students', 'milestones'));
    }

    /**
     * Store a new supervisor milestone for a student
     */
    public function storeMilestone(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'due_date' => 'required|date',
            'due_time' => 'nullable|date_format:H:i',
        ]);

        $supervisor = Auth::user();
        $student = User::find($request->student_id);

        // Security check
        if ($student->supervisor_id !== $supervisor->id) {
            abort(403);
        }
        
        $time = $request->due_time ? ' ' . $request->due_time : ' 23:59:00';
        $dueDateTime = \Carbon\Carbon::parse($request->due_date . $time);

        $milestone = \App\Models\Milestone::create([
            'user_id' => $student->id,
            'created_by' => $supervisor->id,
            'title' => $request->title,
            'due_date' => $dueDateTime,
            'type' => 'sv_milestone',
        ]);

        // Send Notification (Database & Mail)
        $student->notify(new \App\Notifications\MilestoneSetNotification($milestone));

        return redirect()->route('supervisor.milestones')
            ->with('success', 'Milestone assigned to ' . $student->name . ' and notification sent!');
    }
}
