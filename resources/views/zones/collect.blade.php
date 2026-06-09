@extends('layouts.app')

@section('title', 'Recolección de Materiales')

@section('content')

<div class="action-view collect-view text-center">
    <h1 class="action-view__title">Recolectar en {{ $zone->name }}</h1>

    @if (isset($successMessage))
    <div class="alert alert-success mt-4">
        {{ $successMessage }}
    </div>
    @endif

    @isset($error)
    <div class="alert alert-danger mt-4">
        {{ $error }}
    </div>
    @endisset



    <div class="mt-2 mb-3">
        <a href="{{ route('zones.show', $zone->id) }}" class="btn-ghost">← Volver a la Zona</a>
    </div>


    <!-- Temporizador -->
    @if (isset($timeRemaining) && $timeRemaining > 0)
    <div class="mt-4">
        <p id="timer" class="action-timer">
            En progreso · quedan
            <span id="timeRemaining">{{ $timeRemaining }}</span> s
        </p>
    </div>

    <!-- Temporizador en tiempo real -->
    <script>
        let timeRemaining = parseInt(document.getElementById('timeRemaining').innerText || 0);

        if (timeRemaining > 0) {
            const timer = setInterval(() => {
                timeRemaining--;
                document.getElementById('timeRemaining').innerText = timeRemaining;

                if (timeRemaining <= 0) {
                    clearInterval(timer);
                    const timerElement = document.getElementById('timer');
                    timerElement.classList.remove('text-danger');
                    timerElement.classList.add('text-success');
                    timerElement.innerText = "La recolección ha sido completada. Puedes realizar otra acción.";
                }
            }, 1000); // Reducir cada segundo
        }
    </script>
    @endif


    <!-- capacidad del inventario -->
    <div class="mt-3">
        <h4 class="mb-4">Detalles del Inventario</h4>
        <table class="table table-sm table-bordered table-striped">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Cantidad</th>
                </tr>
            </thead>
            <tbody>
         
                <!-- Materiales almacenados -->
                <tr>
                    <td><strong>💎 Materiales almacenados</strong></td>
                    <td>{{ Auth::user()->inventory->materials->sum('quantity') }} unidades</td>
                </tr>

                <!-- Espacio ocupado por inventos -->
                <tr>
                    <td><strong>💡 Espacio ocupado por inventos</strong></td>
                    <td>{{ Auth::user()->inventory->inventions->count() }} unidades</td>
                </tr>

                <!-- Espacio disponible -->
                <tr>
                    <td><strong>📉 inventario disponible</strong></td>
                    <td>
                    {{ $inventoryCapacity }} unidades
                </tr>
            </tbody>
        </table>
    </div>


    <!-- Formulario para recolectar materiales -->
    <form action="{{ route('resources.store') }}" method="POST" class="mt-4">
        @csrf
        <input type="hidden" name="zone_id" value="{{ $zone->id }}">

        <!-- Listado de materiales disponibles -->
        <div class="row justify-content-center">
            @foreach ($availableMaterials as $material)
            <div class="col-md-4 mb-3">
                <label for="material-{{ $material->id }}" class="form-label">
                    {{ $material->name }} (Disponibles: {{ $material->quantity }})
                </label>
                <input
                    type="number"
                    id="material-{{ $material->id }}"
                    name="materials[{{ $material->id }}]"
                    max="{{ $material->quantity }}"
                    min="0"
                    class="form-control text-center"
                    style="width: 100px;"
                    placeholder="0">
            </div>
            @endforeach
        </div>

        <!-- Opciones de almacenamiento -->
        <div class="form-group mt-4">
            <label for="storageOption" class="form-label">Selecciona dónde almacenar los materiales:</label>
            <select id="storageOption" name="storage_option" class="form-control mx-auto" style="width: 200px;">
                <option value="personal">Inventario Personal</option>
                <option value="team">Inventario del Equipo</option>
            </select>
        </div>

        <button type="submit" class="btn btn-warning mt-4" {{ isset($timeRemaining) && $timeRemaining > 0 ? 'disabled' : '' }}>
            Confirmar Almacenamiento
        </button>
    </form>
</div>

@endsection