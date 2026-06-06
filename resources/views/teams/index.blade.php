@extends('layouts.app')

@section('title', 'Gestión de Equipos')

@section('content')
<div class="container">

    <!-- volver  -->
    <div class="mb-4">
        <a href="{{ route('zones.index') }}" class="btn btn-primary">Volver al Mapa</a>
    </div>

    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif



    <h2 class="mt-5 text-center">Gestión de Mi Equipo</h2>

<!-- Inventarios -->
<div class="row mt-4">
    <!-- Inventario Personal -->
    <div class="col-md-6 text-center">
        <h4 class="mb-3">Mi Inventario</h4>
        @if ($userInventory)
        <a href="{{ route('resources.show', $userInventory->id) }}" class="btn btn-danger btn-lg">Ver Inventario Personal</a>
        @else
        <p>No tienes inventario personal.</p>
        <a href="{{ route('resources.create') }}" class="btn btn-success btn-lg">Crear Inventario Personal</a>
        @endif
    </div>

    <!-- Inventario  Equipo -->
    <div class="col-md-6 text-center">
        <h4 class="mb-3">Inventario del Equipo</h4>
        @if ($teamInventory)
        <a href="{{ route('resources.show', $teamInventory->id) }}" class="btn btn-danger btn-lg">Ver Inventario del Equipo</a>
        @else
        <p>No hay inventario asociado a tu equipo.</p>
        <a href="{{ route('resources.create') }}" class="btn btn-success btn-lg">Crear Inventario del Equipo</a>
        @endif
    </div>
</div>
    <!-- Info equipo -->
    @if (auth()->user()->team)
    <div class="mt-5">
        <div class="card bg-soft-blue shadow-lg">

            <div class="card-body text-center">

                <!-- bandera equipo -->
                <div class="mb-4">
                    <img src="{{ asset(auth()->user()->team->image) }}" alt="Bandera equipo" class="img-fluid" style="max-width:230px;">
                </div>


                <h2 class="card-title team-name {{ strtolower(auth()->user()->team->name) }}">{{ auth()->user()->team->name }}</h2>
                <p class="fs-5 mt-3">Eres parte de este clan. Aquí puedes ver y gestionar a los miembros de tu equipo.</p>

                <!-- Acciones del equipo -->
                <a href="{{ route('actions.index', auth()->user()->team->id) }}" class="btn btn-danger mx-3 btn-lg mb-4">Ver Acciones del Equipo</a>
                <a href="{{ route('teams.transfer', auth()->user()->team->id) }}" class="btn btn-danger btn-lg mb-4">Gestionar Transferencias Inventarios</a>
            </div>
        </div>
    </div>

    <!--  lista jugadores equipo -->

    <div class="mt-5">
        <h2 class="text-center mb-4">Miembros del Equipo</h2>
        <div class="row">
            @foreach ($teamMembers as $member)
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <img src="{{ asset('images/avatar.png') }}" alt="Avatar jugador" class="rounded-circle img-fluid mb-3" width="96">
                        <h5 class="text-dark">{{ $member->name }}</h5>
                        <p class="role-icon {{ strtolower($member->role->name) }}">
                            <span class="badge bg-warning fs-6">{{ ucfirst($member->role->name) }}</span>
                        </p>

                        <!-- Puntos jugadores  -->
                        <p><strong>Puntos Totales:</strong> <span class="badge bg-success fs-6">{{ $member->totalPoints }}</span></p>

                        <!-- Capacidad inventario jugadores-->
                         
                        <p><strong>Capacidad Inventario:</strong> <span class="badge bg-success fs-6">{{ $member->totalCapacity }}</span></p>

                        <!-- stats jugadores -->
                        <h5 class="mt-3 py-2 fs-6 badge bg-primary">Mejoras Adquiridas</h5>
                        <table class="table table-sm text-start">
                            <tbody>
                                @foreach ($member->totalStats as $stat => $value)
                                <tr>
                                    <td><strong>{{ ucfirst($stat) }}:</strong></td>
                                    <td class="text-end"><span class="badge bg-primary">{{ $value }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    @else
    <!-- Si el usuario no pertenece a un equipo -->
    <div class="mt-5 text-center">
        <p class="text-danger">No perteneces a ningún equipo.</p>
        <a href="{{ route('teams.index') }}" class="btn btn-success btn-lg">Unirse o Crear un Equipo</a>
    </div>
    @endif

</div>
@endsection