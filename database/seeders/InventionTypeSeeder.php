<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventionType;

class InventionTypeSeeder extends Seeder
{
    public function run(): void
    {
        // tipos de invento 
        $inventionTypes = [
            ['name' => 'Piedra Afilada', 'level' => 1, 'image' => 'images/invention/piedraAfilada.png'],
            ['name' => 'Cuerda', 'level' => 1, 'image' => 'images/invention/cuerda.png'],
            ['name' => 'Fuego', 'level' => 1, 'image' => 'images/invention/fuego.png'],
            ['name' => 'Lanza', 'level' => 2, 'image' => 'images/invention/lanza.png'],
            ['name' => 'Arco y Flecha', 'level' => 2, 'image' => 'images/invention/arcoFlecha.png'],
            ['name' => 'Cesta', 'level' => 1, 'image' => 'images/invention/cesta.png'],
            ['name' => 'Rueda', 'level' => 1, 'image' => 'images/invention/rueda.png'],
            ['name' => 'Trampa', 'level' => 2, 'image' => 'images/invention/trampa.png'],
            ['name' => 'Hacha', 'level' => 2, 'image' => 'images/invention/hacha.png'],
            ['name' => 'Carro', 'level' => 3, 'image' => 'images/invention/carro.png'],
            ['name' => 'Traje de Malla', 'level' => 1, 'image' => 'images/invention/trajeMalla.png'],
            ['name' => 'Espada', 'level' => 3, 'image' => 'images/invention/espada.png'],
            ['name' => 'Escudo', 'level' => 2, 'image' => 'images/invention/escudo.png'],
        ];

        foreach ($inventionTypes as $type) {
            InventionType::create([
                'name' => $type['name'], 
                'level' => $type['level'],
                'image' => $type['image'], 
            ]);
        }
    }

}