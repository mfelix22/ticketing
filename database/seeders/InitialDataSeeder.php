<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Priority;
use App\Models\RequestType;
use App\Models\RequestStatus;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create Departments
        $departments = [
            ['name' => 'IT', 'description' => 'Information Technology Department'],
            ['name' => 'HR', 'description' => 'Human Resources Department'],
            ['name' => 'Finance', 'description' => 'Finance Department'],
            ['name' => 'Operations', 'description' => 'Operations Department'],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }

        // Create Priorities with SLA times and descriptions
        $priorities = [
            [
                'name' => 'Urgent',
                'description' => 'System down, business stopped - Immediate attention required',
                'sla_response_hours' => 2,
                'sla_resolution_hours' => 4,
                'color' => 'danger', // Red
                'icon' => 'fa-exclamation-circle'
            ],
            [
                'name' => 'High',
                'description' => 'Major impact, significant disruption - Urgent attention needed',
                'sla_response_hours' => 4,
                'sla_resolution_hours' => 8,
                'color' => 'warning', // Orange
                'icon' => 'fa-arrow-up'
            ],
            [
                'name' => 'Medium',
                'description' => 'Normal priority, scheduled work - Standard processing',
                'sla_response_hours' => 8,
                'sla_resolution_hours' => 24,
                'color' => 'info', // Blue
                'icon' => 'fa-minus'
            ],
            [
                'name' => 'Low',
                'description' => 'Nice to have, minimal impact - Process when time permits',
                'sla_response_hours' => 24,
                'sla_resolution_hours' => 72,
                'color' => 'success', // Green
                'icon' => 'fa-arrow-down'
            ],
        ];

        foreach ($priorities as $priority) {
            Priority::updateOrCreate(
                ['name' => $priority['name']],
                $priority
            );
        }

        // Create Request Types
        $requestTypes = [
            ['name' => 'Hardware', 'description' => 'Hardware-related requests'],
            ['name' => 'Software', 'description' => 'Software-related requests'],
            ['name' => 'Data', 'description' => 'Data-related requests'],
            ['name' => 'Other', 'description' => 'Other types of requests'],
        ];

        foreach ($requestTypes as $type) {
            RequestType::create($type);
        }

        // Create Request Statuses
        $statuses = [
            ['name' => 'Open', 'description' => 'Newly created ticket'],
            ['name' => 'In Progress', 'description' => 'Ticket is being worked on'],
            ['name' => 'Pending Approval', 'description' => 'Waiting for requester verification'],
            ['name' => 'Completed', 'description' => 'Work completed and verified by requester'],
            ['name' => 'Cancelled', 'description' => 'Ticket rejected by approver'],
            ['name' => 'Closed', 'description' => 'Ticket closed (legacy - use Completed or Cancelled)'],
        ];

        foreach ($statuses as $status) {
            RequestStatus::firstOrCreate(['name' => $status['name']], $status);
        }
    }
}
