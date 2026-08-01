<div class="form-outline mb-4">
    <input type="text" name="name" class="form-control" value="{{ old('name', $item->name ?? '') }}" />
    <label class="form-label">Nom du produit</label>
    @error('name')
        <div class="text-danger mb-2">{{ $message }}</div>
    @enderror
</div>
<div class="form-outline mb-4">
    <input type="number" name="quantity" class="form-control" value="{{ old('quantity', $item->quantity ?? '') }}" />
    <label class="form-label">Quantité</label>
    @error('quantity')
        <div class="text-danger mb-2">{{ $message }}</div>
    @enderror
</div>
<div class="form-outline mb-4">
    <input type="text" name="unit" class="form-control" value="{{ old('unit', $item->unit ?? '') }}" />
    <label class="form-label">Unité</label>
    @error('unit')
        <div class="text-danger mb-2">{{ $message }}</div>
    @enderror
</div>
<div class="form-outline mb-4">
    <input type="text" name="category" class="form-control" value="{{ old('category', $item->category ?? '') }}" />
    <label class="form-label">Catégorie (optionnel)</label>
    @error('category')
        <div class="text-danger mb-2">{{ $message }}</div>
    @enderror
</div>
<div class="form-outline mb-4">
    <textarea name="notes" class="form-control">{{ old('notes', $item->notes ?? '') }}</textarea>
    <label class="form-label">Notes (optionnel)</label>
    @error('notes')
        <div class="text-danger mb-2">{{ $message }}</div>
    @enderror
</div>
<div class="form-outline mb-4">
    <input type="file" name="image" class="form-control" accept="image/*" />
    <label class="form-label">Image du produit (optionnel)</label>
    @error('image')
        <div class="text-danger mb-2">{{ $message }}</div>
    @enderror

    @if(!empty($item->image))
        <div class="mt-3">
            <img src="{{ asset('storage/' . $item->image) }}" alt="Image du produit" class="img-thumbnail" style="max-width: 180px;" />
        </div>
    @endif
</div>
<button type="submit" class="btn btn-primary btn-block">Enregistrer</button>