@extends('layouts.app')

@section('title', 'Explorando Zona')

@section('content')

<!--volver -->
<div class="mb-4">
    <a href="{{ route('zones.show', $zone->id) }}" class="btn btn-primary">Volver a la vista de la zona</a>
</div>


<h2>Explorando la {{ $zone->name }}</h2>

<img src="{{ asset($zone->image_detail) }}" alt="Detalle de {{ $zone->name }}" class="card-img-top img-fluid" style="height: 400px; object-fit: cover;">

<h4 class="mt-4"><strong>Paisaje:</strong> {{ ucfirst($zone->landscape) }}</h4>

<!-- Temporizador -->
<div class="text-center mt-4">
    @if ($timeRemaining > 0)
        <p id="action-timer" class="text-danger  font-timer">
            Estás explorando esta zona. Tiempo restante: 
            <span id="timeRemaining">{{ $timeRemaining }}</span> segundos.
        </p>
    @else
        <p id="action-timer" class="text-success">
            La acción ha sido completada. La zona ahora pertenece a tu equipo.
        </p>
    @endif
</div>

<!-- JavaScript para el temporizador -->
<script>
    let timeRemaining = parseInt(document.getElementById('timeRemaining')?.innerText || 0);

    if (timeRemaining > 0) {
        const timer = setInterval(() => {
            timeRemaining--;
            document.getElementById('timeRemaining').innerText = timeRemaining;

            if (timeRemaining <= 0) {
                clearInterval(timer);

                // Cambiar el mensaje cuando la acción se completa
                const timerElement = document.getElementById('action-timer');
                timerElement.classList.remove('text-danger');
                timerElement.classList.add('text-success');
                timerElement.innerText = "La exploración ha sido completada. La zona ahora pertenece a tu equipo.";
            }
        }, 1000); // Reducir cada segundo
    }
</script>

<div class="mt-4 text-center">
    <h3 class="mb-3 fs-4">Estás explorando esta zona...</h3>
    <img src="{{ asset('images/animation/explore.gif') }}" alt="Animación de exploración" style="width: 600px; height: auto;">
</div>


@endsection