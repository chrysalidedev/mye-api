<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;

class EmailVerificationController extends Controller
{
    /**
     * Vérifier l'email via URL signée
     */
    public function verify(Request $request, string $id, string $hash): View
    {
        if (!$request->hasValidSignature()) {
            return view('emails.verify-error', [
                'message' => 'Ce lien de vérification est invalide ou a expiré.',
            ]);
        }

        $user = User::findOrFail($id);

        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return view('emails.verify-error', [
                'message' => 'Ce lien de vérification est invalide.',
            ]);
        }

        if (!$user->hasVerifiedEmail()) {
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }
        }

        return view('emails.verify-success', [
            'name' => $user->name,
        ]);
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
