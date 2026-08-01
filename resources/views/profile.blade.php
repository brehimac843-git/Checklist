@extends('apps')

@section('content')
    <div class="mb-4">
        <h2 class="mb-0">Profil</h2>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    @if($user->profile_photo)
                        <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->name }}" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 120px; height: 120px; font-size: 2.5rem;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif

                    <h3 class="mb-1">{{ $user->name }}</h3>
                    <p class="text-muted mb-3">{{ $user->email }}</p>

                    <div class="mt-3 text-start">
                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="border rounded p-3 bg-light">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Prénom <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $user->first_name ?: $user->name) }}" required>
                                @error('first_name')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nom</label>
                                <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name ?? '') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                                <div class="form-text">L’email actuel ne peut pas être modifié.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mot de passe</label>
                                <input type="password" name="password" class="form-control" placeholder="Laissez vide pour conserver l’actuel">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Photo de profil</label>
                                <input type="file" name="profile_photo" class="form-control" accept="image/*">
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success flex-grow-1">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Panier</h5>
                    <p class="text-muted mb-0">Accédez à votre panier pour gérer vos articles sélectionnés.</p>
                    <a href="{{ route('cart') }}" class="btn btn-outline-dark mt-3">Voir le panier</a>
                </div>
            </div>

        </div>
    </div>

    <div class="mt-4 d-flex justify-content-between align-items-center">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Retour au dashboard</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-danger">Déconnexion</button>
        </form>
    </div>
@endsection
