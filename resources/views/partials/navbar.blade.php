<nav class="navbar navbar-expand-lg navbar-dark py-3">
    <div class="container">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav py-2 gap-5">
                <li class="nav-item"><a class="nav-link fs-4" href="{{ route('zones.index') }}">Mapa</a></li>
                <li class="nav-item"><a class="nav-link fs-4" href="{{ route('players.show', Auth::user()->id) }}">Jugador</a></li>
                <li class="nav-item"><a class="nav-link fs-4" href="{{ route('teams.index') }}">Equipo</a></li>
                <li class="nav-item"><a class="nav-link fs-4" href="{{ route('actions.index') }}">Acciones</a></li>
            </ul>
            <div class="d-flex ms-auto align-items-center gap-4">

                <!-- Usuario logueado -->

                <span class="navbar-text fs-5 text-white">
                    <i class="fas fa-user"></i> {{ Auth::user()->name }}
                    <!-- Mostrar rol y equipo con separador -->
                    <span class=" text-secondary">
                        <span class="ms-2 role-icon {{ strtolower(Auth::user()->role->name) }}">{{ ucfirst(Auth::user()->role->name) }}</span>
                        <span class="separator">|</span>
                        <span class="team-name {{ strtolower(str_replace(' ', '-', Auth::user()->team->name ?? 'sin-equipo')) }}">
                            {{ Auth::user()->team->name ?? 'Sin equipo' }}
                        </span>
                    </span>
                </span>

         <!--        <a class="nav-link fs-5 text-white" href="{{ route('players.show', Auth::user()->id) }}">
                    <i class="fas fa-user-circle"></i> Perfil
                </a>
                
 -->
                <!-- Cerrar sesión -->
                <a class="nav-link fs-5 ms-5 text-white" href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </div>
</nav>
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>