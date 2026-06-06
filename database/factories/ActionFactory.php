<?php

namespace Database\Factories;

use App\Models\Action;
use App\Models\User;
use App\Models\Type;
use App\Models\Zone;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Action>
 */
class ActionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

     protected $model = Action::class;
     
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type_id' => Type::factory(),
            'actionable_id' => Zone::factory(),
            'actionable_type' => Zone::class,
            'duration' => $this->faker->numberBetween(10, 60), // Duración aleatoria entre 10 y 60 segundos
            'finish' => false,
        ];
    }
}
