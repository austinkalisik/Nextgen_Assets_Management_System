<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        Supplier::insert([
            [
                'name' => 'Samsung Supplier',
                'email' => 'samsung@supplier.com',
                'phone' => '09123456789',
                'company' => 'Samsung Inc.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Apple Distributor',
                'email' => 'apple@supplier.com',
                'phone' => '09987654321',
                'company' => 'Apple Inc.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}