@extends('layouts.app')

@section('music', 'aventura')

@section('title', 'Aventura en ' . $adventure->name)

@section('content')
<div class="adv-view">
    <h1 class="adv-view__title">Aventura en {{ $adventure->name }}</h1>

    <div class="adv-grid">
        {{-- Escenario --}}
        <div class="panel panel--framed adv-scene">
            <img src="{{ asset('images/' . $adventure->image) }}" alt="{{ $adventure->name }}" class="adv-scene__img">
            <div class="adv-scene__body">
                <h3 class="adv-block__title">Descripción</h3>
                <p class="adv-text">{{ $adventure->description }}</p>
            </div>
        </div>

        {{-- Pregunta --}}
        <div class="panel adv-quiz">
            <h3 class="adv-block__title"><i class="fas fa-question-circle" aria-hidden="true"></i> Pregunta</h3>
            <p class="adv-question">{{ $scenario->question }}</p>

            <form action="{{ route('adventure.check', ['scenario' => $scenario->id]) }}" method="POST">
                @csrf
                <input type="hidden" name="scenario_id" value="{{ $scenario->id }}">
                <div class="adv-options">
                    @foreach ($scenario->options as $option)
                        <label class="adv-option">
                            <input type="radio" name="selected_option" value="{{ $option->id }}" required>
                            <span>{{ $option->text }}</span>
                        </label>
                    @endforeach
                </div>
                <button type="submit" class="btn-epic adv-answer">Responder</button>
            </form>

            @if(session('answer_iscorrect'))
                <form action="{{ route('adventure.continue', ['id' => $userAdventure->id]) }}" method="POST" class="adv-next">
                    @csrf
                    <button type="submit" class="btn-epic">Siguiente pregunta →</button>
                </form>
            @endif
        </div>
    </div>

    {{-- Recompensa del escenario --}}
    @if ($scenario->item)
        <div class="panel adv-block">
            <h3 class="adv-block__title"><i class="fas fa-gift" aria-hidden="true"></i> Recompensa</h3>
            <div class="adv-reward">
                <img src="{{ asset('images/' . $scenario->item->image) }}" alt="{{ $scenario->item->name }}">
                <div><strong>{{ $scenario->item->name }}</strong><p>{{ $scenario->item->description }}</p></div>
            </div>
        </div>
    @endif

    {{-- Premios de la aventura --}}
    <div class="panel adv-block">
        <h3 class="adv-block__title"><i class="fas fa-trophy" aria-hidden="true"></i> Premios de la aventura</h3>
        <div class="adv-prizes">
            @foreach ($adventure->items as $item)
                <div class="adv-prize">
                    <img src="{{ asset('images/' . $item->image) }}" alt="{{ $item->name }}">
                    <div><strong>{{ $item->name }}</strong><p>{{ $item->description }}</p></div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
