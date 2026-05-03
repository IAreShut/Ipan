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

        // Get supervisor's groups for the modal checkboxes
        $groupOptions = $supervisor->groups;
            
        return view('supervisor.milestones', compact(
            'supervisor', 'students', 'milestones', 'groupOptions'
        ));
    }

    /**
     * Store milestone(s) for all students matching selected groups
     */
    public function store(Request $request)
    {
        $request->validate([
            'groups' => 'required|array|min:1',
            'groups.*' => 'string',
            'title' => 'required|string|max:255',
            'due_date' => 'required|date',
            'due_time' => 'nullable|date_format:H:i',
        ]);

        $supervisor = Auth::user();

        // Get all students under this supervisor and filter by selected groups
        $selectedGroupsNorm = array_map(function($g) {
            return strtolower(str_replace(' ', '', $g));
        }, $request->groups);

        $students = User::where('supervisor_id', $supervisor->id)
            ->where('role', 'student')
            ->get()
            ->filter(function ($student) use ($selectedGroupsNorm) {
                $studentGroup = strtolower(str_replace(' ', '', ($student->programme_code ?? '') . '-' . ($student->class ?? '')));
                return in_array($studentGroup, $selectedGroupsNorm);
            });

        if ($students->isEmpty()) {
            return redirect()->route('supervisor.milestones')
                ->with('error', 'No students found matching the selected groups.');
        }

        $time = $request->due_time ? ' ' . $request->due_time : ' 23:59:00';
        $dueDateTime = \Carbon\Carbon::parse($request->due_date . $time);

        \DB::beginTransaction();
        try {
            foreach ($students as $student) {
                \App\Models\Milestone::create([
                    'user_id' => $student->id,
                    'created_by' => $supervisor->id,
                    'title' => $request->title,
                    'due_date' => $dueDateTime,
                    'type' => 'sv_milestone',
                ]);
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->route('supervisor.milestones')
                ->with('error', 'Failed to assign milestones. Please try again.');
        }

        $createdMilestones = \App\Models\Milestone::where('created_by', $supervisor->id)
            ->where('title', $request->title)
            ->where('due_date', $dueDateTime)
            ->whereIn('user_id', $students->pluck('id'))
            ->get()
            ->keyBy('user_id');

        foreach ($students as $student) {
            try {
                $milestone = $createdMilestones->get($student->id);
                if ($milestone) {
                    $student->notify(new \App\Notifications\MilestoneSetNotification($milestone));
                }
            } catch (\Exception $e) {
                \Log::warning('MilestoneController@store notify failed for user ' . $student->id . ': ' . $e->getMessage());
                // Continue — don't stop if one notification fails
            }
        }

        $groupNames = implode(', ', $request->groups);

        return redirect()->route('supervisor.milestones')
            ->with('success', 'Milestone assigned to students in ' . $groupNames . ' and notifications sent!');
    }
}
