<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisorTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_can_view_tasks_page_and_students()
    {
        $supervisor = User::factory()->create([
            'role' => 'supervisor',
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'supervisor_id' => $supervisor->id,
        ]);

        $response = $this->actingAs($supervisor)->get(route('supervisor.tasks'));

        $response->assertStatus(200);
        $response->assertSee($student->name);
    }

    public function test_supervisor_can_assign_task_to_all_students()
    {
        $supervisor = User::factory()->create([
            'role' => 'supervisor',
        ]);

        $student1 = User::factory()->create([
            'role' => 'student',
            'supervisor_id' => $supervisor->id,
        ]);

        $student2 = User::factory()->create([
            'role' => 'student',
            'supervisor_id' => $supervisor->id,
        ]);

        $response = $this->actingAs($supervisor)->post(route('supervisor.tasks.store'), [
            'title' => 'Important Meeting',
            'due_date' => now()->addDays(2)->format('Y-m-d'),
            'due_time' => '14:00',
            'assign_to' => 'all',
        ]);

        $response->assertRedirect(route('supervisor.tasks'));
        $response->assertSessionHas('success', 'Task assigned to 2 students and notifications sent!');

        $this->assertDatabaseHas('tasks', [
            'user_id' => $student1->id,
            'created_by' => $supervisor->id,
            'title' => 'Important Meeting',
        ]);

        $this->assertDatabaseHas('tasks', [
            'user_id' => $student2->id,
            'created_by' => $supervisor->id,
            'title' => 'Important Meeting',
        ]);
    }

    public function test_supervisor_can_assign_task_to_specific_student()
    {
        $supervisor = User::factory()->create([
            'role' => 'supervisor',
        ]);

        $student1 = User::factory()->create([
            'role' => 'student',
            'supervisor_id' => $supervisor->id,
        ]);

        $student2 = User::factory()->create([
            'role' => 'student',
            'supervisor_id' => $supervisor->id,
        ]);

        $response = $this->actingAs($supervisor)->post(route('supervisor.tasks.store'), [
            'title' => 'Individual Submission Check',
            'due_date' => now()->addDays(2)->format('Y-m-d'),
            'due_time' => '14:00',
            'assign_to' => (string) $student1->id,
        ]);

        $response->assertRedirect(route('supervisor.tasks'));
        $response->assertSessionHas('success', 'Task assigned to ' . $student1->name . ' and notification sent!');

        $this->assertDatabaseHas('tasks', [
            'user_id' => $student1->id,
            'created_by' => $supervisor->id,
            'title' => 'Individual Submission Check',
        ]);

        $this->assertDatabaseMissing('tasks', [
            'user_id' => $student2->id,
            'created_by' => $supervisor->id,
            'title' => 'Individual Submission Check',
        ]);
    }
}
