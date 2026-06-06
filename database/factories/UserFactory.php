<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Role;
use App\Models\Team;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
           // excluyendo admin
       /*     $role = Role::where('name', 'Admin')->first(); */

        // roles para el factory 
        $roles = Role::where('name', '!=', 'Admin')->get(); // Excluyendo admin

        $role = $roles->random();

        // equipos
        $teams = Team::all();
        $team = $teams->random();

        return [
            'name' => str::limit(fake()->name(), 9, ''), // limitar el nombre a 9 caracteres ( uso cadena vacia para que no ponga 3 puntos)
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('1212'), // Contraseña para pruebas
            'role_id' => $role->id, // Asignamos el id del rol aleatorio
            'team_id' => $team->id, // Asignamos el id del equipo aleatorio
            'zone_id' => null,  
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}