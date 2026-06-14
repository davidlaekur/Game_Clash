<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proposal;
use App\Models\User;
use App\Services\GameService;

class ProposalController extends Controller
{
    protected $gameService;

    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    /** Apoyar o rechazar (veto) una propuesta de rendición del equipo. */
    public function vote(Request $request, Proposal $proposal)
    {
        $user = auth()->user();

        if (!$proposal->isPending()) {
            return redirect()->route('zones.index')->with('warning', 'Esa propuesta ya no está activa.');
        }
        if ((string) $proposal->team_id !== (string) $user->team_id) {
            return redirect()->route('zones.index')->with('error', 'No es una propuesta de tu equipo.');
        }

        // un rechazo basta para cancelar (veto)
        if ($request->input('vote') === 'reject') {
            $proposal->status = 'cancelled';
            $proposal->save();
            return redirect()->route('zones.index')->with('success', 'Has rechazado la rendición: el cónclave queda cancelado.');
        }

        // apoyo (sin duplicar)
        $supporters = collect($proposal->supporters ?? [])->map(fn($i) => (string) $i);
        if (!$supporters->contains((string) $user->id)) {
            $supporters->push((string) $user->id);
            $proposal->supporters = $supporters->unique()->values()->all();
            $proposal->save();
        }

        // ¿mayoría del equipo apuntado?
        $teamCount = User::players()->where('team_id', $proposal->team_id)->where('joined', true)->count();
        $majority = intdiv($teamCount, 2) + 1;
        if (count($proposal->supporters ?? []) >= $majority) {
            $this->gameService->executeSurrenderProposal($proposal);
            return redirect()->route('zones.index')->with('success', 'El cónclave aprueba la rendición. Tu equipo se retira de la guerra.');
        }

        return redirect()->route('zones.index')->with('success', 'Has apoyado la rendición. Faltan más apoyos del equipo.');
    }

    /** El proponente la ejecuta a solas si nadie respondió en el plazo. */
    public function execute(Proposal $proposal)
    {
        $user = auth()->user();

        if (!$proposal->isPending()) {
            return redirect()->route('zones.index')->with('warning', 'Esa propuesta ya no está activa.');
        }
        if ((string) $proposal->proposer_id !== (string) $user->id) {
            return redirect()->route('zones.index')->with('error', 'Solo quien la propuso puede ejecutarla en solitario.');
        }
        if (!$proposal->canExecuteUnilaterally()) {
            return redirect()->route('zones.index')->with('error', 'Aún debes esperar a que tus compañeros respondan.');
        }

        $this->gameService->executeSurrenderProposal($proposal);
        return redirect()->route('zones.index')->with('success', 'Has ejecutado la rendición de la última zona. Tu equipo se retira.');
    }
}
