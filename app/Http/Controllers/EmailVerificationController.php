<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmailVerificationController extends Controller
{
    /**
     * Vérifier l'email via URL signée
     */
    public function verify(Request $request, string $id, string $hash): JsonResponse
    {
        // Vérifier la validité de la signature
        if (!$request->hasValidSignature()) {
            return response()->json([
                'message' => 'Lien de vérification invalide ou expiré',
                'verified' => false,
            ], 403);
        }

        $user = User::findOrFail($id);

        // Vérifier le hash
        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => 'Lien de vérification invalide',
                'verified' => false,
            ], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email déjà vérifié',
                'verified' => true,
            ], 200);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json([
            'message' => 'Email vérifié avec succès ! Vous pouvez maintenant vous connecter.',
            'verified' => true,
            'success' => true,
        ], 200);
    }

    /**
     * Renvoyer l'email de vérification
     */
    public function resend(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email déjà vérifié',
                'verified' => true,
            ], 200);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Email de vérification renvoyé avec succès',
            'success' => true,
        ], 200);
    }

    /**
     * Vérifier le statut de vérification
     */
    public function status(Request $request): JsonResponse
    {
        return response()->json([
            'email_verified' => $request->user()->hasVerifiedEmail(),
            'email' => $request->user()->email,
            'name' => $request->user()->name,
        ], 200);
    }
}
