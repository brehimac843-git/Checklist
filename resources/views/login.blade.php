@extends('apps')

@section('content')
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 540px;">
        <div class="card-body">
            <h4 class="mb-3">Connexion</h4>
            <p class="text-muted small mb-3">Utilisez cette page pour vous connecter en tant qu’administrateur ou utilisateur. Une fois authentifié, vous serez redirigé vers votre tableau de bord.</p>

            <form method="POST" action="{{ route('userlogin.store') }}">
                @csrf

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

                <button type="submit" class="btn btn-primary w-100">Se connecter</button>
            </form>

            <div class="mt-3 text-center">
                <p class="text-muted mb-0">
                    Vous n’avez pas de compte ?
                    <a href="{{ route('register') }}" class="text-primary fw-semibold">Créer un compte</a>
                </p>
            </div>
        </div>
    </div>
@endsection
