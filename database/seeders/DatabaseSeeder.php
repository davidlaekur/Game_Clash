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

        // Las factories (Faker) son datos de relleno de desarrollo y Faker no
        // se instala en producción (--no-dev). Solo se ejecutan fuera de prod.
        if (! app()->environment('production')) {
            User::factory()->count(6)->create();
            Invention::factory()->count(15)->create();
            InventionStat::factory()->count(15)->create();
        }
    }

}