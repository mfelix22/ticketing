<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RequestType;

class UpdateCourierRequestTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update Delivery/Courier request type to require external handling
        $courier = RequestType::where('name', 'LIKE', '%Delivery%')
            ->orWhere('name', 'LIKE', '%delivery%')
            ->orWhere('name', 'LIKE', '%Courier%')
            ->orWhere('name', 'LIKE', '%courier%')
            ->first();

        if ($courier) {
            $courier->update([
                'requires_external_handling' => true
            ]);
            $this->command->info("✅ Updated '{$courier->name}' to require external handling");
        } else {
            $this->command->warn("⚠️  Delivery/Courier request type not found");
        }

        // Show all request types and their settings
        $this->command->info("\nCurrent Request Types:");
        $this->command->table(
            ['ID', 'Name', 'Requires External Handling'],
            RequestType::all()->map(function ($type) {
                return [
                    $type->id,
                    $type->name,
                    $type->requires_external_handling ? 'Yes' : 'No'
                ];
            })
        );
    }
}
