<?php

namespace App\Http\Controllers;

use App\Models\ShoppingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

        $cart = $this->getCart();
        $cartItemIds = array_map('strval', array_keys($cart));

        $items->each(function ($item) use ($cartItemIds) {
            $item->in_cart = in_array((string) $item->id, $cartItemIds, true);
        });

        return view('index', compact('items'));
    }

    public function create()
    {
        abort_unless(auth()->check() && auth()->user()?->role === 'admin', 403);

        return view('create', ['item' => new ShoppingItem()]);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->check() && auth()->user()?->role === 'admin', 403);

        $data = $this->validateData($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('shopping', 'public');
        }

        ShoppingItem::create($data);

        if (auth()->check() && auth()->user()?->role === 'admin') {
            return redirect()->route('admin.dashboard')->with('success', 'Produit ajouté avec succès!');
        }

        return redirect()->route('shopping.index')->with('success', 'Ajouté avec succès!');
    }

    public function show(ShoppingItem $shopping)
    {
        return view('welcome', ['item' => $shopping, 'items' => ShoppingItem::latest()->get()]);
    }

    public function edit(Request $request, ShoppingItem $shopping)
    {
        $shopping = $this->resolveShoppingItem($shopping, $request);

        abort_unless(auth()->check() && auth()->user()?->role === 'admin', 403);

        return view('edit', ['item' => $shopping]);
    }

    public function update(Request $request, ShoppingItem $shopping)
    {
        $shopping = $this->resolveShoppingItem($shopping, $request);

        abort_unless(auth()->check() && auth()->user()?->role === 'admin', 403);

        $data = $this->validateData($request);

        if ($request->hasFile('image')) {
            if ($shopping->image) {
                Storage::disk('public')->delete($shopping->image);
            }

            $data['image'] = $request->file('image')->store('shopping', 'public');
        }

        $shopping->update($data);

        if (auth()->check() && auth()->user()?->role === 'admin') {
            return redirect()->route('admin.dashboard')->with('success', 'Produit modifié avec succès!');
        }

        return redirect()->route('shopping.index')->with('success', 'Modifié avec succès!');
    }

    public function destroy(Request $request, ShoppingItem $shopping)
    {
        $shopping = $this->resolveShoppingItem($shopping, $request);

        abort_unless(auth()->check() && auth()->user()?->role === 'admin', 403);

        $shopping->delete();

        if (auth()->check() && auth()->user()?->role === 'admin') {
            return redirect()->route('admin.dashboard')->with('success', 'Produit supprimé avec succès!');
        }

        return redirect()->route('shopping.index')->with('success', 'Supprimé avec succès!');
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'item_id' => ['required', 'exists:shopping_items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $itemId = (int) $request->input('item_id');
        $quantity = (int) $request->input('quantity', 1);
        $cart = $this->getCart();
        $cart[$itemId] = ($cart[$itemId] ?? 0) + $quantity;
        $this->putCart($cart);

        $item = ShoppingItem::findOrFail($itemId);

        return redirect()->back()->with('success', $item->name . ' a été ajouté au panier.');
    }

    public function updateCartItem(Request $request, $itemId)
    {
        $request->validate([
            'action' => ['required', 'in:increment,decrement,remove'],
        ]);

        $itemId = (int) $itemId;
        $cart = $this->getCart();

        if (!isset($cart[$itemId])) {
            return redirect()->back()->with('info', 'Ce produit n’est plus dans votre panier.');
        }

        switch ($request->input('action')) {
            case 'increment':
                $cart[$itemId] = ($cart[$itemId] ?? 0) + 1;
                break;
            case 'decrement':
                $cart[$itemId] = max(0, ($cart[$itemId] ?? 0) - 1);
                if ($cart[$itemId] === 0) {
                    unset($cart[$itemId]);
                }
                break;
            case 'remove':
                unset($cart[$itemId]);
                break;
        }

        if (empty($cart)) {
            session()->forget('cart');
        } else {
            $this->putCart($cart);
        }

        return redirect()->back()->with('success', 'Panier mis à jour.');
    }

    public function cart()
    {
        $cart = $this->getCart();
        $items = collect();

        if (!empty($cart)) {
            $itemIds = array_keys($cart);
            $items = ShoppingItem::whereIn('id', $itemIds)->get();
        }

        return view('cart', compact('items', 'cart'));
    }

    public function search(Request $request)
    {
        $query = trim((string) $request->query('query', ''));

        if ($query === '') {
            return response()->json([]);
        }

        $items = ShoppingItem::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('category', 'like', "%{$query}%")
            ->orWhere('notes', 'like', "%{$query}%")
            ->limit(8)
            ->get(['id', 'name', 'category']);

        return response()->json($items);
    }

    private function getCart(): array
    {
        $sessionCart = $this->normalizeCart(session()->get('cart', []));

        if (!Auth::check()) {
            return $sessionCart;
        }

        $userCart = $this->normalizeCart(Auth::user()->cart ?? []);

        if (!empty($sessionCart) && $sessionCart !== $userCart) {
            $mergedCart = $this->mergeCart($userCart, $sessionCart);
            $this->persistCartToUser($mergedCart);
            session()->put('cart', $mergedCart);

            return $mergedCart;
        }

        if (!empty($userCart)) {
            session()->put('cart', $userCart);
        }

        return $userCart;

    }

    private function putCart(array $cart): void
    {
        $normalizedCart = $this->normalizeCart($cart);
        session()->put('cart', $normalizedCart);

        if (Auth::check()) {
            $this->persistCartToUser($normalizedCart);
        }
    }

    private function persistCartToUser(array $cart): void
    {
        if (!Auth::check()) {
            return;
        }

        Auth::user()->forceFill(['cart' => $cart])->save();
    }

    private function mergeCart(array $existingCart, array $incomingCart): array
    {
        $mergedCart = $existingCart;

        foreach ($incomingCart as $itemId => $quantity) {
            $itemId = (int) $itemId;
            $mergedCart[$itemId] = ($mergedCart[$itemId] ?? 0) + (int) $quantity;
        }

        return $this->normalizeCart($mergedCart);
    }

    private function normalizeCart(array $cart): array
    {
        $normalized = [];

        foreach ($cart as $itemId => $quantity) {
            $itemId = (int) $itemId;
            $quantity = max(0, (int) $quantity);

            if ($quantity > 0) {
                $normalized[$itemId] = $quantity;
            }
        }

        return $normalized;
    }

    private function resolveShoppingItem($shopping, Request $request): ShoppingItem
    {
        if ($shopping instanceof ShoppingItem && $shopping->exists) {
            return $shopping;
        }

        $routeKey = $request->route('shopping');

        if ($routeKey !== null && $routeKey !== '') {
            return ShoppingItem::findOrFail((int) $routeKey);
        }

        throw new \RuntimeException('Unable to resolve shopping item.');
    }

    private function validateData($request)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:50',
            'category' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);
    }
}
