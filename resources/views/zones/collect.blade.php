@extends('layouts.app')

@section('title', 'Recolectar en ' . $zone->name)

@section('content')
<div class="forge-view collect-view">
    <a href="{{ route('zones.show', $zone->id) }}" class="btn-ghost forge-view__back">← Volver a la Zona</a>

    <h1 class="action-view__title">Recolectar en {{ $zone->name }}</h1>

    @isset($successMessage)<div class="alert alert-success">{{ $successMessage }}</div>@endisset
    @isset($error)<div class="alert alert-danger">{{ $error }}</div>@endisset

    @if (isset($timeRemaining) && $timeRemaining > 0)
        <p id="timer" class="action-timer forge-timer">
            Recolectando · quedan <span id="timeRemaining">{{ $timeRemaining }}</span> s
        </p>
    @endif

    <div class="panel forge-block">
        <h3 class="forge-block__title"><i class="fas fa-box-open" aria-hidden="true"></i> Tu inventario</h3>
        <table class="game-table">
            <thead><tr><th>Descripción</th><th>Cantidad</th></tr></thead>
            <tbody>
                <tr><td><i class="fas fa-gem" aria-hidden="true"></i> Materiales almacenados</td><td>{{ Auth::user()->inventory->materials->sum('quantity') }} ud.</td></tr>
                <tr><td><i class="fas fa-lightbulb" aria-hidden="true"></i> Espacio por inventos</td><td>{{ Auth::user()->inventory->inventions->count() }} ud.</td></tr>
                <tr><td><i class="fas fa-box" aria-hidden="true"></i> Capacidad disponible</td><td>{{ $inventoryCapacity }} ud.</td></tr>
            </tbody>
        </table>
    </div>

    <form action="{{ route('resources.store') }}" method="POST" class="forge-form" data-sfx="collect">
        @csrf
        <input type="hidden" name="zone_id" value="{{ $zone->id }}">

        <div class="panel forge-block">
            <h3 class="forge-block__title"><i class="fas fa-hammer" aria-hidden="true"></i> Materiales disponibles</h3>
            <div class="forge-grid">
                @foreach ($availableMaterials as $material)
                    <div class="collect-mat">
                        <span class="collect-mat__name">{{ $material->name }}</span>
                        <span class="collect-mat__sub">Disp. {{ $material->quantity }}</span>
                        <input type="number" name="materials[{{ $material->id }}]" max="{{ $material->quantity }}" min="0" class="transfer-input" placeholder="0">
                    </div>
                @endforeach
            </div>
        </div>

        <div class="collect-store">
            <label for="storageOption">Almacenar en:</label>
            <select id="storageOption" name="storage_option" class="collect-select">
                <option value="personal">Inventario Personal</option>
                <option value="team">Inventario del Equipo</option>
            </select>
        </div>

        <div class="forge-submit">
            <button type="submit" class="btn-epic" {{ isset($timeRemaining) && $timeRemaining > 0 ? 'disabled' : '' }}><i class="fas fa-box" aria-hidden="true"></i> Confirmar recolección</button>
        </div>
    </form>
</div>

<script>
    let timeRemaining = parseInt(document.getElementById('timeRemaining')?.innerText || 0);
    if (timeRemaining > 0) {
        const timer = setInterval(() => {
            timeRemaining--;
            document.getElementById('timeRemaining').innerText = timeRemaining;
            if (timeRemaining <= 0) {
                clearInterval(timer);
                const t = document.getElementById('timer');
                t.classList.add('action-timer--done');
                t.innerHTML = '<i class="fas fa-check" aria-hidden="true"></i> Recolección completada. <a href="{{ route('zones.show', $zone->id) }}" class="btn-ghost action-done__btn">Volver a la zona →</a>';
            }
        }, 1000);
    }
</script>
@endsection
