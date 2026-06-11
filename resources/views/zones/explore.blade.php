@extends('layouts.app')

@section('title', 'Explorando ' . $zone->name)

@section('content')
<div class="action-view">
    <a href="{{ route('zones.show', $zone->id) }}" class="btn-ghost action-view__back">← Volver a {{ $zone->name }}</a>

    <h1 class="action-view__title">Explorando {{ $zone->name }}</h1>

    <img src="{{ asset($zone->image_detail) }}" alt="{{ $zone->name }}" class="action-view__hero">

    <div class="action-view__meta">
        <span class="chip chip--brass">{{ ucfirst($zone->landscape) }}</span>
    </div>

    @if ($timeRemaining > 0)
        <p id="action-timer" class="action-timer">
            Explorando · quedan <span id="timeRemaining">{{ $timeRemaining }}</span> s
        </p>
    @else
        <p id="action-timer" class="action-timer action-timer--done">
            <i class="fas fa-check-circle" aria-hidden="true"></i> Exploración completada. La zona pertenece a tu equipo.
        </p>
    @endif

    <div class="action-view__stage">
        <h3>Tus exploradores recorren el territorio…</h3>
        <img src="{{ asset('images/animation/explore.gif') }}" alt="Exploración" class="action-view__anim">
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
                t.innerHTML = '<i class="fas fa-check" aria-hidden="true"></i> Exploración completada.';
                setTimeout(() => { window.location.href = "{{ route('zones.show', $zone->id) }}"; }, 1200);
            }
        }, 1000);
    }
</script>
@endsection
