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
        'merit',      // Méritos GASTABLES (moneda): suben al destacar, bajan al gastar
        'rank_score', // Méritos máximos alcanzados: definen el RANGO (permanente, no baja)
        'role_id',    // Relación con Rol
        'team_id',    // Relación con Team
        'zone_id',    // Relación con Zone
        'wounded_until', // estado "Herido" temporal tras perder una batalla defendiendo
        'joined',     // se ha apuntado a la partida en curso (sala de espera)
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
        'wounded_until' => 'datetime',
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
     * Solo jugadores reales: excluye a los administradores, que nunca participan
     * en el juego (no defienden, no atacan, no aparecen en zonas).
     */
    public function scopePlayers($query)
    {
        // role_id se guarda como string -> comparar como string
        $adminRoleIds = \App\Models\Role::where('name', 'Admin')->pluck('id')->map(fn($i) => (string) $i)->all();
        return $query->whereNotIn('role_id', $adminRoleIds);
    }

    /**
     * Rango actual según méritos (array name/merit/icon/level).
     */
    public function rank(): array
    {
        // el rango se basa en la GLORIA (méritos de carrera acumulados), no baja al gastar
        $score = $this->glory();
        $ranks = config('ranks');
        $current = $ranks[0] + ['level' => 0];
        foreach ($ranks as $i => $r) {
            if ($score >= $r['merit']) {
                $current = $r + ['level' => $i];
            }
        }
        return $current;
    }

    /** Nivel numérico de rango (0 = Recluta). */
    public function rankLevel(): int
    {
        return $this->rank()['level'];
    }

    /** Gloria: méritos acumulados de toda la partida (nunca bajan, ni al gastar). Define el rango y el ranking. */
    public function glory(): int
    {
        return (int) ($this->rank_score ?? 0);
    }

    /** Gana méritos: suben la moneda (gastable) y la GLORIA (acumulada, no baja). */
    public function addMerit(int $amount): void
    {
        $this->merit = (int) ($this->merit ?? 0) + $amount;
        $this->rank_score = (int) ($this->rank_score ?? 0) + $amount; // gloria acumulada
        $this->save();
    }

    /** Gasta méritos (p.ej. emprender una aventura): baja la moneda, NO el rango. */
    public function spendMerit(int $amount): void
    {
        $this->merit = max(0, (int) ($this->merit ?? 0) - $amount);
        $this->save();
    }

    /** ¿Está herido ahora mismo? (tras perder una batalla defendiendo) */
    public function isWounded(): bool
    {
        return $this->wounded_until && $this->wounded_until->isFuture();
    }

    /** Penalización de combate mientras está herido (−20% a los stats de combate). */
    public function woundedFactor(): float
    {
        return $this->isWounded() ? 0.8 : 1.0;
    }

    /** Marca al jugador como Herido durante unos minutos (se cura solo con el tiempo). */
    public function wound(int $minutes = 20): void
    {
        $this->wounded_until = \Carbon\Carbon::now()->addMinutes($minutes);
        $this->save();
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
