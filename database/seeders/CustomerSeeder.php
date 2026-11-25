<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'PT. Maju Jaya',
                'code' => 'CUST001',
                'phone' => '021-12345678',
                'address' => 'Jl. Sudirman No. 123, Jakarta Pusat, DKI Jakarta 10110',
                'is_active' => true,
            ],
            [
                'name' => 'CV. Berkah Sejahtera',
                'code' => 'CUST002',
                'phone' => '022-87654321',
                'address' => 'Jl. Asia Afrika No. 45, Bandung, Jawa Barat 40111',
                'is_active' => true,
            ],
            [
                'name' => 'Toko Makmur Sentosa',
                'code' => 'CUST003',
                'phone' => '031-11223344',
                'address' => 'Jl. Tunjungan No. 67, Surabaya, Jawa Timur 60275',
                'is_active' => true,
            ],
            [
                'name' => 'PT. Cahaya Abadi',
                'code' => 'CUST004',
                'phone' => '024-55667788',
                'address' => 'Jl. Pemuda No. 89, Semarang, Jawa Tengah 50132',
                'is_active' => true,
            ],
            [
                'name' => 'UD. Sumber Rezeki',
                'code' => 'CUST005',
                'phone' => '0274-998877',
                'address' => 'Jl. Malioboro No. 21, Yogyakarta, DIY 55213',
                'is_active' => true,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }

        $this->command->info('✅ Sample customers created successfully!');
    }
}
