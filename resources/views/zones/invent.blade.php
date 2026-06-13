@extends('layouts.app')

@section('title', 'Forjar invento')

@section('content')
<div class="forge-view">
    <a href="{{ route('zones.show', $zone->id) }}" class="btn-ghost forge-view__back">← Volver a la Zona</a>

    <h1 class="action-view__title">Forjar un invento en {{ $zone->name }}</h1>

    @if (isset($error))
        <div class="alert alert-danger">{{ $error }}</div>
    @endif

    @if (isset($timeRemaining) && $timeRemaining > 0)
        <p id="timer" class="action-timer forge-timer">
            Forjando · quedan <span id="timeRemaining">{{ $timeRemaining }}</span> s
        </p>
    @endif

    @php
        // icono + clase de color por familia de material
        $matFamily = [
            'Roca'     => ['fa-mountain', 'roca'],
            'Mineral'  => ['fa-gem', 'mineral'],
            'Arena'    => ['fa-hourglass-half', 'arena'],
            'Metal'    => ['fa-cube', 'metal'],
            'Madera'   => ['fa-tree', 'madera'],
            'Fibra'    => ['fa-seedling', 'fibra'],
            'Orgánico' => ['fa-leaf', 'organico'],
            'Estelar'  => ['fa-meteor', 'estelar'],
        ];
    @endphp

    <form action="{{ route('inventions.store') }}" method="POST" class="forge-form" data-sfx="invent">
        @csrf
        <input type="hidden" name="zone_id" value="{{ $zone->id }}">

        <div class="panel forge-block">
            <h3 class="forge-block__title">1 · Invento a crear</h3>
            <div class="forge-grid">
                @foreach ($inventionTypes as $type)
                    @php
                        $hasRequirements = $type->needs->isEmpty() || $type->needs->every(fn($need) => $user->inventory->inventions->contains('inventiontype_id', $need->parent_id));
                        $allowed = $type->material_types ?? [];
                        $extra = $type->extra_materials ?? [];
                        $matsLabel = count($allowed) ? implode(' · ', $allowed) : 'Sin material';
                        if (count($extra)) {
                            $matsLabel .= ' + ' . collect($extra)->map(fn($e) => $e['name'])->implode(', ');
                        }
                    @endphp
                    <label class="forge-opt forge-opt--item {{ $hasRequirements ? '' : 'is-locked' }}">
                        <input type="radio" name="invention_type" value="{{ $type->id }}"
                               data-allowed='@json($allowed)'
                               {{ $hasRequirements ? '' : 'disabled' }} required>
                        <span class="forge-opt__body">
                            @if ($type->image && file_exists(public_path($type->image)))
                                <img src="{{ asset($type->image) }}" alt="{{ $type->name }}" class="forge-opt__img">
                            @else
                                <span class="forge-opt__img forge-opt__icon"><i class="fas {{ $type->icon ?? 'fa-flask' }}" aria-hidden="true"></i></span>
                            @endif
                            <span class="forge-opt__name">{{ $type->name }}</span>
                            <span class="forge-opt__sub">Nivel {{ $type->level }}</span>
                            <span class="forge-opt__mats">{{ $matsLabel }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="panel forge-block" id="material-block">
            <h3 class="forge-block__title">2 · Material necesario</h3>
            <p class="forge-hint" id="material-hint">Elige primero un invento para ver qué materiales sirven.</p>
            <p class="forge-hint">Cuanto más denso el material, más potente el invento (más ataque, defensa…).</p>
            <div class="forge-grid">
                @php
                    // agrupa el inventario por material (suma depósitos del mismo nombre)
                    $grouped = $inventoryMaterials->filter(fn($im) => $im->material)->groupBy(fn($im) => $im->material->name);
                @endphp
                @foreach ($grouped as $name => $group)
                    @php
                        $rep = $group->sortByDesc('quantity')->first();
                        $mat = $rep->material;
                        $cat = optional($mat->materialType)->category;
                        $qty = $group->sum('quantity');
                        $density = $mat->density ?? 0;
                        [$powLabel, $powClass] = $density > 5
                            ? ['Potente', 'high']
                            : ($density >= 1.5 ? ['Media', 'mid'] : ['Ligera', 'low']);
                        [$icon, $iconCls] = $matFamily[$cat] ?? ['fa-gem', 'mineral'];
                    @endphp
                    <label class="forge-opt forge-opt--material is-locked" data-cat="{{ $cat }}">
                        <input type="radio" name="material_id" value="{{ $mat->id }}" disabled>
                        <span class="forge-opt__body">
                            <span class="forge-opt__name"><i class="fas {{ $icon }} mat-fam mat-fam--{{ $iconCls }}" aria-hidden="true"></i> {{ $name }}</span>
                            <span class="forge-opt__sub">{{ $cat ?: '—' }} · x{{ $qty }}</span>
                            <span class="forge-pow forge-pow--{{ $powClass }}">{{ $powLabel }}</span>
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
            <button type="submit" class="btn-epic" {{ session('actionBlocked') ? 'disabled' : '' }}><i class="fas fa-hammer" aria-hidden="true"></i> Crear invento</button>
        </div>

        @php $withNeeds = $inventionTypes->filter(fn($t) => !$t->needs->isEmpty()); @endphp
        @if ($withNeeds->count() > 0)
            <div class="panel forge-block forge-recipes">
                <h3 class="forge-block__title"><i class="fas fa-scroll" aria-hidden="true"></i> Recetas (requisitos previos)</h3>
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

    {{-- reabrir la forja para encadenar otro invento sin salir de la zona --}}
    <form id="forge-again-form" action="{{ route('players.invent', $zone->id) }}" method="POST" style="display:none;">@csrf</form>
</div>

<script>
    // Filtro de materiales según el invento elegido: solo se habilitan
    // los materiales cuya categoría admite el invento (data-allowed).
    (function () {
        const inventionRadios = document.querySelectorAll('input[name="invention_type"]');
        const materialBlock   = document.getElementById('material-block');
        const materialHint     = document.getElementById('material-hint');
        const materialOpts     = Array.from(document.querySelectorAll('.forge-opt--material'));

        function applyFilter(allowed) {
            const needsMaterial = allowed.length > 0;
            materialBlock.classList.toggle('is-hidden', !needsMaterial);
            materialHint.classList.add('is-hidden');
            materialOpts.forEach((opt) => {
                const input = opt.querySelector('input[name="material_id"]');
                const ok = needsMaterial && allowed.includes(opt.dataset.cat);
                opt.classList.toggle('is-locked', !ok);
                input.disabled = !ok;
                input.required = ok;
                if (!ok) input.checked = false;
            });
        }

        inventionRadios.forEach((r) => r.addEventListener('change', () => {
            let allowed = [];
            try { allowed = JSON.parse(r.dataset.allowed || '[]'); } catch (e) {}
            applyFilter(allowed);
        }));

        const checked = document.querySelector('input[name="invention_type"]:checked');
        if (checked) checked.dispatchEvent(new Event('change'));
    })();

    let timeRemaining = parseInt(document.getElementById('timeRemaining')?.innerText || 0);
    if (timeRemaining > 0) {
        const timer = setInterval(() => {
            timeRemaining--;
            document.getElementById('timeRemaining').innerText = timeRemaining;
            if (timeRemaining <= 0) {
                clearInterval(timer);
                const t = document.getElementById('timer');
                t.classList.add('action-timer--done');
                t.innerHTML = '<span class="action-done__msg"><i class="fas fa-check" aria-hidden="true"></i> Invento completado.</span>' +
                    '<button type="button" class="btn-epic action-done__btn" onclick="document.getElementById(\'forge-again-form\').submit()"><i class="fas fa-hammer"></i> Forjar otro</button>' +
                    '<a href="{{ route('zones.show', $zone->id) }}" class="btn-ghost action-done__btn">Volver a la zona</a>';
            }
        }, 1000);
    }
</script>
@endsection
