@extends('layouts.app')

@section('title', 'Inventario')

@section('content')

<div class="col-2">
    <!-- volver -->
    <a href="{{ route('teams.index') }}" class="btn btn-primary ">Volver</a>
</div>



<div class="container">
    <h2 class="mt-4">{{ $inventory->name ?? 'Inventario no encontrado' }}</h2>

    @if ($inventory)
        <h4 class="mt-4">Tipo: {{ $inventory->type }}</h4>

        <!-- acceder a la vista de transferencia -->
        <div class="mt-3">
            <a href="{{ route('teams.transfer') }}" class="btn btn-secondary">Gestionar Transferencias</a>
        </div>

        <!-- Tabla con materiales -->
        <h4 class="mt-4">Materiales:</h4>
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @if ($materials && $materials->count() > 0)
                    @foreach ($materials as $material)
                        <tr>
                            <td>{{ $material->material->name }}</td>
                            <td>{{ $material->quantity }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="2">No hay materiales en este inventario.</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Tabla con inventos -->
        <h4 class="mt-4">Inventos:</h4>
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Eficiencia</th>
                    <th>Puntos</th>
                </tr>
            </thead>
            <tbody>
                @if ($inventory->inventions && $inventory->inventions->count() > 0)
                    @foreach ($inventory->inventions as $invention)
                        <tr>
                            <td>{{ $invention->name }}</td>
                            <td>{{ $invention->efficiency }}</td>
                            <td>{{ $invention->points }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="3">No hay inventos en este inventario.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @else
        <p>El inventario no existe o no está disponible.</p>
    @endif
</div>

@endsection
