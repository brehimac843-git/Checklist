<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                @auth
                    @if(Auth::user()->role !== 'admin')
                        <a href="{{ route('profile') }}" class="d-flex align-items-center gap-2 text-decoration-none text-light me-3">
                            @if(Auth::user()->profile_photo)
                                <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="Profil" class="rounded-circle" style="width: 34px; height: 34px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-light text-dark d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-size: 0.9rem; font-weight: 600;">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                            @endif
                            <span class="fw-semibold">{{ Auth::user()->name }}</span>
                        </a>
                    @endif
                @endauth
                <div>
                    <span class="navbar-brand mb-0 h1">Shoppy+</span>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @guest
                @endguest
            </div>
        </div>
    </nav>

    <div class="container py-4">
        @if (session('success'))
            <div class="alert alert-success mb-4">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')

        @auth
            @unless(request()->routeIs('profile'))
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-dark {{ request()->routeIs('admin.dashboard') ? 'btn-success text-white' : '' }}">Dashboard Admin</a>
                    @else
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-dark {{ request()->routeIs('dashboard') ? 'btn-success text-white' : '' }}">Dashboard</a>
                        <a href="{{ route('cart') }}" class="btn btn-outline-dark {{ request()->routeIs('cart') ? 'btn-success text-white' : '' }}">Panier</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">Déconnexion</button>
                    </form>
                </div>
            @endunless
        @endauth
    </div>

    @yield('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>