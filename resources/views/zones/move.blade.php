@extends('layouts.app')

@section('title', 'Moverse a ' . $zone->name)

@section('content')
<div class="action-view">
    <a href="{{ route('zones.show', $zone->id) }}" class="btn-ghost action-view__back">← Volver a {{ $zone->name }}</a>

    <h1 class="action-view__title">Marchando hacia {{ $zone->name }}</h1>

    <img src="{{ asset($zone->image_detail) }}" alt="{{ $zone->name }}" class="action-view__hero">

    <div class="action-view__meta">
        <span class="chip chip--brass">{{ ucfirst($zone->landscape) }}</span>
        <span class="chip chip--neutral">{{ $zone->team->name ?? 'Zona Neutral' }}</span>
    </div>

    @if ($timeRemaining > 0)
        <p id="action-timer" class="action-timer">
            En ruta · llegas en <span id="timeRemaining">{{ $timeRemaining }}</span> s
        </p>
    @endif

    <div class="action-view__stage">
        <h3>Tu comitiva avanza por los caminos…</h3>
        <img src="{{ asset('images/animation/move.gif') }}" alt="Movimiento" class="action-view__anim">
    </div>
</div>

<script>
    let timeRemaining = parseInt(document.getElementById('timeRemaining')?.innerText || 0);
    if (timeRemaining > 0) {
        const timer = setInterval(() => {
            timeRemaining--;
            document.getElementById('timeRemaining').innerText = timeRemaining;
            if (timeRemaining <= 0) {
                clearInterval(timer);
                const t = document.getElementById('action-timer');
                t.classList.add('action-timer--done');
                t.innerHTML = '<i class="fas fa-check" aria-hidden="true"></i> Has llegado a la zona. <a href="{{ route('zones.show', $zone->id) }}" class="btn-ghost action-done__btn">Ver la zona →</a>';
            }
        }, 1000);
    }
</script>
@endsection
