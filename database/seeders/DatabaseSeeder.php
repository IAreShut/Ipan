<?php

namespace Database\Seeders;

use App\Models\Internship;
use App\Models\LogEntry;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Supervisor
        $supervisor = User::create([
            'name' => 'Dr. Hidayah',
            'email' => 'supervisor@test.com',
            'password' => Hash::make('password123'),
            'role' => 'supervisor',
        ]);

        // Create Student 1
        $student1 = User::create([
            'name' => 'Ali Abu',
            'email' => 'student@test.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'company' => 'Tech Solutions Sdn Bhd',
            'supervisor_id' => $supervisor->id,
        ]);

        // Create Internship for Student 1
        $internship1 = Internship::create([
            'student_id' => $student1->id,
            'company_name' => 'Tech Solutions Sdn Bhd',
            'company_address' => 'Kuala Lumpur',
            'start_date' => now()->subWeeks(5),
            'end_date' => now()->addWeeks(7),
            'total_weeks' => 12,
        ]);

        // Create sample log entries
        LogEntry::create([
            'student_id' => $student1->id,
            'internship_id' => $internship1->id,
            'entry_date' => now()->subDays(3),
            'week_number' => 5,
            'task_description' => 'Implemented login page frontend using Bootstrap 5.',
            'status' => 'pending',
        ]);

        LogEntry::create([
            'student_id' => $student1->id,
            'internship_id' => $internship1->id,
            'entry_date' => now()->subDays(4),
            'week_number' => 5,
            'task_description' => 'Setup database connection and tested CRUD operations.',
            'status' => 'approved',
        ]);

        LogEntry::create([
            'student_id' => $student1->id,
            'internship_id' => $internship1->id,
            'entry_date' => now()->subDays(5),
            'week_number' => 5,
            'task_description' => 'Requirement gathering and analysis for the project.',
            'status' => 'rejected',
            'supervisor_comment' => 'Please provide more details about the requirements discussed.',
        ]);

        LogEntry::create([
            'student_id' => $student1->id,
            'internship_id' => $internship1->id,
            'entry_date' => now()->subDays(7),
            'week_number' => 4,
            'task_description' => 'Weekly meeting with supervisor to discuss project progress.',
            'status' => 'approved',
        ]);

        // Create Student 2
        $student2 = User::create([
            'name' => 'Siti Aminah',
            'email' => 'siti@test.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'company' => 'Data Co.',
            'supervisor_id' => $supervisor->id,
        ]);

        Internship::create([
            'student_id' => $student2->id,
            'company_name' => 'Data Co.',
            'start_date' => now()->subWeeks(3),
            'end_date' => now()->addWeeks(9),
            'total_weeks' => 12,
        ]);

        LogEntry::create([
            'student_id' => $student2->id,
            'internship_id' => 2,
            'entry_date' => now()->subDays(1),
            'week_number' => 3,
            'task_description' => 'Created dashboard wireframes using Figma.',
            'status' => 'pending',
        ]);

        // Admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);
    }
}
