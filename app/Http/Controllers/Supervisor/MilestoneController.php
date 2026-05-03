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

        // Get supervisor's programme codes for the modal dropdowns
        $programmeOptions = $supervisor->programme_codes;
            
        return view('supervisor.milestones', compact(
            'supervisor', 'students', 'milestones', 'programmeOptions'
        ));
    }

    /**
     * Store milestone(s) for all students matching selected programme
     */
    public function store(Request $request)
    {
        $request->validate([
            'programme_code' => 'required|array|min:1',
            'programme_code.*' => 'string',
            'title' => 'required|string|max:255',
            'due_date' => 'required|date',
            'due_time' => 'nullable|date_format:H:i',
        ]);

        $supervisor = Auth::user();

        // Find all students under this supervisor matching the selected criteria
        $students = User::where('supervisor_id', $supervisor->id)
            ->where('role', 'student')
            ->whereIn('programme_code', $request->programme_code)
            ->get();

        if ($students->isEmpty()) {
            return redirect()->route('supervisor.milestones')
                ->with('error', 'No students found matching the selected programme.');
        }

        $time = $request->due_time ? ' ' . $request->due_time : ' 23:59:00';
        $dueDateTime = \Carbon\Carbon::parse($request->due_date . $time);

        \DB::beginTransaction();
        try {
            foreach ($students as $student) {
                $milestone = \App\Models\Milestone::create([
                    'user_id' => $student->id,
                    'created_by' => $supervisor->id,
                    'title' => $request->title,
                    'due_date' => $dueDateTime,
                    'type' => 'sv_milestone',
                ]);

                // Send Notification (Database & Mail)
                $student->notify(new \App\Notifications\MilestoneSetNotification($milestone));
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->route('supervisor.milestones')
                ->with('error', 'Failed to assign milestones. Please try again.');
        }

        $programmeNames = implode(', ', $request->programme_code);

        return redirect()->route('supervisor.milestones')
            ->with('success', 'Milestone assigned to students in ' . $programmeNames . ' and notifications sent!');
    }
}

