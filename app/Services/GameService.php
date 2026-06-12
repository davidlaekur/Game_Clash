<?php

namespace App\Services;

use App\Models\User;
use App\Models\Team;
use App\Models\Zone;
use App\Models\Action;
use App\Models\Combat;
use App\Models\Result;
use App\Models\Type;

use App\Services\UserService;


class GameService
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }



    /**
     * calculo de puntos globales 
     */
    public function calculateGlobalPoints(User $user)
    {
        $pointsFromInventions = $user->inventory->inventions->sum('points');

        $pointsFromResources = 0;
        foreach ($user->inventory->materials as $material) {
            if ($material->quantity > 0) {
                $pointsFromResources += round($material->quantity / (1000 * ($material->probability / 100)));
            }
        }

        $pointsFromZones = $user->team ? $user->team->zones->count() * 10 : 0;

        return $pointsFromInventions + $pointsFromResources + $pointsFromZones;
    }

    public function calculateCombatPoints(Zone $zone)
    {
        $baseDefense = $zone->effectiveDefense(); // la tormenta baja la defensa
        $controlledByTeam = optional($zone->team)->id === optional(auth()->user())->team_id;

        // === DEFENSA ===
        // La mandan los stats: defensa + salud de los defensores. El genérico
        // 'points' ya NO infla la defensa (lo deciden los stats que se forjan).
        $defenders = $zone->users()->players()->with(['inventory.inventions.stats'])->get();

        $playerDefense = 0;   // Σ (defensa + salud) de los defensores
        $teamDefense = 0;     // refuerzo si el equipo controla la zona
        $bonusTimeDefense = count($defenders) * 2; // +2 por defensor presente

        foreach ($defenders as $player) {
            $s = $this->userService->getTotalStats($player);
            $def = ($s['defensa'] ?? 0) + ($s['salud'] ?? 0);
            $playerDefense += $def;
            if ($controlledByTeam) {
                $teamDefense += (int) floor($def / 2); // refuerzo: la mitad
            }
        }

        // bonus por tiempo atrincherado (condiciones de la zona)
        if (session()->has('zone_entry_time')) {
            $timeInZone = now()->diffInSeconds(session('zone_entry_time'));
            $bonusTimeDefense += floor($timeInZone / 1800) * 2; // +2 cada 30 min
        }

        // factor de azar por condiciones (lluvia, enfermedades...): varía por consulta
        $defenseFactor = rand(70, 130) / 100;
        $totalDefense = round($baseDefense + ($playerDefense + $teamDefense + $bonusTimeDefense) * $defenseFactor);

        // === ATAQUE (posicional) ===
        // El ataque lo lanza un jugador desde una zona propia adyacente; la fuerza
        // = suma de TODOS los compañeros de su equipo presentes en esa zona de origen
        // (la guarnición). Espejo de la defensa. velocidad = iniciativa; suerte = tirada.
        $attackTypeId = Type::where('name', 'attack')->first()->id;
        $attackAction = Action::where('actionable_id', $zone->id)
            ->where('actionable_type', Zone::class)
            ->where('type_id', $attackTypeId)
            ->where('finish', false)
            ->latest('created_at')
            ->first();

        $attackBase = 0;
        $initiativeBonus = 0;
        $luck = 0;
        if ($attackAction) {
            $initiator = User::find($attackAction->user_id);
            $origin = $initiator ? $initiator->zone : null;
            if ($origin && $initiator->team_id) {
                $garrison = User::where('zone_id', $origin->id)
                    ->where('team_id', $initiator->team_id)
                    ->players()
                    ->with(['inventory.inventions.stats'])
                    ->get();
                foreach ($garrison as $g) {
                    $s = $this->userService->getTotalStats($g);
                    $attackBase += $s['ataque'] ?? 0;
                    $initiativeBonus += $s['velocidad'] ?? 0;
                    $luck += $s['suerte'] ?? 0;
                }
            }
        }

        $luckBonus = min(0.30, $luck * 0.01); // suerte mejora la tirada, máx +0.30
        $attackFactor = rand(70, 130) / 100 + $luckBonus;
        $attackPoints = round(($attackBase + $initiativeBonus) * $attackFactor);

        return [
            'totalDefense' => $totalDefense,
            'playerDefense' => $playerDefense,
            'teamDefense' => $teamDefense,
            'bonusTimeDefense' => $bonusTimeDefense,
            'attackPoints' => $attackPoints,
            'attackBase' => $attackBase,
            'initiativeBonus' => $initiativeBonus,
            'luckBonus' => $luckBonus,
            'defenseFactor' => $defenseFactor,
            'attackFactor' => $attackFactor,
            'defenders' => $defenders,
            // alias para vistas existentes
            'totalDefensePoints' => $teamDefense,
            'totalAttackPoints' => $initiativeBonus,
        ];
    }




    /**
     * comporbacion si un equipo ha ganado la partida
     */
    public function checkVictoryCondition()
    {
        $winningTeam = Team::withCount('zones')->orderByDesc('zones_count')->first();

        if ($winningTeam && $winningTeam->zones_count >= 9) {
            return "¡El equipo {$winningTeam->name} ha conquistado el mapa!";
        }

        return null;
    }

    public function resolveAttack(Zone $zone, ?array $combatData = null)
    {
        // usa el combate ya calculado (lo que ve el jugador == lo que decide).
        $combatData = $combatData ?: $this->calculateCombatPoints($zone);
        $totalDefense = $combatData['totalDefense'];
        $attackPoints = $combatData['attackPoints'];

        // todas las acciones de ataque pendientes en la zona
        $attackActions = Action::where('actionable_id', $zone->id)
            ->where('actionable_type', Zone::class)
            ->where('type_id', Type::where('name', 'attack')->first()->id)
            ->where('finish', false)
            ->get();

        if ($attackActions->isEmpty()) {
            return "No hay ataque registrado.";
        }

        $attackingTeam = optional(User::find($attackActions->first()->user_id))->team_id;
        $defendingTeam = $zone->team_id;

        $winnerTeamId = null;
        $resultDescription = "";

        if ($attackPoints > $totalDefense) {
            // ataque exitoso: la zona pasa directamente al equipo atacante
            $winnerTeamId = $attackingTeam;
            $resultDescription = "¡Ataque exitoso! La zona pasa a tu equipo.";
            $zone->update(['team_id' => $attackingTeam]);

            // mérito por conquistar: a la guarnición de la zona de origen
            $initiator = User::find($attackActions->first()->user_id);
            if ($initiator && $initiator->zone_id) {
                foreach (User::where('zone_id', $initiator->zone_id)->where('team_id', $attackingTeam)->players()->get() as $g) {
                    $g->addMerit(20);
                }
            }
        } else {
            // la defensa aguanta (incluye empate)
            $winnerTeamId = $defendingTeam;
            $resultDescription = $attackPoints == $totalDefense
                ? "¡Empate! La zona se mantiene bajo control del equipo defensor."
                : "¡El equipo defensor ha resistido el ataque!";

            // castigo: cada atacante pierde ALGUNOS inventos (los más débiles)
            $lost = [];
            foreach ($attackActions->pluck('user_id')->unique() as $aid) {
                $attacker = User::find($aid);
                if ($attacker) {
                    $lost = array_merge($lost, $this->loseSomeInventions($attacker));
                }
            }
            if (!empty($lost)) {
                $resultDescription .= ' Los atacantes pierden: ' . implode(', ', $lost) . '.';
            }

            // recompensa: los defensores ganan experiencia y mérito por aguantar
            $defenders = $zone->users()->players()->get();
            foreach ($defenders as $d) {
                $d->points = ($d->points ?? 0) + 5;
                $d->merit = (int) ($d->merit ?? 0) + 10;
                $d->save();
            }
            if ($defenders->isNotEmpty()) {
                $resultDescription .= ' Los defensores ganan +5 de experiencia y +10 de mérito por resistir.';
            }
        }

        // CERRAR las acciones para que el combate no se vuelva a resolver
        Action::where('actionable_id', $zone->id)
            ->where('actionable_type', Zone::class)
            ->where('type_id', Type::where('name', 'attack')->first()->id)
            ->where('finish', false)
            ->update(['finish' => true]);

        $combat = Combat::create([
            'random_factor' => $combatData['attackFactor'] ?? 1,
            'score' => $attackPoints - $totalDefense,
            'attacker_team_id' => $attackingTeam,
            'defender_team_id' => $defendingTeam,
            'winner_team_id' => $winnerTeamId,
        ]);

        Result::create([
            'result' => $resultDescription,
            'description' => "Combate en la zona " . $zone->name,
            'resultable_id' => $combat->id,
            'resultable_type' => Combat::class
        ]);

        return $resultDescription;
    }

    /**
     * El atacante derrotado pierde ~1/3 de sus inventos, los de menor valor
     * (points) primero. No lo pierde todo: castigo real pero no demoledor.
     */
    private function loseSomeInventions(User $attacker): array
    {
        if (!$attacker->inventory) {
            return [];
        }
        $inventions = $attacker->inventory->inventions;
        $count = $inventions->count();
        if ($count === 0) {
            return [];
        }
        $toLose = max(1, (int) floor($count / 3));
        $lost = [];
        foreach ($inventions->sortBy('points')->take($toLose) as $invention) {
            $lost[] = $invention->name;
            $invention->delete();
        }
        return $lost;
    }

    /**
     * Reset de la partida  
     */
    public function resetGame()
    {
        Zone::whereNotNull('team_id')->update(['team_id' => null]);
        User::where('points', '>', 0)->update(['points' => 0]);

        foreach (User::all() as $user) {
            $user->inventory->inventions()->delete();
            $user->inventory->materials()->delete();
        }
    }
}
