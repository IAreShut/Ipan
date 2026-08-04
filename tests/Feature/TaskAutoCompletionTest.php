<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use App\Models\LogEntry;
use App\Models\Internship;
use App\Services\TaskVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskAutoCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_is_assigned_with_target_week()
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $student = User::factory()->create(['role' => 'student', 'supervisor_id' => $supervisor->id]);

        $response = $this->actingAs($supervisor)->post(route('supervisor.tasks.store'), [
            'title' => 'Submit Week 2 Logbook',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'assign_to' => (string) $student->id,
            'target_week' => 2,
        ]);

        $response->assertRedirect(route('supervisor.tasks'));
        $this->assertDatabaseHas('tasks', [
            'user_id' => $student->id,
            'title' => 'Submit Week 2 Logbook',
            'target_week' => 2,
            'completed_at' => null,
        ]);
    }

    public function test_task_does_not_auto_complete_if_less_than_5_days_per_week()
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $student = User::factory()->create(['role' => 'student', 'supervisor_id' => $supervisor->id]);
        $internship = Internship::create([
            'student_id' => $student->id,
            'company_name' => 'Tech Corp',
            'company_address' => '123 St',
            'start_date' => now()->subWeeks(3),
            'end_date' => now()->addWeeks(9),
            'total_weeks' => 12,
        ]);

        $task = Task::create([
            'user_id' => $student->id,
            'created_by' => $supervisor->id,
            'title' => 'Week 1 Milestone',
            'due_date' => now()->addDays(2),
            'type' => 'sv_task',
            'target_week' => 1,
        ]);

        // Create only 4 log entries for Week 1
        for ($i = 0; $i < 4; $i++) {
            LogEntry::create([
                'student_id' => $student->id,
                'internship_id' => $internship->id,
                'entry_date' => now()->subDays(10 - $i),
                'week_number' => 1,
                'log_type' => 'work',
                'task_description' => "Day $i activities",
                'status' => 'pending',
            ]);
        }

        TaskVerificationService::evaluateStudentTasks($student);

        $task->refresh();
        $this->assertNull($task->completed_at);
    }

    public function test_task_auto_completes_when_cumulative_weeks_have_5_unique_days()
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $student = User::factory()->create(['role' => 'student', 'supervisor_id' => $supervisor->id]);
        $internship = Internship::create([
            'student_id' => $student->id,
            'company_name' => 'Tech Corp',
            'company_address' => '123 St',
            'start_date' => now()->subWeeks(3),
            'end_date' => now()->addWeeks(9),
            'total_weeks' => 12,
        ]);

        $task = Task::create([
            'user_id' => $student->id,
            'created_by' => $supervisor->id,
            'title' => 'Week 2 Milestone',
            'due_date' => now()->addDays(2),
            'type' => 'sv_task',
            'target_week' => 2,
        ]);

        // Create 5 unique days for Week 1
        for ($i = 0; $i < 5; $i++) {
            LogEntry::create([
                'student_id' => $student->id,
                'internship_id' => $internship->id,
                'entry_date' => now()->subDays(20 - $i),
                'week_number' => 1,
                'log_type' => 'work',
                'task_description' => "Week 1 Day $i",
                'status' => 'pending',
            ]);
        }

        // Create 5 unique days for Week 2
        for ($i = 0; $i < 5; $i++) {
            LogEntry::create([
                'student_id' => $student->id,
                'internship_id' => $internship->id,
                'entry_date' => now()->subDays(10 - $i),
                'week_number' => 2,
                'log_type' => 'work',
                'task_description' => "Week 2 Day $i",
                'status' => 'pending',
            ]);
        }

        TaskVerificationService::evaluateStudentTasks($student);

        $task->refresh();
        $this->assertNotNull($task->completed_at);
    }
}
