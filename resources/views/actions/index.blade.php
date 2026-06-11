@extends('layouts.app')

@section('title', 'Acciones del Equipo')

@section('content')
<div class="inv-view">
    <a href="{{ route('zones.index') }}" class="btn-ghost inv-view__back">← Volver al mapa</a>

    <h1 class="inv-view__title">Acciones del Equipo</h1>

    @if ($teamActions->count() > 0)
        <div class="panel inv-block">
            <table class="game-table">
                <thead>
                    <tr><th>#</th><th>Tipo</th><th>Usuario</th><th>Duración</th><th>Fecha</th></tr>
                </thead>
                <tbody>
                    @foreach ($teamActions as $action)
                        <tr>
                            <td>{{ ($teamActions->currentPage() - 1) * $teamActions->perPage() + $loop->iteration }}</td>
                            <td>{{ $action->type->name ?? 'Sin tipo' }}</td>
                            <td>{{ $action->user->name ?? 'Desconocido' }}</td>
                            <td>{{ $action->duration }} s</td>
                            <td>{{ $action->created_at->format('d-m-Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="inv-pagination">{{ $teamActions->links() }}</div>
    @else
        <div class="panel inv-block"><p class="inv-empty">No hay acciones finalizadas en tu equipo.</p></div>
    @endif
</div>
@endsection
