@extends('apps')
@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Modifier un produit</h4>
                    <p class="text-muted mb-0">Mettez à jour les informations du produit.</p>
                </div>
                <a href="{{ route('shopping.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>

            <form action="{{ route('shopping.update', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('_form', ['item' => $item])
            </form>
        </div>
    </div>
@endsection