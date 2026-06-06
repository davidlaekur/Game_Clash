<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use MongoDB\Laravel\Auth\User as Authenticatable;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;



class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'users';    
    protected $primaryKey = '_id';     
    public $incrementing = false;      
    protected $keyType = 'string';  

    protected $fillable = [
        'name',   // Nick del usuario
        'capacity', // Capacidad de inventario
        'email',
        'password',
        'points',    // Puntos de experiencia
        'role_id',    // Relación con Rol
        'team_id',    // Relación con Team
        'zone_id',    // Relación con Zone
    ];



    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relación 1:N con Action.
     * Un usuario puede realizar varias acciones.
     */



    // Implementación de métodos requeridos por JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }


    /**
     * Relación 1:N con Action.
     */

    public function actions()
    {
        return $this->hasMany(Action::class, 'user_id'); // Relación con Action
    }

    /**
     * Relación N:1 con Rol
     * Un usuario pertenece a un equipo.
     */

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Relación N:1 estoy en user con Team.
     * Un usuario pertenece a un equipo.
     */
    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    /**
     * Relación N:1 con Zone.
     * Un usuario ocupa una zona.
     */
    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'user_id'); // Mensajes recibidos
    }

    /**
     * Relación polimórfica 1:N con Message (mensajes enviados).
     */
    public function sentMessages()
    {
        return $this->morphMany(Message::class, 'messageable'); // Mensajes enviados
    }

    /**
     * Relación polimórfica 1:1 con Inventory (propietario del inventario).
     */
    public function inventory()
    {
        return $this->morphOne(Inventory::class, 'inventoriable'); // Inventario del usuario
    }


    /**
     * Relación N:M con Stat a través de UserStat. usando una relacion 1:N con UserStat
     * Un usuario puede tener múltiples estadísticas con valores asociados.
     */
    public function stats()
    {
        return $this->hasMany(UserStat::class, 'user_id'); // Relación con UserStat
    }


    /**
     * Relación N:M con Adventure a través de UserAdventure. usando una relacion 1:N con UserAdventure
     * Un usuario puede tener muchas aventuras.
     */
    public function adventures()
    {
        return $this->hasMany(UserAdventure::class, 'user_id'); 
    }



}
