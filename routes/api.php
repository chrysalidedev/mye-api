<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\EmailVerificationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('auth')->group(function () {
    Route::post('/google-login',[AuthController::class,'googleLogin']);
    Route::post('/login',[AuthController::class,'login']);
    Route::post('/register',[AuthController::class,'register']);
});

Route::group([
    'prefix' => 'users',
    'middleware' => 'auth:sanctum',
], function () {
    Route::get('/', [App\Http\Controllers\UserController::class, 'index']);
    Route::get('/user',[AuthController::class,'user']);
    Route::get('/{id}', [App\Http\Controllers\UserController::class, 'show']);
    Route::put('/update', [App\Http\Controllers\UserController::class, 'updateUser']);
});


// Routes protégées par auth:sanctum
// Route::middleware('auth:sanctum')->group(function () {

//     // Vérification email
//     Route::prefix('email')->group(function () {
//         Route::get('/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
//             ->middleware(['signed'])
//             ->name('verification.verify');

//         Route::post('/verification-notification', [EmailVerificationController::class, 'resend'])
//             ->middleware(['throttle:6,1'])
//             ->name('verification.send');

//         Route::get('/verification-status', [EmailVerificationController::class, 'status']);
//     });

//     // Routes nécessitant email vérifié
//     Route::middleware('verified')->group(function () {
//         // Vos routes protégées ici
//     });
// });
// Route de vérification email (publique mais signée)
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    // Renvoyer email de vérification
    Route::post('/email/resend', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Statut de vérification
    Route::get('/email/status', [EmailVerificationController::class, 'status']);

    // Routes nécessitant email vérifié
    Route::middleware('verified')->group(function () {
        // Vos routes protégées ici
        Route::get('/profile', function (Request $request) {
            return response()->json($request->user());
        });
    });

    Route::post('/logout', [AuthController::class, 'logout']);
});
