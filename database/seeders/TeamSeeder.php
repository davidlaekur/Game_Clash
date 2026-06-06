<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            ['name' => 'Guardians of Laraveland','image' => 'images/flags/laravelFlag.gif'],
            ['name' => 'Legion of Mordor','image' => 'images/flags/mordorFlag.gif'],    
        ];

        foreach ($teams as $team) {
            Team::create($team);
        }
    }
}
