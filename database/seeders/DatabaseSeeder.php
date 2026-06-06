<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Invention;
use App\Models\InventionStat;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Llamar a los seeders en el orden correcto
        $this->call([
        
                    StatSeeder::class,           // Seed de estadísticas
                    RoleSeeder::class,            // Seed de role
                    TypeSeeder::class,           // Seed de tipos de acción 
                    TeamSeeder::class,           // Seed de equipos
                    ZoneSeeder::class,           // Seed de zonas
                    MaterialTypeSeeder::class,   // Seed de tipos de materiales
                    MaterialSeeder::class,       // Seed de materiales
                    InventionTypeSeeder::class,  // Seed de tipos de inventos
               /*      InventionStatSeeder::class, */  // Relación inventos y stats
                    UserSeeder::class,           // Seed de usuarios
                    UserStatSeeder::class,       // Relación usuarios y stats
                    NeedSeeder::class,          // Seed de dependencias entre inventos
                    AdventureSeeder::class,
                ]);

        // Datos de relleno (jugadores e inventos) generados con Faker. Faker
        // está en "require" (no require-dev) para que funcione también en prod.
        User::factory()->count(6)->create();
        Invention::factory()->count(15)->create();
        InventionStat::factory()->count(15)->create();
    }

}