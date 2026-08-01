@extends('apps')

@section('content')
    <div class="mb-4">
        <h3 class="mb-1">Ajouter un utilisateur</h3>
        <p class="text-muted mb-0">Créez un compte utilisateur ou administrateur depuis cette page.</p>
    </div>

    <div class="card shadow-sm border-0 mx-auto" style="max-width: 720px;">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.create') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Nom</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Rôle</label>
                    <select name="role" class="form-select">
                        <option value="user">Utilisateur</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Créer l’utilisateur</button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Retour</a>
                </div>
            </form>
        </div>
    </div>
@endsection
