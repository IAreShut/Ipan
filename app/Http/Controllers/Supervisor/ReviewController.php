<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\LogEntry;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Show review logbook page
     */
    public function index(Request $request)
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
    public function approve(Request $request, $id)
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
    public function reject(Request $request, $id)
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
}
