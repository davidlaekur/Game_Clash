@extends('layouts.app')

@section('title', $inventory->name ?? 'Inventario')

@section('content')
<div class="inv-view">
    <a href="{{ route('teams.index') }}" class="btn-ghost inv-view__back">← Volver</a>

    <h1 class="inv-view__title">{{ $inventory->name ?? 'Inventario no encontrado' }}</h1>

    @if ($inventory)
        <div class="inv-view__meta">
            <span class="chip chip--brass">{{ ucfirst($inventory->type) }}</span>
            <a href="{{ route('teams.transfer') }}" class="btn-ghost"><i class="fas fa-sync-alt" aria-hidden="true"></i> Gestionar transferencias</a>
        </div>

        <div class="panel inv-block">
            <h3 class="inv-block__title"><i class="fas fa-gem" aria-hidden="true"></i> Materiales</h3>
            <table class="game-table">
                <thead><tr><th>Material</th><th>Cantidad</th></tr></thead>
                <tbody>
                    @forelse ($materials ?? [] as $material)
                        <tr><td>{{ $material->material->name }}</td><td>{{ $material->quantity }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="inv-empty">No hay materiales en este inventario.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel inv-block">
            <h3 class="inv-block__title"><i class="fas fa-lightbulb" aria-hidden="true"></i> Inventos</h3>
            <table class="game-table">
                <thead><tr><th>Nombre</th><th>Eficiencia</th><th>Puntos</th></tr></thead>
                <tbody>
                    @forelse ($inventory->inventions ?? [] as $invention)
                        <tr><td>{{ $invention->name }}</td><td>{{ $invention->efficiency }}</td><td>{{ $invention->points }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="inv-empty">No hay inventos en este inventario.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="panel inv-block"><p class="inv-empty">El inventario no existe o no está disponible.</p></div>
    @endif
</div>
@endsection
