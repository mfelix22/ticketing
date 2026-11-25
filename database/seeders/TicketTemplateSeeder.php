<?php

namespace Database\Seeders;

use App\Models\TicketTemplate;
use App\Models\RequestType;
use App\Models\Priority;
use Illuminate\Database\Seeder;

class TicketTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get actual request types and priorities
        $hardware = RequestType::where('name', 'Hardware')->first();
        $software = RequestType::where('name', 'Software')->first();
        $data = RequestType::where('name', 'Data')->first();
        $other = RequestType::where('name', 'Other')->first();
        $courier = RequestType::where('name', 'LIKE', '%Courier%')->first();

        $urgent = Priority::where('name', 'Urgent')->first();
        $high = Priority::where('name', 'High')->first();
        $medium = Priority::where('name', 'Medium')->first();
        $low = Priority::where('name', 'Low')->first();

        // Debug: Show what we found
        $this->command->info('Found Request Types:');
        $this->command->info('  Hardware: ' . ($hardware ? $hardware->name : 'NOT FOUND'));
        $this->command->info('  Software: ' . ($software ? $software->name : 'NOT FOUND'));
        $this->command->info('  Data: ' . ($data ? $data->name : 'NOT FOUND'));
        $this->command->info('  Other: ' . ($other ? $other->name : 'NOT FOUND'));
        $this->command->info('  Courier: ' . ($courier ? $courier->name : 'NOT FOUND'));

        $templates = [];

        // Hardware Templates
        if ($hardware) {
            $templates[] = [
                'name' => 'Laptop Screen Repair',
                'description' => 'For laptop screen issues (cracked, black screen, flickering)',
                'request_type_id' => $hardware->id,
                'title_template' => 'Laptop screen replacement needed',
                'default_priority_id' => $high?->id,
                'field_defaults' => [
                    'issue_type' => 'Screen Damage',
                    'problem_description' => 'Laptop screen is cracked/not displaying properly',
                ],
                'is_active' => true,
            ];

            $templates[] = [
                'name' => 'Laptop Battery Issue',
                'description' => 'For laptop battery problems (not charging, draining fast)',
                'request_type_id' => $hardware->id,
                'title_template' => 'Laptop battery replacement required',
                'default_priority_id' => $medium?->id,
                'field_defaults' => [
                    'issue_type' => 'Battery Issue',
                    'problem_description' => 'Laptop battery not holding charge or draining quickly',
                ],
                'is_active' => true,
            ];

            $templates[] = [
                'name' => 'Keyboard Malfunction',
                'description' => 'For keyboard issues (keys not working, stuck keys)',
                'request_type_id' => $hardware->id,
                'title_template' => 'Keyboard repair needed',
                'default_priority_id' => $medium?->id,
                'field_defaults' => [
                    'issue_type' => 'Keyboard Malfunction',
                    'problem_description' => 'Keyboard keys not responding or stuck',
                ],
                'is_active' => true,
            ];

            $templates[] = [
                'name' => 'Printer Not Working',
                'description' => 'For printer issues (not printing, paper jam, toner)',
                'request_type_id' => $hardware->id,
                'title_template' => 'Printer repair/maintenance needed',
                'default_priority_id' => $medium?->id,
                'field_defaults' => [
                    'issue_type' => 'Printer Issue',
                    'problem_description' => 'Printer not functioning properly',
                ],
                'is_active' => true,
            ];

            $templates[] = [
                'name' => 'Mouse/Keyboard Request',
                'description' => 'Request for new mouse or keyboard',
                'request_type_id' => $hardware->id,
                'title_template' => 'New mouse/keyboard needed',
                'default_priority_id' => $low?->id,
                'field_defaults' => [
                    'item_requested' => 'Mouse/Keyboard',
                ],
                'is_active' => true,
            ];
        }

        // Software Templates
        if ($software) {
            $templates[] = [
                'name' => 'Microsoft Office Installation',
                'description' => 'Install or repair Microsoft Office suite',
                'request_type_id' => $software->id,
                'title_template' => 'Microsoft Office installation needed',
                'default_priority_id' => $medium?->id,
                'field_defaults' => [
                    'software_name' => 'Microsoft Office',
                    'request_type' => 'Installation',
                ],
                'is_active' => true,
            ];

            $templates[] = [
                'name' => 'Password Reset',
                'description' => 'Reset password for system/application access',
                'request_type_id' => $software->id,
                'title_template' => 'Password reset required',
                'default_priority_id' => $high?->id,
                'field_defaults' => [
                    'system_name' => 'System Access',
                    'issue_description' => 'Unable to login - password reset needed',
                ],
                'is_active' => true,
            ];

            $templates[] = [
                'name' => 'Email Access Issue',
                'description' => 'Problems with email access or Outlook',
                'request_type_id' => $software->id,
                'title_template' => 'Email access problem',
                'default_priority_id' => $high?->id,
                'field_defaults' => [
                    'software_name' => 'Email/Outlook',
                    'issue_description' => 'Cannot access email',
                ],
                'is_active' => true,
            ];

            $templates[] = [
                'name' => 'Software License Request',
                'description' => 'Request for software license or activation',
                'request_type_id' => $software->id,
                'title_template' => 'Software license needed',
                'default_priority_id' => $medium?->id,
                'field_defaults' => [
                    'request_type' => 'License Activation',
                ],
                'is_active' => true,
            ];
        }

        // Data Templates
        if ($data) {
            $templates[] = [
                'name' => 'Data Recovery Request',
                'description' => 'Recover deleted or lost data/files',
                'request_type_id' => $data->id,
                'title_template' => 'Data recovery needed',
                'default_priority_id' => $high?->id,
                'field_defaults' => [
                    'data_type' => 'Files/Documents',
                    'urgency' => 'High',
                ],
                'is_active' => true,
            ];

            $templates[] = [
                'name' => 'Shared Drive Access',
                'description' => 'Request access to shared network drive',
                'request_type_id' => $data->id,
                'title_template' => 'Shared drive access needed',
                'default_priority_id' => $medium?->id,
                'field_defaults' => [
                    'access_type' => 'Shared Drive',
                    'permission_level' => 'Read/Write',
                ],
                'is_active' => true,
            ];

            $templates[] = [
                'name' => 'Data Backup Request',
                'description' => 'Request for data backup service',
                'request_type_id' => $data->id,
                'title_template' => 'Data backup needed',
                'default_priority_id' => $medium?->id,
                'field_defaults' => [
                    'backup_type' => 'Full Backup',
                ],
                'is_active' => true,
            ];
        }

        // Other Templates
        if ($other) {
            $templates[] = [
                'name' => 'General IT Support',
                'description' => 'General IT support or inquiry',
                'request_type_id' => $other->id,
                'title_template' => 'IT support needed',
                'default_priority_id' => $low?->id,
                'field_defaults' => [
                    'issue_category' => 'General Support',
                ],
                'is_active' => true,
            ];

            $templates[] = [
                'name' => 'Office Supplies Request',
                'description' => 'Request for office supplies (stationery, toner, etc.)',
                'request_type_id' => $other->id,
                'title_template' => 'Office supplies needed',
                'default_priority_id' => $low?->id,
                'field_defaults' => [
                    'item_category' => 'Office Supplies',
                ],
                'is_active' => true,
            ];
        }

        // Courier Templates
        if ($courier) {
            $templates[] = [
                'name' => 'Document Pickup - Urgent',
                'description' => 'Urgent document pickup and delivery',
                'request_type_id' => $courier->id,
                'title_template' => 'Urgent document pickup needed',
                'default_priority_id' => $urgent?->id,
                'field_defaults' => [
                    'package_description' => 'Important documents',
                ],
                'is_active' => true,
            ];

            $templates[] = [
                'name' => 'Package Pickup - Same Day',
                'description' => 'Same-day package pickup and delivery service',
                'request_type_id' => $courier->id,
                'title_template' => 'Same-day package pickup required',
                'default_priority_id' => $high?->id,
                'field_defaults' => [
                    'preferred_delivery_time' => 'Same Day',
                ],
                'is_active' => true,
            ];
        }

        // Insert all templates
        foreach ($templates as $template) {
            TicketTemplate::create($template);
        }

        $this->command->info('Ticket templates seeded successfully!');
    }
}
