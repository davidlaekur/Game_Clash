@extends('layouts.app')

@section('title', 'Transferencia de Inventarios')

@section('content')
<div class="inv-view transfer-view">
    <a href="{{ route('teams.index') }}" class="btn-ghost inv-view__back">← Volver</a>

    <h1 class="inv-view__title">Transferencia de Inventarios</h1>

    <div class="transfer-grid">
        {{-- Materiales del equipo -> personal --}}
        <div class="panel inv-block">
            <h3 class="inv-block__title"><i class="fas fa-chess-rook" aria-hidden="true"></i> Materiales del equipo</h3>
            @if ($teamInventory && $teamInventory->materials->count() > 0)
                <form action="{{ route('teams.processTransfer') }}" method="POST">
                    @csrf
                    <input type="hidden" name="team_id" value="{{ $team->id }}">
                    <input type="hidden" name="direction" value="team_to_personal">
                    <table class="game-table">
                        <thead><tr><th>Material</th><th>Disp.</th><th>Transferir</th></tr></thead>
                        <tbody>
                            @foreach ($teamInventory->materials as $material)
                                <tr>
                                    <td>{{ $material->material->name }}</td>
                                    <td>{{ $material->quantity }}</td>
                                    <td><input type="number" name="materials[{{ $material->id }}]" max="{{ $material->quantity }}" min="0" class="transfer-input" placeholder="0"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="submit" class="btn-epic transfer-btn">→ A mi inventario</button>
                </form>
            @else
                <p class="inv-empty">No hay materiales en el inventario del equipo.</p>
            @endif
        </div>

        {{-- Materiales personal -> equipo --}}
        <div class="panel inv-block">
            <h3 class="inv-block__title"><i class="fas fa-box-open" aria-hidden="true"></i> Mis materiales</h3>
            @if ($userInventory && $userInventory->materials->count() > 0)
                <form action="{{ route('teams.processTransfer') }}" method="POST">
                    @csrf
                    <input type="hidden" name="team_id" value="{{ $team->id }}">
                    <input type="hidden" name="direction" value="personal_to_team">
                    <table class="game-table">
                        <thead><tr><th>Material</th><th>Disp.</th><th>Transferir</th></tr></thead>
                        <tbody>
                            @foreach ($userInventory->materials as $material)
                                <tr>
                                    <td>{{ $material->material->name }}</td>
                                    <td>{{ $material->quantity }}</td>
                                    <td><input type="number" name="materials[{{ $material->id }}]" max="{{ $material->quantity }}" min="0" class="transfer-input" placeholder="0"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="submit" class="btn-epic transfer-btn">← Al equipo</button>
                </form>
            @else
                <p class="inv-empty">No hay materiales en tu inventario personal.</p>
            @endif
        </div>

        {{-- Inventos del equipo -> personal --}}
        <div class="panel inv-block">
            <h3 class="inv-block__title"><i class="fas fa-chess-rook" aria-hidden="true"></i> Inventos del equipo</h3>
            @if ($teamInventory && $teamInventory->inventions->count() > 0)
                <form action="{{ route('teams.processTransfer') }}" method="POST">
                    @csrf
                    <input type="hidden" name="team_id" value="{{ $team->id }}">
                    <input type="hidden" name="direction" value="team_to_personal">
                    <input type="hidden" name="item_type" value="invention">
                    <table class="game-table">
                        <thead><tr><th>Invento</th><th>Efic.</th><th>Pts</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($teamInventory->inventions as $invention)
                                <tr>
                                    <td>{{ $invention->name }}</td>
                                    <td>{{ $invention->efficiency }}</td>
                                    <td>{{ $invention->points }}</td>
                                    <td><input type="checkbox" name="items[{{ $invention->id }}]" value="1"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="submit" class="btn-epic transfer-btn">→ A mi inventario</button>
                </form>
            @else
                <p class="inv-empty">No hay inventos en el inventario del equipo.</p>
            @endif
        </div>

        {{-- Inventos personal -> equipo --}}
        <div class="panel inv-block">
            <h3 class="inv-block__title"><i class="fas fa-box-open" aria-hidden="true"></i> Mis inventos</h3>
            @if ($userInventory && $userInventory->inventions->count() > 0)
                <form action="{{ route('teams.processTransfer') }}" method="POST">
                    @csrf
                    <input type="hidden" name="team_id" value="{{ $team->id }}">
                    <input type="hidden" name="direction" value="personal_to_team">
                    <input type="hidden" name="item_type" value="invention">
                    <table class="game-table">
                        <thead><tr><th>Invento</th><th>Efic.</th><th>Pts</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($userInventory->inventions as $invention)
                                <tr>
                                    <td>{{ $invention->name }}</td>
                                    <td>{{ $invention->efficiency }}</td>
                                    <td>{{ $invention->points }}</td>
                                    <td><input type="checkbox" name="items[{{ $invention->id }}]" value="1"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="submit" class="btn-epic transfer-btn">← Al equipo</button>
                </form>
            @else
                <p class="inv-empty">No hay inventos en tu inventario personal.</p>
            @endif
        </div>
    </div>
</div>
@endsection
