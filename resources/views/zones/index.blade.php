@extends('layouts.app')

@section('title', 'Mapa de Campaña')

@section('content')
@php
    // Datos de zonas para el componente WarMap.
    $myTeamId = Auth::user()->team_id;
    $factionOf = function ($name) {
        $n = strtolower($name ?? '');
        if (str_contains($n, 'laraveland')) return 'laraveland';
        if (str_contains($n, 'itaca')) return 'itaca';
        return 'neutral';
    };
    // $mapZones lo construye ZoneController (mismo shape) para reutilizarlo en el
    // endpoint de refresco en vivo (map.state).
    $worldMap = asset('images/world/mapa-mundo.png');

    $total = max(1, $zones->count());
    $mine = $zones->filter(fn($z) => $z->team_id && $z->team_id === $myTeamId)->count();
    $enemy = $zones->filter(fn($z) => $z->team_id && $z->team_id !== $myTeamId)->count();
    $free = $total - $mine - $enemy;
    $myTeamName = Auth::user()->team->name ?? 'Sin facción';
    $myTeamMod = $factionOf($myTeamName);

    $warmapProps = [
        'zones'       => $mapZones,
        'showUrlBase' => url('/zones'),
        'worldMap'    => $worldMap,
        'worldW'      => 1408,
        'worldH'      => 768,
        'adjacency'   => config('zone_adjacency'),
        'pollUrl'     => route('map.state'), // refresco en vivo sin recargar (no corta la música)
    ];

    // Ubicación actual del jugador
    $me = Auth::user();
    $currentZone = $zones->firstWhere('id', $me->zone_id);

    // Acciones en curso del jugador (finish=false) con tiempo restante
    $activeActions = \App\Models\Action::with('type')
        ->where('user_id', $me->id)
        ->where('finish', false)
        ->latest()
        ->get()
        ->map(function ($a) use ($zones) {
            $ends = $a->created_at->copy()->addSeconds($a->duration);
            $remaining = max(0, now()->diffInSeconds($ends, false));
            $zoneName = null;
            if ($a->actionable_type === \App\Models\Zone::class) {
                $zoneName = optional($zones->firstWhere('id', $a->actionable_id))->name;
            }
            return [
                'type'      => $a->type->name ?? 'acción',
                'zone'      => $zoneName,
                'remaining' => $remaining,
            ];
        });

    $actionLabels = [
        'move' => 'Moviéndote', 'explore' => 'Explorando',
        'collect' => 'Recolectando', 'invent' => 'Forjando',
        'attack' => 'Atacando',
    ];
@endphp

<div class="warmap-page">

    @if (!empty($victory))
        <div class="victory-podium">
            <div class="victory-podium__head">
                <i class="fas fa-crown" aria-hidden="true"></i>
                <span>{{ $victory }}</span>
            </div>
            @if (!empty($podium))
                <ol class="podium">
                    @foreach ($podium as $pos => $champ)
                        <li class="podium__pos podium__pos--{{ $pos + 1 }}">
                            <span class="podium__medal">{{ ['🥇','🥈','🥉'][$pos] ?? '🏅' }}</span>
                            <span class="podium__name">{{ $champ['name'] ?? '—' }}</span>
                            <small class="podium__team">{{ $champ['team'] ?? 'Sin facción' }}</small>
                            <b class="podium__glory"><i class="fas fa-star" aria-hidden="true"></i> {{ $champ['glory'] ?? 0 }}</b>
                        </li>
                    @endforeach
                </ol>
            @endif
            @if (auth()->user()->role->name === 'Admin')
                <form action="{{ route('game.new') }}" method="POST" class="victory-podium__new"
                      onsubmit="return confirm('¿Iniciar una nueva partida? Se archivará el podio en el Salón de la Fama y se reiniciará todo (zonas, inventarios, méritos y gloria).');">
                    @csrf
                    <button type="submit" class="btn-epic"><i class="fas fa-redo" aria-hidden="true"></i> Iniciar nueva partida</button>
                </form>
            @else
                <p class="victory-podium__wait">La partida ha terminado. El admin iniciará una nueva.</p>
            @endif
        </div>
    @endif

    {{-- Mapa a ancho completo + panel lateral con toda la info --}}
    <div class="warmap-layout">
        <div class="warmap-stage-wrap">
            <div data-react-island="WarMap" data-props='@json($warmapProps)'></div>
        </div>

        <aside class="warmap-side">
            {{-- Cabecera: título + acción principal + música --}}
            <div class="panel side-block side-head">
                <div class="side-head__top">
                    <h1 class="side-head__title">Mapa de Campaña</h1>
                    <button type="button" id="music-toggle" class="hud__music" aria-pressed="false" title="Música">
                        <span class="hud__music-on"><i class="fas fa-volume-up" aria-hidden="true"></i> </span><span class="hud__music-off"><i class="fas fa-volume-mute" aria-hidden="true"></i> </span>
                    </button>
                </div>
                <p class="side-head__sub">El Reino de Laraveland · {{ $zones->count() }} territorios en disputa</p>
                @if(auth()->user()->role->name !== 'Admin')
                    @php $advRank = auth()->user()->rankLevel(); $advMerit = (int) (auth()->user()->merit ?? 0); @endphp
                    @if (($hasAdventure ?? false) || ($advRank >= 2 && $advMerit >= 100))
                        <a href="{{ route('adventure.intro') }}" class="btn-epic side-head__cta btn-spaceship" id="adventure-btn">
                            <i class="fas fa-jedi"></i>
                            Iniciar / Continuar Aventura
                        </a>
                    @else
                        <span class="btn-epic side-head__cta btn-spaceship is-locked" title="Requiere rango Veterano y 100 méritos">
                            <i class="fas fa-lock"></i>
                            Aventura bloqueada
                        </span>
                        @if ($advRank < 2)
                            <p class="adventure-locked-note"><i class="fas fa-medal" aria-hidden="true"></i> Necesitas rango <b>Veterano</b> · {{ $advMerit }}/100 méritos</p>
                        @else
                            <p class="adventure-locked-note"><i class="fas fa-coins" aria-hidden="true"></i> Necesitas <b>100 méritos</b> para partir · tienes {{ $advMerit }}</p>
                        @endif
                    @endif
                @else
                    <form action="{{ route('import.zones') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-epic side-head__cta"><i class="fas fa-plus" aria-hidden="true"></i> Importar Territorios</button>
                    </form>
                @endif
            </div>

            {{-- Facción + reparto de territorios --}}
            <div class="panel side-block">
                <div class="side-faction side-faction--{{ $myTeamMod }}">
                    <span class="side-faction__icon"><i class="fas fa-shield-alt" aria-hidden="true"></i> </span>
                    <div>
                        <span class="side-block__label">Tu facción</span>
                        <span class="side-faction__name">{{ $myTeamName }}</span>
                    </div>
                </div>
                <div class="hud__bar" title="Tuyas {{ $mine }} · Rivales {{ $enemy }} · Neutrales {{ $free }}">
                    <span class="hud__bar-seg hud__bar-seg--mine"  style="width: {{ $mine / $total * 100 }}%"></span>
                    <span class="hud__bar-seg hud__bar-seg--enemy" style="width: {{ $enemy / $total * 100 }}%"></span>
                    <span class="hud__bar-seg hud__bar-seg--free"  style="width: {{ $free / $total * 100 }}%"></span>
                </div>
                <div class="hud__counts">
                    <span class="hud__count hud__count--mine">⬤ {{ $mine }} tuyas</span>
                    <span class="hud__count hud__count--enemy">⬤ {{ $enemy }} rivales</span>
                    <span class="hud__count hud__count--free">⬤ {{ $free }} libres</span>
                </div>
            </div>

            {{-- Ubicación actual --}}
            <div class="panel side-block">
                <h3 class="side-block__title"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> Tu ubicación</h3>
                @if ($currentZone)
                    <p class="side-loc">{{ $currentZone->name }}</p>
                    <p class="side-loc__sub">{{ ucfirst($currentZone->landscape) }}</p>
                    <a href="{{ route('zones.show', $currentZone->id) }}" class="btn-ghost side-block__btn">Ir a la zona</a>
                @else
                    <p class="side-empty">No estás en ninguna zona.</p>
                @endif

                @if ($me->isWounded())
                    <div class="wounded-note">
                        <i class="fas fa-heart-broken" aria-hidden="true"></i>
                        <span>Estás <b>herido</b> (−20% en combate) · se cura en {{ max(1, (int) ceil(now()->diffInSeconds($me->wounded_until, false) / 60)) }} min</span>
                    </div>
                @endif
            </div>

            {{-- Acciones en marcha --}}
            <div class="panel side-block">
                <h3 class="side-block__title"><i class="fas fa-hourglass-half" aria-hidden="true"></i> Acciones en marcha</h3>
                @forelse ($activeActions as $act)
                    <div class="side-action" data-remaining="{{ $act['remaining'] }}">
                        <div class="side-action__head">
                            <span class="side-action__type">{{ $actionLabels[$act['type']] ?? ucfirst($act['type']) }}</span>
                            @if ($act['remaining'] > 0)
                                <span class="side-action__time">{{ gmdate('i:s', $act['remaining']) }}</span>
                            @else
                                <span class="side-action__done">✓ Lista</span>
                            @endif
                        </div>
                        @if ($act['zone'])
                            <span class="side-action__zone">en {{ $act['zone'] }}</span>
                        @endif
                    </div>
                @empty
                    <p class="side-empty">Sin acciones en curso.</p>
                @endforelse
            </div>

            {{-- Qué ocurre: actividad del equipo y del mundo --}}
            <div class="panel side-block">
                <h3 class="side-block__title"><i class="fas fa-satellite-dish" aria-hidden="true"></i> ¿Qué ocurre?</h3>
                @forelse ($feed as $item)
                    <div class="side-feed side-feed--{{ $item['kind'] }}">
                        <i class="fas {{ $item['icon'] }}" aria-hidden="true"></i>
                        <span>{{ $item['text'] }}</span>
                    </div>
                @empty
                    <p class="side-empty">Todo tranquilo en el reino…</p>
                @endforelse
            </div>
        </aside>
    </div>

</div>

<script>
    // Audio countdown antes de ir a aventura (respeta el silencio del juego)
    document.getElementById('adventure-btn')?.addEventListener('click', function(e) {
        e.preventDefault();
        const url = this.href;
        const soundOn = localStorage.getItem('col_audio_on') === '1';

        if (!soundOn) {
            window.location.href = url; // silenciado: sin cuenta atrás sonora, entra ya
            return;
        }

        const audio = new Audio('/audio/prepare-space.wav');
        audio.play().catch(() => { window.location.href = url; });
        audio.onended = () => { window.location.href = url; };
        // Fallback si el audio no termina
        setTimeout(() => { window.location.href = url; }, 7000);
    });
    
    // cuenta atrás en vivo de las acciones del panel lateral
    document.querySelectorAll('.side-action[data-remaining]').forEach(function (el) {
        let s = parseInt(el.dataset.remaining || '0', 10);
        const time = el.querySelector('.side-action__time');
        if (s <= 0 || !time) return;
        const t = setInterval(function () {
            s--;
            if (s <= 0) {
                clearInterval(t);
                time.outerHTML = '<span class="side-action__done">✓ Lista</span>';
                return;
            }
            const m = String(Math.floor(s / 60)).padStart(2, '0');
            const ss = String(s % 60).padStart(2, '0');
            time.textContent = m + ':' + ss;
        }, 1000);
    });

    // refresco EN VIVO del HUD lateral sin recargar la página (la música no se
    // corta). El mapa lo refresca WarMap por su cuenta con el mismo endpoint.
    (function () {
        const pollUrl = @json(route('map.state'));
        setInterval(function () {
            if (document.hidden) return;
            fetch(pollUrl, { headers: { Accept: 'application/json' } })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (d) {
                    if (!d || !d.counts) return;
                    const c = d.counts, t = c.total || 1;
                    const setW = function (sel, n) { const e = document.querySelector(sel); if (e) e.style.width = (n / t * 100) + '%'; };
                    const setT = function (sel, txt) { const e = document.querySelector(sel); if (e) e.textContent = txt; };
                    setW('.hud__bar-seg--mine', c.mine);
                    setW('.hud__bar-seg--enemy', c.enemy);
                    setW('.hud__bar-seg--free', c.free);
                    setT('.hud__count--mine', '⬤ ' + c.mine + ' tuyas');
                    setT('.hud__count--enemy', '⬤ ' + c.enemy + ' rivales');
                    setT('.hud__count--free', '⬤ ' + c.free + ' libres');
                })
                .catch(function () {});
        }, 20000);
    })();
</script>
@endsection
