@extends('layouts.app')

@section('title', 'Moverse a Zona')

@section('content')

<!-- volver -->
<div class="mb-4">

    <a href="{{ route('zones.show', $zone->id) }}" class="btn btn-primary">Volver a vista {{ $zone->name }}</a>

</div>

<h2>Estás en la {{ $zone->name }}</h2>


<!-- Contenido principal -->
<img src="{{ asset($zone->image_detail) }}" alt="Detalle de {{ $zone->name }}" class="card-img-top img-fluid" style="height: 400px; object-fit: cover;">
<h4 class="mt-4"><strong>Paisaje:</strong> {{ ucfirst($zone->landscape) }}</h4>
<h4 class="mt-4">Controlada por:</strong> {{ $zone->team->name ?? 'Zona Neutral' }}</h4>

<h4 class="m-4">Recursos Disponibles</h4>
<ul>
    @foreach ($zone->materials as $material)
    <li>{{ $material->name }} (Probabilidad: {{ $material->probability }}%)</li>
    @endforeach
</ul>

<!-- Temporizador -->
<div class="text-center mt-4">
    @if ($timeRemaining > 0)
    <p id="action-timer" class="text-danger font-timer">
        No puedes realizar otra acción. Tiempo restante:
        <span id="timeRemaining">{{ $timeRemaining }}</span> segundos.
    </p>
    @endif
</div>


<!-- JavaScript para el temporizador -->
<script>
    let timeRemaining = parseInt(document.getElementById('timeRemaining').innerText);

    if (timeRemaining > 0) {
        const timer = setInterval(() => {
            timeRemaining--;
            document.getElementById('timeRemaining').innerText = timeRemaining;



            if (timeRemaining <= 0) {
                clearInterval(timer);


                // mensaje para avisar que la acción ha sido completada y se puede realizar otra
                document.getElementById('action-timer').classList.remove('text-danger');
                document.getElementById('action-timer').classList.add('text-success');
                document.getElementById('action-timer').innerText = "La acción ha sido completada. Puedes realizar otra acción.";
                document.getElementById('timer').appendChild(successMessage);
            }
        }, 1000); // Disminuir cada segundo
    }
</script>


<div class="mt-4 text-center">
    <h3 class="mb-3 fs-4">Estás moviéndote a esta zona...</h3>
    <img src="{{ asset('images/animation/move.gif') }}" alt="Animación" style="width: 600px; height: auto;">
</div>


@endsection