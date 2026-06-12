<?php

namespace App\Services;

use App\Models\InventionType;
use App\Models\Material;

class InventionPointsService
{

    public function calculatePointsAndEfficiency(InventionType $inventionType, $materialId = null)
    {
        // Caso especial para la "Trampa"
        if ($inventionType->name === 'Trampa') {
            return [
                'points' => 7,
                'efficiency' => 50,
                'statFactor' => 1,
            ];
        }
      // Si el invento tiene un material asociado, calculamos en función de la densidad
      if ($materialId) {
        $material = Material::findOrFail($materialId);
        
        // Definir el mínimo de puntos según el nivel del invento
        $minPoints = $inventionType->level;  // Nivel 1 -> mínimo 1 punto, nivel 2 -> mínimo 2 puntos, etc.
        $maxPoints = 10;  // El máximo de puntos es 10

        // Calcular los puntos basados en la densidad del material
        $points = (int)($material->density * $maxPoints / 10); // Asegurar que los puntos estén balanceados por la densidad

        // Asegurar que los puntos estén dentro del rango [minPoints, maxPoints]
        $points = max($minPoints, min($points, $maxPoints));  // Asegurar que no sea menor que el mínimo según el nivel, ni mayor que 10

        // Calcular la eficiencia basada en la densidad del material
        $efficiency = round($material->density * 10);
        $efficiency = min(100, max(10, $efficiency));  // Limitar la eficiencia entre 10 y 100

        return [
            'points' => $points,
            'efficiency' => $efficiency,
            'statFactor' => $this->statFactor($material->density),
        ];
    }

    // Si no hay material asociado, devolver valores por defecto
    return [
        'points' => $inventionType->level, // El mínimo de puntos será el nivel del invento
        'efficiency' => 15,
        'statFactor' => 1,
    ];
}

    /**
     * Multiplicador de stats según densidad del material: a mayor densidad,
     * más ataque/defensa/etc. Escala suave (sqrt) acotada a [0.85, 1.6] para
     * que metales potencien sin romper el balance y madera/fibra no inutilice.
     */
    private function statFactor(float $density): float
    {
        return round(min(1.6, max(0.85, 0.7 + sqrt($density) / 5)), 2);

}
}