<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * Estado global de la partida (una sola partida a la vez). Fases:
 *  - lobby:  sala de espera, los jugadores se apuntan; arranca sola al mínimo.
 *  - active: partida en curso (se puede conquistar).
 *  - ended:  hay ganador; se muestra el podio hasta que el admin abra otra.
 */
class GameState extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'game_state';

    protected $fillable = ['phase', 'started_at', 'result_message', 'min_per_team', 'event_level'];

    protected $casts = ['started_at' => 'datetime'];

    /** Mínimo de jugadores por bando para arrancar (ajustable por el admin). */
    public function minPerTeam(): int
    {
        return max(1, (int) ($this->min_per_team ?? 1));
    }

    /** Nivel de eventos del mundo: off | low | normal | high. */
    public function eventLevel(): string
    {
        return $this->event_level ?? 'normal';
    }

    /** Documento único de estado (lo crea en 'lobby' la primera vez). */
    public static function current(): self
    {
        return static::first() ?: static::create(['phase' => 'lobby']);
    }

    public function isLobby(): bool
    {
        return ($this->phase ?? 'lobby') === 'lobby';
    }

    public function isActive(): bool
    {
        return $this->phase === 'active';
    }

    public function isEnded(): bool
    {
        return $this->phase === 'ended';
    }

    public function setPhase(string $phase): void
    {
        $this->phase = $phase;
        if ($phase === 'active') {
            $this->started_at = now();
        }
        $this->save();
    }

    /** Termina la partida con un mensaje de resultado (victoria real o fin del admin). */
    public function endWith(string $message): void
    {
        $this->phase = 'ended';
        $this->result_message = $message;
        $this->save();
    }
}
