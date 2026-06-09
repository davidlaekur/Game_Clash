@extends('layouts.app')

@section('title', 'Crear Invento')

@section('content')

<div class="container mt-4 invent-view">
    <a href="{{ route('zones.show', $zone->id) }}" class="btn-ghost mb-3">← Volver a la Zona</a>

    <div class="text-center">
        <h1 class="action-view__title">Forjar un invento en {{ $zone->name }}</h1>

        @if (isset($error))
        <div class="alert alert-danger">
            {{ $error }}
        </div>
        @endif

        @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        @if (isset($timeRemaining) && $timeRemaining > 0)
        <div class="mt-4">
            <p id="timer" class="action-timer">
                Forjando · quedan
                <span id="timeRemaining">{{ $timeRemaining }}</span> s
            </p>
        </div>

        <script>
            let timeRemaining = parseInt(document.getElementById('timeRemaining')?.innerText || 0);

            if (timeRemaining > 0) {
                const timer = setInterval(() => {
                    timeRemaining--;
                    document.getElementById('timeRemaining').innerText = timeRemaining;

                    if (timeRemaining <= 0) {
                        clearInterval(timer);
                        const timerElement = document.getElementById('timer');
                        timerElement.classList.remove('text-danger');
                        timerElement.classList.add('text-success');
                        timerElement.innerText = "El invento ha sido completado. Puedes realizar otra acción.";
                    }
                }, 1000);
            }
        </script>
        @endif

        <form action="{{ route('inventions.store') }}" method="POST" class="mt-4">
            @csrf
            <input type="hidden" name="zone_id" value="{{ $zone->id }}">

            <!-- Selección de material -->
            <h3 class="mb-3">Selecciona el material necesario</h3>
            <div class="row row-cols-2 row-cols-md-4 g-3">
                @foreach ($inventoryMaterials as $material)
                <div class="col">
                    <div class="card text-center p-1" style="max-width: 140px;">
                        <div class="card-body p-2">
                            <label for="material-{{ $material->material->id }}" class="form-label">
                                💎 {{ $material->material->name }}<br>
                                <small class="text-muted">Disponibles: {{ $material->quantity }}</small>
                            </label>
                            <input
                                type="radio"
                                id="material-{{ $material->material->id }}"
                                name="material_id"
                                value="{{ $material->material->id }}"
                                class="form-check-input mx-auto d-block"
                                required>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Inventos disponibles para crear -->
            <h3 class="mt-4 mb-3">Selecciona el invento para crear:</h3>
            <div class="row row-cols-2 row-cols-md-4 g-3">
                @foreach ($inventionTypes as $type)
                @php
                $hasRequirements = $type->needs->isEmpty() || $type->needs->every(fn($need) => $user->inventory->inventions->contains('inventiontype_id', $need->parent_id));
                @endphp
                <div class="col">
                    <div class="card text-center @if(!$hasRequirements) bg-light @else bg-success @endif">
                        <div class="card-body">
                            <img src="{{ asset($type->image) }}" alt="{{ $type->name }}" style="width: 80px; height: 80px; margin-bottom: 10px;">
                            <label for="invention-{{ $type->id }}" class="form-label">
                                {{ $type->name }} (Nivel: {{ $type->level }})
                            </label>
                            <input
                                type="radio"
                                id="invention-{{ $type->id }}"
                                name="invention_type"
                                value="{{ $type->id }}"
                                class="form-check-input mx-auto d-block"
                                {{ !$hasRequirements ? 'disabled' : '' }}
                                required>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

                    <!-- Inventos en el inventario -->
                    <div class="mt-4 mb-4">
                <h3 class="mb-3">Inventos en tu inventario</h3>
                <div class="row row-cols-2 row-cols-md-4 g-3">
                    @foreach ($user->inventory->inventions as $invention)
                    <div class="col">
                        <div class="card text-center p-2" style="max-width: 180px;">
                            <div class="card-body">
                                <label for="invention-{{ $invention->id }}" class="form-label">
                                    {{ $invention->name }}
                                </label>
                                <input
                                    type="checkbox"
                                    id="invention-{{ $invention->id }}"
                                    name="required_inventions[]"
                                    value="{{ $invention->id }}"
                                    class="form-check-input mx-auto d-block">
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn btn-success p-3 mt-4" 
                {{ session('actionBlocked') ? 'disabled' : '' }}>
                Crear Invento
            </button>

            <!-- Inventos previos requeridos -->
            <div class="mt-4">
                <h3 class="mb-3">Inventos previos necesarios</h3>
                <div class="row row-cols-2 row-cols-md-4 g-3">
                    @foreach ($inventionTypes as $type)
                    @if (!$type->needs->isEmpty())
                    <div class="col">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">{{ $type->name }}</h5>
                                <ul class="list-group">
                                    @foreach ($type->needs as $need)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $need->parent->name }}
                                        <span class="badge {{ $user->inventory->inventions->contains('inventiontype_id', $need->parent_id) ? 'bg-success' : 'bg-danger' }}">
                                            {{ $user->inventory->inventions->contains('inventiontype_id', $need->parent_id) ? 'Disponible' : 'No disponible' }}
                                        </span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>

    
        </form>
    </div>
</div>

@endsection
