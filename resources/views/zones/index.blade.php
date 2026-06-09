@extends('layouts.app')

@section('title', 'Mapa de Campaña')

@section('content')
@php
    // Datos de zonas para el componente WarMap.
    $myTeamId = Auth::user()->team_id;
    $mapZones = $zones->map(function ($zone) use ($myTeamId) {
        $ownership = 'neutral';
        if ($zone->team_id) {
            $ownership = $zone->team_id === $myTeamId ? 'mine' : 'enemy';
        }
        return [
            'id'        => $zone->id,
            'name'      => $zone->name,
            'landscape' => $zone->landscape,
            'defense'   => $zone->defense,
            'lat'       => (int) $zone->latitude,
            'lon'       => (int) $zone->longitude,
            'teamName'  => $zone->team->name ?? null,
            'ownership' => $ownership,
            'current'   => Auth::user()->zone_id === $zone->id,
        ];
    })->values();

    $worldMap = asset('images/world/mapa-mundo.png');

    $total = max(1, $zones->count());
    $mine = $zones->filter(fn($z) => $z->team_id && $z->team_id === $myTeamId)->count();
    $enemy = $zones->filter(fn($z) => $z->team_id && $z->team_id !== $myTeamId)->count();
    $free = $total - $mine - $enemy;
    $myTeamName = Auth::user()->team->name ?? 'Sin facción';
    $myTeamMod = str_contains(strtolower($myTeamName), 'mordor') ? 'mordor'
        : (str_contains(strtolower($myTeamName), 'laraveland') ? 'laraveland' : 'neutral');

    $warmapProps = [
        'zones'       => $mapZones,
        'showUrlBase' => url('/zones'),
        'worldMap'    => $worldMap,
        'worldW'      => 1408,
        'worldH'      => 768,
    ];
@endphp

<div class="warmap-page">

    @if (session('success'))
        <div class="container"><div class="alert alert-success">{{ session('success') }}</div></div>
    @endif
    @if (session('warning'))
        <div class="container"><div class="alert alert-warning">{{ session('warning') }}</div></div>
    @endif
    @if (session('error'))
        <div class="container"><div class="alert alert-danger">{{ session('error') }}</div></div>
    @endif

    <div class="warmap-head">
        <div>
            <h1 class="warmap-title">Mapa de Campaña</h1>
            <p class="warmap-subtitle">El Reino de Laraveland · {{ $zones->count() }} territorios en disputa</p>
        </div>
        @if(auth()->user()->role->name !== 'Admin')
            <a href="{{ route('adventure.intro') }}" class="btn-epic">⚔ Iniciar / Continuar Aventura</a>
        @else
            <form action="{{ route('import.zones') }}" method="POST">
                @csrf
                <button type="submit" class="btn-epic">➕ Importar Territorios</button>
            </form>
        @endif
    </div>

    {{-- HUD: facción, reparto de territorios y control de música --}}
    <div class="warmap-wide hud">
        <div class="hud__faction hud__faction--{{ $myTeamMod }}">
            <span class="hud__faction-icon">🛡️</span>
            <div>
                <span class="hud__label">Tu facción</span>
                <span class="hud__faction-name">{{ $myTeamName }}</span>
            </div>
        </div>

        <div class="hud__territory">
            <span class="hud__label">Territorios · {{ $total }}</span>
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

        <button type="button" id="music-toggle" class="hud__music" aria-pressed="false" title="Música">
            <span class="hud__music-on">🔊</span><span class="hud__music-off">🔇</span>
        </button>
    </div>

    {{-- Mapa interactivo (isla React: mapa-mundo de fondo + marcadores de zona) --}}
    <div class="warmap-wide">
        <div data-react-island="WarMap" data-props='@json($warmapProps)'></div>
    </div>

    {{-- Leyenda --}}
    <div class="warmap-legend">
        <span><i class="legend-dot chip--neutral"></i> Neutral</span>
        <span><i class="legend-dot chip--team-laraveland"></i> Guardians of Laraveland</span>
        <span><i class="legend-dot chip--team-mordor"></i> Legion of Mordor</span>
        <span>🛡 = Defensa del terreno · ★ = Tu posición</span>
    </div>

</div>
@endsection
