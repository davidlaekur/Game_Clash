@extends('layouts.app')

@section('title', 'Perfil del Jugador')

@section('content')
<div class="container mt-4">
    <h2 class="text-center mb-4">Perfil del jugador {{ $user->name }}</h2>

    @if (session('success'))
    <div class="alert alert-success">
        {!! session('success') !!}
    </div>
    @endif


    <!-- volver  -->
    <div class="mt-4">
        <a href="{{ route('zones.index') }}" class="btn btn-primary">Volver al Mapa</a>
    </div>

    <div class="row mt-5">

        <!-- Avatar y detalles del jugador -->
        <div class="col-md-6 d-flex justify-content-center">
            <div class="card shadow-sm bg-light border-0" style="width: 350px;">
                <div class="card-body text-center">


                    <!-- Avatar -->
                    <img src="{{ asset('images/avatar.png') }}" alt="Avatar del jugador" class="rounded-circle img-fluid mb-3" width="140">

                    <h4 class="text-dark">{{ $user->name }}</h4>

                    <!-- Rol del Jugador -->
                    <p class="role-icon {{ strtolower($user->role->name) }}">
                        <span class="badge bg-warning fs-6">{{ ucfirst($user->role->name) }}</span>
                    </p>

                    <p class="mt-2"><strong>Puntos Totales:</strong>
                        <span class="badge bg-success fs-6">{{ $totalPoints }}</span>
                    </p>

                    <p class="mt-2"><strong>Capacidad Inventario:</strong>
                        <span class="badge bg-success fs-6">{{ $totalCapacity }}</span>
                    </p>

                    <!-- mejoras Adquiridas -->
                    <h5 class="mt-3 py-2 fs-6 badge bg-primary">Mejoras Adquiridas</h5>
                    <table class="table table-sm text-start">
                        <tbody>
                            @foreach ($totalStats as $stat => $value)
                            <tr>
                                <td><strong>{{ ucfirst($stat) }}:</strong></td>
                                <td class="text-end"><span class="badge bg-primary">{{ $value }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!--  editar perfil -->
                    <a href="{{ route('players.edit', $user->id) }}" class="btn btn-warning mt-3">Editar Perfil</a>
                </div>
            </div>
        </div>



        <!-- recompensas y premios --> 

     <div class="col-md-6 reward">
     @if (count($earnedItems) > 0)

    <div class="text-center">
        <div class="reward-title mb-4">Recompensas de Aventuras</div>
    </div>
    <div class="d-flex justify-content-center gap-3 flex-wrap">
        @foreach ($earnedItems as $item)
        <div class="card text-center" style="width: 180px;">
            <div class="card-body">
                <img src="{{ asset('images/' . $item->image) }}">
                <strong>{{ $item->name }}</strong>
                <p class="small">{{ $item->description }}</p>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    

    @if (count($rewards) > 0)

    <div class="text-center mt-4">
        <div class="earned-title mb-4">Premios de Aventuras</div>
    </div>
    <div class="d-flex justify-content-center gap-3 flex-wrap ">
        @foreach ($rewards as $reward)
        <div class="card text-center" style="width: 180px;">
            <div class="card-body">
                <img src="{{ asset('images/' . $reward->image) }}">
                <strong>{{ $reward->name }}</strong>
                <p class="small">{{ $reward->description }}</p>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>


    <!-- Inventario Personal -->
    <div class="col-md-12 mt-5 text-center">
        <h4 class="mb-3">Mi Inventario</h4>
        @if ($userInventory)
        <a href="{{ route('resources.show', $userInventory->id) }}" class="btn btn-danger btn-lg">Ver Inventario Personal</a>
        @else
        <p>No tienes inventario personal.</p>
        <a href="{{ route('resources.create') }}" class="btn btn-success btn-lg">Crear Inventario Personal</a>
        @endif
    </div>

    @endsection