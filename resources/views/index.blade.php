@extends('apps')
@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Liste des produits</h4>
                    <p class="text-muted mb-0">Parcourez les produits disponibles et ajoutez-les à votre panier.</p>
                </div>
                @auth
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('shopping.create') }}" class="btn btn-primary">Ajouter un produit</a>
                    @endif
                @endauth
            </div>

            <div class="position-relative mb-4">
                <input type="text" id="product-search" class="form-control" placeholder="Rechercher un produit..." autocomplete="off">
                <div id="search-suggestions" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1000; top: 100%;"></div>
            </div>

            @if($items->isEmpty())
                <div class="alert alert-info mb-0">
                    Aucun produit pour le moment.
                </div>
            @else
                <div class="row g-3" id="product-grid">
                    @foreach($items as $item)
                        <div class="col-md-6 col-xl-4 product-card" data-search="{{ strtolower($item->name . ' ' . ($item->category ?? '') . ' ' . ($item->notes ?? '')) }}">
                            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                                <div class="position-relative">
                                    @if($item->image)
                                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="img-fluid w-100" style="height: 220px; object-fit: cover;" />
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
                                    <div class="mt-auto">
                                        @auth
                                            @if(Auth::user()->role === 'admin')
                                                <div class="d-flex gap-2 flex-wrap">
                                                    <a href="{{ route('shopping.edit', $item->id) }}" class="btn btn-sm btn-outline-secondary">Modifier</a>
                                                    <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteItemModal-{{ $item->id }}">Supprimer</button>
                                                </div>

                                                <div class="modal fade" id="deleteItemModal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
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
                                                                <form action="{{ route('shopping.destroy', $item->id) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button class="btn btn-danger" type="submit">Supprimer</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                @if($item->in_cart)
                                                    <button class="btn btn-sm btn-secondary w-100" type="button" disabled>Déjà dans le panier</button>
                                                @else
                                                    <form action="{{ route('cart.add') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="item_id" value="{{ $item->id }}">
                                                        <input type="hidden" name="quantity" value="1">
                                                        <button class="btn btn-sm btn-success w-100" type="submit">Ajouter</button>
                                                    </form>
                                                @endif
                                            @endif
                                        @endauth

                                        @guest
                                            <span class="text-muted small">Connectez-vous pour acheter</span>
                                        @endguest
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

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('product-search');
            const suggestions = document.getElementById('search-suggestions');
            const cards = Array.from(document.querySelectorAll('.product-card'));

            if (!input || !suggestions) {
                return;
            }

            function filterProducts(query) {
                const normalized = query.trim().toLowerCase();

                cards.forEach(card => {
                    const searchable = card.dataset.search || '';
                    const matches = searchable.includes(normalized);
                    card.style.display = matches ? '' : 'none';
                });

                suggestions.innerHTML = '';

                if (!normalized) {
                    suggestions.classList.add('d-none');
                    return;
                }

                const visibleCards = cards.filter(card => card.style.display !== 'none');

                if (!visibleCards.length) {
                    suggestions.classList.remove('d-none');
                    suggestions.innerHTML = '<div class="list-group-item text-muted">Aucun produit trouvé</div>';
                    return;
                }

                suggestions.classList.remove('d-none');
                visibleCards.slice(0, 5).forEach(card => {
                    const title = card.querySelector('h5')?.textContent?.trim() || 'Produit';
                    const option = document.createElement('button');
                    option.type = 'button';
                    option.className = 'list-group-item list-group-item-action';
                    option.textContent = title;
                    option.addEventListener('click', function () {
                        input.value = title;
                        filterProducts(title);
                        suggestions.classList.add('d-none');
                    });
                    suggestions.appendChild(option);
                });
            }

            input.addEventListener('input', function () {
                filterProducts(this.value);
            });

            input.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    const firstVisible = cards.find(card => card.style.display !== 'none');
                    if (firstVisible) {
                        const title = firstVisible.querySelector('h5')?.textContent?.trim() || 'Produit';
                        input.value = title;
                        filterProducts(title);
                    }
                    suggestions.classList.add('d-none');
                }
            });

            document.addEventListener('click', function (event) {
                if (!input.contains(event.target) && !suggestions.contains(event.target)) {
                    suggestions.classList.add('d-none');
                }
            });
        });
    </script>
@endsection
