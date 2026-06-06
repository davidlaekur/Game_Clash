@extends('layouts.app')

@section('title', 'Mapa de Zonas')

@section('content')

@if (session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif


@if (session('warning'))
<div class="alert alert-warning">
    {{ session('warning') }}
</div>
@endif


@if (session('error '))
<div class="alert alert-danger">
    {{ session('error') }}
</div>

@endif

<h1 class="mb-4 text-center">Mapa de Zonas</h1>


<!-- acceso a aventuras, oculto para el admin -->

@if(auth()->user()->role->name !== 'Admin')

<div class="col-12 mb-5 d-flex justify-content-end">
    <a href="{{ route('adventure.intro') }}" class="btn-adventure">
        Iniciar / Continuar Aventura
        <i class="bolt lt"></i>
        <i class="bolt rt"></i>
        <i class="bolt lb"></i>
        <i class="bolt rb"></i>
    </a>
</div>

@endif

<!-- solo el admin puede importar nuevas zonas -->
@if(auth()->user()->role->name === 'Admin')

<form action="{{ route('import.zones') }}" method="POST">
    @csrf
    <button type="submit" class="btn btn-primary mb-5">Importar Zonas</button>
</form>
@endif

<div class="row">
    @foreach($zones as $zone)
    <div class="col-md-4 mb-4">
        <div class="card shadow {{ Auth::user()->zone_id === $zone->id ? 'bg-warning text-dark' : '' }}">
            <!-- Imagen ajustada -->
            <img src="{{ asset($zone->image) }}" class="card-img-top img-fluid" style="height: 200px; object-fit: cover;" alt="{{ $zone->landscape }}">
            <div class="card-body">
                <h5 class="card-title">
                    {{ $zone->name }}
                    @if (Auth::user()->zone_id === $zone->id)
                    <span class="badge bg-primary">Tu ubicación</span>
                    <span class="role-icon {{ strtolower(Auth::user()->role->name) }}"></span>
                    @endif
                </h5>
                <p class="card-text">Paisaje: {{ ucfirst($zone->landscape) }}</p>
                <p class="card-text">Defensa Base: {{ $zone->defense }}</p>
                <p class="card-text">Coordenadas: ({{ $zone->latitude }}, {{ $zone->longitude }})</p>
                <p class="card-text">Controlada por: {{ $zone->team->name ?? 'Neutral' }}</p>
                <a href="{{ route('zones.show', $zone->id) }}" class="btn btn-primary">Ver Zona</a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection