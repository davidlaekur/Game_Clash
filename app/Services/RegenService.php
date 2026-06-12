<?php

namespace App\Services;

use App\Models\Zone;
use App\Models\Material;
use Carbon\Carbon;

/**
 * Regeneración perezosa de recursos: cuando se visita/recolecta una zona, sus
 * materiales reponen cantidad según el tiempo transcurrido. Así el mapa nunca
 * queda yermo. Las familias comunes (más probabilidad) regeneran más rápido;
 * las raras, despacio. Una mina en la zona multiplica el ritmo.
 */
class RegenService
{
    // fracción base por hora respecto al tope (escala el ritmo general)
    private const RATE = 0.3;

    public function regenerateZone(Zone $zone): void
    {
        // mina (regen_boost) y evento de mundo (bonanza/plaga) se combinan
        $boost = (float) ($zone->regen_boost ?? 1) * $zone->eventRegenMultiplier();
        foreach ($zone->materials as $material) {
            $this->regenerateMaterial($material, $boost);
        }
    }

    public function regenerateMaterial(Material $material, float $boost = 1.0): void
    {
        $max = (int) ($material->max_quantity ?? $material->quantity);
        if ($max <= 0 || $material->quantity >= $max) {
            return;
        }

        $since = $material->regenerated_at
            ? Carbon::parse($material->regenerated_at)
            : Carbon::parse($material->updated_at);
        $hours = $since->diffInSeconds(now()) / 3600;
        if ($hours <= 0) {
            return;
        }

        // ritmo: más rápido cuanto más común (probabilidad), por mina y por tiempo
        $perHour = max(1, $max * ($material->probability / 100) * self::RATE * $boost);
        $add = (int) floor($hours * $perHour);
        if ($add <= 0) {
            return;
        }

        $material->quantity = min($max, $material->quantity + $add);
        $material->regenerated_at = now();
        $material->save();
    }
}
