@extends('apps')

@section('content')
    <div class="mb-4">
        <h3 class="mb-1">Tableau de bord admin</h3>
        <p class="text-muted mb-0">Gestion complète des produits et utilisateurs.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted">Produits</div>
                    <div class="display-6 fw-semibold">{{ $totalProducts }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted">Utilisateurs</div>
                    <div class="display-6 fw-semibold">{{ $totalUsers }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted">Statut</div>
                    <div class="display-6 fw-semibold">Admin</div>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active" href="#products" data-bs-toggle="tab">Produits</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#users" data-bs-toggle="tab">Utilisateurs</a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="products">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Produits récents</h5>
                        <a href="{{ route('shopping.create') }}" class="btn btn-primary">Ajouter un produit</a>
                    </div>
                    <div class="row g-3">
                        @foreach($items as $item)
                            <div class="col-md-6 col-xl-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body d-flex flex-column">
                                        @if($item->image)
                                            <img src="{{ asset('storage/' . $item->image) }}" class="img-fluid rounded mb-3" style="height: 160px; width: 100%; object-fit: cover;">
                                        @endif
                                        <h6 class="fw-semibold mb-2">{{ $item->name }}</h6>
                                        <p class="text-muted mb-1">Catégorie : {{ $item->category ?? '—' }}</p>
                                        <p class="text-muted mb-0">{{ $item->notes ?? '—' }}</p>
                                        <div class="mt-auto d-flex gap-2 flex-wrap pt-3">
                                            <a href="{{ route('shopping.edit', $item->id) }}" class="btn btn-sm btn-outline-secondary">Modifier</a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteProductModal-{{ $item->id }}">Supprimer</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="deleteProductModal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Confirmer la suppression</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="mb-0">Êtes-vous sûr de vouloir supprimer le produit <strong>{{ $item->name }}</strong> ?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <form method="POST" action="{{ route('shopping.destroy', $item->id) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Supprimer</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="users">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Gestion des utilisateurs</h5>
                        <a href="{{ route('admin.users.create.page') }}" class="btn btn-primary">Ajouter</a>
                    </div>

                    <div class="row g-3">
                        @foreach($users as $user)
                            <div class="col-md-6 col-xl-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body d-flex flex-column">
                                        <h6 class="fw-semibold mb-2">{{ $user->name }}</h6>
                                        <p class="text-muted mb-1">{{ $user->email }}</p>
                                        <p class="text-muted mb-3">Rôle : {{ $user->role }}</p>
                                        <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-secondary' }} mb-3 align-self-start">
                                            {{ $user->is_active ? 'Actif' : 'Désactivé' }}
                                        </span>
                                        <div class="mt-auto d-flex gap-2 flex-wrap">
                                            <form method="POST" action="{{ route('admin.users.toggle', $user) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                                    {{ $user->is_active ? 'Désactiver' : 'Réactiver' }}
                                                </button>
                                            </form>
                                            @if($user->id !== auth()->id())
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal-{{ $user->id }}">Supprimer</button>

                                                <div class="modal fade" id="deleteUserModal-{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Confirmer la suppression</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p class="mb-0">Êtes-vous sûr de vouloir supprimer l'utilisateur <strong>{{ $user->name }}</strong> ?</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                <form method="POST" action="{{ route('admin.users.delete', $user) }}" class="d-inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger">Supprimer</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
