@extends('apps')

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="mb-4">
                <h4 class="mb-1">Votre panier</h4>
                <p class="text-muted mb-0">Voici les produits que vous avez ajoutés.</p>
            </div>

            @if($items->isEmpty())
                <div class="alert alert-info mb-0">
                    Votre panier est vide pour le moment.
                </div>
            @else
                <div class="row g-3">
                    @foreach($items as $item)
                        @php($quantityInCart = $cart[$item->id] ?? 1)
                        <div class="col-md-6 col-xl-4">
                            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                                <div class="position-relative">
                                    @if($item->image)
                                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="img-fluid w-100" style="height: 220px; object-fit: cover;">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 220px;">
                                            <span class="text-muted">Aucune image</span>
                                        </div>
                                    @endif

                                    <div class="position-absolute bottom-0 start-0 end-0 p-3" style="background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.78) 100%);">
                                        <div class="d-flex justify-content-between align-items-end text-white">
                                            <h6 class="mb-0">{{ $item->name }}</h6>
                                            <span class="badge bg-light text-dark">{{ $item->category ?? 'Sans catégorie' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted small">Quantité</span>
                                        <span class="badge bg-dark-subtle text-dark fw-semibold">{{ $quantityInCart }}</span>
                                    </div>
                                    <div class="mt-auto d-flex gap-2 flex-wrap">
                                        <form action="{{ route('cart.update', $item->id) }}" method="POST" class="d-flex">
                                            @csrf
                                            <input type="hidden" name="action" value="decrement">
                                            <button class="btn btn-outline-secondary btn-sm" type="submit">−</button>
                                        </form>
                                        <form action="{{ route('cart.update', $item->id) }}" method="POST" class="d-flex">
                                            @csrf
                                            <input type="hidden" name="action" value="increment">
                                            <button class="btn btn-outline-secondary btn-sm" type="submit">+</button>
                                        </form>
                                        <button class="btn btn-outline-danger btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#removeCartItemModal-{{ $item->id }}">Supprimer</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="removeCartItemModal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Retirer du panier</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="mb-0">Êtes-vous sûr de vouloir retirer <strong>{{ $item->name }}</strong> de votre panier ?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <form action="{{ route('cart.update', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="action" value="remove">
                                            <button class="btn btn-danger" type="submit">Retirer</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
