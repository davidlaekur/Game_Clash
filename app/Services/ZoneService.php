<?php

namespace App\Services;

use App\Models\Zone;

class ZoneService
{
    public function zonesAdjacent(Zone $zone1, Zone $zone2): bool
    {
        if (!$zone1 || !$zone2) {
            return false;
        }

        // Fronteras explícitas (config/zone_adjacency.php), indexadas por "lat,lon".
        // Las distancias de lat/lon siguen rigiendo el tiempo de movimiento; esto
        // solo decide qué territorios limitan entre sí para poder atacar.
        $key    = (int) $zone1->latitude . ',' . (int) $zone1->longitude;
        $target = (int) $zone2->latitude . ',' . (int) $zone2->longitude;

        $neighbors = config('zone_adjacency')[$key] ?? [];

        return in_array($target, $neighbors, true);
    }
}
