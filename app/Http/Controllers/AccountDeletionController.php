<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccountDeletionController extends Controller
{
    /**
     * Affiche la page publique de demande de suppression de compte et de données.
     */
    public function show()
    {
        return view('delete-account');
    }

    /**
     * Traite la soumission du formulaire de demande de suppression.
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'reason' => 'nullable|string|max:1000',
            'confirmation' => 'accepted',
        ], [
            'email.required' => 'Veuillez saisir votre adresse e-mail associée au compte.',
            'email.email' => 'Veuillez saisir une adresse e-mail valide.',
            'confirmation.accepted' => 'Vous devez cocher la case confirmant votre demande de suppression.',
        ]);

        $email = strtolower(trim($validated['email']));

        Log::info('Demande de suppression de compte et de données reçue via le web', [
            'email' => $email,
            'phone' => $validated['phone'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'requested_at' => now()->toDateTimeString(),
        ]);

        $user = User::where('email', $email)->first();

        if ($user) {
            Log::info('Compte correspondant trouvé pour la suppression', ['user_id' => $user->id, 'email' => $email]);
            // On peut révoquer les sessions actives
            $user->tokens()->delete();
        }

        return redirect()->route('account.delete')->with('success', 'Votre demande de suppression a bien été enregistrée pour ' . e($email) . '. Conformément à nos engagements et à la politique de Google Play, votre compte et toutes vos données associées seront supprimés dans un délai maximal de 30 jours.');
    }
}
