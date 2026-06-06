<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Team;


class UserUnitTest extends TestCase

{

    /** @test */
    /*   public function check_database_connection()
      {
          $database = config('database.connections.mongodb.database');
          dd("Base de datos: " . $database);
      }
  
  */

    /** @test */
    public function user_not_null()
    {
        $user = User::create([

            'name' => 'Test User',
            'email' => 'test' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ]);


        $this->assertNotNull($user);
    }


    /** @test */
    public function user_name_not_empty()
    {
        $user = User::create([
            'name' => 'admin',
            'email' => 'admin@laraveland.es',
            'password' => bcrypt('password'),
        ]);

        $this->assertFalse(empty($user->name));
    }

    /** @test */
    public function user_email_equality()
    {
        $user = User::create([
            'name' => 'admin',
            'email' => 'admin@laraveland.es',
            'password' => bcrypt('password'),
        ]);

        $this->assertEquals('admin@laraveland.es', $user->email);
    }

    /** @test */
    public function user_instance_usermodel()
    {
        $user = User::create([
            'name' => 'admin',
            'email' => 'admin@laraveland.es',
            'password' => bcrypt('password'),
        ]);

        $this->assertInstanceOf(User::class, $user);
    }

    /** @test */
    public function user_has_role()
    {
        $role = Role::create(['name' => 'Admin']);

        $user =  User::create([
            'name' => 'Admin User',
            'email' => 'admin' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
        ]);
        $this->assertTrue($user->role->name === 'Admin');
        $this->assertNotNull($user->role_id);
        $this->assertEquals($role->id, $user->role_id);
    }

    /** @test */
    public function user_belongs_team()
    {
        $team = Team::create(['name' => 'Team Alpha']);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'team_id' => $team->id,
        ]);

        $this->assertNotNull($user->team_id);
        $this->assertEquals($team->id, $user->team_id);
    }

    /** @test */
    public function user_default_capacity()
    {
        $role = Role::create([
            'name' => 'Explorer',
            'base_capacity' => 50,
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
        ]);

        // capacidad basada en el rol
        $user->capacity = $role->base_capacity;
        $user->save();

        $this->assertNotNull($user->capacity, "El usuario no tiene capacidad base asignada.");
        $this->assertEquals(50, $user->capacity, "El usuario no tiene la capacidad correcta.");
    }
}
