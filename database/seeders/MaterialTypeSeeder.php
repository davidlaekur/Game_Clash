<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MaterialType;

class MaterialTypeSeeder extends Seeder
{
    public function run(): void
    {
        $materialTypes = [
            ['category' => 'Roca'],
            ['category' => 'Mineral'],
            ['category' => 'Arena'],
            ['category' => 'Metal'],
            ['category' => 'Madera'],
            ['category' => 'Fibra'],
            ['category' => 'Orgánico'], // plantas/alimento -> salud
            ['category' => 'Estelar'],  // materia del espacio (premio de aventura)
        ];

        foreach ($materialTypes as $type) {
            MaterialType::create($type);
        }
    }
}
