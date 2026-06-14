<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\GameController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\InventionController;
use App\Http\Controllers\ActionController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdventureController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\AdminController;






/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|

*/

Route::get('/', function () {
    if (Auth::check()) {
        // Si el usuario está autenticado, entra al mapa principa
        return redirect()->route('zones.index');
    }
    // Si no vamos a login
    return redirect()->route('login');
});


Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::middleware(['auth'])->group(function () {
    Route::resource('zones', ZoneController::class); // Vista donde el usuario entra después de loguearse
    Route::post('/import-zones', [ZoneController::class, 'importZones'])->name('import.zones');
    Route::post('zones/{zone}/mina', [ZoneController::class, 'buildMine'])->name('zones.buildMine');
    Route::post('zones/{zone}/rendir', [ZoneController::class, 'surrender'])->name('zones.surrender');
    Route::get('ranking', [PlayerController::class, 'ranking'])->name('ranking');
    Route::get('map-state', [ZoneController::class, 'state'])->name('map.state'); // refresco en vivo del mapa
    Route::post('game/new', [GameController::class, 'newGame'])->name('game.new');     // abrir nueva partida (admin)
    Route::post('game/start', [GameController::class, 'startGame'])->name('game.start'); // empezar ya (admin override)
    Route::post('game/end', [GameController::class, 'forceEnd'])->name('game.end');     // terminar partida (admin)
    Route::post('players/join', [PlayerController::class, 'join'])->name('players.join'); // unirse a la partida
    Route::post('proposals/{proposal}/vote', [ProposalController::class, 'vote'])->name('proposals.vote');       // cónclave: apoyar/rechazar
    Route::post('proposals/{proposal}/execute', [ProposalController::class, 'execute'])->name('proposals.execute'); // cónclave: ejecutar unilateral

    // Panel de mando del admin (árbitro)
    Route::get('admin', [AdminController::class, 'index'])->name('admin.panel');
    Route::post('admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('admin/event', [AdminController::class, 'forceEvent'])->name('admin.event');
    Route::post('admin/zone', [AdminController::class, 'reassignZone'])->name('admin.zone');
    Route::post('admin/merit', [AdminController::class, 'adjustMerit'])->name('admin.merit');
    Route::post('admin/heal', [AdminController::class, 'healPlayer'])->name('admin.heal');
    Route::post('admin/expel', [AdminController::class, 'expelPlayer'])->name('admin.expel');


    Route::get('teams/transfer', [TeamController::class, 'transfer'])->name('teams.transfer');
    Route::post('teams/process-transfer', [TeamController::class, 'processTransfer'])->name('teams.processTransfer');
    Route::resource('teams', TeamController::class);
    Route::resource('players', PlayerController::class);
    Route::resource('inventions', InventionController::class)->except(['store']); // metodo store a traves del middleware
    Route::resource('actions', ActionController::class);
    Route::resource('communications', CommunicationController::class);
    Route::resource('resources', ResourceController::class);
    Route::resource('games', GameController::class);


    Route::post('players/explore/{zone}', [PlayerController::class, 'explore'])->middleware('monitor.action')->name('players.explore');
    Route::post('players/move/{zone}', [PlayerController::class, 'move'])->middleware('monitor.action')->name('players.move');
    Route::post('players/invent/{zone}', [PlayerController::class, 'invent'])->middleware('monitor.action')->name('players.invent');
    Route::post('players/collect/{zone}', [PlayerController::class, 'collect'])->middleware('monitor.action')->name('players.collect');
    Route::post('players/attack/{zone}', [PlayerController::class, 'attack'])->middleware('monitor.action')->name('players.attack');
    Route::post('players/defend/{zone}', [PlayerController::class, 'defend'])->middleware('monitor.action')->name('players.defend');
    Route::post('inventions/store', [InventionController::class, 'store'])->middleware('monitor.action')->name('inventions.store');

    Route::get('/adventure/intro', [AdventureController::class, 'showIntro'])->name('adventure.intro');
    Route::get('/adventure/run', [AdventureController::class, 'runAdventure'])->name('adventure.run');
    Route::get('/adventure/start', [AdventureController::class, 'createUserAdventure'])->name('adventure.start');
    Route::get('/adventure/scenario/{id}', [AdventureController::class, 'getScenario'])->name('adventure.scenario');
    Route::post('/adventure/check', [AdventureController::class, 'checkSelectedOption'])->name('adventure.check');
    Route::post('/adventure/continue/{id}', [AdventureController::class, 'continueAdventure'])->middleware('check.adventure')->name('adventure.continue');
});
