<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Action;
use App\Models\Invention;
use App\Models\InventionType;
use App\Models\InventionStat;
use App\Models\Stat;
use App\Models\User;

class MonitorAction
{


    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if ($user) {
            $action = Action::where('user_id', $user->id)
                ->where('finish', false)
                ->latest('created_at')
                ->first();

            if ($action) {
                $timeElapsed = Carbon::now()->diffInSeconds($action->created_at);
                $timeRemaining = max(0, $action->duration - $timeElapsed);

                if ($timeRemaining > 0) {
                    User::where('_id', $user->id)->update([
                        'actionBlocked' => true,
                        'timeRemaining' => $timeRemaining
                    ]);
                } else {
                    
                    // marcar la acción como finalizada
                    $action->update(['finish' => true]);
                    session()->forget('actionBlocked');

                    if ($action->type->name === 'explore') {
                        $zone = $action->actionable;
                        $zone->update(['team_id' => $user->team_id]);

                        session()->flash('success', "Has completado la exploración. La {$zone->name} ahora pertenece a tu equipo.");
                    }

                    if ($action->type->name === 'invent') {
                        $inventionType = InventionType::find($action->actionable_id);
                        if ($inventionType) {
                               // Recuperar los datos de puntos y eficiencia desde la sesión
                               $pointsAndEfficiency = session('inventionPoints');

                            $invention = Invention::create([
                                'name' => $inventionType->name,
                                'efficiency' => $pointsAndEfficiency['efficiency'],
                                'level' => $inventionType->level,
                                'points' => $pointsAndEfficiency['points'],
                                'inventiontype_id' => $inventionType->id,
                                'inventory_id' => $user->inventory->id,
                            ]);

                                    // asignar estadísticas al invento
                            $inventionStats = [
                                'Piedra Afilada' => ['ataque' => 5],
                                'Cuerda' => ['ingenio' => 3, 'velocidad' => 2],
                                'Fuego' => ['ingenio' => 4, 'defensa' => 3],
                                'Lanza' => ['ataque' => 6, 'defensa' => 2],
                                'Arco y Flecha' => ['ataque' => 7, 'defensa' => 3],
                                'Cesta' => ['capacidad' => 5, 'suerte' => 2],
                                'Rueda' => ['velocidad' => 5],
                                'Trampa' => ['defensa' => 6],
                                'Hacha' => ['suerte' => 4],
                                'Carro' => ['capacidad' => 7, 'velocidad' => 3],
                                'Traje de Malla' => ['salud' => 5, 'defensa' => 4],
                                'Espada' => ['ataque' => 8],
                                'Escudo' => ['salud' => 6, 'defensa' => 5],
                            ];

                            if (isset($inventionStats[$invention->name])) {
                                foreach ($inventionStats[$invention->name] as $statName => $value) {
                                    $stat = Stat::where('name', $statName)->first();
                                    if ($stat) {
                                        InventionStat::create([
                                            'invention_id' => $invention->id,
                                            'stat_id' => $stat->id,
                                            'value' => $value,
                                        ]);
                                    }
                                }
                            }

                            session()->flash('success', "Se ha completado la creación del invento {$inventionType->name}.");
                        } 
                        
                        else {
                            session()->flash('error', "Error: El tipo de invento no existe.");
                        }
                    }
                }
            }
        }

        return $next($request);
    }
}
