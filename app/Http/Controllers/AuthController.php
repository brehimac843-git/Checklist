<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showRegistrationForm()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        return redirect()->route('shopping.index')->with('success', 'Inscription réussie !');
    }

    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'Email ou mot de passe incorrect.',
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Votre compte est désactivé.',
            ]);
        }

        if (! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Email ou mot de passe incorrect.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        $this->restoreCartForAuthenticatedUser($request);

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            $this->persistCartForAuthenticatedUser($request, Auth::user());
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('shopping.index');
    }

    private function restoreCartForAuthenticatedUser(Request $request): void
    {
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();
        $sessionCart = $this->normalizeCart($request->session()->get('cart', []));
        $userCart = $this->normalizeCart($user->cart ?? []);

        if (!empty($sessionCart)) {
            $mergedCart = $this->mergeCart($userCart, $sessionCart);
            $request->session()->put('cart', $mergedCart);
            $user->forceFill(['cart' => $mergedCart])->save();

            return;
        }

        if (!empty($userCart)) {
            $request->session()->put('cart', $userCart);
        }
    }

    private function persistCartForAuthenticatedUser(Request $request, User $user): void
    {
        $cart = $this->normalizeCart($request->session()->get('cart', []));
        $user->forceFill(['cart' => $cart])->save();
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
}
