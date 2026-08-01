@extends('apps')

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="mb-4">
                <h4 class="mb-1">Dashboard utilisateur</h4>
                <p class="text-muted mb-0">Vue rapide des produits disponibles.</p>
            </div>

            <div class="position-relative mb-4">
                <input type="text" id="product-search" class="form-control" placeholder="Rechercher un produit..." autocomplete="off">
                <div id="search-suggestions" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1000; top: 100%;"></div>
            </div>

            <div class="row g-3" id="product-grid">
                @foreach($items as $item)
                    <div class="col-md-4 product-card" data-search="{{ strtolower($item->name . ' ' . ($item->category ?? '') . ' ' . ($item->notes ?? '')) }}">
                        <div class="card h-100 border-0 shadow-sm overflow-hidden">
                            <div class="position-relative">
                                @if($item->image)
                                    <img src="{{ asset('storage/' . $item->image) }}" class="img-fluid w-100" style="height: 220px; object-fit: cover;">
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
                                @auth
                                    @if(Auth::user()->role !== 'admin')
                                        <div class="mt-auto">
                                            @if($item->in_cart)
                                                <button class="btn btn-secondary w-100" type="button" disabled>Déjà dans le panier</button>
                                            @else
                                                <form action="{{ route('cart.add') }}" method="POST" class="add-to-cart-form">
                                                    @csrf
                                                    <input type="hidden" name="item_id" value="{{ $item->id }}">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button class="btn btn-success w-100 add-to-cart-button" type="submit">Ajouter au panier</button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('product-search');
            const suggestions = document.getElementById('search-suggestions');
            const cards = Array.from(document.querySelectorAll('.product-card'));
            const forms = Array.from(document.querySelectorAll('.add-to-cart-form'));

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

                const visibleCards = cards.filter(card => card.style.display !== 'none');
                suggestions.innerHTML = '';

                if (!normalized) {
                    suggestions.classList.add('d-none');
                    return;
                }

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

            forms.forEach(form => {
                form.addEventListener('submit', function (event) {
                    const button = form.querySelector('.add-to-cart-button');
                    const itemTitle = form.closest('.product-card')?.querySelector('h5')?.textContent?.trim() || 'Cet article';

                    if (!button) {
                        return;
                    }

                    button.disabled = true;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-secondary');
                    button.textContent = 'Ajouté au panier';

                    const warning = document.createElement('div');
                    warning.className = 'alert alert-info py-2 px-3 mt-2 mb-0 small';
                    warning.textContent = `${itemTitle} a été ajouté à votre panier.`;
                    form.insertAdjacentElement('afterend', warning);

                    setTimeout(() => {
                        warning.remove();
                    }, 1800);
                });
            });

            document.addEventListener('click', function (event) {
                if (!input.contains(event.target) && !suggestions.contains(event.target)) {
                    suggestions.classList.add('d-none');
                }
            });
        });
    </script>
@endsection
