<?php

namespace App\Http\Controllers;

use App\Models\LogEntry;
use App\Models\Internship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
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
        
        // Get recent log entries
        $recentLogs = LogEntry::where('student_id', $user->id)
            ->orderBy('entry_date', 'desc')
            ->take(5)
            ->get();
        
        // Calculate progress
        $progress = 0;
        if ($internship && $internship->total_weeks > 0) {
            $currentWeek = now()->diffInWeeks($internship->start_date) + 1;
            $progress = min(100, ($currentWeek / $internship->total_weeks) * 100);
        }

        return view('student.dashboard', compact(
            'user', 
            'internship', 
            'totalLogs', 
            'approvedLogs', 
            'pendingLogs', 
            'rejectedLogs',
            'recentLogs',
            'progress'
        ));
    }

    /**
     * Show log entries page
     */
    public function logEntries()
    {
        $user = Auth::user();
        $internship = Internship::where('student_id', $user->id)->first();
        $logs = LogEntry::where('student_id', $user->id)
            ->orderBy('entry_date', 'desc')
            ->paginate(10);

        return view('student.log-entries', compact('user', 'internship', 'logs'));
    }

    /**
     * Store new log entry
     */
    public function storeLogEntry(Request $request)
    {
        $request->validate([
            'entry_date' => 'required|date',
            'week_number' => 'required|integer|min:1',
            'task_description' => 'required|string',
        ]);

        $user = Auth::user();
        $internship = Internship::where('student_id', $user->id)->first();

        LogEntry::create([
            'student_id' => $user->id,
            'internship_id' => $internship ? $internship->id : 1,
            'entry_date' => $request->entry_date,
            'week_number' => $request->week_number,
            'task_description' => $request->task_description,
            'status' => $request->has('save_draft') ? 'draft' : 'pending',
        ]);

        return redirect()->route('student.log-entries')
            ->with('success', 'Log entry submitted successfully!');
    }

    /**
     * Show profile page
     */
    public function profile()
    {
        $user = Auth::user();
        $internship = Internship::where('student_id', $user->id)->first();
        return view('student.profile', compact('user', 'internship'));
    }

    /**
     * Show progress page
     */
    public function progress()
    {
        $user = Auth::user();
        $internship = Internship::where('student_id', $user->id)->first();
        $logs = LogEntry::where('student_id', $user->id)->get();
        
        return view('student.progress', compact('user', 'internship', 'logs'));
    }
}
