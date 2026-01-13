<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::firstOrCreate(['name' => 'superadmin']);
        Role::firstOrCreate(['name' => 'admin_toko']);
        Role::firstOrCreate(['name' => 'cashier']);
        Role::firstOrCreate(['name' => 'member']);
    }
}
