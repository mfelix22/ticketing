<?php

namespace Database\Seeders;

use App\Models\RequestType;
use App\Models\RequestTypeField;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AllRequestTypesFieldsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $requestTypes = RequestType::all();

        foreach ($requestTypes as $requestType) {
            $this->command->info("Setting up fields for: {$requestType->name}");

            // Check if this request type already has fields
            if ($requestType->fields()->count() > 0) {
                $this->command->warn("  - Already has {$requestType->fields()->count()} field(s), skipping");
                continue;
            }

            // Define default fields based on request type name
            $fields = $this->getFieldsForRequestType($requestType->name);

            foreach ($fields as $fieldData) {
                $fieldData['request_type_id'] = $requestType->id;
                RequestTypeField::create($fieldData);
            }

            $fieldsCount = count($fields);
            $this->command->info("  - Added {$fieldsCount} field(s)");
        }

        $this->command->info('All request types now have custom fields!');
    }

    /**
     * Get default fields for each request type
     */
    private function getFieldsForRequestType(string $typeName): array
    {
    // Normalize the name for matching
    $typeName = strtolower($typeName);

    // Delivery already has fields from CourierRequestTypeSeeder
    if (str_contains($typeName, 'delivery') || str_contains($typeName, 'courier')) {
        return [];
    }        // Software/Application request
        if (str_contains($typeName, 'software') || str_contains($typeName, 'application') || str_contains($typeName, 'app')) {
            return [
                [
                    'field_name' => 'Software/Application Name',
                    'field_key' => 'software_name',
                    'field_type' => 'text',
                    'is_required' => true,
                    'placeholder' => 'Enter software or application name',
                    'order' => 1,
                ],
                [
                    'field_name' => 'License Type',
                    'field_key' => 'license_type',
                    'field_type' => 'select',
                    'is_required' => false,
                    'field_options' => ['Individual', 'Team', 'Enterprise', 'Free/Open Source'],
                    'order' => 2,
                ],
                [
                    'field_name' => 'Purpose/Justification',
                    'field_key' => 'purpose',
                    'field_type' => 'textarea',
                    'is_required' => true,
                    'placeholder' => 'Explain why you need this software',
                    'order' => 3,
                ],
                [
                    'field_name' => 'Additional Notes',
                    'field_key' => 'additional_notes',
                    'field_type' => 'textarea',
                    'is_required' => false,
                    'placeholder' => 'Any other information',
                    'order' => 4,
                ],
            ];
        }

        // Hardware request
        if (str_contains($typeName, 'hardware') || str_contains($typeName, 'equipment')) {
            return [
                [
                    'field_name' => 'Item/Equipment Name',
                    'field_key' => 'item_name',
                    'field_type' => 'text',
                    'is_required' => true,
                    'placeholder' => 'Enter hardware or equipment name',
                    'order' => 1,
                ],
                [
                    'field_name' => 'Quantity',
                    'field_key' => 'quantity',
                    'field_type' => 'number',
                    'is_required' => true,
                    'placeholder' => '1',
                    'order' => 2,
                ],
                [
                    'field_name' => 'Specifications',
                    'field_key' => 'specifications',
                    'field_type' => 'textarea',
                    'is_required' => false,
                    'placeholder' => 'Describe required specifications',
                    'order' => 3,
                ],
                [
                    'field_name' => 'Purpose',
                    'field_key' => 'purpose',
                    'field_type' => 'textarea',
                    'is_required' => true,
                    'placeholder' => 'Explain why you need this hardware',
                    'order' => 4,
                ],
                [
                    'field_name' => 'Additional Notes',
                    'field_key' => 'additional_notes',
                    'field_type' => 'textarea',
                    'is_required' => false,
                    'placeholder' => 'Any other information',
                    'order' => 5,
                ],
            ];
        }

        // IT Support/Technical Issue
        if (str_contains($typeName, 'support') || str_contains($typeName, 'technical') || str_contains($typeName, 'issue') || str_contains($typeName, 'problem')) {
            return [
                [
                    'field_name' => 'Issue Description',
                    'field_key' => 'issue_description',
                    'field_type' => 'textarea',
                    'is_required' => true,
                    'placeholder' => 'Describe the issue in detail',
                    'order' => 1,
                ],
                [
                    'field_name' => 'Affected System/Application',
                    'field_key' => 'affected_system',
                    'field_type' => 'text',
                    'is_required' => false,
                    'placeholder' => 'Which system or application is affected?',
                    'order' => 2,
                ],
                [
                    'field_name' => 'Error Message (if any)',
                    'field_key' => 'error_message',
                    'field_type' => 'textarea',
                    'is_required' => false,
                    'placeholder' => 'Copy and paste any error messages',
                    'order' => 3,
                ],
                [
                    'field_name' => 'Steps to Reproduce',
                    'field_key' => 'steps_to_reproduce',
                    'field_type' => 'textarea',
                    'is_required' => false,
                    'placeholder' => 'How can we reproduce this issue?',
                    'order' => 4,
                ],
            ];
        }

        // Access Request
        if (str_contains($typeName, 'access') || str_contains($typeName, 'permission')) {
            return [
                [
                    'field_name' => 'System/Resource Name',
                    'field_key' => 'system_name',
                    'field_type' => 'text',
                    'is_required' => true,
                    'placeholder' => 'Which system or resource do you need access to?',
                    'order' => 1,
                ],
                [
                    'field_name' => 'Access Level Required',
                    'field_key' => 'access_level',
                    'field_type' => 'select',
                    'is_required' => true,
                    'field_options' => ['Read Only', 'Read/Write', 'Admin', 'Full Access'],
                    'order' => 2,
                ],
                [
                    'field_name' => 'Business Justification',
                    'field_key' => 'justification',
                    'field_type' => 'textarea',
                    'is_required' => true,
                    'placeholder' => 'Explain why you need this access',
                    'order' => 3,
                ],
                [
                    'field_name' => 'Duration',
                    'field_key' => 'duration',
                    'field_type' => 'select',
                    'is_required' => false,
                    'field_options' => ['Temporary (specify end date)', 'Permanent', '30 days', '90 days', '1 year'],
                    'order' => 4,
                ],
            ];
        }

        // Default/Generic request type
        return [
            [
                'field_name' => 'Request Details',
                'field_key' => 'request_details',
                'field_type' => 'textarea',
                'is_required' => true,
                'placeholder' => 'Please provide detailed information about your request',
                'order' => 1,
            ],
            [
                'field_name' => 'Expected Outcome',
                'field_key' => 'expected_outcome',
                'field_type' => 'textarea',
                'is_required' => false,
                'placeholder' => 'What do you expect as a result of this request?',
                'order' => 2,
            ],
            [
                'field_name' => 'Additional Notes',
                'field_key' => 'additional_notes',
                'field_type' => 'textarea',
                'is_required' => false,
                'placeholder' => 'Any other information',
                'order' => 3,
            ],
        ];
    }
}
