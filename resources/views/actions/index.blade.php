@extends('layouts.app')

@section('title', 'Acciones del Equipo')

@section('content')
<div class="container">

    <!-- Botón de Volver -->
    <div class="mt-4">
        <a href="{{ route('zones.index') }}" class="btn btn-primary">Volver a Zonas</a>
    </div>

    <h2 class="mt-5 text-center">Acciones del Equipo</h2>

    @if (session('error'))
    <div class="alert alert-danger text-center mt-3">
        {{ session('error') }}
    </div>
    @endif

    @if ($teamActions->count() > 0)
    <table class="table table-bordered mt-4">
        <thead class="thead-dark">
            <tr>
                <th>⏳</th>
                <th>Tipo</th>
        <!--         <th>Lugar</th> -->
                <th>Usuario</th>
                <th>Duración</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($teamActions as $index => $action)
            <tr>
                <td>{{ ($teamActions->currentPage() - 1) * $teamActions->perPage() + $loop->iteration }}</td>
                <td>{{ $action->type->name ?? 'Sin Tipo' }}</td>
      <!--           <td>{{ $action->actionable->name ?? 'Zona Desconocida' }}</td> -->
                <td>{{ $action->user->name ?? 'Desconocido' }}</td>
                <td>{{ $action->duration }} segundos</td>
                <td>{{ $action->created_at->format('d-m-Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>

    </table>

    <!-- Paginación -->
    <div class="d-flex justify-content-center mt-4">
        {{ $teamActions->links() }}
    </div>
    @else
    <p class="text-center mt-5">No hay acciones finalizadas en tu equipo.</p>
    @endif

</div>
@endsection