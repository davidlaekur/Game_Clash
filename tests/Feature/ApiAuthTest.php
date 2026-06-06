<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Team;
use App\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiAuthTest extends TestCase
{


    /** @test */
    public function unauthenticated_usernot_access_protected_routes()
    {
        $protectedGetRoutes = [
            '/api/profile',
            '/api/players',
            '/api/roles',
            '/api/teams',
            '/api/actions',
        ];

        $protectedPostRoutes = [
            '/api/logout',
            '/api/refresh',
        ];

        // intento get sin autentificación
        foreach ($protectedGetRoutes as $route) {
            $response = $this->getJson($route);
            $response->assertStatus(401);
        }

        // intento de post sin autentificación
        foreach ($protectedPostRoutes as $route) {
            $response = $this->postJson($route);
            $response->assertStatus(401);
        }
    }

    /** @test */
    public function authenticated_user_access_profile()
    {

        $team = Team::factory()->create();
        $role = Role::factory()->create();

        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => bcrypt('password123'),
            'team_id' => $team->id,
            'role_id' => $role->id,
        ]);

        // recoger usuario de  base de datos
        $user = User::where('email', 'test@test.com')->first();

        // convertir _id  a -> id 
        $user->id = (string) $user->_id;

        // token 
        $token = JWTAuth::fromUser($user);

        // chequear token
        $this->assertNotNull($token, "El token JWT no fue generado correctamente");

        // hacer la request  a la ruta con el token
        $response = $this->getJson('/api/profile', [
            'Authorization' => "Bearer $token",
        ]);

        // chequear acceso a la ruta
        $response->assertStatus(200)
            ->assertJson([
                'email' => $user->email,
            ]);
    }
}
