@extends('layouts.app')

@section('title', 'Registro')

@section('content')

@include('partials.auth_navbar')

<div class="auth-stage">
    <img src="{{ asset('images/welcome/laraveland2.png') }}" alt="" class="auth-stage__bg">
    <div class="auth-stage__veil"></div>

    <div class="auth-card auth-card--wide">
        <span class="auth-card__crest"><i class="fas fa-jedi" aria-hidden="true"></i></span>
        <h2 class="auth-card__title">Regístrate en Laraveland</h2>
        <p class="auth-card__sub">Forja tu leyenda</p>

        <form action="{{ route('register') }}" method="POST" class="auth-form">
            @csrf
            <div class="auth-field">
                <label for="name">Nombre</label>
                <input id="name" type="text" name="name" placeholder="Tu nombre" value="{{ old('name') }}" required>
                @error('name')<span class="auth-error">{{ $message }}</span>@enderror
            </div>

            <div class="auth-field">
                <label for="email">Correo Electrónico</label>
                <input id="email" type="email" name="email" placeholder="ejemplo@correo.com" value="{{ old('email') }}" required>
                @error('email')<span class="auth-error">{{ $message }}</span>@enderror
            </div>

            <div class="auth-grid">
                <div class="auth-field">
                    <label for="password">Clave</label>
                    <input id="password" type="password" name="password" placeholder="••••••••" required>
                    @error('password')<span class="auth-error">{{ $message }}</span>@enderror
                </div>
                <div class="auth-field">
                    <label for="password_confirmation">Confirmar Clave</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" placeholder="••••••••" required>
                </div>
            </div>

            <div class="auth-grid">
                <div class="auth-field">
                    <label for="team_id">Selecciona un Equipo</label>
                    <select name="team_id" id="team_id" required>
                        @foreach($teams as $team)
                            @if($team->users->count() < 10)
                                <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>
                                    {{ $team->name }} ({{ $team->users->count() }}/10 jugadores)
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @error('team_id')<span class="auth-error">{{ $message }}</span>@enderror
                </div>
                <div class="auth-field">
                    <label for="role_id">Selecciona tu Rol</label>
                    <select name="role_id" id="role_id" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role_id')<span class="auth-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <button type="submit" class="btn-epic auth-submit">🛡 Registrarse</button>
        </form>

        <p class="auth-foot">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
    </div>
</div>

@include('partials.auth_footer')

@endsection
