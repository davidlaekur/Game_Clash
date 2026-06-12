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
    $mapZones = $zones->map(function ($zone) use ($myTeamId, $factionOf) {
        $ownership = 'neutral';
        if ($zone->team_id) {
            $ownership = $zone->team_id === $myTeamId ? 'mine' : 'enemy';
        }
        $ev = $zone->activeEvent();
        return [
            'id'        => $zone->id,
            'name'      => $zone->name,
            'landscape' => $zone->landscape,
            'defense'   => $zone->defense,
            'lat'       => (int) $zone->latitude,
            'lon'       => (int) $zone->longitude,
            'teamName'  => $zone->team->name ?? null,
            'ownership' => $ownership,
            'faction'   => $factionOf($zone->team->name ?? null),
            'current'   => Auth::user()->zone_id === $zone->id,
            'mine_active' => ($zone->regen_boost ?? 1) > 1,
            'event'     => $ev ? ['type' => $ev['type'], 'icon' => $ev['icon'], 'label' => $ev['label']] : null,
        ];
    })->values();

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
                    <a href="{{ route('adventure.intro') }}" class="btn-epic side-head__cta btn-spaceship" id="adventure-btn">
                        <i class="fas fa-jedi"></i>
                        Iniciar / Continuar Aventura
                    </a>
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
    // Audio countdown antes de ir a aventura
    document.getElementById('adventure-btn')?.addEventListener('click', function(e) {
        e.preventDefault();
        const url = this.href;
        
        const audio = new Audio('/audio/prepare-space.wav');
        audio.play();
        
        audio.onended = () => {
            window.location.href = url;
        };
        
        // Fallback si no funciona el audio
        setTimeout(() => {
            window.location.href = url;
        }, 7000);
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
</script>
@endsection
