<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            SupplierSeeder::class,
            DepartmentSeeder::class,
            ItemSeeder::class,
            StockMovementSeeder::class,
            AssignmentSeeder::class,
            AssetLogSeeder::class,
        ]);
    }
}
