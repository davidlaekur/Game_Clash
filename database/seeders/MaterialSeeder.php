<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Material;
use App\Models\Zone;
use App\Models\MaterialType;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $zones = Zone::all();

        $materials = [
            // Rocas
            ['name' => 'Silex', 'category' => 'Roca', 'density' => 2.65],
            ['name' => 'Obsidiana', 'category' => 'Roca', 'density' => 2.4],
            ['name' => 'Granito', 'category' => 'Roca', 'density' => 2.7],

            // Minerales
            ['name' => 'Caolinita', 'category' => 'Mineral', 'density' => 2.6],
            ['name' => 'Illita', 'category' => 'Mineral', 'density' => 2.7],
            ['name' => 'Montmorillonita', 'category' => 'Mineral', 'density' => 2.35],
            ['name' => 'Cuarzo', 'category' => 'Mineral', 'density' => 2.65],
            ['name' => 'Grafito', 'category' => 'Mineral', 'density' => 2.2],
            ['name' => 'Minerales semiconductores', 'category' => 'Mineral', 'density' => 5.0],
            ['name' => 'Cristales naturales', 'category' => 'Mineral', 'density' => 2.5],
            ['name' => 'Materiales magneticos naturales', 'category' => 'Mineral', 'density' => 5.2],

            // Arenas
            ['name' => 'Arena de silice', 'category' => 'Arena', 'density' => 1.6],
            ['name' => 'Arena de cuarzo', 'category' => 'Arena', 'density' => 1.55],
            ['name' => 'Arena de playa', 'category' => 'Arena', 'density' => 1.4],

            // Metales
            ['name' => 'Hierro', 'category' => 'Metal', 'density' => 7.874],
            ['name' => 'Cobre', 'category' => 'Metal', 'density' => 8.96],
            ['name' => 'Estaño', 'category' => 'Metal', 'density' => 7.28],
            ['name' => 'Plata', 'category' => 'Metal', 'density' => 10.49],
            ['name' => 'Oro', 'category' => 'Metal', 'density' => 19.3],
            ['name' => 'Plomo', 'category' => 'Metal', 'density' => 11.34],

            // Maderas
            ['name' => 'Roble', 'category' => 'Madera', 'density' => 0.71],
            ['name' => 'Pino', 'category' => 'Madera', 'density' => 0.55],
            ['name' => 'Cedro', 'category' => 'Madera', 'density' => 0.38],
            ['name' => 'Madera de pino', 'category' => 'Madera', 'density' => 0.55],
            ['name' => 'Madera seca', 'category' => 'Madera', 'density' => 0.45],

            // Fibras
            ['name' => 'Cañamo', 'category' => 'Fibra', 'density' => 1.5],
            ['name' => 'Lino', 'category' => 'Fibra', 'density' => 1.4],
            ['name' => 'Yute', 'category' => 'Fibra', 'density' => 1.3],
            ['name' => 'Caña comun', 'category' => 'Fibra', 'density' => 0.6],
            ['name' => 'Totora', 'category' => 'Fibra', 'density' => 0.5],
            ['name' => 'Carrizo', 'category' => 'Fibra', 'density' => 0.4],
            ['name' => 'Bambu', 'category' => 'Fibra', 'density' => 0.7],
            ['name' => 'Algodon', 'category' => 'Fibra', 'density' => 1.54],
            ['name' => 'Lana', 'category' => 'Fibra', 'density' => 1.3],
        ];

        foreach ($materials as $materialData) {
            $materialType = MaterialType::where('category', $materialData['category'])->first();

            // probabilidad segun  categoría del material
            $baseProbability = match ($materialType->category) {
                'Roca' => 30,
                'Mineral' => 20,
                'Arena' => 70,
                'Metal' => 10,
                'Madera' => 50,
                'Fibra' => 60,
                default => 10,
            };
        
            // probabilidad  inversamente proporcional a la densidad solo un decimal 
            $probability = round(max(1, min(100, $baseProbability / (1 + ($materialData['density'] / 2)))), 1); // aumento el valor con +1 para uamentar la probabilidad
        
            // eficiencia como inversa de la probabilidad maximo 10
            $efficiency = min(10, round(10 / $probability, 1));
        
            // cantidad inicial basada en probabilidad
            $quantity = max(10, (int)($probability * 5));
    

            
            Material::create([
                'name' => $materialData['name'],
                'density' => $materialData['density'],
                'probability' => $probability,
                'efficiency' => $efficiency,
                'quantity' => $quantity,
                'zone_id' => $zones->random()->id,
                'materialtype_id' => $materialType->id,
            ]);
        }
    }
}
