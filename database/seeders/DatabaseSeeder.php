<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
/**
 * MAIN SEEDER
 * This file calls ALL other seeders
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,          
            SupplierSeeder::class,
            ItemSeeder::class,
        ]);
    }
}