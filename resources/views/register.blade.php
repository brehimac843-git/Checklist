@extends('apps')

@section('content')
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 540px;">
        <div class="card-body">
            <h4 class="mb-3">Créer un compte</h4>

            <form method="POST" action="{{ route('register.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Nom</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" required>
                    @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
            </form>

            <div class="mt-3 text-center">
                <p class="text-muted mb-0">
                    Vous avez déjà un compte ?
                    <a href="{{ route('userlogin') }}" class="text-primary fw-semibold">Connectez-vous ici</a>
                </p>
            </div>
        </div>
    </div>
@endsection
