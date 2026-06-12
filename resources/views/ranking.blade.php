@extends('layouts.app')

@section('title', 'Ranking')

@section('content')
<div class="ranking-view">
    <h1 class="action-view__title"><i class="fas fa-trophy" aria-hidden="true"></i> Ranking</h1>

    <div class="ranking-grid">
        {{-- Jugadores por mérito --}}
        <div class="panel ranking-block">
            <h3 class="ranking-block__title"><i class="fas fa-medal" aria-hidden="true"></i> Jugadores por mérito</h3>
            <ol class="ranking-list">
                @forelse ($players as $i => $p)
                    @php $rank = $p->rank(); @endphp
                    <li class="ranking-row {{ $p->id === auth()->id() ? 'is-me' : '' }}">
                        <span class="ranking-pos">{{ $i + 1 }}</span>
                        <span class="ranking-name">
                            {{ $p->name }}
                            <small class="ranking-team">{{ $p->team->name ?? 'Sin facción' }}</small>
                        </span>
                        <span class="chip chip--rank"><i class="fas {{ $rank['icon'] }}" aria-hidden="true"></i> {{ $rank['name'] }}</span>
                        <b class="ranking-merit">{{ (int) ($p->merit ?? 0) }}</b>
                    </li>
                @empty
                    <li class="ranking-empty">Aún no hay méritos. ¡A combatir!</li>
                @endforelse
            </ol>
        </div>

        {{-- Equipos por territorios --}}
        <div class="panel ranking-block">
            <h3 class="ranking-block__title"><i class="fas fa-flag" aria-hidden="true"></i> Equipos por territorios</h3>
            <ol class="ranking-list">
                @forelse ($teams as $i => $t)
                    <li class="ranking-row">
                        <span class="ranking-pos">{{ $i + 1 }}</span>
                        <span class="ranking-name">{{ $t->name }}</span>
                        <b class="ranking-merit"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> {{ $t->zones }}</b>
                    </li>
                @empty
                    <li class="ranking-empty">Sin equipos.</li>
                @endforelse
            </ol>
            <p class="ranking-hint">El primer equipo en controlar 9 territorios gana la partida.</p>
        </div>
    </div>
</div>
@endsection
