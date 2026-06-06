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


        $lat1 = $zone1->latitude;
        $lon1 = $zone1->longitude;
        $lat2 = $zone2->latitude;
        $lon2 = $zone2->longitude;

        // chequeamos posiciones adyacentes para atacar 
        return 
            ($lat1 === $lat2 && ($lon1 === $lon2 + 1 || $lon1 === $lon2 - 1)) || ($lon1 === $lon2 && ($lat1 === $lat2 + 1 || $lat1 === $lat2 - 1));
    }
}
