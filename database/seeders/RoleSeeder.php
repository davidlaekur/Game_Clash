<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['name' => 'Explorer', 'base_capacity' => 50],
            ['name' => 'Collector', 'base_capacity' => 100],
            ['name' => 'Strategist', 'base_capacity' => 75],
            ['name' => 'Inventor', 'base_capacity' => 75],
            ['name' => 'Admin', 'base_capacity' => null],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
