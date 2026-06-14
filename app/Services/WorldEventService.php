<?php

namespace App\Services;

use App\Models\Zone;
use App\Models\GameState;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Eventos de mundo: el mapa cobra vida solo. Al cargar el mapa se caducan los
 * eventos vencidos y, cada cierto intervalo, se genera uno nuevo en una zona al
 * azar (tormenta, bonanza o plaga). Sin cron: todo perezoso.
 */
class WorldEventService
{
    // intensidad de eventos ajustable por el admin (GameState.event_level)
    private const LEVELS = [
        'off'    => ['min' => 0, 'max' => 0, 'interval' => 999999],
        'low'    => ['min' => 0, 'max' => 2, 'interval' => 360],
        'normal' => ['min' => 2, 'max' => 5, 'interval' => 180],
        'high'   => ['min' => 3, 'max' => 8, 'interval' => 90],
    ];

    public function tick(): void
    {
        $this->expireOld();

        $cfg = self::LEVELS[GameState::current()->eventLevel()] ?? self::LEVELS['normal'];

        $active = Zone::whereNotNull('event_type')->count();

        // mantener un mínimo de eventos vivos: el mundo siempre se siente activo
        while ($active < $cfg['min'] && $this->spawn()) {
            $active++;
        }

        // spawn periódico extra para variedad, hasta el máximo
        $last = (int) Cache::get('world_event_last', 0);
        if ($active < $cfg['max'] && now()->timestamp - $last >= $cfg['interval']) {
            if ($this->spawn()) {
                Cache::put('world_event_last', now()->timestamp, 86400);
            }
        }
    }

    /** El admin fuerza un evento concreto en una zona (tormenta/bonanza/plaga). */
    public function force(Zone $zone, string $type): bool
    {
        $meta = config('world_events')[$type] ?? null;
        if (!$meta) {
            return false;
        }
        $zone->event_type = $type;
        $zone->event_ends_at = now()->addSeconds($meta['duration']);
        $zone->event_magnitude = $type === 'tormenta' ? (int) round($zone->defense * 0.35) : 0;
        $zone->save();
        return true;
    }

    private function expireOld(): void
    {
        foreach (Zone::whereNotNull('event_type')->get() as $zone) {
            if ($zone->event_ends_at && Carbon::parse($zone->event_ends_at)->isPast()) {
                $zone->event_type = null;
                $zone->event_ends_at = null;
                $zone->event_magnitude = 0;
                $zone->save();
            }
        }
    }

    private function spawn(): bool
    {
        $free = Zone::whereNull('event_type')->get();
        if ($free->isEmpty()) {
            return false;
        }
        $zone = $free->random();

        $types = array_keys(config('world_events'));
        $type = $types[array_rand($types)];
        $meta = config('world_events')[$type];

        $zone->event_type = $type;
        $zone->event_ends_at = now()->addSeconds($meta['duration']);
        // la tormenta resta ~35% de la defensa base
        $zone->event_magnitude = $type === 'tormenta' ? (int) round($zone->defense * 0.35) : 0;
        $zone->save();

        return true;
    }
}
