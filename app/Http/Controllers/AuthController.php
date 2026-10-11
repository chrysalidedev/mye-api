<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Services\RegisterService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request, AuthService $authService)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $data = $authService->login($validator->validated());

            return response()->json([
                'message' => 'Connexion réussie',
                'data'    => $data,
                'success' => true
            ], 200);

        } catch (ValidationException $e) {

            return response()->json([
                'message' => 'Échec de connexion',
                'errors'  => $e->errors(),
            ], 401);
        }
    }


   public function register(Request $request, RegisterService $registerService)
{
    $validator = Validator::make($request->all(), [
        'name'      => 'required|string|max:255',
        'email'     => 'required|email|unique:users,email',
        'phone'     => 'nullable|string|unique:users,phone',
        'password'  => 'nullable|min:8|confirmed',
        'role'      => 'required|in:worker,manager',
        'profession' => 'nullable|string|max:150',
        'google_id' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        Log::alert('Validation errors: ' . $validator->errors());
        return response()->json([
            'message' => 'Erreur de validation',
            'errors'  => $validator->errors(),
        ], 422);
    }

    try {
        $data = $registerService->register($validator->validated());

        $message = $data['email_verification_required']
            ? 'Inscription réussie. Veuillez vérifier votre email.'
            : 'Inscription réussie';

        return response()->json([
            'message' => $message,
            'data'    => $data,
            'success' => true
        ], 201);

    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Erreur lors de l\'inscription',
            'errors'  => $e->errors(),
        ], 422);
    }
}

public function user(AuthService $authService){
    $user = $authService->user();
    return response()->json([
        'message' => 'Informations de l\'utilisateur connecté',
        'data'    => array_merge(
            is_array($user) ? $user : $user->toArray(),
            ['is_subscribed' => auth()->user()->hasActiveSubscription()]
        ),
        'success' => true
    ], 200);
}

public function googleLogin(Request $request, AuthService $authService)
{
    $validator = Validator::make($request->all(), [
        'name'      => 'required|string|max:255',
        'email'     => 'required|email',
        'google_id' => 'required|string',
        'role'      => 'nullable|in:worker,manager',
        'phone'     => 'nullable|string|max:20',
        'avatar'    => 'nullable|string|max:500',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Erreur de validation',
            'errors'  => $validator->errors(),
            "success" => false
        ], 422);
    }

    try {
        $data = $authService->googleLogin($validator->validated());

        return response()->json([
            'message' => 'Connexion Google réussie',
            'data'    => $data,
            'success' => true
        ], 200);

    } catch (ValidationException $e) {

        return response()->json([
            'message' => 'Connexion refusée',
            'errors'  => $e->errors(),
            "success" => false
        ], 401);
    }
}

public function deleteAccount(Request $request)
{
    try {
        $user = $request->user();
        
        Log::info('Tentative de suppression du compte', ['user_id' => $user ? $user->id : null]);
        
        if (!$user) {
            Log::warning('Utilisateur non authentifié lors de la suppression');
            return response()->json([
                'message' => 'Utilisateur non authentifié',
                'success' => false
            ], 401);
        }

        // Révoquer tous les tokens de l'utilisateur
        Log::info('Révocation des tokens', ['user_id' => $user->id]);
        $user->tokens()->delete();

        // Soft delete de l'utilisateur
        Log::info('Soft delete de l\'utilisateur', ['user_id' => $user->id]);
        $user->delete();

        Log::info('Compte supprimé avec succès', ['user_id' => $user->id]);
        
        return response()->json([
            'message' => 'Compte supprimé avec succès',
            'success' => true
        ], 200);

    } catch (\Exception $e) {
        Log::error('Erreur lors de la suppression du compte: ' . $e->getMessage(), [
            'exception' => $e,
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'message' => 'Erreur lors de la suppression du compte: ' . $e->getMessage(),
            'success' => false
        ], 500);
    }
}

    /**
     * Demande d'envoi du code de réinitialisation
     */
    public function forgotPassword(Request $request, AuthService $authService)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Adresse email invalide.',
                'errors'  => $validator->errors(),
                'success' => false,
            ], 422);
        }

        try {
            $result = $authService->sendResetPasswordOtp($request->email);

            return response()->json([
                'message' => $result['message'],
                'data'    => ['email' => $result['email']],
                'success' => true,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->validator->errors()->first() ?: 'Erreur de réinitialisation',
                'errors'  => $e->errors(),
                'success' => false,
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Erreur forgotPassword: ' . $e->getMessage());
            return response()->json([
                'message' => 'Une erreur est survenue lors du traitement de votre demande.',
                'success' => false,
            ], 500);
        }
    }

    /**
     * Vérification du code OTP
     */
    public function verifyResetCode(Request $request, AuthService $authService)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code'  => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Le code et l\'adresse email sont requis.',
                'errors'  => $validator->errors(),
                'success' => false,
            ], 422);
        }

        try {
            $authService->verifyResetCode($request->email, $request->code);

            return response()->json([
                'message' => 'Code vérifié avec succès.',
                'success' => true,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->validator->errors()->first() ?: 'Code invalide ou expiré.',
                'errors'  => $e->errors(),
                'success' => false,
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Erreur verifyResetCode: ' . $e->getMessage());
            return response()->json([
                'message' => 'Une erreur est survenue lors de la vérification du code.',
                'success' => false,
            ], 500);
        }
    }

    /**
     * Réinitialisation effective du mot de passe
     */
    public function resetPassword(Request $request, AuthService $authService)
    {
        $validator = Validator::make($request->all(), [
            'email'                 => 'required|email',
            'code'                  => 'required|string|size:6',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors'  => $validator->errors(),
                'success' => false,
            ], 422);
        }

        try {
            $result = $authService->resetPassword($validator->validated());

            return response()->json([
                'message' => $result['message'],
                'success' => true,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->validator->errors()->first() ?: 'Erreur de réinitialisation',
                'errors'  => $e->errors(),
                'success' => false,
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Erreur resetPassword: ' . $e->getMessage());
            return response()->json([
                'message' => 'Une erreur est survenue lors de la mise à jour du mot de passe.',
                'success' => false,
            ], 500);
        }
    }
}
