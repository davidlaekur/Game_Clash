@extends('layouts.app')

@section('title', 'Mi Equipo')

@section('content')
@php
    $myTeam = auth()->user()->team;
    $teamMod = $myTeam
        ? (str_contains(strtolower($myTeam->name), 'mordor') ? 'mordor' : (str_contains(strtolower($myTeam->name), 'laraveland') ? 'laraveland' : 'neutral'))
        : 'neutral';
@endphp

<div class="team-view">
    <a href="{{ route('zones.index') }}" class="btn-ghost team-view__back">← Volver al mapa</a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <h1 class="team-view__title">Mi Equipo</h1>

    {{-- Inventarios --}}
    <div class="team-inventories">
        <div class="panel team-inv">
            <h3 class="team-inv__title">🎒 Mi inventario</h3>
            @if ($userInventory)
                <a href="{{ route('resources.show', $userInventory->id) }}" class="btn-epic">Ver inventario personal</a>
            @else
                <p class="zone-empty">No tienes inventario personal.</p>
                <a href="{{ route('resources.create') }}" class="btn-epic">Crear inventario</a>
            @endif
        </div>
        <div class="panel team-inv">
            <h3 class="team-inv__title">🏰 Inventario del equipo</h3>
            @if ($teamInventory)
                <a href="{{ route('resources.show', $teamInventory->id) }}" class="btn-epic">Ver inventario del equipo</a>
            @else
                <p class="zone-empty">No hay inventario de equipo.</p>
                <a href="{{ route('resources.create') }}" class="btn-epic">Crear inventario</a>
            @endif
        </div>
    </div>

    @if ($myTeam)
        {{-- Estandarte del equipo --}}
        <div class="panel panel--framed team-banner team-banner--{{ $teamMod }}">
            <img src="{{ asset($myTeam->image) }}" alt="Bandera de {{ $myTeam->name }}" class="team-banner__flag">
            <h2 class="team-banner__name">{{ $myTeam->name }}</h2>
            <p class="team-banner__desc">Eres parte de este clan. Gestiona a sus miembros e inventarios.</p>
            <div class="team-banner__actions">
                <a href="{{ route('actions.index', $myTeam->id) }}" class="btn-ghost">⏳ Acciones del equipo</a>
                <a href="{{ route('teams.transfer', $myTeam->id) }}" class="btn-ghost">🔄 Transferir inventario</a>
            </div>
        </div>

        {{-- Miembros --}}
        <h2 class="team-view__sub">Miembros del Equipo</h2>
        <div class="team-members">
            @foreach ($teamMembers as $member)
                <div class="panel member-card">
                    <img src="{{ asset('images/avatar.png') }}" alt="Avatar" class="member-card__avatar">
                    <h3 class="member-card__name">{{ $member->name }}</h3>
                    <span class="chip chip--brass">{{ ucfirst($member->role->name) }}</span>
                    <div class="member-card__points">
                        <span>Puntos <b>{{ $member->totalPoints }}</b></span>
                        <span>Capacidad <b>{{ $member->totalCapacity }}</b></span>
                    </div>
                    <ul class="profile-stats member-card__stats">
                        @foreach ($member->totalStats as $stat => $value)
                            <li><span>{{ ucfirst($stat) }}</span><b>{{ $value }}</b></li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @else
        <div class="panel team-noteam">
            <p class="zone-empty">No perteneces a ningún equipo.</p>
            <a href="{{ route('teams.index') }}" class="btn-epic">Unirse o crear un equipo</a>
        </div>
    @endif
</div>
@endsection
