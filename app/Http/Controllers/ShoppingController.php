<?php

namespace App\Http\Controllers;

use App\Models\ShoppingItem;
use Illuminate\Http\Request;

class ShoppingController extends Controller
{
    public function index()
    {
        // Avoid querying when the table doesn't exist (e.g., in fresh test DBs)
        if (!\Illuminate\Support\Facades\Schema::hasTable('shopping_items')) {
            $items = collect();
        } else {
            $items = ShoppingItem::latest()->get();
        }

        return view('index', compact('items'));
    }

    public function create()
    {
        return view('create', ['item' => new ShoppingItem()]);
    }

    public function store(Request $request)
    {
        ShoppingItem::create($this->validateData($request));

        return redirect()->route('shopping.index')->with('success', 'Ajouté avec succès!');
    }

    public function show(ShoppingItem $shopping)
    {
        return view('welcome', ['item' => $shopping, 'items' => ShoppingItem::latest()->get()]);
    }

    public function edit(ShoppingItem $shopping)
    {
        return view('edit', ['item' => $shopping]);
    }

    public function update(Request $request, ShoppingItem $shopping)
    {
        $shopping->update($this->validateData($request));

        return redirect()->route('shopping.index')->with('success', 'Modifié avec succès!');
    }

    public function destroy(ShoppingItem $shopping)
    {
        $shopping->delete();

        return redirect()->route('shopping.index')->with('success', 'Supprimé avec succès!');
    }

    private function validateData($request)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:50',
            'category' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);
    }
}
