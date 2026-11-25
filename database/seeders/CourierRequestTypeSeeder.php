<?php

namespace Database\Seeders;

use App\Models\RequestType;
use App\Models\RequestTypeField;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourierRequestTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or find the Delivery request type
        $courierType = RequestType::firstOrCreate(
            ['name' => 'Delivery'],
            ['description' => 'Delivery and courier service requests']
        );

        // Define the fields for Delivery request type
        $fields = [
            [
                'field_name' => 'Delivery Type',
                'field_key' => 'delivery_type',
                'field_type' => 'select',
                'is_required' => true,
                'field_options' => ['Courier', 'Expedition'],
                'order' => 1,
            ],
            [
                'field_name' => 'Delivery Address',
                'field_key' => 'delivery_address',
                'field_type' => 'textarea',
                'is_required' => true,
                'placeholder' => 'Enter the complete delivery address',
                'order' => 2,
            ],
            [
                'field_name' => 'Recipient Name',
                'field_key' => 'recipient_name',
                'field_type' => 'text',
                'is_required' => true,
                'placeholder' => 'Name of the person receiving the delivery',
                'order' => 3,
            ],
            [
                'field_name' => 'Phone Number',
                'field_key' => 'phone_number',
                'field_type' => 'tel',
                'is_required' => true,
                'placeholder' => 'Contact number for delivery',
                'validation_rules' => 'regex:/^[0-9]{10,15}$/',
                'order' => 4,
            ],
            [
                'field_name' => 'Preferred Delivery Time',
                'field_key' => 'delivery_time',
                'field_type' => 'select',
                'is_required' => false,
                'field_options' => ['Morning (8AM - 12PM)', 'Afternoon (12PM - 5PM)', 'Evening (5PM - 8PM)'],
                'order' => 5,
            ],
            [
                'field_name' => 'Package Description',
                'field_key' => 'package_description',
                'field_type' => 'textarea',
                'is_required' => false,
                'placeholder' => 'Describe the items to be delivered',
                'order' => 6,
            ],
            [
                'field_name' => 'Additional Notes',
                'field_key' => 'additional_notes',
                'field_type' => 'textarea',
                'is_required' => false,
                'placeholder' => 'Any other information or special instructions',
                'order' => 7,
            ],
        ];

        // Create the fields
        foreach ($fields as $fieldData) {
            RequestTypeField::updateOrCreate(
                [
                    'request_type_id' => $courierType->id,
                    'field_key' => $fieldData['field_key'],
                ],
                $fieldData
            );
        }

        $this->command->info('Courier request type and fields created successfully!');
    }
}
