@extends('layouts.app')

@section('music', 'aventura')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header text-white bg-danger text-center">
            <h2>Aventura en {{ $adventure->name }}</h2>
        </div>
        <div class="card-body form-advenrure">
            <div class="row">

                <!-- iniciamos aventura  -->
                <div class="col-md-6">
                    <img src="{{ asset('images/' . $adventure->image) }}" class=" card-img-top img-fluid" style="height: 500px; object-fit: cover;" alt="{{ $adventure->name }}">
                    <h3>Descripción:</h3>
                    <p>{{ $adventure->description }}</p>
                </div>

                <!-- preguntas-->
                <div class="col-md-6">
                    <h3>Pregunta:</h3>
            
                    <p>{{ $scenario->question }}</p>
                    
                    <form action="{{ route('adventure.check', ['scenario' => $scenario->id]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="scenario_id" value="{{ $scenario->id }}">
                        @foreach ($scenario->options as $option)
                        <div class="form-check">
                            <input class=" form-check-input" type="radio" name="selected_option" value="{{ $option->id }}" required>
                            <label class="form-check-label">{{ $option->text }}</label>
                        </div>
                        @endforeach
                        <button type="submit " class="  btn btn-primary mt-3">Responder</button>
                    </form>
                </div>
            </div>

            <!-- items ganados -->
            @if ($scenario->item)
            <div class="row mt-4">
                <div class="col-md-12">
                    <h3>Recompensa:</h3>
                    <img src="{{ asset('images/' . $scenario->item->image) }}" class="img-thumbnail" width="100">
                    <p><strong>{{ $scenario->item->name }}</strong>: {{ $scenario->item->description }}</p>
                </div>
            </div>
            @endif

            <!-- premios -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h3>Premios:</h3>
                    <ul>
                        @foreach ($adventure->items as $item)
                        <li>
                            <img src="{{ asset('images/' . $item->image) }}" class="img-thumbnail" width="100">
                            <strong>{{ $item->name }}</strong>: {{ $item->description }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            @if(session('success'))
            <div class="alert alert-success mt-3">
            {!! session('success') !!}
            </div>


            @elseif(session('error'))
            <div class="alert alert-danger mt-3">
                {{ session('error') }}
            </div>
            @endif

            <!-- continuar  pregunta  -->
            @if(session('answer_iscorrect'))
            <form action="{{ route('adventure.continue', ['id' => $userAdventure->id]) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success mt-3">Siguiente Pregunta</button>
            </form>
            @endif

        </div>
    </div>
</div>
@endsection