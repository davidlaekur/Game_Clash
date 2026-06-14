@extends('layouts.app')

@section('title', 'Panel de Admin')

@section('content')
<div class="adminpanel">
    <header class="adminpanel__hero">
        <span class="adminpanel__crest"><i class="fas fa-gavel" aria-hidden="true"></i></span>
        <h1 class="adminpanel__title">Panel de mando</h1>
        <p class="adminpanel__sub">Eres el árbitro de Laraveland.</p>
    </header>

    @if (session('success'))<div class="admin-flash admin-flash--ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>@endif
    @if (session('error'))<div class="admin-flash admin-flash--err"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>@endif

    <div class="adminpanel__grid">
        {{-- Control de partida --}}
        <section class="rankcard">
            <h3 class="rankcard__title"><i class="fas fa-chess-king" aria-hidden="true"></i> Control de partida</h3>
            <p class="admin-state">Fase: <b>{{ ucfirst($state->phase ?? 'lobby') }}</b> · {{ $joinedCount }} jugador(es) apuntados</p>
            <div class="admin-btns">
                @if ($state->isLobby())
                    <form action="{{ route('game.start') }}" method="POST">@csrf<button class="btn-epic"><i class="fas fa-play"></i> Empezar ya</button></form>
                @elseif ($state->isActive())
                    <form action="{{ route('game.end') }}" method="POST" onsubmit="return confirm('¿Terminar la partida ahora? Se mostrará el podio.');">@csrf<button class="btn-ghost"><i class="fas fa-flag"></i> Terminar partida</button></form>
                @elseif ($state->isEnded())
                    <form action="{{ route('game.new') }}" method="POST" onsubmit="return confirm('¿Abrir nueva partida? Se reinicia todo (zonas, inventarios, méritos y gloria).');">@csrf<button class="btn-epic"><i class="fas fa-redo"></i> Nueva partida</button></form>
                @endif
            </div>
            <form action="{{ route('admin.settings') }}" method="POST" class="admin-form">
                @csrf
                <label>Mínimo de jugadores por bando
                    <input type="number" name="min_per_team" min="1" max="10" value="{{ $state->minPerTeam() }}">
                </label>
                <label>Frecuencia de eventos del mundo
                    <select name="event_level">
                        @foreach (['off' => 'Sin eventos', 'low' => 'Baja', 'normal' => 'Normal', 'high' => 'Alta'] as $k => $v)
                            <option value="{{ $k }}" {{ $state->eventLevel() === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="btn-ghost"><i class="fas fa-save"></i> Guardar ajustes</button>
            </form>
        </section>

        {{-- Eventos del mundo --}}
        <section class="rankcard">
            <h3 class="rankcard__title"><i class="fas fa-bolt" aria-hidden="true"></i> Desatar un evento</h3>
            <form action="{{ route('admin.event') }}" method="POST" class="admin-form">
                @csrf
                <label>Zona
                    <select name="zone_id">@foreach ($zones as $z)<option value="{{ $z->id }}">{{ $z->name }}</option>@endforeach</select>
                </label>
                <label>Evento
                    <select name="type">@foreach ($eventTypes as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach</select>
                </label>
                <button class="btn-epic"><i class="fas fa-bolt"></i> Desatar</button>
            </form>
        </section>

        {{-- Reasignar zona --}}
        <section class="rankcard">
            <h3 class="rankcard__title"><i class="fas fa-exchange-alt" aria-hidden="true"></i> Reasignar zona</h3>
            <form action="{{ route('admin.zone') }}" method="POST" class="admin-form">
                @csrf
                <label>Zona
                    <select name="zone_id">@foreach ($zones as $z)<option value="{{ $z->id }}">{{ $z->name }} — {{ optional($z->team)->name ?? 'neutral' }}</option>@endforeach</select>
                </label>
                <label>Asignar a
                    <select name="team_id"><option value="">Neutral</option>@foreach ($teams as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select>
                </label>
                <button class="btn-ghost"><i class="fas fa-flag-checkered"></i> Reasignar</button>
            </form>
        </section>
    </div>

    {{-- Vigilancia + intervención por jugador --}}
    <section class="rankcard rankcard--hof">
        <h3 class="rankcard__title"><i class="fas fa-binoculars" aria-hidden="true"></i> Jugadores ({{ $joinedCount }} en partida)</h3>
        <div class="admin-table">
            <div class="admin-trow admin-trow--head">
                <span>Jugador</span><span>Facción</span><span>Rol</span><span>Zona</span><span>Gloria/Mérito</span><span>Estado</span><span>Acciones</span>
            </div>
            @forelse ($players as $p)
                <div class="admin-trow">
                    <span class="admin-tname">{{ $p->name }}</span>
                    <span>{{ $p->team ?? '—' }}</span>
                    <span>{{ ucfirst($p->role ?? '—') }}</span>
                    <span>{{ $p->zone ?? '—' }}</span>
                    <span class="admin-tnum">⭐{{ $p->glory }} · 🪙{{ $p->merit }}</span>
                    <span class="admin-tstate">
                        @if ($p->joined)<span class="admin-badge admin-badge--in">En partida</span>@else<span class="admin-badge">Fuera</span>@endif
                        @if ($p->wounded)<span class="admin-badge admin-badge--hurt">Herido</span>@endif
                        <small>{{ $p->lastAt ? \Carbon\Carbon::parse($p->lastAt)->diffForHumans() : 'sin actividad' }}</small>
                    </span>
                    <span class="admin-tactions">
                        <form action="{{ route('admin.merit') }}" method="POST" class="admin-merit">
                            @csrf<input type="hidden" name="user_id" value="{{ $p->id }}">
                            <input type="number" name="amount" value="10" title="+ suma, − resta">
                            <button class="admin-mini" title="Ajustar méritos"><i class="fas fa-coins"></i></button>
                        </form>
                        <form action="{{ route('admin.heal') }}" method="POST">@csrf<input type="hidden" name="user_id" value="{{ $p->id }}"><button class="admin-mini" title="Curar herido"><i class="fas fa-heart"></i></button></form>
                        <form action="{{ route('admin.expel') }}" method="POST" onsubmit="return confirm('¿Expulsar a {{ $p->name }} de la partida?');">@csrf<input type="hidden" name="user_id" value="{{ $p->id }}"><button class="admin-mini admin-mini--danger" title="Expulsar"><i class="fas fa-user-slash"></i></button></form>
                    </span>
                </div>
            @empty
                <div class="rlist__empty">No hay jugadores.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
