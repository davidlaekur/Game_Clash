@php $u = Auth::user(); @endphp
<nav class="epic-nav">
    {{-- Marca del juego --}}
    <a href="{{ route('zones.index') }}" class="epic-brand">
        <span class="epic-brand__crest"><i class="fas fa-jedi" aria-hidden="true"></i></span>
        <span class="epic-brand__text">World Of <span>Laraveland</span></span>
    </a>

    {{-- Navegación --}}
    <ul class="epic-menu">
        <li><a href="{{ route('zones.index') }}" class="{{ request()->routeIs('zones.*') ? 'is-active' : '' }}"><i class="epic-menu__ico fas fa-map-marked-alt" aria-hidden="true"></i> <span class="epic-menu__txt">Mapa</span></a></li>
        <li><a href="{{ route('players.show', $u->id) }}" class="{{ request()->routeIs('players.*') ? 'is-active' : '' }}"><i class="epic-menu__ico fas fa-user-shield" aria-hidden="true"></i> <span class="epic-menu__txt">Jugador</span></a></li>
        <li><a href="{{ route('teams.index') }}" class="{{ request()->routeIs('teams.*') ? 'is-active' : '' }}"><i class="epic-menu__ico fas fa-shield-alt" aria-hidden="true"></i> <span class="epic-menu__txt">Equipo</span></a></li>
        <li><a href="{{ route('actions.index') }}" class="{{ request()->routeIs('actions.*') ? 'is-active' : '' }}"><i class="epic-menu__ico fas fa-hourglass-half" aria-hidden="true"></i> <span class="epic-menu__txt">Acciones</span></a></li>
        <li><a href="{{ route('ranking') }}" class="{{ request()->routeIs('ranking') ? 'is-active' : '' }}"><i class="epic-menu__ico fas fa-trophy" aria-hidden="true"></i> <span class="epic-menu__txt">Ranking</span></a></li>
        @if (optional($u->role)->name === 'Admin')
            <li><a href="{{ route('admin.panel') }}" class="{{ request()->routeIs('admin.*') ? 'is-active' : '' }}"><i class="epic-menu__ico fas fa-gavel" aria-hidden="true"></i> <span class="epic-menu__txt">Admin</span></a></li>
        @endif
    </ul>

    {{-- HUD del jugador --}}
    <div class="epic-hud">
        <div class="epic-hud__player">
            <span class="epic-hud__name">{{ $u->name }}</span>
            <span class="epic-hud__meta">
                <span class="epic-hud__role">{{ optional($u->role)->name ? ucfirst($u->role->name) : 'Sin rol' }}</span>
                @php
                    $teamName = $u->team->name ?? null;
                    $teamMod = $teamName ? (str_contains(strtolower($teamName), 'laraveland') ? 'laraveland' : (str_contains(strtolower($teamName), 'itaca') ? 'itaca' : 'none')) : 'none';
                @endphp
                <span class="epic-hud__team epic-hud__team--{{ $teamMod }}">{{ $teamName ?? 'Sin equipo' }}</span>
            </span>
        </div>
        <a href="{{ route('logout') }}" class="epic-hud__logout" title="Cerrar sesión"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fas fa-power-off" aria-hidden="true"></i> 
        </a>
    </div>
</nav>
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
