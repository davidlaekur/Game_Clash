@php $u = Auth::user(); @endphp
<nav class="epic-nav">
    {{-- Marca del juego --}}
    <a href="{{ route('zones.index') }}" class="epic-brand">
        <span class="epic-brand__crest">⚔️</span>
        <span class="epic-brand__text">World Of <span>Laraveland</span></span>
    </a>

    {{-- Navegación --}}
    <ul class="epic-menu">
        <li><a href="{{ route('zones.index') }}" class="{{ request()->routeIs('zones.*') ? 'is-active' : '' }}">🗺️ Mapa</a></li>
        <li><a href="{{ route('players.show', $u->id) }}" class="{{ request()->routeIs('players.*') ? 'is-active' : '' }}">🧙 Jugador</a></li>
        <li><a href="{{ route('teams.index') }}" class="{{ request()->routeIs('teams.*') ? 'is-active' : '' }}">🛡️ Equipo</a></li>
        <li><a href="{{ route('actions.index') }}" class="{{ request()->routeIs('actions.*') ? 'is-active' : '' }}">⏳ Acciones</a></li>
    </ul>

    {{-- HUD del jugador --}}
    <div class="epic-hud">
        <div class="epic-hud__player">
            <span class="epic-hud__name">{{ $u->name }}</span>
            <span class="epic-hud__meta">
                <span class="epic-hud__role">{{ ucfirst($u->role->name) }}</span>
                @php
                    $teamName = $u->team->name ?? null;
                    $teamMod = $teamName ? (str_contains(strtolower($teamName), 'mordor') ? 'mordor' : (str_contains(strtolower($teamName), 'laraveland') ? 'laraveland' : 'none')) : 'none';
                @endphp
                <span class="epic-hud__team epic-hud__team--{{ $teamMod }}">{{ $teamName ?? 'Sin equipo' }}</span>
            </span>
        </div>
        <a href="{{ route('logout') }}" class="epic-hud__logout" title="Cerrar sesión"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            ⏻
        </a>
    </div>
</nav>
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
