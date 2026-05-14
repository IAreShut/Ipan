<?php

namespace Database\Seeders;

use App\Models\SupervisorAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SupervisorAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $faculty = 'Faculty of Computing';

        $supervisors = [
            [
                'id' => 'SV0001', 'name' => 'Dr. Ahmad Fauzi',
                'email' => 'sv1@sv.my', 'password' => 'password123',
                'employee_id' => 'SV0001',
                'group' => 'CS266-5A',
            ],
            [
                'id' => 'SV0002', 'name' => 'Prof. Nurul Huda',
                'email' => 'sv2@sv.my', 'password' => 'password123',
                'employee_id' => 'SV0002',
                'group' => 'CS240-4B',
            ],
            [
                'id' => 'SV0003', 'name' => 'Dr. Ramesh Kumar',
                'email' => 'sv3@sv.my', 'password' => 'password123',
                'employee_id' => 'SV0003',
                'group' => 'CS230-3C',
            ],
            [
                'id' => 'SV0004', 'name' => 'Dr. Siti Fatimah',
                'email' => 'sv4@sv.my', 'password' => 'password123',
                'employee_id' => 'SV0004',
                'group' => 'CS266-5D',
            ],
            [
                'id' => 'SV0005', 'name' => 'Prof. Tan Wei Ming',
                'email' => 'sv5@sv.my', 'password' => 'password123',
                'employee_id' => 'SV0005',
                'group' => 'CS240-4E',
            ],
        ];

        $studentData = [];

        foreach ($supervisors as $sv) {
            $svUser = User::create([
                'name' => $sv['name'],
                'email' => $sv['email'],
                'password' => Hash::make($sv['password']),
                'role' => 'supervisor',
                'matrix_id' => $sv['employee_id'],
                'employee_id' => $sv['employee_id'],
                'faculty' => $faculty,
            ]);

            $group = $sv['group'];
            $parts = explode('-', $group);
            $programmeCode = $parts[0];
            $class = $parts[1];

            for ($i = 1; $i <= 5; $i++) {
                $studentNum = (($svUser->id - 6) * 5) + $i;
                $matrixId = '2026'.str_pad((string) ($studentNum * 173 + 1000), 6, '0', STR_PAD_LEFT);

                $studentData[] = [
                    'matrix_id' => $matrixId,
                    'student_name' => 'Student '.str_pad((string) $studentNum, 2, '0', STR_PAD_LEFT),
                    'sv_matrix_id' => $sv['id'],
                    'programme_code' => $programmeCode,
                    'class' => $class,
                ];
            }
        }

        foreach ($studentData as $student) {
            SupervisorAssignment::create([
                'student_matrix_id' => $student['matrix_id'],
                'student_name' => $student['student_name'],
                'supervisor_matrix_id' => $student['sv_matrix_id'],
                'faculty' => $faculty,
                'programme_code' => $student['programme_code'],
                'class' => $student['class'],
            ]);
        }

        $this->command->info('Supervisor accounts created:');
        $this->command->info('  sv1@sv.my / password123  — Dr. Ahmad Fauzi');
        $this->command->info('  sv2@sv.my / password123  — Prof. Nurul Huda');
        $this->command->info('  sv3@sv.my / password123  — Dr. Ramesh Kumar');
        $this->command->info('  sv4@sv.my / password123  — Dr. Siti Fatimah');
        $this->command->info('  sv5@sv.my / password123  — Prof. Tan Wei Ming');
        $this->command->info('25 pre-assigned students created in supervisor_assignments table.');
    }
}
