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

            // Orgánicos (plantas/alimento/curativos -> sustento, dan salud).
            // Aquí 'density' = VALOR nutricional/medicinal: a más valor, más raro
            // (menos probabilidad) y cura más. Coherente con minerales (valor->rareza+potencia).
            ['name' => 'Bayas', 'category' => 'Orgánico', 'density' => 0.5],
            ['name' => 'Setas', 'category' => 'Orgánico', 'density' => 0.55],
            ['name' => 'Hierba medicinal', 'category' => 'Orgánico', 'density' => 0.7],
            ['name' => 'Aloe', 'category' => 'Orgánico', 'density' => 0.9],
            ['name' => 'Miel', 'category' => 'Orgánico', 'density' => 1.4],
            ['name' => 'Raiz curativa', 'category' => 'Orgánico', 'density' => 1.8],
            ['name' => 'Pescado', 'category' => 'Orgánico', 'density' => 0.8, 'landscapes' => ['isla', 'playa']],

            // Fauna (caza -> comida -> salud). 'density' = valor: presas grandes
            // (oso, bisonte) raras y muy nutritivas; pequeñas (conejo) comunes.
            ['name' => 'Conejo', 'category' => 'Orgánico', 'density' => 0.7, 'landscapes' => ['pradera', 'bosque', 'meseta']],
            ['name' => 'Cabra montes', 'category' => 'Orgánico', 'density' => 1.1, 'landscapes' => ['montaña', 'meseta']],
            ['name' => 'Venado', 'category' => 'Orgánico', 'density' => 1.3, 'landscapes' => ['bosque', 'pradera', 'selva', 'jungla']],
            ['name' => 'Jabali', 'category' => 'Orgánico', 'density' => 1.4, 'landscapes' => ['bosque', 'selva', 'pantano']],
            ['name' => 'Foca', 'category' => 'Orgánico', 'density' => 1.5, 'landscapes' => ['glaciar', 'polo', 'playa']],
            ['name' => 'Oso', 'category' => 'Orgánico', 'density' => 2.0, 'landscapes' => ['bosque', 'montaña', 'cueva']],
            ['name' => 'Bisonte', 'category' => 'Orgánico', 'density' => 2.4, 'landscapes' => ['pradera', 'meseta']],

            // Mineral igniter para el fuego
            ['name' => 'Fosforo', 'category' => 'Mineral', 'density' => 1.82],
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
                'Orgánico' => 55,
                default => 10,
            };
        
            // probabilidad  inversamente proporcional a la densidad solo un decimal 
            $probability = round(max(1, min(100, $baseProbability / (1 + ($materialData['density'] / 2)))), 1); // aumento el valor con +1 para uamentar la probabilidad
        
            // eficiencia como inversa de la probabilidad maximo 10
            $efficiency = min(10, round(10 / $probability, 1));
        
            // cantidad inicial basada en probabilidad
            $quantity = max(10, (int)($probability * 5));
    

            
            // sorteo PONDERADO: familias valiosas tienden a zonas de más defensa
            // orográfica, con azar. 'landscapes' del material fija su hábitat (fauna).
            $zoneId = $this->pickZone($zones, $materialType->category, $materialData['landscapes'] ?? null)->id;

            Material::create([
                'name' => $materialData['name'],
                'density' => $materialData['density'],
                'probability' => $probability,
                'efficiency' => $efficiency,
                'quantity' => $quantity,
                'max_quantity' => $quantity,   // tope de regeneración
                'regenerated_at' => now(),
                'zone_id' => $zoneId,
                'materialtype_id' => $materialType->id,
            ]);
        }

        // material estelar: NO se recolecta (sin zona, stock 0). Solo se obtiene
        // como premio de aventura, que lo añade al inventario del jugador.
        $estelar = MaterialType::where('category', 'Estelar')->first();
        if ($estelar) {
            Material::create([
                'name' => 'Aleacion estelar',
                'density' => 8.0,
                'probability' => 0,
                'efficiency' => 10,
                'quantity' => 0,
                'zone_id' => null,
                'materialtype_id' => $estelar->id,
            ]);
        }
    }

    /**
     * Sorteo ponderado de zona: las familias valiosas (material_value alto) tienden
     * a zonas de más defensa orográfica y las abundantes a las de menos, con azar
     * para que cada partida sea distinta. El bioma (material_landscapes) actúa de
     * filtro suave: las zonas no acordes son posibles pero mucho menos probables.
     */
    private function pickZone($zones, string $category, ?array $override = null)
    {
        $value = config('material_value')[$category] ?? 0.4;
        $suitable = $override ?? (config('material_landscapes')[$category] ?? []);
        $defMin = $zones->min('defense');
        $span = max(1, $zones->max('defense') - $defMin);

        $weighted = [];
        $total = 0;
        foreach ($zones as $z) {
            $d = ($z->defense - $defMin) / $span;                 // 0..1 defensa orográfica
            $affinity = $value * $d + (1 - $value) * (1 - $d);    // alto si valor y defensa coinciden
            $biomeFit = in_array($z->landscape, $suitable, true) ? 1.0 : 0.10;
            $w = ($affinity + 0.1) * $biomeFit;
            $weighted[] = [$z, $w];
            $total += $w;
        }

        $r = (mt_rand() / mt_getrandmax()) * $total;
        foreach ($weighted as [$z, $w]) {
            if (($r -= $w) <= 0) {
                return $z;
            }
        }
        return $zones->random();
    }
}
