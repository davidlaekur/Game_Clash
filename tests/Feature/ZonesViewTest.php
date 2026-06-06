<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Zone;
use App\Models\Team;
use App\Models\Role;


class ZonesViewTest extends TestCase
{

    /** @test */
    public function authenticated_user_access_zones_index()
    {

        $team = Team::factory()->create();
        $role = Role::factory()->create();

        $zone = Zone::factory()->create([
            'name' => 'Zona Test',
            'landscape' => 'bosque',
            'defense' => 10
        ]);

        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => bcrypt('password123'),
            'team_id' => $team->id,
            'role_id' => $role->id,
        ]);

        $this->post('/login', [
            'email' => 'test@test.com',
            'password' => 'password123',
        ]);

        try {
            // hacer el request a la ruta
            $response = $this->get('/zones');


            $response->assertStatus(200)
                ->assertViewIs('zones.index')
                ->assertSee('Mapa de Zonas')
                ->assertSee('Zona Test')
                ->assertSee('bosque')
                ->assertSee('Defensa Base: 10')
                ->assertDontSee('Zona Fake');
        } catch (\Exception $e) {
           $this->fail("necesites ejecutar npm run dev para que este test funcione." . $e->getMessage());
        }
    }
}
