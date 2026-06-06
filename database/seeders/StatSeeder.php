<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Stat;

class StatSeeder extends Seeder
{
    public function run(): void
    {
        $stats = [
            ['name' => 'ataque'],
            ['name' => 'defensa'],
            ['name' => 'salud'],
            ['name' => 'velocidad'],
            ['name' => 'suerte'],
            ['name' => 'capacidad'],
            ['name' => 'ingenio'], // wit
        ];

        foreach ($stats as $stat) {
            Stat::create($stat);
        }
    }
}
