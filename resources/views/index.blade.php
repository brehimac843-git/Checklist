@extends('apps')
@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Liste des produits</h4>
                    <p class="text-muted mb-0">Gestion des articles de la liste de courses.</p>
                </div>
                <a href="{{ route('shopping.create') }}" class="btn btn-primary">Ajouter un produit</a>
            </div>

            @if($items->isEmpty())
                <div class="alert alert-info mb-0">
                    Aucun produit pour le moment.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Quantité</th>
                                <th>Unité</th>
                                <th>Catégorie</th>
                                <th>Notes</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $item->unit }}</td>
                                    <td>{{ $item->category ?? '—' }}</td>
                                    <td>{{ $item->notes ?? '—' }}</td>
                                    <td class="text-end">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <a href="{{ route('shopping.edit', $item->id) }}" class="btn btn-sm btn-outline-secondary">Modifier</a>
                                            <form action="{{ route('shopping.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Supprimer cet article ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
