@extends('layouts.app')

@section('title', 'Ranking')

@php
    $faction = function ($name) {
        $n = strtolower($name ?? '');
        return str_contains($n, 'laraveland') ? 'laraveland' : (str_contains($n, 'itaca') ? 'itaca' : 'neutral');
    };
    $medals = ['🥇', '🥈', '🥉'];
    $totalZones = isset($teams) ? $teams->sum('zones') : 0;
@endphp

@section('content')
<div class="rankpage">
    <header class="rankpage__hero">
        <span class="rankpage__crest"><i class="fas fa-trophy" aria-hidden="true"></i></span>
        <h1 class="rankpage__title">Salón de los Héroes</h1>
        <p class="rankpage__sub">La gloria de la partida en curso y la leyenda de los campeones.</p>
    </header>

    <div class="rankpage__grid">
        {{-- Jugadores por gloria (partida en curso) --}}
        <section class="rankcard">
            <h3 class="rankcard__title"><i class="fas fa-medal" aria-hidden="true"></i> Jugadores por gloria</h3>
            <ol class="rlist">
                @forelse ($players as $i => $p)
                    @php $rank = $p->rank(); $f = $faction($p->team->name ?? null); @endphp
                    <li class="rrow rrow--{{ $f }} rrow--p{{ $i + 1 }} {{ $p->id === auth()->id() ? 'is-me' : '' }}">
                        <span class="rrow__pos">{!! $i < 3 ? $medals[$i] : ($i + 1) !!}</span>
                        <span class="rrow__name">
                            {{ $p->name }}
                            <small>{{ $p->team->name ?? 'Sin facción' }}</small>
                        </span>
                        <span class="chip chip--rank"><i class="fas {{ $rank['icon'] }}" aria-hidden="true"></i> {{ $rank['name'] }}</span>
                        <b class="rrow__glory" title="Gloria (carrera)"><i class="fas fa-star" aria-hidden="true"></i> {{ $p->glory() }}</b>
                        <small class="rrow__wallet" title="Méritos disponibles"><i class="fas fa-coins" aria-hidden="true"></i> {{ (int) ($p->merit ?? 0) }}</small>
                    </li>
                @empty
                    <li class="rlist__empty"><i class="fas fa-hourglass-half" aria-hidden="true"></i> Nadie se ha unido a la partida todavía.</li>
                @endforelse
            </ol>
            <p class="rankcard__hint">La <b>gloria</b> son los méritos de carrera: nunca bajan, aunque gastes méritos en aventuras.</p>
        </section>

        {{-- Equipos por territorios --}}
        <section class="rankcard">
            <h3 class="rankcard__title"><i class="fas fa-flag" aria-hidden="true"></i> Equipos por territorios</h3>
            <ul class="rlist rlist--teams">
                @forelse ($teams as $t)
                    @php $f = $faction($t->name); $pct = $totalZones > 0 ? round($t->zones / $totalZones * 100) : 0; @endphp
                    <li class="trow trow--{{ $f }}">
                        <span class="trow__name"><i class="fas fa-shield-alt" aria-hidden="true"></i> {{ $t->name }}</span>
                        <span class="trow__bar"><span style="width: {{ $pct }}%"></span></span>
                        <b class="trow__count"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> {{ $t->zones }}</b>
                    </li>
                @empty
                    <li class="rlist__empty">Sin equipos.</li>
                @endforelse
            </ul>
            <p class="rankcard__hint">Se gana expulsando al rival (0 territorios) y dominando al menos la mitad del mapa.</p>
        </section>
    </div>

    {{-- Salón de la Fama: campeones de partidas anteriores --}}
    @if (!empty($hallOfFame) && $hallOfFame->isNotEmpty())
        <section class="rankcard rankcard--hof">
            <h3 class="rankcard__title"><i class="fas fa-crown" aria-hidden="true"></i> Salón de la Fama</h3>
            <div class="hofgrid">
                @foreach ($hallOfFame as $match)
                    <article class="hofcard">
                        <p class="hofcard__winner"><i class="fas fa-trophy" aria-hidden="true"></i> {{ $match->winner }}</p>
                        <ol class="hofpodium">
                            @foreach (($match->podium ?? []) as $pos => $champ)
                                <li class="hofpos hofpos--{{ $pos + 1 }}">
                                    <span class="hofpos__medal">{{ $medals[$pos] ?? '🏅' }}</span>
                                    <span class="hofpos__name">{{ $champ['name'] ?? '—' }}</span>
                                    <small class="hofpos__team">{{ $champ['team'] ?? 'Sin facción' }}</small>
                                    <b class="hofpos__glory"><i class="fas fa-star" aria-hidden="true"></i> {{ $champ['glory'] ?? 0 }}</b>
                                </li>
                            @endforeach
                        </ol>
                        @if ($match->ended_at)
                            <time class="hofcard__date">{{ \Carbon\Carbon::parse($match->ended_at)->format('d/m/Y · H:i') }}</time>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
