@extends('layouts.app')

@section('title', 'Forjar invento')

@section('content')
<div class="forge-view">
    <a href="{{ route('zones.show', $zone->id) }}" class="btn-ghost forge-view__back">← Volver a la Zona</a>

    <h1 class="action-view__title">Forjar un invento en {{ $zone->name }}</h1>

    @if (isset($error))
        <div class="alert alert-danger">{{ $error }}</div>
    @endif
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (isset($timeRemaining) && $timeRemaining > 0)
        <p id="timer" class="action-timer forge-timer">
            Forjando · quedan <span id="timeRemaining">{{ $timeRemaining }}</span> s
        </p>
    @endif

    <form action="{{ route('inventions.store') }}" method="POST" class="forge-form" data-sfx="invent">
        @csrf
        <input type="hidden" name="zone_id" value="{{ $zone->id }}">

        <div class="panel forge-block">
            <h3 class="forge-block__title">1 · Material necesario</h3>
            <div class="forge-grid">
                @foreach ($inventoryMaterials as $material)
                    <label class="forge-opt">
                        <input type="radio" name="material_id" value="{{ $material->material->id }}" required>
                        <span class="forge-opt__body">
                            <span class="forge-opt__name">💎 {{ $material->material->name }}</span>
                            <span class="forge-opt__sub">x{{ $material->quantity }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="panel forge-block">
            <h3 class="forge-block__title">2 · Invento a crear</h3>
            <div class="forge-grid">
                @foreach ($inventionTypes as $type)
                    @php
                        $hasRequirements = $type->needs->isEmpty() || $type->needs->every(fn($need) => $user->inventory->inventions->contains('inventiontype_id', $need->parent_id));
                    @endphp
                    <label class="forge-opt forge-opt--item {{ $hasRequirements ? '' : 'is-locked' }}">
                        <input type="radio" name="invention_type" value="{{ $type->id }}" {{ $hasRequirements ? '' : 'disabled' }} required>
                        <span class="forge-opt__body">
                            <img src="{{ asset($type->image) }}" alt="{{ $type->name }}" class="forge-opt__img">
                            <span class="forge-opt__name">{{ $type->name }}</span>
                            <span class="forge-opt__sub">Nivel {{ $type->level }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        @if ($user->inventory->inventions->count() > 0)
            <div class="panel forge-block">
                <h3 class="forge-block__title">3 · Inventos previos a consumir</h3>
                <div class="forge-grid">
                    @foreach ($user->inventory->inventions as $invention)
                        <label class="forge-opt">
                            <input type="checkbox" name="required_inventions[]" value="{{ $invention->id }}">
                            <span class="forge-opt__body"><span class="forge-opt__name">{{ $invention->name }}</span></span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="forge-submit">
            <button type="submit" class="btn-epic" {{ session('actionBlocked') ? 'disabled' : '' }}>⚒️ Crear invento</button>
        </div>

        @php $withNeeds = $inventionTypes->filter(fn($t) => !$t->needs->isEmpty()); @endphp
        @if ($withNeeds->count() > 0)
            <div class="panel forge-block forge-recipes">
                <h3 class="forge-block__title">📜 Recetas (requisitos previos)</h3>
                <div class="forge-recipes__grid">
                    @foreach ($withNeeds as $type)
                        <div class="forge-recipe">
                            <h4 class="forge-recipe__name">{{ $type->name }}</h4>
                            <ul>
                                @foreach ($type->needs as $need)
                                    @php $have = $user->inventory->inventions->contains('inventiontype_id', $need->parent_id); @endphp
                                    <li>
                                        <span>{{ $need->parent->name }}</span>
                                        <span class="chip {{ $have ? 'chip--ok' : 'chip--bad' }}">{{ $have ? 'Tienes' : 'Falta' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
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
                t.innerText = "✅ Invento completado. Ya puedes actuar.";
            }
        }, 1000);
    }
</script>
@endsection
