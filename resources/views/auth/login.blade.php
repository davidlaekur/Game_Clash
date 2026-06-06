@extends('layouts.app')

@section('title', 'Inicio de Sesión')

@section('content')

@include('partials.auth_navbar')

<!-- Contenedor de la imagen con el formulario centrado -->
<div class="position-relative vh-90">
    <img src="{{ asset('images/welcome/laraveland1.png') }}" alt="imagen juego" class="w-100 h-100 position-absolute" style="object-fit: cover; z-index: -1;">
    
    <!-- Formulario centrado con espacio extra -->
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="card shadow-lg border-0 mt-5 mb-5" style="max-width: 500px; width: 100%; background-color: rgba(255, 255, 255, 0.9);">
            <div class="card-body p-5">
                <h2 class="card-title text-center mb-4" style="font-weight: bold; color: #343a40;">Bienvenido a Laraveland</h2>
                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label" style="font-weight: 500;">Correo Electrónico</label>
                        <input type="email" name="email" class="form-control" placeholder="ejemplo@correo.com" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label" style="font-weight: 500;">Clave</label>
                        <input type="password" name="password" class="form-control" placeholder="********" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="remember" class="form-check-input">
                        <label class="form-check-label" for="remember" style="font-weight: 500;">Recuérdame</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2" style="font-size: 1.2rem;">Iniciar Sesión</button>
                </form>
                <p class="mt-4 text-center" style="font-size: 1rem;">
                    ¿No tienes cuenta? <a href="{{ route('register') }}" class="text-primary" style="font-weight: bold;">Regístrate</a>
                </p>
            </div>
        </div>
    </div>
</div>

@include('partials.auth_footer')

@endsection
