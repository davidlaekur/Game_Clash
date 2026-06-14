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

            <p class="auth-hint auth-hint--center">Elegirás tu facción y tu rol al unirte a la partida.</p>

            <button type="submit" class="btn-epic auth-submit"><i class="fas fa-shield-alt" aria-hidden="true"></i> Registrarse</button>
        </form>

        <p class="auth-foot">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
    </div>
</div>

@include('partials.auth_footer')

@endsection
