<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Juego Colectivo')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/islands.jsx', 'resources/js/audio.js'])
    @stack('styles') 

</head>

<body class="text-dark d-flex flex-column min-vh-100" data-music="@yield('music', '')">
    @if(!request()->is('login', 'register')) <!-- Oculta el navbar en login y register -->
    <header class="epic-header">
        <div class="container-fluid px-4">
            @include('partials.navbar')
        </div>
    </header>
    @endif

    @include('partials.flash')

    <main class="game-main flex-grow-1">
        @yield('content')
    </main>
</body>

</html> 
