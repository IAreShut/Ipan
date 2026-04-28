<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MilestoneController extends Controller
{
    /**
     * Show milestones management page
     */
    public function index()
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
    public function store(Request $request)
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
