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
        $baseDefense = $zone->defense;
        $controlledByTeam = optional($zone->team)->id === optional(auth()->user())->team_id;

        // defensores en la zona
        $defenders = $zone->users()->with(['inventory.inventions.stats'])->get();



        //Defensa de los jugadores
        $playerDefense = 0;
        $totalDefensePoints = 0;
        $teamDefense = 0;
        $bonusTimeDefense = count($defenders) * 2; // Min +2 por defensor presente
        $totalPlayerPoints = 0; //  puntos base de cada jugador

        foreach ($defenders as $player) {
            // suma de stats de defensa y salud
            $defenseStat = $this->userService->getTotalStats($player)['defensa'] ?? 0;
            $healthStat = $this->userService->getTotalStats($player)['salud'] ?? 0;
            $playerDefense += $defenseStat + $healthStat;

            //  puntos de los inventos equipados
            $totalDefensePoints += $this->userService->getTotalPoints($player);

            //  puntos base del jugador en la zona
            $totalPlayerPoints += $player->points;

            // Defensa del equipo si están en la zona
            if ($controlledByTeam) {
                $teamDefense += $defenseStat + $healthStat;
            }

            // Bonus por tiempo 
            if (session()->has('zone_entry_time')) {
            }
            $timeInZone = now()->diffInSeconds(session('zone_entry_time'));
            $bonusTimeDefense += floor($timeInZone / 1800) * 2; // +2 por cada  30 min.
        }


        // calcular la defensa total
        $randomFactor = rand(70, 130) / 100;
        $totalDefense = round(
            $baseDefense + ($playerDefense + $totalDefensePoints + $bonusTimeDefense + $teamDefense + $totalPlayerPoints) * $randomFactor
        );

        // Ataque
        $attackAction = Action::where('actionable_id', $zone->id)
            ->where('actionable_type', Zone::class)
            ->where('type_id', Type::where('name', 'attack')->first()->id)
            ->where('finish', false)
            ->first();

        $attackPoints = 0;
        $totalAttackPoints = 0;

        if ($attackAction) {
            $attacker = User::with(['inventory.inventions.stats'])->find($attackAction->user_id);

            if ($attacker && $attacker->zone_id !== $zone->id) {
                $baseAttack =  $this->userService->getTotalStats($attacker)['ataque'] ?? 0;
                $totalAttackPoints = $this->userService->getTotalPoints($attacker);
                $attackPoints = round(($baseAttack + $totalAttackPoints) * $randomFactor);
            }
        }

        return compact(
            'totalDefense',
            'playerDefense',
            'bonusTimeDefense',
            'attackPoints',
            'totalDefensePoints',
            'totalAttackPoints',
            'totalPlayerPoints',
            'defenders'
        );
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

    public function resolveAttack(Zone $zone)
    {
        // obtener los datos de combate
        $combatData = $this->calculateCombatPoints($zone);
        $totalDefense = $combatData['totalDefense'];
        $attackPoints = $combatData['attackPoints'];

        // aplicar factor de aleatoriedad (0.7 - 1.3)
        $randomFactor = rand(70, 130) / 100;
        $attackPoints *= $randomFactor;

        // determinar el equipo atacante y defensor
        $attackAction = Action::where('actionable_id', $zone->id)
            ->where('actionable_type', Zone::class)
            ->where('type_id', Type::where('name', 'attack')->first()->id)
            ->where('finish', false)
            ->first();

        if (!$attackAction) {
            return "No hay ataque registrado.";
        }

        $attacker = User::find($attackAction->user_id);
        $attackingTeam = $attacker->team_id;
        $defendingTeam = $zone->team_id;

        // determinar el resultado del combate
        $winnerTeamId = null;
        $resultDescription = "";

        if ($attackPoints > $totalDefense) {
            // ataque exitoso: la zona se vuelve neutral
            $winnerTeamId = null;
            $resultDescription = "¡Ataque exitoso! La zona ahora es neutral.";

            // resetear la zona (eliminar equipo dueño)
            $zone->update(['team_id' => null]);

            // cancelar actividades en progreso en la zona ( por si habilitamos hacer mas de una accion)
            Action::where('actionable_id', $zone->id)
                ->where('actionable_type', Zone::class)
                ->where('finish', false)
                ->delete();
        } elseif ($attackPoints < $totalDefense) {
            // defensa exitosa: los atacantes pierden
            $winnerTeamId = $defendingTeam;
            $resultDescription = "¡El equipo defensor ha ganado!";

            // los atacantes pierden sus inventos equipados

            if ($attacker->inventory) {
                foreach ($attacker->inventory->inventions as $invention) {
                    $invention->delete();
                }
            }
        } else {
            // empate => gana el equipo defensor
            $winnerTeamId = $defendingTeam;
            $resultDescription = "¡Empate! La zona se mantiene bajo control del equipo defensor.";
        }

        // registrar el combate en la base de datos
        $combat = Combat::create([
            'random_factor' => $randomFactor,
            'score' => $attackPoints - $totalDefense,
            'attacker_team_id' => $attackingTeam,
            'defender_team_id' => $defendingTeam,
            'winner_team_id' => $winnerTeamId,
        ]);

        // guardar el resultado en la tabla Result
        Result::create([
            'result' => $resultDescription,
            'description' => "Combate en la zona " . $zone->name,
            'resultable_id' => $combat->id,
            'resultable_type' => Combat::class
        ]);

        return $resultDescription;
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
