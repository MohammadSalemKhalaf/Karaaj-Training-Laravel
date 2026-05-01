<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Database\Seeder;

class OperationalDataSeeder extends Seeder
{
    public function run(): void
    {
        $employee = Employee::query()->first();
        $admin = User::query()->whereHas('role', function ($query): void {
            $query->where('name', 'admin');
        })->first();

        if (! $employee) {
            return;
        }

        if (! Salary::query()->where('employee_id', $employee->id)->whereDate('effective_date', now()->subMonth()->startOfMonth())->exists()) {
            Salary::query()->forceCreate([
                'employee_id' => $employee->id,
                'effective_date' => now()->subMonth()->startOfMonth()->toDateString(),
                'amount' => 1000,
                'bonuses' => 200,
                'deductions' => 50,
                'notes' => 'Initial seeded salary record.',
                'net_salary' => 1150,
            ]);
        }

        LeaveRequest::query()->firstOrCreate(
            [
                'employee_id' => $employee->id,
                'start_date' => now()->subDays(12)->toDateString(),
                'end_date' => now()->subDays(10)->toDateString(),
            ],
            [
                'approved_by' => $admin?->id,
                'description' => 'Seeded annual leave for frontend validation.',
                'total_days' => 3,
                'status' => LeaveRequest::STATUS_APPROVED,
                'rejection_reason' => null,
            ]
        );

        AttendanceRecord::query()->firstOrCreate(
            [
                'employee_id' => $employee->id,
                'attendance_date' => now()->subDay()->toDateString(),
            ],
            [
                'check_in_time' => now()->subDay()->setTime(9, 0, 0),
                'check_out_time' => now()->subDay()->setTime(17, 0, 0),
                'status' => 'present',
                'notes' => 'Seeded attendance record for validation.',
            ]
        );
    }
}
