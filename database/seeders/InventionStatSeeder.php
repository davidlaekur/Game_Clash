<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventionType;
use App\Models\Stat;
use App\Models\InventionStat;

class InventionStatSeeder extends Seeder
{
    public function run(): void
    {
        // estadísticas que  incrementa cada tipo de invento
        $inventionStats = [
            'Piedra Afilada' => ['ataque' => 5],
            'Cuerda' => ['ingenio' => 3, 'velocidad' => 2],
            'Fuego' => ['ingenio' => 4, 'defensa' => 3],
            'Lanza' => ['ataque' => 6, 'defensa' => 2],
            'Arco y Flecha' => ['ataque' => 7, 'defensa' => 3],
            'Cesta' => ['capacidad' => 5, 'suerte' => 2],
            'Rueda' => ['velocidad' => 5],
            'Trampa' => ['defensa' => 6],
            'Hacha' => ['suerte' => 4],
            'Carro' => ['capacidad' => 7, 'velocidad' => 3],
            'Traje de Malla' => ['salud' => 5, 'defensa' => 4],
            'Espada' => ['ataque' => 8],
            'Escudo' => ['salud' => 6, 'defensa' => 5],
        ];

        // creamos la tabla InventionStat
        foreach ($inventionStats as $inventionName => $stats) {
            $invention = InventionType::where('name', $inventionName)->first();
            if ($invention) {
                foreach ($stats as $statName => $value) {
                    $stat = Stat::where('name', $statName)->first();
                    if ($stat) {
                        InventionStat::create([
                            'invention_id' => $invention->id,
                            'stat_id' => $stat->id,
                            'value' => $value,
                        ]);
                    }
                }
            }
        }
    }
}
