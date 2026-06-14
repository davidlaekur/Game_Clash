@extends('layouts.app')

@section('title', 'Sala de espera')

@php
    $roleMeta = [
        'Explorer'   => ['icon' => 'fa-compass',      'desc' => 'Explora y conquista territorios más rápido.'],
        'Collector'  => ['icon' => 'fa-hand-holding', 'desc' => 'Recolecta materias primas más rápido.'],
        'Inventor'   => ['icon' => 'fa-lightbulb',    'desc' => 'Forja inventos más rápido.'],
        'Strategist' => ['icon' => 'fa-chess',        'desc' => 'Liderazgo: +10% al combate de su grupo (ataque y defensa).'],
    ];
@endphp

@section('content')
<div class="warlobby">
    <div class="warlobby__bg" style="background-image:url('{{ asset('images/welcome/laraveland2.png') }}')"></div>
    <div class="warlobby__veil"></div>

    <div class="warlobby__inner">
        <header class="warlobby__hero">
            <span class="warlobby__crest"><i class="fas fa-jedi" aria-hidden="true"></i></span>
            <h1 class="warlobby__title">Preparaos para la guerra</h1>
            <p class="warlobby__sub">El Reino de Laraveland arde. Elige tu bando, toma tu rol y entra en la leyenda.</p>
            <p class="warlobby__rule"><i class="fas fa-hourglass-half" aria-hidden="true"></i> La batalla comienza cuando cada facción reúna al menos <b>{{ $minPerTeam }}</b> guerrero(s).</p>
        </header>

        @if ($isAdmin)
            <div class="warlobby__panel warlobby__panel--admin">
                <p><i class="fas fa-gavel" aria-hidden="true"></i> Eres el árbitro. Arranca sola al mínimo, o fuérzala.</p>
                <form action="{{ route('game.start') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-epic btn-epic--lg"><i class="fas fa-play" aria-hidden="true"></i> Empezar ya</button>
                </form>
            </div>
        @endif

        <form action="{{ route('players.join') }}" method="POST" id="enlistForm">
            @csrf
            <input type="hidden" name="team_id" id="team_id" value="">

            <div class="warlobby__field">
                @foreach ($musters as $i => $m)
                    @if ($i === 1)<div class="warlobby__vs"><span>VS</span></div>@endif
                    <button type="button"
                            class="warbanner warbanner--{{ $m['faction'] }} {{ ($joined && $m['mine']) ? 'is-mine' : '' }}"
                            data-team="{{ $m['id'] }}"
                            @if ($joined || $isAdmin) disabled @endif>
                        @if ($joined && $m['mine'])<span class="warbanner__tag">Tu facción</span>@endif
                        <span class="warbanner__glow"></span>
                        <span class="warbanner__crest"><i class="fas fa-shield-alt" aria-hidden="true"></i></span>
                        <span class="warbanner__name">{{ $m['name'] }}</span>
                        <span class="warbanner__count">{{ $m['joined'] }}</span>
                        <span class="warbanner__count-label">en pie de guerra</span>
                        <ul class="warbanner__roster">
                            @forelse ($m['players'] as $p)
                                <li><i class="fas fa-user" aria-hidden="true"></i> {{ $p['name'] }} <small>{{ ucfirst($p['role'] ?? '') }}</small></li>
                            @empty
                                <li class="warbanner__empty">Sin alistados todavía</li>
                            @endforelse
                        </ul>
                    </button>
                @endforeach
            </div>

            @if ($isAdmin)
                {{-- el admin no se alista --}}
            @elseif ($joined)
                <div class="warlobby__panel warlobby__panel--ready">
                    <p class="warlobby__ready"><i class="fas fa-check-circle" aria-hidden="true"></i> Estás listo como <b>{{ ucfirst($myRole) }}</b>.</p>
                    <p class="warlobby__waiting">Aguardando a los guerreros necesarios… la batalla comenzará sola.</p>
                </div>
            @else
                <div class="warlobby__panel warlobby__enlist">
                    <p class="warlobby__step" id="step1"><i class="fas fa-flag" aria-hidden="true"></i> 1 · Pulsa una facción arriba</p>
                    <div class="warlobby__role" id="roleBox" hidden>
                        <label for="role_id"><i class="fas fa-user-shield" aria-hidden="true"></i> 2 · Elige tu rol</label>
                        <select name="role_id" id="role_id" required>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}"
                                    data-icon="{{ $roleMeta[ucfirst($role->name)]['icon'] ?? 'fa-user' }}"
                                    data-desc="{{ $roleMeta[ucfirst($role->name)]['desc'] ?? '' }}">
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                        <p class="warlobby__roledesc" id="roleDesc"><i class="fas fa-user" aria-hidden="true"></i> <span></span></p>
                        <p class="warlobby__full" id="roleFull" hidden>Esa facción tiene todos los roles ocupados. Elige la otra.</p>
                        <button type="submit" class="btn-epic btn-epic--lg" id="enlistBtn"><i class="fas fa-flag-checkered" aria-hidden="true"></i> Unirse a la batalla</button>
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>

<script>
    (function () {
        const occupied = @json($occupied ?? []);
        const teamInput = document.getElementById('team_id');
        const banners = document.querySelectorAll('.warbanner[data-team]');
        const roleBox = document.getElementById('roleBox');
        const step1 = document.getElementById('step1');
        const sel = document.getElementById('role_id');
        const desc = document.getElementById('roleDesc');
        const full = document.getElementById('roleFull');
        const btn = document.getElementById('enlistBtn');

        function showDesc() {
            if (!sel || !desc) return;
            const opt = sel.selectedOptions[0];
            if (!opt) return;
            desc.querySelector('span').textContent = opt.dataset.desc || '';
            desc.querySelector('i').className = 'fas ' + (opt.dataset.icon || 'fa-user');
        }
        function filterRoles(teamId) {
            if (!sel) return;
            const taken = occupied[teamId] || [];
            let firstFree = null, freeCount = 0;
            Array.from(sel.options).forEach(function (opt) {
                const isTaken = taken.indexOf(opt.value) !== -1;
                opt.hidden = isTaken; opt.disabled = isTaken;
                if (!isTaken) { freeCount++; if (!firstFree) firstFree = opt; }
            });
            if (firstFree) firstFree.selected = true;
            if (full) full.hidden = freeCount > 0;
            if (btn) btn.disabled = freeCount === 0;
            if (sel) sel.style.display = freeCount === 0 ? 'none' : '';
            showDesc();
        }

        banners.forEach(function (b) {
            b.addEventListener('click', function () {
                banners.forEach(x => x.classList.remove('is-selected'));
                b.classList.add('is-selected');
                if (teamInput) teamInput.value = b.dataset.team;
                if (step1) step1.hidden = true;
                if (roleBox) roleBox.hidden = false;
                filterRoles(b.dataset.team);
            });
        });
        sel && sel.addEventListener('change', showDesc);

        // refresca alistados y entra al mapa cuando empiece la partida
        setInterval(function () { if (!document.hidden) window.location.reload(); }, 12000);
    })();
</script>
@endsection
