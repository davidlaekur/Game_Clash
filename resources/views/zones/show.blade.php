@extends('layouts.app')

@section('title', $zone->name)

@section('content')
@php
    $teamName = $zone->team->name ?? null;
    $teamMod = $teamName
        ? (str_contains(strtolower($teamName), 'laraveland') ? 'laraveland' : (str_contains(strtolower($teamName), 'itaca') ? 'itaca' : 'neutral'))
        : 'neutral';
@endphp

<div class="zone-view">
    <a href="{{ route('zones.index') }}" class="btn-ghost zone-back">← Volver al mapa</a>

    @error('error')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    @php
        $event = $zone->activeEvent();
        $isMyZone = $zone->team_id && $zone->team_id === auth()->user()->team_id;
    @endphp
    @if ($event)
        <div class="world-event world-event--{{ $event['type'] }}">
            <i class="fas {{ $event['icon'] }}" aria-hidden="true"></i>
            <span><b>{{ $event['label'] }}</b> —
                @if ($event['type'] === 'tormenta')
                    @if ($isMyZone)
                        tu zona queda expuesta (defensa −{{ $event['magnitude'] }}): vigila los ataques enemigos.
                    @else
                        la defensa de la zona baja −{{ $event['magnitude'] }}: es el momento de atacar.
                    @endif
                @else
                    {{ $event['desc'] }}
                @endif
            </span>
        </div>
    @endif

    <div class="zone-grid">
        {{-- Columna izquierda: ilustración --}}
        <div class="zone-hero panel panel--framed">
            <img src="{{ asset($zone->image_detail) }}" alt="{{ $zone->name }}">
            <div class="zone-hero__overlay">
                <h1 class="zone-hero__name">{{ $zone->name }}</h1>
                <div class="zone-hero__tags">
                    <span class="chip chip--brass">{{ ucfirst($zone->landscape) }}</span>
                    <span class="chip chip--team-{{ $teamMod === 'neutral' ? 'neutral' : $teamMod }}">
                        {{ $teamName ?? 'Zona Neutral' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Columna derecha: datos y acciones --}}
        <div class="zone-side">
            <div class="panel zone-block">
                <h3 class="zone-block__title"><i class="fas fa-shield-alt" aria-hidden="true"></i> Defensa</h3>
                <ul class="zone-stats">
                    <li><span>Terreno</span><b>{{ $zone->defense }}</b></li>
                    <li><span>Jugadores (stats + inventos)</span><b>{{ $totalDefender }}</b></li>
                    <li><span>Bonus por tiempo</span><b>{{ $bonusTimeDefense }}</b></li>
                    <li class="zone-stats__total"><span>Total</span><b>{{ $totalDefense }}</b></li>
                </ul>
            </div>

            <div class="panel zone-block">
                <h3 class="zone-block__title"><i class="fas fa-gem" aria-hidden="true"></i> Recursos</h3>
                @if($zone->materials->count())
                    <div class="zone-res">
                        @foreach($zone->materials as $material)
                            <span class="zone-res__item">{{ $material->name }} <em>{{ $material->probability }}%</em></span>
                        @endforeach
                    </div>
                @else
                    <p class="zone-empty">Sin recursos conocidos.</p>
                @endif
            </div>

            <div class="panel zone-block">
                <h3 class="zone-block__title"><i class="fas fa-bolt" aria-hidden="true"></i> Acciones</h3>

                @if ($timeRemaining > 0)
                    <p id="timer" class="zone-timer">
                        <i class="fas fa-hourglass-half" aria-hidden="true"></i>
                        Acción en curso · <span id="timeRemaining">{{ $timeRemaining }}</span> s
                    </p>
                @endif

                <div class="zone-actions">
                    @if ($user->zone_id !== $zone->id)
                        <form action="{{ route('players.move', $zone->id) }}" method="POST" data-sfx="move">
                            @csrf
                            <button type="submit" class="btn-action btn-action--move" {{ $timeRemaining > 0 ? 'disabled' : '' }}><i class="fas fa-walking" aria-hidden="true"></i> Moverse</button>
                        </form>
                    @endif

                    @if ($user->zone_id === $zone->id && $zone->team_id === null)
                        <form action="{{ route('players.explore', $zone->id) }}" method="POST" data-sfx="explore">
                            @csrf
                            <button type="submit" class="btn-action btn-action--explore" {{ $timeRemaining > 0 ? 'disabled' : '' }}><i class="fas fa-compass" aria-hidden="true"></i> Explorar</button>
                        </form>
                    @endif

                    @if ($user->zone_id === $zone->id && $zone->team_id === $user->team_id)
                        <form action="{{ route('players.collect', $zone->id) }}" method="POST" data-sfx="collect">
                            @csrf
                            <button type="submit" class="btn-action btn-action--collect" {{ $timeRemaining > 0 ? 'disabled' : '' }}><i class="fas fa-hand-holding" aria-hidden="true"></i> Recolectar</button>
                        </form>
                        <form action="{{ route('players.invent', $zone->id) }}" method="POST" data-sfx="invent">
                            @csrf
                            <button type="submit" class="btn-action btn-action--invent" {{ $timeRemaining > 0 ? 'disabled' : '' }}><i class="fas fa-lightbulb" aria-hidden="true"></i> Inventar</button>
                        </form>
                        @if (($zone->regen_boost ?? 1) > 1)
                            <span class="btn-action btn-action--mine is-active"><i class="fas fa-hard-hat" aria-hidden="true"></i> Mina activa (x{{ $zone->regen_boost }})</span>
                        @elseif (!empty($mineRemaining) && $mineRemaining > 0)
                            <span class="btn-action btn-action--mine" id="mine-building"><i class="fas fa-hard-hat" aria-hidden="true"></i> Construyendo mina · <span id="mineRemaining">{{ $mineRemaining }}</span>s</span>
                        @elseif ($mineCanBuild)
                            <form action="{{ route('zones.buildMine', $zone->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-action btn-action--mine" title="Cuesta 10 metal + 15 madera"><i class="fas fa-hard-hat" aria-hidden="true"></i> Construir mina (regen x3)</button>
                            </form>
                        @else
                            <div class="mine-locked">
                                <span class="btn-action btn-action--mine is-disabled"><i class="fas fa-hard-hat" aria-hidden="true"></i> Construir mina</span>
                                <small class="mine-reqs"><i class="fas fa-lock" aria-hidden="true"></i> Te falta: {{ implode(' · ', $mineMissing) }}</small>
                            </div>
                        @endif

                        <form action="{{ route('zones.surrender', $zone->id) }}" method="POST"
                              onsubmit="return confirm('¿Rendir {{ $zone->name }}? Quedará neutral (no pasa al enemigo), perderás la mina y te replegarás a una zona propia.');">
                            @csrf
                            <button type="submit" class="btn-action btn-action--surrender" title="Abandonar la zona y replegarse"><i class="fas fa-flag" aria-hidden="true"></i> Rendir zona</button>
                        </form>
                    @endif

                    @if ($user->team_id !== null && $zone->team_id !== $user->team_id && $zone->team_id !== null && $zoneAdjacent)
                        <form action="{{ route('players.attack', $zone->id) }}" method="POST" data-sfx="attack">
                            @csrf
                            <button type="submit" class="btn-action btn-action--attack" {{ $timeRemaining > 0 ? 'disabled' : '' }}><i class="fas fa-khanda" aria-hidden="true"></i> Atacar</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let timeRemaining = parseInt(document.getElementById('timeRemaining')?.innerText || 0);
    if (timeRemaining > 0) {
        const timer = setInterval(() => {
            timeRemaining--;
            document.getElementById('timeRemaining').innerText = timeRemaining;
            if (timeRemaining <= 0) {
                clearInterval(timer);
                const t = document.getElementById('timer');
                t.classList.add('zone-timer--done');
                t.innerHTML = '<i class="fas fa-check" aria-hidden="true"></i> Acción completada';
                // recarga para reflejar el estado real del servidor (acción finalizada,
                // botones activos, propietario/defensa actualizados). Vale para cualquier acción.
                setTimeout(() => { window.location.reload(); }, 1200);
            }
        }, 1000);
    }

    // cuenta atrás de la mina (2º plano): al terminar, recarga para activarla
    let mineRemaining = parseInt(document.getElementById('mineRemaining')?.innerText || 0);
    if (mineRemaining > 0) {
        const mineTimer = setInterval(() => {
            mineRemaining--;
            document.getElementById('mineRemaining').innerText = mineRemaining;
            if (mineRemaining <= 0) {
                clearInterval(mineTimer);
                setTimeout(() => { window.location.reload(); }, 1200);
            }
        }, 1000);
    }
</script>
@endsection
