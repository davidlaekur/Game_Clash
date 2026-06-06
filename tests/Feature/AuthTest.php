<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Team;
use App\Models\Role;

use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    /** @test */
    public function user_can_register()
    {
        $team = Team::factory()->create();
        $role = Role::factory()->create(['name' => 'Explorer']);

        $email = 'test' . uniqid() . '@test.com';

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'team_id' => $team->id,
            'role_id' => $role->id,
        ]);

        $this->assertDatabaseHas('users', ['email' => $email]); // check con base de datos
        $response->assertRedirect('/login');
        
        $response = $this->get('/login');
        $response->assertSuccessful();

    }

    /** @test */
    public function user_can_login()
    {

        $team = Team::factory()->create();
        $role = Role::factory()->create();


        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => bcrypt('password123'),
            'team_id' => $team->id,
            'role_id' => $role->id,
        ]);

        // solicitud de inicio de sesion 
        $response = $this->post('/login', [
            'email' => 'test@test.com',
            'password' => 'password123',
        ]);

        // chequear si el usuario esta autenticado
        $this->assertAuthenticated();

        //chequear si va  a la página de zones.index
        $response->assertRedirect(route('zones.index'));

        $response = $this->get('/zones');
        $response->assertSuccessful();
    }

    /** @test */
    public function usernot_login_with_wrong_credentials()
    {

        $team = Team::factory()->create();
        $role = Role::factory()->create();


        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => bcrypt('password123'),
            'team_id' => $team->id,
            'role_id' => $role->id,
        ]);

        //  iniciar sesión con una pass incorrecto
        $response = $this->post('/login', [
            'email' => 'test@test.com',
            'password' => 'wrongpassword',
        ]);

        // el user no debe estar autenticado
        $this->assertGuest();

        // chequear error en la sesión
        $response->assertSessionHasErrors('email');
    }


    /** @test */
    public function user_can_logout()
    {

        $team = Team::factory()->create();
        $role = Role::factory()->create();


        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => bcrypt('password123'),
            'team_id' => $team->id,
            'role_id' => $role->id,
        ]);

        // usuario autenticado (forzado)
        $this->actingAs($user);

        //  logout
        $response = $this->post('/logout');

        //chequear si hace logout
        $this->assertGuest();

        // chequear si redirige a  login
        $response->assertRedirect(route('login'));
    }


    /** @test */
    public function user_cannot_register_with_invalid_data()
    {

        $team = Team::factory()->create();
        $role = Role::factory()->create();

        // registrarse sin datos
        $response = $this->post('/register', []);
        $response->assertSessionHasErrors(['name', 'email', 'password', 'team_id', 'role_id']);

        //registrarse con un email incorrecto
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'team_id' => $team->id,
            'role_id' => $role->id,
        ]);
        $response->assertSessionHasErrors('email');

        // registrarse con mail  que ya existe
        User::factory()->create(['email' => 'duplicate@test.com']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'duplicate@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'team_id' => $team->id,
            'role_id' => $role->id,
        ]);
        $response->assertSessionHasErrors('email');

        // contraseña demasiado corta
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => '123',
            'password_confirmation' => '123',
            'team_id' => $team->id,
            'role_id' => $role->id,
        ]);
        $response->assertSessionHasErrors('password');

        // contraseñas que no coinciden
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password321',
            'team_id' => $team->id,
            'role_id' => $role->id,
        ]);
        $response->assertSessionHasErrors('password');

        // intentar registrarse con el rol restringido Admin
        $adminRole = Role::factory()->create(['name' => 'Admin']);
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'team_id' => $team->id,
            'role_id' => $adminRole->id,
        ]);
        $response->assertSessionHasErrors('role_id');
    }


    /** @test */
    public function usernot_access_protected_routes_without_authentication()
    {
        $protectedRoutes = [
            '/zones',
            '/teams',
            '/players',
            '/inventions',
            '/actions',
            '/communications',
            '/resources',
            '/games',
        ];

        foreach ($protectedRoutes as $route) {
            // intentar acceder a la ruta sin estar autentificado
            $response = $this->get($route);

 
            // chequear si el  usuario va a  login    
            $response->assertRedirect(route('login'));
        }
    }
}
