<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Combat extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $connection = 'mongodb'; 

    protected $fillable = [
        'random_factor',
        'score',
        'result_id', // Relación polimórfica con Result
        'result_type', // Tipo del resultado (polimórfico)
        'attacker_team_id',
        'defender_team_id',
        'winner_team_id'
    ];

    /**
     * Relación N:1 con el equipo atacante.
     */
    public function attacker()
    {
        return $this->belongsTo(Team::class, 'attacker_team_id');
    }

    /**
     * Relación N:1 con el equipo defensor.
     */
    public function defender()
    {
        return $this->belongsTo(Team::class, 'defender_team_id');
    }

    /**
     * Relación N:1 con el equipo ganador.
     */
    public function winner()
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }

    /**
     * Relación polimórfica 1:1 con Result.
     */
    public function result()
    {
        return $this->morphOne(Result::class, 'resultable');
    }
} 