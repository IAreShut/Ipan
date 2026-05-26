<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\SupervisorAssignment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AssignStudentController extends Controller
{
    public function assignedStudents()
    {
        $supervisor = Auth::user();

        $preAssigned = SupervisorAssignment::where('supervisor_matrix_id', $supervisor->matrix_id)
            ->orWhere('supervisor_matrix_id', $supervisor->employee_id)
            ->get();

        $totalAssigned = $preAssigned->count();

        $students = User::where('supervisor_id', $supervisor->id)
            ->where('role', 'student')
            ->with(['logEntries', 'internships'])
            ->get();

        $completedCount = 0;
        $inProgressCount = 0;
        $overdueCount = 0;

        foreach ($students as $student) {
            $internship = $student->internships->first();
            $totalWeeks = $internship ? ($internship->total_weeks ?? 12) : 12;
            $requiredApproved = $totalWeeks * 5;
            $approvedCount = $student->logEntries->where('status', 'approved')->count();

            if ($approvedCount >= $requiredApproved) {
                $completedCount++;
            } else {
                $hasOverdueTask = Task::where('user_id', $student->id)
                    ->where('created_by', $supervisor->id)
                    ->whereNull('completed_at')
                    ->where('due_date', '<', now())
                    ->exists();

                if ($hasOverdueTask) {
                    $overdueCount++;
                } else {
                    $inProgressCount++;
                }
            }
        }

        $tableStudents = [];

        // Active students
        foreach ($students as $student) {
            $tAssigned = Task::where('user_id', $student->id)->where('created_by', $supervisor->id)->count();
            $tCompleted = Task::where('user_id', $student->id)->where('created_by', $supervisor->id)->whereNotNull('completed_at')->count();
            $tInProgress = Task::where('user_id', $student->id)->where('created_by', $supervisor->id)->whereNull('completed_at')->where('due_date', '>=', now())->count();
            $tOverdue = Task::where('user_id', $student->id)->where('created_by', $supervisor->id)->whereNull('completed_at')->where('due_date', '<', now())->count();

            $tableStudents[] = (object) [
                'registered' => true,
                'user_id' => $student->id,
                'name' => $student->name,
                'matrix_id' => $student->matrix_id,
                'email' => $student->email,
                'avatar' => $student->avatar,
                'programme_code' => $student->programme_code,
                'class' => $student->class,
                'company' => $student->company ?? 'N/A',
                'tasks_assigned' => $tAssigned,
                'tasks_completed' => $tCompleted,
                'tasks_in_progress' => $tInProgress,
                'tasks_overdue' => $tOverdue,
                'status_label' => 'Active',
            ];
        }

        // Pending students (from pre-assignment that haven't registered)
        foreach ($preAssigned as $pa) {
            $matchedUser = $students->firstWhere('matrix_id', $pa->student_matrix_id);
            if (! $matchedUser) {
                $tableStudents[] = (object) [
                    'registered' => false,
                    'user_id' => null,
                    'name' => $pa->student_name,
                    'matrix_id' => $pa->student_matrix_id,
                    'email' => '-',
                    'avatar' => null,
                    'programme_code' => $pa->programme_code,
                    'class' => $pa->class,
                    'company' => 'N/A',
                    'tasks_assigned' => '-',
                    'tasks_completed' => '-',
                    'tasks_in_progress' => '-',
                    'tasks_overdue' => '-',
                    'status_label' => 'Pending Registration',
                ];
            }
        }

        return view('supervisor.assign-student', compact(
            'supervisor',
            'students',
            'totalAssigned',
            'completedCount',
            'inProgressCount',
            'overdueCount',
            'tableStudents'
        ));
    }
}
