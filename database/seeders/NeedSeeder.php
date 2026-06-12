<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Need;
use App\Models\InventionType;

class NeedSeeder extends Seeder
{
    public function run(): void
    {
        // dependencia  entre los inventos
        $dependencies = [
            // 'Hijo' => ['Padre' => cantidad]
            'Lanza' => ['Piedra Afilada' => 1, 'Cuerda' => 1],
            'Arco y Flecha' => ['Lanza' => 1, 'Cuerda' => 1],
            'Trampa' => ['Cuerda' => 1, 'Cesta' => 1, 'Arco y Flecha' => 1],
            'Hacha' => ['Piedra Afilada' => 1],
            'Carro' => ['Cuerda' => 1, 'Cesta' => 1, 'Rueda' => 1, 'Hacha' => 1],
            'Traje de Malla' => ['Cuerda' => 1],
            'Catalejo' => ['Vidrio' => 1],
            'Cañón' => ['Pólvora' => 1],
        ];

        foreach ($dependencies as $child => $parents) {
            // recuepar  el  invento hijo
            $childInvention = InventionType::where('name', $child)->first();

            foreach ($parents as $parent => $quantity) {
                // recuperar  el invento padre
                $parentInvention = InventionType::where('name', $parent)->first();

                //  relación en la tabla needs
                Need::create([
                    'child_id' => $childInvention->id, 
                    'parent_id' => $parentInvention->id,
                    'quantity' => $quantity,
                ]);
            }
        }
    }
}