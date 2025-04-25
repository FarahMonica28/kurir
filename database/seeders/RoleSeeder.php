<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'name' => 'admin',
            'guard_name' => 'api',
            'full_name' => 'Administrator',
        ]);
        Role::create([
            'name' => 'kurir',
            'guard_name' => 'api',
            'full_name' => 'Kurir',
        ]);
        Role::create([
            'name' => 'pengguna',
            'guard_name' => 'api',
            'full_name' => 'Pengguna',
        ]);
    }
}
