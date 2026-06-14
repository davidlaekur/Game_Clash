<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use App\Models\Adventure;
use App\Models\UserAdventure;
use App\Models\Scenario;
use App\Models\Option;
use App\Models\Item;
use App\Models\User;
use App\Models\Material;
use App\Models\Inventory;
use App\Models\InventoryMaterial;
use App\Services\StarWarsApiService;
use Illuminate\Support\Facades\Auth;

class AdventureController extends Controller
{
    protected $starWarsApiService;


    /**
     * Servicio API
     */
    public function __construct(StarWarsApiService $starWarsApiService)
    {
        $this->starWarsApiService = $starWarsApiService;
    }


        /**
     *  intro de la aventura
     */
    public function showIntro()
    {
        $user = Auth::user();
        $hasActive = UserAdventure::where('user_id', $user->id)->where('completed', false)->exists();
        if (!$hasActive) {
            if ($user->rankLevel() < 2) {
                return redirect()->route('zones.index')->with('error', 'Necesitas rango Veterano para emprender una aventura.');
            }
            if ((int) ($user->merit ?? 0) < 100) {
                return redirect()->route('zones.index')->with('error', 'Necesitas 100 méritos disponibles (se gastan al partir). Sigue destacando en combate.');
            }
        }
        return view('adventures.intro');
    }


    /**
     * Obtenemos la aventura activa del usuario y la mostramos
     */
    public function runAdventure()
    {
        $user = Auth::user();
        $hasActive = UserAdventure::where('user_id', $user->id)->where('completed', false)->exists();
        if (!$hasActive) {
            if ($user->rankLevel() < 2) {
                return redirect()->route('zones.index')->with('error', 'Necesitas rango Veterano para emprender una aventura.');
            }
            if ((int) ($user->merit ?? 0) < 100) {
                return redirect()->route('zones.index')->with('error', 'Necesitas 100 méritos disponibles para emprender la aventura.');
            }
        }
        $userAdventure = $this->obtenerAventuraActiva($user);

        if (!$userAdventure) {
            return redirect()->route('zones.index')->with('error', 'No hay aventuras disponibles en este momento.');
        }

        $adventure = Adventure::with(['items', 'scenarios'])->find($userAdventure->adventure_id);

        $scenario = $this->getScenario($userAdventure->scenario_id);

        // ¿ya respondió esta pregunta? (bloquea re-responder y muestra feedback)
        $answered = (string) $userAdventure->answered_scenario === (string) $userAdventure->scenario_id;
        $wasCorrect = (bool) $userAdventure->answered_correct;

        return view('adventures.screen', compact('adventure', 'scenario', 'userAdventure', 'answered', 'wasCorrect'));
    }


    /**
     * Obtener la aventura activa del usuario sino la creamos
     */
    private function obtenerAventuraActiva($user)
    {
        $userAdventure = UserAdventure::where('user_id', $user->id)
            ->where('completed', false)
            ->first();

        if (!$userAdventure) {
            $userAdventure = $this->createUserAdventure($user);
        }

        return $userAdventure;
    }


    /**
     * Crea una nueva aventura para el usuario
     */

    private function createUserAdventure($user)
    {
        $adventures = Adventure::all();

        if ($adventures->isEmpty()) {
            return null; // null si no hay aventuras
        }

        $adventure = $adventures->random();
        $firstScenario = Scenario::where('adventure_id', $adventure->id)->first();

        if (!$firstScenario) {
            return null; // Evitar error si no hay escenarios en la aventura
        }

        // emprender una aventura CUESTA méritos (moneda), pero NO baja el rango:
        // gastas 100 méritos y tienes que volver a ganarlos para otra aventura.
        $user->spendMerit(100);

        return UserAdventure::create([
            'user_id' => $user->id,
            'adventure_id' => $adventure->id,
            'scenario_id' => $firstScenario->id,
            'completed' => false
        ]);
    }


    /**
     * Recuperar el escenario correspondiente a un identificador y nombre con servicio API 
     */

    public function getScenario($id)
    {
        $scenario = Scenario::find($id);

        if (!$scenario) {
            return redirect()->back()->with('error', 'Escenario no encontrado');
        }

        // la API externa puede fallar/tardar: si pasa, seguimos con un genérico
        try {
            $character = $this->starWarsApiService->getRandomCharacter();
        } catch (\Throwable $e) {
            $character = null;
        }
        $character = $character ?: 'un viajero misterioso';

        $scenario->question = "Aparece {$character} y nos pregunta: " . $scenario->question;

        return $scenario;
    }


    /**
     * validaciones respuestas y construcción del mensaje
     */

    public function checkSelectedOption(Request $request)
    {
        // Validar los datos usando los nombres correctos
        $request->validate([
            'selected_option' => 'required|exists:options,_id',
            'scenario' => 'required|exists:scenarios,_id',
        ]);

        // Recuperar la aventura activa del usuario
        $userAdventure = UserAdventure::where('user_id', Auth::id())->where('completed', false)->first();

        if (!$userAdventure) {
            return redirect()->back()->with('error', 'No tienes una aventura activa');
        }

        // un intento por pregunta: si ya respondiste este escenario, no se repite
        if ((string) $userAdventure->answered_scenario === (string) $userAdventure->scenario_id) {
            return redirect()->route('adventure.run')->with('error', 'Ya has respondido esta pregunta.');
        }

        // Recuperar la opción seleccionada y evaluar
        $option = Option::where('_id', $request->selected_option)->first();
        $correct = (bool) ($option->is_correct ?? false);

        // registrar la respuesta (bloquea el escenario)
        $userAdventure->answered_scenario = $userAdventure->scenario_id;
        $userAdventure->answered_correct = $correct;
        $userAdventure->save();

        if ($correct) {
            $message = "<strong>¡Correcto!</strong>";
            $item = Item::where('itemable_id', $userAdventure->scenario_id)->first();
            if ($item) {
                $message .= " Has encontrado un {$item->name}. {$item->description}";
            }
            session(['answer_iscorrect' => true]);
            return redirect()->route('adventure.run')->with('success', $message);
        }

        return redirect()->route('adventure.run')->with('error', '¡Fallaste! Esa no era la respuesta correcta.');
    }



    /**
     *  Continuamos  la aventura  
     */

    public function continueAdventure(Request $request, $id)
    {
        $userAdventure = UserAdventure::find($id);
        if (!$userAdventure) {
            return redirect()->route('zones.index')->with('error', 'Aventura no encontrada');
        }

        // debe haber respondido la pregunta actual (correcta o no) antes de avanzar
        if ((string) $userAdventure->answered_scenario !== (string) $userAdventure->scenario_id) {
            return redirect()->route('adventure.run')->with('error', 'Responde la pregunta antes de avanzar.');
        }

        session()->forget('answer_iscorrect');


        $adventure = Adventure::with('scenarios')->find($userAdventure->adventure_id);
        $scenarios = $adventure->scenarios->pluck('id')->toArray();

        $actualScenario = array_search($userAdventure->scenario_id, $scenarios);


        if ($actualScenario !== false && isset($scenarios[$actualScenario + 1])) {
            $nextScenarioId = $scenarios[$actualScenario + 1];
            // avanza y limpia el estado de respuesta para la nueva pregunta
            $userAdventure->update([
                'scenario_id' => $nextScenarioId,
                'answered_scenario' => null,
                'answered_correct' => null,
            ]);

            return redirect()->route('adventure.run');
        }


        // ponemos a true la aventura y ponemos a null el escenario
        $userAdventure->update(['completed' => true, 'scenario_id' => null, 'answered_scenario' => null, 'answered_correct' => null]);

        // premio jugable: materia estelar al inventario (habilita inventos de élite)
        $stellarGranted = $this->grantStellarMaterial($userAdventure->user_id, 1);

        // premios de la aventura
        $rewards = Item::where('itemable_id', $userAdventure->adventure_id)
            ->where('itemable_type', 'App\Models\Adventure')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'description' => $item->description,
                    'image' => $item->image,
                ];
            });

        // recompensas ganadas en los escenarios
        $earnedItems = Item::whereIn('itemable_id', $scenarios)
            ->where('itemable_type', 'App\Models\Scenario')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'description' => $item->description,
                    'image' => $item->image,
                ];
            });

        // Construir el mensaje de final de la aventura con los premios ( no muestro las recompensas en el mensaje para hacerlo idéntico al pdf)

        $successMessage = "<strong>¡Felicidades!</strong><br>¡Enhorabuena! Aventura completada.";

        if ($rewards->isNotEmpty()) {
            foreach ($rewards as $reward) {
                $successMessage .= " Has conseguido un " . $reward['name'] . ".";
            }
        }

        if ($stellarGranted) {
            $successMessage .= " Has traído <strong>Aleación estelar</strong> del espacio: úsala para forjar inventos de élite.";
        }

        // cargo los premios y recompensas en la vista del jugador para mostrarlos
        return redirect()->route('players.show', ['player' => $userAdventure->user_id])->with(['success' => $successMessage, 'rewards' => $rewards, 'earnedItems' => $earnedItems]);
    }

    /**
     * Añade materia estelar al inventario personal del jugador (premio de aventura).
     * La materia estelar no se recolecta en zonas: solo llega de las aventuras.
     */
    private function grantStellarMaterial($userId, int $amount = 1): bool
    {
        $user = User::find($userId);
        $material = Material::where('name', 'Aleacion estelar')->first();
        if (!$user || !$material) {
            return false;
        }

        $inventory = Inventory::firstOrCreate(
            ['inventoriable_id' => $user->id, 'inventoriable_type' => get_class($user)],
            ['type' => 'personal', 'name' => 'Inventario de ' . $user->name]
        );

        $line = InventoryMaterial::where('inventory_id', $inventory->_id)
            ->where('material_id', $material->_id)
            ->first();

        if ($line) {
            $line->quantity += $amount;
            $line->save();
        } else {
            InventoryMaterial::create([
                'inventory_id' => $inventory->_id,
                'material_id' => $material->_id,
                'quantity' => $amount,
            ]);
        }

        return true;
    }
}
