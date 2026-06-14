@extends('layouts.app')

@section('title', 'Ranking')

@section('content')
<div class="ranking-view">
    <h1 class="action-view__title"><i class="fas fa-trophy" aria-hidden="true"></i> Ranking</h1>

    <div class="ranking-grid">
        {{-- Jugadores por gloria (méritos de carrera, no bajan al gastar) --}}
        <div class="panel ranking-block">
            <h3 class="ranking-block__title"><i class="fas fa-medal" aria-hidden="true"></i> Jugadores por gloria</h3>
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
                        <b class="ranking-merit" title="Gloria (carrera)"><i class="fas fa-star" aria-hidden="true"></i> {{ $p->glory() }}</b>
                        <small class="ranking-wallet" title="Méritos disponibles (monedero)"><i class="fas fa-coins" aria-hidden="true"></i> {{ (int) ($p->merit ?? 0) }}</small>
                    </li>
                @empty
                    <li class="ranking-empty">Aún no hay gloria. ¡A combatir!</li>
                @endforelse
            </ol>
            <p class="ranking-hint">La <b>gloria</b> son los méritos de carrera: nunca bajan, aunque gastes méritos en aventuras.</p>
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
            <p class="ranking-hint">Se gana la partida expulsando al rival (0 territorios) y dominando al menos la mitad del mapa.</p>
        </div>
    </div>

    {{-- Salón de la Fama: campeones de partidas anteriores --}}
    @if (!empty($hallOfFame) && $hallOfFame->isNotEmpty())
        <div class="panel ranking-block hall-of-fame">
            <h3 class="ranking-block__title"><i class="fas fa-crown" aria-hidden="true"></i> Salón de la Fama</h3>
            @foreach ($hallOfFame as $match)
                <div class="hof-match">
                    <p class="hof-winner"><i class="fas fa-trophy" aria-hidden="true"></i> {{ $match->winner }}</p>
                    <ol class="hof-podium">
                        @foreach (($match->podium ?? []) as $pos => $champ)
                            <li class="hof-pos hof-pos--{{ $pos + 1 }}">
                                <span class="hof-medal">{{ ['🥇','🥈','🥉'][$pos] ?? '🏅' }}</span>
                                <span class="hof-name">{{ $champ['name'] ?? '—' }}</span>
                                <small class="hof-team">{{ $champ['team'] ?? 'Sin facción' }}</small>
                                <b class="hof-glory"><i class="fas fa-star" aria-hidden="true"></i> {{ $champ['glory'] ?? 0 }}</b>
                            </li>
                        @endforeach
                    </ol>
                    @if ($match->ended_at)
                        <small class="hof-date">{{ \Carbon\Carbon::parse($match->ended_at)->format('d/m/Y H:i') }}</small>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
