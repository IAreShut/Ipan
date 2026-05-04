<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Show task management page
     */
    public function index()
    {
        $supervisor = Auth::user();
        
        $students = User::where('supervisor_id', $supervisor->id)
            ->where('role', 'student')
            ->get();
            
        // Get all tasks assigned by this supervisor
        $tasks = Task::where('created_by', $supervisor->id)
            ->where('type', 'sv_task')
            ->with('user')
            ->orderBy('due_date', 'asc')
            ->get();

        // Get supervisor's groups for the modal checkboxes
        $groupOptions = $supervisor->groups;
            
        return view('supervisor.tasks', compact(
            'supervisor', 'students', 'tasks', 'groupOptions'
        ));
    }

    /**
     * Store task(s) for all students matching selected groups
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
            return redirect()->route('supervisor.tasks')
                ->with('error', 'No students found matching the selected groups.');
        }

        $time = $request->due_time ? ' ' . $request->due_time : ' 23:59:00';
        $dueDateTime = \Carbon\Carbon::parse($request->due_date . $time);

        \DB::beginTransaction();
        try {
            foreach ($students as $student) {
                Task::create([
                    'user_id' => $student->id,
                    'created_by' => $supervisor->id,
                    'title' => $request->title,
                    'due_date' => $dueDateTime,
                    'type' => 'sv_task',
                ]);
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->route('supervisor.tasks')
                ->with('error', 'Failed to assign tasks. Please try again.');
        }

        $createdTasks = Task::where('created_by', $supervisor->id)
            ->where('title', $request->title)
            ->where('due_date', $dueDateTime)
            ->whereIn('user_id', $students->pluck('id'))
            ->get()
            ->keyBy('user_id');

        foreach ($students as $student) {
            try {
                $task = $createdTasks->get($student->id);
                if ($task) {
                    $student->notify(new \App\Notifications\TaskSetNotification($task));
                }
            } catch (\Exception $e) {
                \Log::warning('TaskController@store notify failed for user ' . $student->id . ': ' . $e->getMessage());
                // Continue — don't stop if one notification fails
            }
        }

        $groupNames = implode(', ', $request->groups);

        return redirect()->route('supervisor.tasks')
            ->with('success', 'Task assigned to students in ' . $groupNames . ' and notifications sent!');
    }
}
