<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminSubscriptionController;
use App\Http\Controllers\AccountDeletionController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/privacy-policy', function () {
    return view('privacy-policy');
})->name('privacy-policy');

// Suppression de compte et des données (Exigence Google Play & RGPD)
Route::get('/delete-account', [AccountDeletionController::class, 'show'])->name('account.delete');
Route::post('/delete-account', [AccountDeletionController::class, 'submit'])->name('account.delete.submit');

// Alias pour éviter toute erreur 404 quel que soit le lien déclaré sur Google Play Console
Route::get('/delete-data', [AccountDeletionController::class, 'show']);
Route::get('/data-deletion', [AccountDeletionController::class, 'show']);
Route::get('/account-deletion', [AccountDeletionController::class, 'show']);
Route::get('/suppression-compte', [AccountDeletionController::class, 'show']);

// --- ADMINISTRATION MYE ---
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::middleware(['web', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Utilisateurs
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('/users/{user}/toggle-verify', [AdminUserController::class, 'toggleVerifyCompany'])->name('users.toggle-verify');

    // Abonnements & Finances
    Route::get('/subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');

    // Matchs & Relations
    Route::get('/matches', [AdminMatchController::class, 'index'])->name('matches.index');
});

