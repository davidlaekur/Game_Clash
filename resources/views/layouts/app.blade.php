<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Juego Colectivo')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    @vite('resources/css/app.css')
    @stack('styles') <!-- Aquí Laravel inyectará los estilos de intro.blade.php -->

</head>

<body class="text-dark d-flex flex-column min-vh-100">
    @if(!request()->is('login', 'register')) <!-- Oculta el navbar en login y register -->
    <header class="bg-black py-3">
    <div class="container">
        @include('partials.navbar')
    </div>
</header>

    @endif

    <!-- Main content con flex-grow-1 empujo el footer hacia abajo se me quedaba en medio de la página-->
    <main class="container my-5 flex-grow-1">
        
    <!-- Mostrar mensajes flash -->
<!--     @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-info">
            {{ session('info') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif -->

    <!-- Contenido principal -->
    @yield('content')
</main>
        
    </main>

    @if(!request()->is('login', 'register')) <!-- Oculta el footer en login y register -->
    <!-- Footer -->
    <footer>
        @include('partials.footer')
    </footer>
    @endif
</body>

</html> 