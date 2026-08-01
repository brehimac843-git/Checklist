@extends('apps')

@section('content')
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 960px;">
        <div class="card-body p-4 p-md-5">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <span class="badge text-bg-primary-subtle text-primary-emphasis mb-3">Shopping App</span>
                    <h1 class="fw-bold mb-3">Bienvenue</h1>
                    <p class="text-muted mb-4">
                        Connectez-vous pour accéder à votre espace personnel. Les produits ne sont visibles qu’une fois authentifié.
                    </p>

                    <div class="d-grid gap-3 mx-auto" style="max-width: 360px;">
                        <a href="{{ route('adminlogin') }}" class="btn btn-dark btn-lg">Connexion Admin</a>
                        <a href="{{ route('userlogin') }}" class="btn btn-primary btn-lg">Connexion Utilisateur</a>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg">Créer un compte</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
