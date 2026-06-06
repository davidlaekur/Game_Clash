<?php

namespace Database\Factories;

use App\Models\Invention;
use App\Models\InventionType;
use App\Models\User;
use App\Models\Material;
use App\Models\Inventory;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $inventionType = InventionType::get()->random();

        // usuarios
        $user = User::get()->random();

        // materiales
        $material = Material::get()->random();

        // inventarios
        $inventory = Inventory::firstOrCreate(
            ['inventoriable_id' => $user->id, 'inventoriable_type' => User::class], 
            [
                'type' => 'personal',
                'name' => 'Inventario de ' . $user->name,
            ]
        );

        return [
            'name' => $inventionType->name,
            'inventiontype_id' => $inventionType->id,
            'points' => rand(1, 10),
            'user_id' => $user->id,
            'material_id' => $material->id,
            'inventory_id' => $inventory->id, 
            'efficiency' => rand(10, 100),
            'level' => rand(1, 3),
        ];
    }
}