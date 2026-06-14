@extends('layouts.app')

@section('title', 'Editar Perfil')

@section('content')
<div class="profile-edit">
    <div class="profile-edit__card">
        <div class="profile-edit__head">
            <span class="profile-edit__crest"><i class="fas fa-user-edit" aria-hidden="true"></i></span>
            <h1 class="profile-edit__title">Editar perfil</h1>
            <p class="profile-edit__sub">Afina tu identidad de guerrero.</p>
        </div>

        <form action="{{ route('players.update', $user->id) }}" method="POST" enctype="multipart/form-data" class="auth-form">
            @csrf
            @method('PUT')

            <div class="auth-field">
                <label for="name">Nombre de usuario</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name')<span class="auth-error">{{ $message }}</span>@enderror
            </div>

            <div class="auth-field">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email')<span class="auth-error">{{ $message }}</span>@enderror
            </div>

            <div class="auth-field">
                <label for="avatar">Avatar (opcional)</label>
                <input type="file" id="avatar" name="avatar">
                <span class="auth-hint">Imagen de perfil · JPG o PNG, hasta 2 MB.</span>
            </div>

            <div class="auth-field">
                <label for="bio">Descripción (opcional)</label>
                <textarea id="bio" name="bio" rows="3" placeholder="Cuenta tu leyenda…">{{ old('bio', $user->bio ?? '') }}</textarea>
            </div>

            <div class="profile-edit__sep"><span>Cambiar contraseña (opcional)</span></div>

            <div class="auth-field">
                <label for="current_password">Contraseña actual</label>
                <input type="password" id="current_password" name="current_password" placeholder="••••••••">
                @error('current_password')<span class="auth-error">{{ $message }}</span>@enderror
            </div>

            <div class="auth-grid">
                <div class="auth-field">
                    <label for="password">Nueva contraseña</label>
                    <input type="password" id="password" name="password" placeholder="••••••••">
                </div>
                <div class="auth-field">
                    <label for="password_confirmation">Confirmar</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••">
                </div>
            </div>

            <div class="profile-edit__actions">
                <a href="{{ route('players.show', Auth::user()->id) }}" class="btn-ghost">Volver</a>
                <button type="submit" class="btn-epic"><i class="fas fa-save" aria-hidden="true"></i> Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection
