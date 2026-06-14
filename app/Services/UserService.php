<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public function getTotalPoints(User $user)
    {
        return $user->inventory ? $user->inventory->inventions->sum('points') : 0;
    }

    /**
     * Factor de duración de acciones según ingenio: cada punto de ingenio acorta
     * un 2% el tiempo de explorar/recolectar/forjar, hasta un máximo del 40%.
     */
    public function actionSpeedFactor(User $user): float
    {
        $ingenio = $this->getTotalStats($user)['ingenio'] ?? 0;
        return max(0.6, 1 - $ingenio * 0.02);
    }

    public function getTotalStats(User $user)
    {
        $stats = [
            'ataque' => 0,
            'defensa' => 0,
            'salud' => 0,
            'velocidad' => 0,
            'suerte' => 0,
            'capacidad' => 0,
            'ingenio' => 0,
        ];

        if (!$user->inventory || !$user->inventory->inventions) {
            return $stats; // Si no hay inventario o inventos, devolvemos las estadísticas en 0.
        }

        foreach ($user->inventory->inventions as $invention) {
            if (!$invention->stats) {
                continue; // si no tiene stats saltamos al siguiente invento
            }

            foreach ($invention->stats as $inventionStat) {
                if ($inventionStat->stat) { // chequeamos que la relación con stat exista
                    $statName = strtolower($inventionStat->stat->name);
                    if (isset($stats[$statName])) {
                        $stats[$statName] += $inventionStat->value;
                    }
                }
            }
        }

        // un jugador Herido pelea peor: −20% a los stats de combate (no afecta a
        // ingenio/capacidad). Se cura solo con el tiempo.
        if ($user->isWounded()) {
            foreach (['ataque', 'defensa', 'salud', 'velocidad', 'suerte'] as $k) {
                $stats[$k] = (int) round($stats[$k] * 0.8);
            }
        }

        return $stats;
    }

    public function getTotalCapacity(User $user)
    {
        // Obtener la capacidad base desde el rol del usuario
        $baseCapacity = $user->role ? $user->role->base_capacity : 0;

        // Verificar si el usuario tiene inventario antes de acceder a los inventos
        if (!$user->inventory) {
            return $baseCapacity;
        }

        // Capacidad adicional otorgada por los inventos equipados
        $bonusCapacity = $user->inventory->inventions->sum(function ($invention) {
            return $invention->stats->where('stat.name', 'capacidad')->sum('value');
        });


        // capacidad base y la capacidad adicional por inventos
        $capacity = $baseCapacity + $bonusCapacity;

        //ocupado por inventos
        $occupiedByInventions = $user->inventory->inventions->count(); // Restar 1 espacio por cada invento

        // materiales tiene el usuario en su inventario 
        $occupiedByMaterials = $user->inventory->materials->sum('quantity');

       // capacidad disponible
       $availableCapacity = $capacity - ($occupiedByInventions + $occupiedByMaterials);

       //sin valores negativos
       return max(0, $availableCapacity);
   }
}
