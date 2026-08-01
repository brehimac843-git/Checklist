<?php

namespace App\Http\Controllers;

use App\Models\ShoppingItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function adminDashboard()
    {
        return view('admin.dashboard', [
            'items' => ShoppingItem::latest()->get(),
            'totalProducts' => ShoppingItem::count(),
            'totalUsers' => User::where('role', '!=', 'admin')->count(),
            'users' => User::where('role', '!=', 'admin')->orderBy('name')->get(),
        ]);
    }

    public function userDashboard()
    {
        $items = ShoppingItem::latest()->get();
        $cart = $this->getCartForCurrentUser();
        $cartItemIds = array_map('strval', array_keys($cart));

        $items->each(function ($item) use ($cartItemIds) {
            $item->in_cart = in_array((string) $item->id, $cartItemIds, true);
        });

        return view('dashboard', [
            'items' => $items,
        ]);
    }

    public function createUserPage()
    {
        return view('admin.create-user');
    }

    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['nullable', 'in:user,admin'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'user',
            'is_active' => true,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Utilisateur ajouté.');
    }

    public function toggleUserStatus(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);

        return redirect()->route('admin.dashboard')->with('success', 'Statut utilisateur mis à jour.');
    }

    public function deleteUser(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.dashboard')->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return redirect()->route('admin.dashboard')->with('success', 'Utilisateur supprimé.');
    }

    public function adminLoginForm()
    {
        return view('admin.login');
    }

    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credentials['is_active'] = true;

        $user = User::where('email', $credentials['email'])->first();

        if (! $user) {
            return back()->withErrors([
                'email' => 'Email ou mot de passe incorrect.',
            ]);
        }

        if (! $user->is_active) {
            return back()->withErrors([
                'email' => 'Votre compte est désactivé.',
            ]);
        }

        if (! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Email ou mot de passe incorrect.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        Auth::logout();

        return back()->withErrors([
            'email' => 'Accès réservé à l’admin.',
        ]);
    }

    private function getCartForCurrentUser(): array
    {
        $sessionCart = session()->get('cart', []);

        if (!Auth::check()) {
            return $sessionCart;
        }

        $userCart = Auth::user()->cart ?? [];

        if (!empty($sessionCart) && $sessionCart !== $userCart) {
            $mergedCart = [];

            foreach ($userCart as $itemId => $quantity) {
                $mergedCart[$itemId] = (int) $quantity;
            }

            foreach ($sessionCart as $itemId => $quantity) {
                $itemId = (int) $itemId;
                $mergedCart[$itemId] = ($mergedCart[$itemId] ?? 0) + (int) $quantity;
            }

            session()->put('cart', $mergedCart);
            Auth::user()->forceFill(['cart' => $mergedCart])->save();

            return $mergedCart;
        }

        return is_array($userCart) ? $userCart : [];
    }
}
