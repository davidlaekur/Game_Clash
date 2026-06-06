@extends('layouts.app')

@section('title', 'Batalla en ' . $zone->name)

@section('content')

<!-- volver -->
<div class="mb-4">
    <a href="{{ route('zones.show', $zone->id) }}" class="btn btn-primary">Volver a la Zona</a>
</div>

<h2 class="mb-4 text-center">⚔️ Batalla en {{ $zone->name }}</h2>

<!-- @if ($attackResult)
<div class="alert alert-info text-center">
    <strong>{{ $attackResult }}</strong>
</div>
@endif -->

<!-- timer -->
@if ($timeRemaining > 0)
<p id="battle-timer" class="text-danger text-center">
    ⏳ La batalla sigue en curso. Tiempo restante:
    <span id="timeRemaining">{{ $timeRemaining }}</span> segundos.
</p>
@endif

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let timeRemaining = parseInt(document.getElementById('timeRemaining')?.innerText || 0);
        let battleResult = document.getElementById('battle-result'); // Obtener el resultado de la batalla

        if (timeRemaining > 0) {
            const timer = setInterval(() => {
                timeRemaining--;
                document.getElementById('timeRemaining').innerText = timeRemaining;

                if (timeRemaining <= 0) {
                    clearInterval(timer);
                    document.getElementById('battle-timer').innerText = "¡La batalla ha terminado!";

                    // Mostrar el resultado de la batalla cuando el tiempo llegue a 0
                    if (battleResult) {
                        battleResult.style.display = "block";
                    }
                }
            }, 1000);
        } else {
            // Si el tiempo ya es 0 al cargar la página, mostrar el resultado 
            if (battleResult) {
                battleResult.style.display = "block";
            }
        }
    });
</script>

<!-- resumen del combate -->
<h4 class="mt-4 text-center">Resumen del Combate</h4>

<div class="row mt-4">
    <!-- atacantes -->
    <div class="col-md-6">
        <h3 class="text-danger">⚔️ Atacantes</h3>
        <ul class="list-group">
            @foreach ($attackers as $attacker)
            <li class="list-group-item">
                <strong>{{ $attacker->name }}</strong>
                <span class="badge bg-danger">Stat de Ataque: {{ $attacker->attackStats['ataque'] ?? 0 }}</span>
                <span class="badge bg-danger">Puntos de Inventos: {{ $attacker->attackPoints ?? 0 }}</span>
            </li>
            @endforeach
        </ul>
    </div>

    <!-- defensores -->
    <div class="col-md-6">
        <h3 class="text-primary">🛡️ Defensores</h3>
        <ul class="list-group">
            @foreach ($defenders as $defender)
            <li class="list-group-item">
                <strong>{{ $defender->name }}</strong>
                <span class="badge bg-primary">Stat de Defensa: {{ $defender->defenseStats['defensa'] ?? 0 }}</span>
                <span class="badge bg-primary">Stat de Salud: {{ $defender->defenseStats['salud'] ?? 0 }}</span>
                <span class="badge bg-primary">Puntos de Inventos: {{ $defender->defensePoints ?? 0 }}</span>
            </li>
            @endforeach
        </ul>
    </div>
</div>



<!-- progreso de batalla -->
<h4 class="mt-5 text-center">📌 Estado del Combate</h4>
<div class="progress mt-3" style="height: 40px;">
    <div class="progress-bar bg-danger" role="progressbar"
        style="width: {{ min(100, ($attackPoints / max(1, $totalDefense + $attackPoints)) * 100) }}%"
        aria-valuenow="{{ $attackPoints }}" aria-valuemin="0"
        aria-valuemax="{{ $totalDefense + $attackPoints }}">
        ⚔️ Ataque: {{ $attackPoints }}
    </div>
    <div class="progress-bar bg-primary" role="progressbar"
        style="width: {{ min(100, ($totalDefense / max(1, $totalDefense + $attackPoints)) * 100) }}%"
        aria-valuenow="{{ $totalDefense }}" aria-valuemin="0"
        aria-valuemax="{{ $totalDefense + $attackPoints }}">
        🛡️ Defensa: {{ $totalDefense }}
    </div>
</div>

<!-- desglose de puntos -->
<h4 class="mt-4 text-center">📜 Desglose de la Batalla</h4>
<ul class="list-group text-center">
    <li class="list-group-item"><strong>🛡️ Defensa Base de la Zona:</strong> {{ $zone->defense }}</li>
    <li class="list-group-item"><strong>🛡️ Defensa por Stats de Jugadores:</strong> {{ $playerDefense }}</li>
    <li class="list-group-item"><strong>🛡️ Defensa por Inventos:</strong> {{ $totalDefensePoints }}</li>
    <li class="list-group-item"><strong>⏳ Bonus por Tiempo en la Zona:</strong> {{ $bonusTimeDefense }}</li>
    <li class="list-group-item"><strong>🔥 Ataque Base del Atacante:</strong> {{ $attackPoints }}</li>
    <li class="list-group-item"><strong>🔥 Ataque por Inventos del Atacante:</strong> {{ $totalAttackPoints }}</li>
    <li class="list-group-item"><strong>🎲 Factor de Aleatoriedad Aplicado:</strong> 70% - 130%</li>
    <li class="list-group-item"><strong>📌 Resultado Final:</strong></li>
</ul>


<!-- resultado final -->
<!-- resultado final -->
<div id="battle-result"
    class="alert text-center {{ $attackPoints > $totalDefense ? 'battle-won' : 'battle-lost' }}"
    style="display: none;">
    <strong>{{ $attackResult }}</strong>
</div>

<!-- animación de batalla -->
<div class="mt-4 text-center">
    <h3 class="mb-3 fs-4"></h3>
    <img src="{{ asset('images/animation/combat2.gif') }}" alt="Animación de ataque" style="width: 700px; height: auto;">
</div>

@endsection