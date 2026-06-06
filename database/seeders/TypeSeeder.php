<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Type;

class TypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          // Tipos de acción
          $actionTypes = [
            'attack',
            'explore',
            'collect',
            'move',
            'invent',
            'defend',
        ];

        

    /**
     * Insertar los tipos de acción en la base de datos.
     */

     foreach ($actionTypes as $type) {
        Type::create(['name' => $type]);
    }

    }
}

