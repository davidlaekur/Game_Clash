<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Team extends Model
{
    use SoftDeletes;
    use HasFactory; 
    
    protected $connection = 'mongodb';

    
    protected $fillable = ['name', 'image'];

    /**
     * Relación 1:N con usuarios.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relación 1:N con zonas (zones).
     */
    public function zones()
    {
        return $this->hasMany(Zone::class);
    }

    /**
     * Relación 1:N como atacante en combates.
     */
    public function attacks()
    {
        return $this->hasMany(Combat::class, 'attacker_team_id');
    }

    /**
     * Relación 1:N como defensor en combates.
     */
    public function defends()
    {
        return $this->hasMany(Combat::class, 'defender_team_id');
    }

    /**
     * Relación 1:N como ganador en combates.
     */
    public function wins()
    {
        return $this->hasMany(Combat::class, 'winner_team_id');
    }

    /**
     * Relación polimórfica 1:1 con inventarios.
     */
    public function inventory()
    {
        return $this->morphOne(Inventory::class, 'inventoriable');
    }
}
