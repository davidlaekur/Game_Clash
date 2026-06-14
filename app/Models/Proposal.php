<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * Cónclave del equipo: propuesta de rendir la ÚLTIMA zona (auto-eliminación).
 * El equipo la apoya/rechaza; si nadie responde, el proponente puede ejecutarla
 * unilateralmente pasado un tiempo. Cualquier rechazo la cancela (veto).
 */
class Proposal extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'proposals';

    protected $fillable = [
        'team_id',
        'zone_id',
        'proposer_id',
        'status',      // pending | executed | cancelled
        'supporters',  // array de user_id (incluye al proponente)
        'rejecters',   // array de user_id
    ];

    /** Minutos sin respuesta tras los que el proponente puede ejecutar a solas. */
    const UNILATERAL_AFTER_MIN = 5;

    public function isPending(): bool
    {
        return ($this->status ?? 'pending') === 'pending';
    }

    /** ¿Ha pasado el tiempo para que el proponente la ejecute unilateralmente? */
    public function canExecuteUnilaterally(): bool
    {
        return $this->created_at
            && \Carbon\Carbon::parse($this->created_at)->addMinutes(self::UNILATERAL_AFTER_MIN)->isPast();
    }
}
