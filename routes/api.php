<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ConnectionController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\NotificationController;

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
    Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);
});

// Route pour l'upload d'avatar
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/upload/avatar', [UploadController::class, 'uploadAvatar']);
});

// Routes pour les connexions
Route::middleware('auth:sanctum')->prefix('connections')->group(function () {
    Route::post('/send', [ConnectionController::class, 'sendRequest']);
    Route::post('/{id}/accept', [ConnectionController::class, 'acceptRequest']);
    Route::post('/{id}/reject', [ConnectionController::class, 'rejectRequest']);
    Route::delete('/{id}/cancel', [ConnectionController::class, 'cancelRequest']);
    Route::delete('/{id}/remove', [ConnectionController::class, 'removeConnection']);
    Route::get('/my-connections', [ConnectionController::class, 'myConnections']);
    Route::get('/pending', [ConnectionController::class, 'pendingRequests']);
    Route::get('/sent', [ConnectionController::class, 'sentRequests']);
    Route::get('/status/{userId}', [ConnectionController::class, 'checkStatus']);
});

// Routes pour la messagerie
Route::middleware('auth:sanctum')->prefix('conversations')->group(function () {
    Route::get('/', [MessageController::class, 'index']);
    Route::get('/unread-count', [MessageController::class, 'unreadCount']);
    Route::get('/{userId}', [MessageController::class, 'getOrCreateConversation']);
    Route::get('/{id}/messages', [MessageController::class, 'getMessages']);
});

Route::middleware('auth:sanctum')->prefix('messages')->group(function () {
    Route::post('/', [MessageController::class, 'sendMessage']);
    Route::put('/{id}/read', [MessageController::class, 'markAsRead']);
});

// Routes pour le matching géolocalisé
Route::middleware('auth:sanctum')->prefix('matching')->group(function () {
    Route::post('/location', [MatchController::class, 'updateLocation']);
    Route::get('/nearby', [MatchController::class, 'getNearbyUsers']);
    Route::post('/like/{userId}', [MatchController::class, 'likeUser']);
    Route::post('/pass/{userId}', [MatchController::class, 'passUser']);
    Route::get('/matches', [MatchController::class, 'getMatches']);
});

// Routes pour les notifications
Route::middleware('auth:sanctum')->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::get('/unread-count', [NotificationController::class, 'getUnreadCount']);
    Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/{id}', [NotificationController::class, 'destroy']);
    Route::post('/fcm-token', [NotificationController::class, 'updateFcmToken']);
    Route::post('/send-test-push', [NotificationController::class, 'sendTestPush']);
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
