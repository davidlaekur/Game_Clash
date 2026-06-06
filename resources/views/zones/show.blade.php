@extends('layouts.app')

@section('title', 'Detalle de Zona')

@section('content')

<!-- Volver al Mapa -->
<div class="mb-4">
    <a href="{{ route('zones.index') }}" class="btn btn-primary">Volver</a>
</div>

<!-- @if ($attackResult)
    <div class="alert alert-info">
        {{ $attackResult }}
    </div>
@endif
 -->
 @error('error')
    <div class="alert alert-danger mt-4">
        {{ $message }}
    </div>
@enderror


<h2 class="mb-4">{{ $zone->name }}</h2>

<!-- Imagen  de la zona -->
<img src="{{ asset($zone->image_detail) }}" alt="Detalle de {{ $zone->name }}" class="card-img-top img-fluid" style="height: 500px; object-fit: cover;">

<h4 class="mt-4">🌍 Paisaje: {{ ucfirst($zone->landscape) }}</h4>
<h4 class="mt-4">📍 Coordenadas: ({{ $zone->latitude }}, {{ $zone->longitude }})</h4>
<h4 class="mt-4">🎖️ Controlada por: {{ $zone->team->name ?? 'Zona Neutral' }}</h4>

<!-- Defensa Zona -->
<h4 class="mt-4">🛡️ Defensa de la Zona</h4>
<ul class="list-group">
    <li class="list-group-item"><strong>Base (Terreno):</strong> {{ $zone->defense }}</li>
    <li class="list-group-item"><strong>Por Jugadores (Incluye Inventos y Stats):</strong> {{ $totalDefender}}</li>
    <li class="list-group-item"><strong>Bonus por Tiempo en la Zona:</strong> {{ $bonusTimeDefense }}</li>
    <li class="list-group-item"><strong>Total:</strong> <span class="badge bg-primary fs-6">{{ $totalDefense }}</span></li>
</ul>




<!-- ⛏️ Recursos en la zona -->
<h4 class="mt-4">⛏️ Recursos Disponibles</h4>
<ul>
    @foreach($zone->materials as $material)
    <li>{{ $material->name }} (🎲 Probabilidad: {{ $material->probability }}%)</li>
    @endforeach
</ul>

<!-- ⏳ Acciones y Timer -->
<h4 class="mt-4">🎮 Acciones</h4>
@if ($timeRemaining > 0)
    <p id="timer" class="text-danger">
        ⏳ No puedes realizar otra acción. Tiempo restante: <span id="timeRemaining">{{ $timeRemaining }}</span> segundos.
    </p>
@endif

<script>
    let timeRemaining = parseInt(document.getElementById('timeRemaining')?.innerText || 0);
    if (timeRemaining > 0) {
        const timer = setInterval(() => {
            timeRemaining--;
            document.getElementById('timeRemaining').innerText = timeRemaining;

            if (timeRemaining <= 0) {
                clearInterval(timer);
                document.getElementById('timer').classList.remove('text-danger');
                document.getElementById('timer').classList.add('text-success');
                document.getElementById('timer').innerText = "La acción ha sido completada. Puedes realizar otra acción.";
            }
        }, 1000);
    }
</script>

<!-- Botones -->
<div class="d-flex gap-3 mt-4">
    @if ($user->zone_id !== $zone->id)
        <form action="{{ route('players.move', $zone->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-secondary" {{ $timeRemaining > 0 ? 'disabled' : '' }}>🚶‍♂️ Moverse</button>
        </form>
    @endif

    @if ($user->zone_id === $zone->id && $zone->team_id === null)
        <form action="{{ route('players.explore', $zone->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger" {{ $timeRemaining > 0 ? 'disabled' : '' }}>🧭 Explorar</button>
        </form>
    @endif

    @if ($user->zone_id === $zone->id && $zone->team_id === $user->team_id)
        <form action="{{ route('players.collect', $zone->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-warning" {{ $timeRemaining > 0 ? 'disabled' : '' }}>⛏️ Recolectar</button>
        </form>
    @endif

    @if ($user->zone_id === $zone->id && $zone->team_id === $user->team_id)
        <form action="{{ route('players.invent', $zone->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success" {{ $timeRemaining > 0 ? 'disabled' : '' }}>💡 Inventar</button>
        </form>
    @endif

    

    @if ($user->team_id !== null && $zone->team_id !== $user->team_id && $zone->team_id !== null && $zoneAdjacent)
   <form action="{{ route('players.attack', $zone->id) }}" method="POST">
    @csrf
    <button type="submit" class="btn btn-danger"{{ $timeRemaining > 0 ? 'disabled' : '' }}>⚔️ Iniciar Batalla</button>
</form>

@endif

</div>

@endsection
