<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Zone;

class ZoneSeeder extends Seeder
{
    public function run(): void
    {
        
        $zones = [
            ['name' => 'Zona 1', 'landscape' => 'bosque', 'image' => 'images/zones/bosque.png', 'image_detail' => 'images/maps/mapaBosque.png', 'latitude' => 0.0, 'longitude' => 0.0],
            ['name' => 'Zona 2', 'landscape' => 'selva', 'image' => 'images/zones/selva.png', 'image_detail' => 'images/maps/mapaSelva.png', 'latitude' => 1.0, 'longitude' => 0.0],
            ['name' => 'Zona 3', 'landscape' => 'pradera', 'image' => 'images/zones/pradera.png', 'image_detail' => 'images/maps/mapaPradera.png', 'latitude' => 2.0, 'longitude' => 0.0],
            ['name' => 'Zona 4', 'landscape' => 'desierto', 'image' => 'images/zones/desierto.png', 'image_detail' => 'images/maps/mapaDesierto.png', 'latitude' => 0.0, 'longitude' => 1.0],
            ['name' => 'Zona 5', 'landscape' => 'montaña', 'image' => 'images/zones/montanya.png', 'image_detail' => 'images/maps/mapaMontanya.png', 'latitude' => 1.0, 'longitude' => 1.0],
            ['name' => 'Zona 6', 'landscape' => 'polo', 'image' => 'images/zones/polo.png', 'image_detail' => 'images/maps/mapaPolo.png', 'latitude' => 2.0, 'longitude' => 1.0],
            ['name' => 'Zona 7', 'landscape' => 'bosque', 'image' => 'images/zones/bosque2.png', 'image_detail' => 'images/maps/mapaBosque2.png', 'latitude' => 0.0, 'longitude' => 2.0],
            ['name' => 'Zona 8', 'landscape' => 'selva', 'image' => 'images/zones/selva2.png', 'image_detail' => 'images/maps/mapaSelva.png', 'latitude' => 1.0, 'longitude' => 2.0],
            ['name' => 'Zona 9', 'landscape' => 'montaña', 'image' => 'images/zones/montanya2.png', 'image_detail' => 'images/maps/mapaMontanya2.png', 'latitude' => 2.0, 'longitude' => 2.0],
        ];
        

        foreach ($zones as $zoneData) {
            Zone::create([
                'name' => $zoneData['name'],
                'landscape' => $zoneData['landscape'],
                'image' => $zoneData['image'],
                'image_detail' => $zoneData['image_detail'],
                'latitude' => $zoneData['latitude'],
                'longitude' => $zoneData['longitude'],
                'defense' => $this->calculateDefense($zoneData['landscape']), 
                'team_id' => null, 
            ]);
        }
        
    }

    /**
     * añado un valor de defensa base  a cada zona en función de la orografia
     */
    private function calculateDefense(string $landscape): int
    {
        return match ($landscape) {
            'bosque', 'selva' => rand(40, 70),
            'pradera', 'desierto' => rand(5, 40),
            'montaña', 'polo' => rand(70, 120),
            'pradera' => rand(5, 40),
        };
    }
}
