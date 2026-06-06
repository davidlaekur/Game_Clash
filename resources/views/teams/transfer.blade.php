@extends('layouts.app')

@section('title', 'Transferencia de Inventarios')

@section('content')
<div class="container">
    <!-- volver -->
    <div class="mb-4">
        <a href="{{ route('teams.index')}}" class="btn btn-primary">
       Volver
        </a>
    </div>

    @if (session('success'))
    <div class="alert alert-success text-center">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger text-center">
        {{ session('error') }}
    </div>
@endif

  
    <h2 class="text-center fs-1 mb-5">Transferencia de Inventarios</h2>

    <div class="row">
        <!-- Transferir del Equipo al Personal -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-secondary text-white text-center">
                    <h4>Inventario del Equipo</h4>
                </div>
                <div class="card-body">
                    @if ($teamInventory && $teamInventory->materials->count() > 0)
                    <form action="{{ route('teams.processTransfer') }}" method="POST">
                        @csrf
                        <input type="hidden" name="team_id" value="{{ $team->id }}">
                        <input type="hidden" name="direction" value="team_to_personal">

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Material</th>
                                    <th>Cantidad Disponible</th>
                                    <th>Cantidad a Transferir</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($teamInventory->materials as $material)
                                <tr>
                                    <td>{{ $material->material->name }}</td>
                                    <td>{{ $material->quantity }}</td>
                                    <td>
                                        <input
                                            type="number"
                                            name="materials[{{ $material->id }}]"
                                            max="{{ $material->quantity }}"
                                            min="0"
                                            class="form-control"
                                            placeholder="0">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-arrow-right"></i> Transferir a Mi Inventario
                            </button>
                        </div>
                    </form>
                    @else
                    <p class="text-center">No hay materiales en el inventario del equipo.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Transferir del Personal al Equipo -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-secondary text-white text-center">
                    <h4>Mi Inventario</h4>
                </div>
                <div class="card-body">
                    @if ($userInventory && $userInventory->materials->count() > 0)
                    <form action="{{ route('teams.processTransfer') }}" method="POST">
                        @csrf
                        <input type="hidden" name="team_id" value="{{ $team->id }}">
                        <input type="hidden" name="direction" value="personal_to_team">

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Material</th>
                                    <th>Cantidad Disponible</th>
                                    <th>Cantidad a Transferir</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($userInventory->materials as $material)
                                <tr>
                                    <td>{{ $material->material->name }}</td>
                                    <td>{{ $material->quantity }}</td>
                                    <td>
                                        <input
                                            type="number"
                                            name="materials[{{ $material->id }}]"
                                            max="{{ $material->quantity }}"
                                            min="0"
                                            class="form-control"
                                            placeholder="0">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-arrow-left"></i> Transferir al Inventario del Equipo
                            </button>
                        </div>
                    </form>
                    @else
                    <p class="text-center">No hay materiales en tu inventario personal.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Transferencia de Materiales del Equipo al Personal -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-secondary text-white text-center">
                <h4>Inventos del Equipo</h4>
            </div>
            <div class="card-body">
                @if ($teamInventory && $teamInventory->inventions->count() > 0)
                <form action="{{ route('teams.processTransfer') }}" method="POST">
                    @csrf
                    <input type="hidden" name="team_id" value="{{ $team->id }}">
                    <input type="hidden" name="direction" value="team_to_personal">
                    <input type="hidden" name="item_type" value="invention">

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Invento</th>
                                <th>Eficiencia</th>
                                <th>Puntos</th>
                                <th>Transferir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($teamInventory->inventions as $invention)
                            <tr>
                                <td>{{ $invention->name }}</td>
                                <td>{{ $invention->efficiency }}</td>
                                <td>{{ $invention->points }}</td>
                                <td>
                                    <input 
                                        type="checkbox" 
                                        name="items[{{ $invention->id }}]" 
                                        value="1">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-arrow-right"></i> Transferir a Mi Inventario
                        </button>
                    </div>
                </form>
                @else
                <p class="text-center">No hay inventos en el inventario del equipo.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Transferencia de Inventos del Personal al Equipo -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-secondary text-white text-center">
                <h4>Mis Inventos</h4>
            </div>
            <div class="card-body">
                @if ($userInventory && $userInventory->inventions->count() > 0)
                <form action="{{ route('teams.processTransfer') }}" method="POST">
                    @csrf
                    <input type="hidden" name="team_id" value="{{ $team->id }}">
                    <input type="hidden" name="direction" value="personal_to_team">
                    <input type="hidden" name="item_type" value="invention">

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Invento</th>
                                <th>Eficiencia</th>
                                <th>Puntos</th>
                                <th>Transferir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($userInventory->inventions as $invention)
                            <tr>
                                <td>{{ $invention->name }}</td>
                                <td>{{ $invention->efficiency }}</td>
                                <td>{{ $invention->points }}</td>
                                <td>
                                    <input 
                                        type="checkbox" 
                                        name="items[{{ $invention->id }}]" 
                                        value="1">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-arrow-left"></i> Transferir al Inventario del Equipo
                        </button>
                    </div>
                </form>
                @else
                <p class="text-center">No hay inventos en tu inventario personal.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection