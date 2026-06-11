@extends('layouts.app')

@section('title', 'Inicio de Sesión')

@section('content')

@include('partials.auth_navbar')

<div class="auth-stage">
    <img src="{{ asset('images/welcome/laraveland1.png') }}" alt="" class="auth-stage__bg">
    <div class="auth-stage__veil"></div>

    <div class="auth-card">
        <span class="auth-card__crest"><i class="fas fa-jedi" aria-hidden="true"></i></span>
        <h2 class="auth-card__title">Bienvenido a Laraveland</h2>
        <p class="auth-card__sub">Entra al reino, aventurero</p>

        @if ($errors->any())
            <div class="auth-alert">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="auth-form">
            @csrf
            <div class="auth-field">
                <label for="email">Correo Electrónico</label>
                <input id="email" type="email" name="email" placeholder="ejemplo@correo.com" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="auth-field">
                <label for="password">Clave</label>
                <input id="password" type="password" name="password" placeholder="••••••••" required>
            </div>
            <label class="auth-check">
                <input type="checkbox" name="remember"> Recuérdame
            </label>
            <button type="submit" class="btn-epic auth-submit"><i class="fas fa-khanda" aria-hidden="true"></i> Iniciar Sesión</button>
        </form>

        <p class="auth-foot">¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate</a></p>
    </div>
</div>

@include('partials.auth_footer')

@endsection
