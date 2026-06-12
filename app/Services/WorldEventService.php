<?php

namespace App\Services;

use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Eventos de mundo: el mapa cobra vida solo. Al cargar el mapa se caducan los
 * eventos vencidos y, cada cierto intervalo, se genera uno nuevo en una zona al
 * azar (tormenta, bonanza o plaga). Sin cron: todo perezoso.
 */
class WorldEventService
{
    private const SPAWN_INTERVAL = 180; // cada 3 min, intento de evento extra (variedad)
    private const MIN_ACTIVE = 2;       // siempre al menos estos eventos vivos
    private const MAX_ACTIVE = 5;       // tope de eventos simultáneos

    public function tick(): void
    {
        $this->expireOld();

        $active = Zone::whereNotNull('event_type')->count();

        // mantener un mínimo de eventos vivos: el mundo siempre se siente activo
        while ($active < self::MIN_ACTIVE && $this->spawn()) {
            $active++;
        }

        // spawn periódico extra para variedad, hasta el máximo
        $last = (int) Cache::get('world_event_last', 0);
        if ($active < self::MAX_ACTIVE && now()->timestamp - $last >= self::SPAWN_INTERVAL) {
            if ($this->spawn()) {
                Cache::put('world_event_last', now()->timestamp, 86400);
            }
        }
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
