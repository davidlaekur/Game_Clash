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
    private const SPAWN_INTERVAL = 240; // cada 4 min, intento de nuevo evento
    private const MAX_ACTIVE = 4;       // tope de eventos simultáneos

    public function tick(): void
    {
        $this->expireOld();

        $last = (int) Cache::get('world_event_last', 0);
        if (now()->timestamp - $last < self::SPAWN_INTERVAL) {
            return;
        }
        Cache::put('world_event_last', now()->timestamp, 86400);

        if (Zone::whereNotNull('event_type')->count() >= self::MAX_ACTIVE) {
            return;
        }
        $this->spawn();
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

    private function spawn(): void
    {
        $free = Zone::whereNull('event_type')->get();
        if ($free->isEmpty()) {
            return;
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
    }
}
