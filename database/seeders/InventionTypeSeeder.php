<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventionType;

class InventionTypeSeeder extends Seeder
{
    public function run(): void
    {
        // tipos de invento. material_types = categorías de material admitidas;
        // [] significa que el invento no consume material principal (Trampa).
        // extra_materials = ingredientes adicionales por nombre (p.ej. Fósforo).
        // icon = respaldo Font Awesome cuando no hay imagen.
        $inventionTypes = [
            ['name' => 'Piedra Afilada', 'level' => 1, 'image' => 'images/invention/piedraAfilada.png', 'icon' => 'fa-gem', 'material_types' => ['Roca', 'Mineral'], 'extra_materials' => []],
            ['name' => 'Cuerda', 'level' => 1, 'image' => 'images/invention/cuerda.png', 'icon' => 'fa-link', 'material_types' => ['Fibra'], 'extra_materials' => []],
            ['name' => 'Fuego', 'level' => 1, 'image' => 'images/invention/fuego.png', 'icon' => 'fa-fire', 'material_types' => ['Madera'], 'extra_materials' => []],
            ['name' => 'Lanza', 'level' => 2, 'image' => 'images/invention/lanza.png', 'icon' => 'fa-location-arrow', 'material_types' => ['Madera', 'Roca', 'Metal'], 'extra_materials' => []],
            ['name' => 'Arco y Flecha', 'level' => 2, 'image' => 'images/invention/arcoFlecha.png', 'icon' => 'fa-bullseye', 'material_types' => ['Madera', 'Fibra'], 'extra_materials' => []],
            ['name' => 'Cesta', 'level' => 1, 'image' => 'images/invention/cesta.png', 'icon' => 'fa-shopping-basket', 'material_types' => ['Fibra', 'Madera'], 'extra_materials' => []],
            ['name' => 'Rueda', 'level' => 1, 'image' => 'images/invention/rueda.png', 'icon' => 'fa-dharmachakra', 'material_types' => ['Madera', 'Metal'], 'extra_materials' => []],
            ['name' => 'Trampa', 'level' => 2, 'image' => 'images/invention/trampa.png', 'icon' => 'fa-bug', 'material_types' => [], 'extra_materials' => []],
            ['name' => 'Hacha', 'level' => 2, 'image' => 'images/invention/hacha.png', 'icon' => 'fa-hammer', 'material_types' => ['Roca', 'Metal'], 'extra_materials' => []],
            ['name' => 'Carro', 'level' => 3, 'image' => 'images/invention/carro.png', 'icon' => 'fa-truck', 'material_types' => ['Madera', 'Metal'], 'extra_materials' => []],
            ['name' => 'Traje de Malla', 'level' => 1, 'image' => 'images/invention/trajeMalla.png', 'icon' => 'fa-tshirt', 'material_types' => ['Metal'], 'extra_materials' => []],
            ['name' => 'Espada', 'level' => 3, 'image' => 'images/invention/espada.png', 'icon' => 'fa-khanda', 'material_types' => ['Metal'], 'extra_materials' => []],
            ['name' => 'Escudo', 'level' => 2, 'image' => 'images/invention/escudo.png', 'icon' => 'fa-shield-alt', 'material_types' => ['Madera', 'Metal'], 'extra_materials' => []],

            // sustento (familia Orgánico -> salud)
            ['name' => 'Vendaje', 'level' => 1, 'image' => '', 'icon' => 'fa-band-aid', 'material_types' => ['Fibra', 'Orgánico'], 'extra_materials' => []],
            ['name' => 'Ración', 'level' => 1, 'image' => '', 'icon' => 'fa-drumstick-bite', 'material_types' => ['Orgánico'], 'extra_materials' => []],
            ['name' => 'Poción', 'level' => 2, 'image' => '', 'icon' => 'fa-flask', 'material_types' => ['Orgánico'], 'extra_materials' => []],

            // arena / óptica
            ['name' => 'Vidrio', 'level' => 1, 'image' => '', 'icon' => 'fa-wine-glass', 'material_types' => ['Arena'], 'extra_materials' => []],
            ['name' => 'Catalejo', 'level' => 2, 'image' => '', 'icon' => 'fa-binoculars', 'material_types' => ['Madera', 'Metal'], 'extra_materials' => []],

            // élite estelar (premio de aventura)
            ['name' => 'Núcleo Estelar', 'level' => 3, 'image' => '', 'icon' => 'fa-meteor', 'material_types' => ['Estelar'], 'extra_materials' => []],

            // rama explosiva: Fósforo -> Pólvora -> Cañón (arma de alto ataque sin aventura)
            ['name' => 'Pólvora', 'level' => 2, 'image' => '', 'icon' => 'fa-bomb', 'material_types' => ['Madera'], 'extra_materials' => [['name' => 'Fosforo', 'qty' => 1]]],
            ['name' => 'Cañón', 'level' => 3, 'image' => '', 'icon' => 'fa-crosshairs', 'material_types' => ['Metal'], 'extra_materials' => []],
        ];

        foreach ($inventionTypes as $type) {
            InventionType::create([
                'name' => $type['name'],
                'level' => $type['level'],
                'image' => $type['image'],
                'icon' => $type['icon'],
                'material_types' => $type['material_types'],
                'extra_materials' => $type['extra_materials'],
            ]);
        }
    }

}