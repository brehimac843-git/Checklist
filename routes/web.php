<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShoppingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('welcome');
})->name('home');
Route::get('/dashboard', [DashboardController::class, 'userDashboard'])->name('dashboard');

Route::get('/adminlogin', [DashboardController::class, 'adminLoginForm'])->name('adminlogin');
Route::post('/adminlogin', [DashboardController::class, 'adminLogin'])->name('adminlogin.store');
Route::get('/userlogin', [AuthController::class, 'showLoginForm'])->name('userlogin');
Route::post('/userlogin', [AuthController::class, 'login'])->name('userlogin.store');

Route::get('/admin/login', function () {
    return redirect()->route('adminlogin');
})->name('admin.login');
Route::post('/admin/login', [DashboardController::class, 'adminLogin'])->name('admin.login.store');
Route::get('/login', function () {
    return redirect()->route('userlogin');
})->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');

Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
Route::get('/admin/users/create', [DashboardController::class, 'createUserPage'])->name('admin.users.create.page');
Route::post('/admin/users', [DashboardController::class, 'createUser'])->name('admin.users.create');
Route::post('/admin/users/{user}/toggle', [DashboardController::class, 'toggleUserStatus'])->name('admin.users.toggle');
Route::delete('/admin/users/{user}', [DashboardController::class, 'deleteUser'])->name('admin.users.delete');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});
Route::middleware('auth')->group(function () {
    Route::get('/cart', [ShoppingController::class, 'cart'])->name('cart');
    Route::post('/cart/add', [ShoppingController::class, 'addToCart'])->name('cart.add');
    Route::post('/cart/update/{itemId}', [ShoppingController::class, 'updateCartItem'])->name('cart.update');
    Route::get('/products/search', [ShoppingController::class, 'search'])->name('products.search');
    Route::resource('shopping', ShoppingController::class);
});
