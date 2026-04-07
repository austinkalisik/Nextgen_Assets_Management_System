<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $laptop = Category::where('name', 'Laptops')->first();
        $network = Category::where('name', 'Networking')->first();
        $printer = Category::where('name', 'Printers')->first();

        $itSupport = Department::where('name', 'IT Support')->first();
        $networking = Department::where('name', 'Networking')->first();
        $admin = Department::where('name', 'Administration')->first();

        $dell = Supplier::where('email', 'dell@supplier.local')->first();
        $hp = Supplier::where('email', 'hp@supplier.local')->first();
        $cisco = Supplier::where('email', 'cisco@supplier.local')->first();

        $rows = [
            [
                'name' => 'Dell Latitude 5440',
                'category_id' => $laptop?->id,
                'supplier_id' => $dell?->id,
                'department_id' => $itSupport?->id,
                'asset_tag' => 'NGA-0001',
                'serial_number' => 'DL5440-001',
                'quantity' => 5,
                'status' => 'available',
                'location' => 'Port Moresby HQ',
                'purchase_date' => '2026-01-10',
            ],
            [
                'name' => 'HP LaserJet Pro 4003',
                'category_id' => $printer?->id,
                'supplier_id' => $hp?->id,
                'department_id' => $admin?->id,
                'asset_tag' => 'NGA-0002',
                'serial_number' => 'HP4003-002',
                'quantity' => 2,
                'status' => 'maintenance',
                'location' => 'Admin Office',
                'purchase_date' => '2026-01-12',
            ],
            [
                'name' => 'Cisco Catalyst 9200',
                'category_id' => $network?->id,
                'supplier_id' => $cisco?->id,
                'department_id' => $networking?->id,
                'asset_tag' => 'NGA-0003',
                'serial_number' => 'CS9200-003',
                'quantity' => 1,
                'status' => 'assigned',
                'location' => 'Server Room',
                'purchase_date' => '2026-01-15',
            ],
        ];

        foreach ($rows as $row) {
            Item::updateOrCreate(
                ['asset_tag' => $row['asset_tag']],
                $row
            );
        }
    }
}