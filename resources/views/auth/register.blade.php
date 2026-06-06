@extends('layouts.app')

@section('title', 'Registro')

@section('content')

@include('partials.auth_navbar')

<!-- Contenedor de la imagen con el formulario centrado -->
<div class="position-relative vh-90">
    <img src="{{ asset('images/welcome/laraveland2.png') }}" alt="imagen juego" class="w-100 h-100 position-absolute" style="object-fit: cover; z-index: -1;">

    <!-- Formulario centrado con espacio extra -->
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="card shadow-lg border-0 mt-5 mb-5" style="max-width: 500px; width: 100%; background-color: rgba(255, 255, 255, 0.9);">
            <div class="card-body p-5">
                <h2 class="card-title text-center mb-4" style="font-weight: bold; color: #343a40;">Regístrate en Laraveland</h2>
                <form action="{{ route('register') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label" style="font-weight: 500;">Nombre</label>
                        <input type="text" name="name" class="form-control" placeholder="Tu nombre" value="{{ old('name') }}" required>
                        @error('name')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label" style="font-weight: 500;">Correo Electrónico</label>
                        <input type="email" name="email" class="form-control" placeholder="ejemplo@correo.com" value="{{ old('email') }}" required>
                        @error('email')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label" style="font-weight: 500;">Clave</label>
                        <input type="password" name="password" class="form-control" placeholder="********" required>
                        @error('password')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label" style="font-weight: 500;">Confirmar Clave</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="********" required>
                        @error('password_confirmation')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Selección de Equipo -->
                    <div class="mb-3">  <!-- tenia a 4 el maximo de jugadores por equipo , pero al poner el factory lo aumento para las pruebas -->
                        <label for="team_id" class="form-label" style="font-weight: 500;">Selecciona un Equipo</label>
                        <select name="team_id" id="team_id" class="form-select" required>
                            @foreach($teams as $team)
                            @if($team->users->count() < 10)
                                <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>
                                {{ $team->name }} ({{ $team->users->count() }}/10 jugadores)
                                </option>
                                @endif
                                @endforeach
                        </select>
                        @error('team_id')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Selección de Rol -->
                    <div class="mb-3">
                        <label for="role_id" class="form-label" style="font-weight: 500;">Selecciona tu Rol</label>
                        <select name="role_id" id="role_id" class="form-select" required>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('role_id')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2" style="font-size: 1.2rem;">Registrarse</button>
                </form>


                <p class="mt-4 text-center" style="font-size: 1rem;">
                    ¿Ya tienes cuenta? <a href="{{ route('login') }}" class="text-primary" style="font-weight: bold;">Inicia sesión</a>
                </p>
            </div>
        </div>
    </div>
</div>

@include('partials.auth_footer')

@endsection