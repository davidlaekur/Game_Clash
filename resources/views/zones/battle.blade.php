@extends('layouts.app')

@section('title', 'Batalla en ' . $zone->name)

@section('music', 'batalla')

@section('content')
<div class="battle-view">
    <a href="{{ route('zones.show', $zone->id) }}" class="btn-ghost battle-view__back">← Volver a la Zona</a>

    <h1 class="battle-view__title"><i class="fas fa-khanda" aria-hidden="true"></i> Batalla en {{ $zone->name }}</h1>

    @if ($timeRemaining > 0)
        <p id="battle-timer" class="action-timer battle-view__timer">
            La batalla sigue en curso · <span id="timeRemaining">{{ $timeRemaining }}</span> s
        </p>
    @endif

    {{-- Bandos --}}
    <div class="battle-sides">
        <div class="panel battle-side battle-side--atk">
            <h3 class="battle-side__title"><i class="fas fa-khanda" aria-hidden="true"></i> Atacantes</h3>
            @forelse ($attackers as $attacker)
                <div class="battle-unit">
                    <span class="battle-unit__name">{{ $attacker->name }}</span>
                    <div class="battle-unit__stats">
                        <span class="stat stat--atk">ATK {{ $attacker->attackStats['ataque'] ?? 0 }}</span>
                        <span class="stat stat--atk">Inventos {{ $attacker->attackPoints ?? 0 }}</span>
                    </div>
                </div>
            @empty
                <p class="battle-empty">Sin atacantes.</p>
            @endforelse
        </div>

        <div class="panel battle-side battle-side--def">
            <h3 class="battle-side__title"><i class="fas fa-shield-alt" aria-hidden="true"></i> Defensores</h3>
            @forelse ($defenders as $defender)
                <div class="battle-unit">
                    <span class="battle-unit__name">{{ $defender->name }}</span>
                    <div class="battle-unit__stats">
                        <span class="stat stat--def">DEF {{ $defender->defenseStats['defensa'] ?? 0 }}</span>
                        <span class="stat stat--def">Salud {{ $defender->defenseStats['salud'] ?? 0 }}</span>
                        <span class="stat stat--def">Inventos {{ $defender->defensePoints ?? 0 }}</span>
                    </div>
                </div>
            @empty
                <p class="battle-empty">Sin defensores.</p>
            @endforelse
        </div>
    </div>

    {{-- Balanza ataque vs defensa --}}
    <div class="panel battle-block">
        <h3 class="battle-block__title"><i class="fas fa-thumbtack" aria-hidden="true"></i> Estado del combate</h3>
        <div class="battle-bar">
            <div class="battle-bar__atk" style="width: {{ min(100, ($attackPoints / max(1, $totalDefense + $attackPoints)) * 100) }}%">
                <i class="fas fa-khanda" aria-hidden="true"></i> {{ $attackPoints }}
            </div>
            <div class="battle-bar__def" style="width: {{ min(100, ($totalDefense / max(1, $totalDefense + $attackPoints)) * 100) }}%">
                <i class="fas fa-shield-alt" aria-hidden="true"></i> {{ $totalDefense }}
            </div>
        </div>
    </div>

    {{-- Desglose --}}
    <div class="panel battle-block">
        <h3 class="battle-block__title"><i class="fas fa-scroll" aria-hidden="true"></i> Desglose</h3>
        <ul class="battle-breakdown">
            <li><span><i class="fas fa-shield-alt" aria-hidden="true"></i> Defensa base de la zona</span><b>{{ $zone->defense }}</b></li>
            <li><span><i class="fas fa-shield-alt" aria-hidden="true"></i> Defensa por stats</span><b>{{ $playerDefense }}</b></li>
            <li><span><i class="fas fa-shield-alt" aria-hidden="true"></i> Defensa por inventos</span><b>{{ $totalDefensePoints }}</b></li>
            <li><span><i class="fas fa-hourglass-half" aria-hidden="true"></i> Bonus por tiempo</span><b>{{ $bonusTimeDefense }}</b></li>
            <li><span><i class="fas fa-fire" aria-hidden="true"></i> Ataque base</span><b>{{ $attackPoints }}</b></li>
            <li><span><i class="fas fa-fire" aria-hidden="true"></i> Ataque por inventos</span><b>{{ $totalAttackPoints }}</b></li>
            <li><span><i class="fas fa-dice" aria-hidden="true"></i> Factor de aleatoriedad</span><b>70% – 130%</b></li>
        </ul>
    </div>

    <div id="battle-result" class="battle-result {{ $attackPoints > $totalDefense ? 'battle-result--won' : 'battle-result--lost' }}" style="display:none;">
        {{ $attackResult }}
    </div>

    <div class="battle-view__stage">
        <img src="{{ asset('images/animation/combat2.gif') }}" alt="Combate" class="action-view__anim">
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let timeRemaining = parseInt(document.getElementById('timeRemaining')?.innerText || 0);
        const result = document.getElementById('battle-result');
        const showResult = () => { if (result) result.style.display = "block"; };
        if (timeRemaining > 0) {
            const timer = setInterval(() => {
                timeRemaining--;
                document.getElementById('timeRemaining').innerText = timeRemaining;
                if (timeRemaining <= 0) {
                    clearInterval(timer);
                    const t = document.getElementById('battle-timer');
                    if (t) { t.classList.add('action-timer--done'); t.innerText = "¡La batalla ha terminado!"; }
                    showResult();
                }
            }, 1000);
        } else {
            showResult();
        }
    });
</script>
@endsection
