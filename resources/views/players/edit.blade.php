@extends('layouts.app')

@section('title', 'Editar Perfil')

@section('content')
<div class="container mt-4">

    <!-- volver  -->
    <div class="mt-4">
        <a href="{{ route('players.show', Auth::user()->id) }}" class="btn btn-primary">Volver</a>
    </div>

    <form action="{{ route('players.update', $user->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Nombre de usuario -->
        <div class="form-group mt-5">
            <label for="name">Nombre de Usuario</label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
        </div>
        <!-- mail-->
        <div class="form-group mt-4">
            <label for="email">Correo Electrónico</label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
        </div>

        <!-- avatar -->
        <div class="form-group mt-4">
            <label for="avatar">Avatar (Opcional)</label>
            <input type="file" id="avatar" name="avatar" class="form-control">
            <small class="text-muted">Imagen de perfil (JPG, PNG, hasta 2MB)</small>
        </div>

        <!-- Descripción del usuario -->
        <div class="form-group mt-4">
            <label for="bio" class="form-label">Descripción (Opcional)</label>
            <textarea id="bio" name="bio" class="form-control" rows="4" placeholder="Describe algo sobre ti...">{{ old('bio', $user->bio ?? '') }}</textarea>
        </div>

        <!-- Contraseña actual -->
        <div class="form-group mt-4">
            <label for="current_password">Contraseña Actual</label>
            <input type="password" id="current_password" name="current_password" class="form-control">
            @error('current_password')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Nueva contraseña -->
        <div class="form-group mt-4">
            <label for="password">Nueva Contraseña</label>
            <input type="password" id="password" name="password" class="form-control">
        </div>

        <!-- Confirmar nueva contraseña -->
        <div class="form-group mt-4">
            <label for="password_confirmation">Confirmar Nueva Contraseña</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
        </div>

        <!-- Actualizar perfil -->
        <button type="submit" class="btn btn-success mt-4">Actualizar Perfil</button>
    </form>
</div>
@endsection