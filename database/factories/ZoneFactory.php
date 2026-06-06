<?php

namespace Database\Factories;

use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

class ZoneFactory extends Factory
{
    protected $model = Zone::class;

    public function definition()
    {
        $landscapes = ['bosque', 'selva', 'pradera', 'desierto', 'montaña', 'polo'];

        $landscape = $this->faker->randomElement($landscapes);

        return [
            'name' => $this->faker->unique()->word(),
            'landscape' => $landscape,
            'image' => 'images/zones/' . $landscape . '.png',
            'image_detail' => 'images/maps/mapa' . ucfirst($landscape) . '.png',
            'latitude' => $this->faker->randomFloat(1, 0, 10),
            'longitude' => $this->faker->randomFloat(1, 0, 10),
            'defense' => $this->calculateDefense($landscape),
            'team_id' => null, 
        ];
    }


    private function calculateDefense(string $landscape): int
    {
        return match ($landscape) {
            default => rand(5, 120),
        };
    }
}
