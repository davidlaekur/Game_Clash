@extends('layouts.app')

@section('title', 'Perfil de ' . $user->name)

@section('content')
<div class="profile-view">
    <a href="{{ route('zones.index') }}" class="btn-ghost profile-view__back">← Volver al mapa</a>

    <h1 class="profile-view__title">Perfil de {{ $user->name }}</h1>

    <div class="profile-grid">
        {{-- Tarjeta del jugador --}}
        <div class="panel panel--framed profile-card">
            <img src="{{ asset('images/avatar.png') }}" alt="Avatar" class="profile-card__avatar">
            <h2 class="profile-card__name">{{ $user->name }}</h2>
            <span class="chip chip--brass">{{ ucfirst($user->role->name) }}</span>
            @php $rank = $user->rank(); $ranks = config('ranks'); $next = $ranks[$rank['level'] + 1] ?? null; @endphp
            <span class="chip chip--rank"><i class="fas {{ $rank['icon'] }}" aria-hidden="true"></i> {{ $rank['name'] }}</span>

            <div class="profile-card__points">
                <div><span class="profile-stat__label">Puntos</span><b>{{ $totalPoints }}</b></div>
                <div><span class="profile-stat__label">Mérito</span><b>{{ (int) ($user->merit ?? 0) }}@if($next) <small class="profile-next">/ {{ $next['merit'] }} → {{ $next['name'] }}</small>@endif</b></div>
                <div><span class="profile-stat__label">Capacidad</span><b>{{ $totalCapacity }}</b></div>
            </div>

            <h3 class="profile-card__sub">Mejoras adquiridas</h3>
            <ul class="profile-stats">
                @foreach ($totalStats as $stat => $value)
                    <li><span>{{ ucfirst($stat) }}</span><b>{{ $value }}</b></li>
                @endforeach
            </ul>

            <a href="{{ route('players.edit', $user->id) }}" class="btn-epic profile-card__edit"><i class="fas fa-pen" aria-hidden="true"></i> Editar perfil</a>
        </div>

        {{-- Recompensas + inventario --}}
        <div class="profile-side">
            @if (count($earnedItems) > 0)
                <div class="panel profile-block">
                    <h3 class="profile-block__title"><i class="fas fa-trophy" aria-hidden="true"></i> Recompensas de aventuras</h3>
                    <div class="profile-rewards">
                        @foreach ($earnedItems as $item)
                            <div class="reward-card">
                                <img src="{{ asset('images/' . $item->image) }}" alt="{{ $item->name }}">
                                <strong>{{ $item->name }}</strong>
                                <p>{{ $item->description }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (count($rewards) > 0)
                <div class="panel profile-block">
                    <h3 class="profile-block__title"><i class="fas fa-medal" aria-hidden="true"></i> Premios de aventuras</h3>
                    <div class="profile-rewards">
                        @foreach ($rewards as $reward)
                            <div class="reward-card">
                                <img src="{{ asset('images/' . $reward->image) }}" alt="{{ $reward->name }}">
                                <strong>{{ $reward->name }}</strong>
                                <p>{{ $reward->description }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="panel profile-block profile-block--inv">
                <h3 class="profile-block__title"><i class="fas fa-box-open" aria-hidden="true"></i> Mi inventario</h3>
                @if ($userInventory)
                    <a href="{{ route('resources.show', $userInventory->id) }}" class="btn-epic">Ver inventario personal</a>
                @else
                    <p class="zone-empty">No tienes inventario personal.</p>
                    <a href="{{ route('resources.create') }}" class="btn-epic">Crear inventario</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
