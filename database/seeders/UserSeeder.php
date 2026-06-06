<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {

        $adminRole = Role::where('name', 'Admin')->first(); 


        User::create([
            'name' => 'admin',
            'email' => 'admin@laraveland.es',
            'password' => bcrypt('admin'),
            'team_id' => null,
            'zone_id' => null,
            'role_id' => $adminRole->id,
        ]);
    }
}
