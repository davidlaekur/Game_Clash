<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['Explorer', 'Collector', 'Strategist', 'Inventor']),
            'base_capacity' => $this->faker->randomElement([50, 75, 100]),
        ];
    }
}
