<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'System Administrator',
                'email' => 'admin@nextgen.local',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Asset Officer',
                'email' => 'assets@nextgen.local',
                'role' => 'asset_officer',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'ICT Support',
                'email' => 'support@nextgen.local',
                'role' => 'staff',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Operations Manager',
                'email' => 'operations@nextgen.local',
                'role' => 'manager',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Procurement Officer',
                'email' => 'procurement@nextgen.local',
                'role' => 'procurement_officer',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Internal Auditor',
                'email' => 'auditor@nextgen.local',
                'role' => 'auditor',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
