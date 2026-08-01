@extends('apps')

@section('content')
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 520px;">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <h3 class="fw-bold mb-1">Admin Login</h3>
                <p class="text-muted mb-0">Connexion réservée à l’administration.</p>
            </div>

            <form method="POST" action="{{ route('adminlogin.store') }}">
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

                <button type="submit" class="btn btn-dark w-100">Se connecter</button>
            </form>
        </div>
    </div>
@endsection
