<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class HallOfFame extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'hall_of_fame';

    protected $fillable = [
        'winner',   // mensaje de victoria (facción y dominio)
        'podium',   // array Top 3: [{name, team, glory}] (array nativo de Mongo, sin cast)
        'ended_at', // cuándo terminó la partida
    ];

    protected $casts = [
        'ended_at' => 'datetime',
    ];
}
