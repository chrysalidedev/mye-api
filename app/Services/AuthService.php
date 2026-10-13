<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\ResetPasswordOtpNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(array $data): array
    {
        // Chercher l'utilisateur, même s'il est supprimé (soft deleted)
        $user = User::withTrashed()->where('email', $data['email'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Email ou mot de passe incorrect'],
            ]);
        }

        // Si le compte est supprimé, le restaurer après vérification du mot de passe
        $isDeleted = $user->trashed();

        // Compte bloqué
        if ($user->status === 'blocked') {
            throw ValidationException::withMessages([
                'account' => ['Votre compte est bloqué'],
            ]);
        }

        // Utilisateur Google sans mot de passe
        if (is_null($user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Connexion via Google requise'],
            ]);
        }

        // Email non vérifié
        if (!$user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['Veuillez vérifier votre adresse email avant de vous connecter.'],
            ]);
        }

        if (!Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email ou mot de passe incorrect'],
            ]);
        }

        // Restaurer le compte s'il était supprimé
        if ($isDeleted) {
            \Log::info('Restauration du compte supprimé via login', ['email' => $user->email, 'user_id' => $user->id]);
            $user->restore();
            $user->update(['status' => 'active']);
        }

        // Supprimer anciens tokens
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    //Retouner les info de l'utilisateur connecté
    public function user()
    {
        return auth()->user();
    }

    /**
     * Connexion Google ou Apple.
     *
     * $identity provient d'un jeton Firebase vérifié (FirebaseTokenVerifier) :
     * l'email et les identifiants qu'il contient font foi, jamais ceux du corps
     * de la requête. $identity n'est null que pour les anciennes versions de
     * l'app (Google uniquement, si FIREBASE_ALLOW_LEGACY_GOOGLE_LOGIN=true).
     *
     * @param string $column Colonne de l'identifiant fournisseur (google_id ou apple_id)
     */
    public function socialLogin(array $data, ?array $identity, string $column): array
    {
        if ($identity) {
            if (empty($identity['email']) || !$identity['email_verified']) {
                throw ValidationException::withMessages([
                    'email' => ['Adresse email non vérifiée par le fournisseur.'],
                ]);
            }
            $email      = $identity['email'];
            $providerId = $identity['provider_id'] ?? $data[$column] ?? null;
            $uid        = $identity['uid'];
        } else {
            $email      = $data['email'];
            $providerId = $data[$column] ?? null;
            $uid        = null;
        }

        // Chercher l'utilisateur, même s'il est supprimé (soft deleted)
        $user = null;
        if ($uid) {
            $user = User::withTrashed()->where('firebase_uid', $uid)->first();
        }
        if (!$user && $providerId) {
            $user = User::withTrashed()->where($column, $providerId)->first();
        }
        if (!$user) {
            $user = User::withTrashed()->where('email', $email)->first();
        }

        if ($user) {
            // Compte bloqué
            if ($user->status === 'blocked') {
                throw ValidationException::withMessages([
                    'account' => ['Votre compte est bloqué'],
                ]);
            }

            // Si le compte est supprimé, le restaurer
            if ($user->trashed()) {
                \Log::info('Restauration du compte supprimé', ['email' => $user->email, 'user_id' => $user->id]);
                $user->restore();
                $user->update(['status' => 'active']);
            }

            // Lier les identifiants absents
            $updates = [];
            if (is_null($user->{$column}) && $providerId) {
                $updates[$column] = $providerId;
            }
            if ($uid && is_null($user->firebase_uid)) {
                $updates['firebase_uid'] = $uid;
            }
            if (is_null($user->avatar) && !empty($data['avatar'])) {
                $updates['avatar'] = $data['avatar'];
            }
            if ($updates) {
                $user->update($updates);
            }

        } else {
            // Création nouvel utilisateur
            $user = User::create([
                'name'      => !empty($data['name']) ? $data['name'] : explode('@', $email)[0],
                'email'     => $email,
                $column     => $providerId,
                'firebase_uid' => $uid,
                'avatar'    => $data['avatar'] ?? null,
                'role'      => $data['role'] ?? 'worker',
                'phone'     => !empty($data['phone']) ? $data['phone'] : null,
                'email_verified_at' => now(),
                'status'    => 'active',
                'password'  => null,
                'company_verified' => true,
            ]);
        }

        // Nettoyage tokens
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    /**
     * Envoi du code OTP pour réinitialisation de mot de passe
     */
    public function sendResetPasswordOtp(string $email): array
    {
        $user = User::withTrashed()->where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Aucun compte n\'est associé à cette adresse email.'],
            ]);
        }

        if ($user->status === 'blocked') {
            throw ValidationException::withMessages([
                'email' => ['Votre compte est actuellement bloqué. Veuillez contacter le support.'],
            ]);
        }

        // Limitation : 60 secondes entre deux demandes
        $existing = DB::table('password_reset_tokens')->where('email', $email)->first();
        if ($existing && $existing->created_at && Carbon::parse($existing->created_at)->addSeconds(60)->isFuture()) {
            $secondsLeft = 60 - Carbon::now()->diffInSeconds(Carbon::parse($existing->created_at));
            throw ValidationException::withMessages([
                'email' => ["Veuillez patienter encore {$secondsLeft}s avant de demander un nouveau code."],
            ]);
        }

        // Génération d'un code OTP à 6 chiffres
        $code = sprintf('%06d', random_int(100000, 999999));

        Cache::forget('reset_code_attempts:' . sha1($user->email));

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token'      => Hash::make($code),
                'created_at' => now(),
            ]
        );

        try {
            $user->notify(new ResetPasswordOtpNotification($code, $user->name));
        } catch (\Throwable $e) {
            Log::error('Erreur lors de l\'envoi de l\'email OTP mot de passe oublié: ' . $e->getMessage());
            throw ValidationException::withMessages([
                'email' => ['Impossible d\'envoyer l\'email pour le moment. Veuillez réessayer.'],
            ]);
        }

        return [
            'email'   => $user->email,
            'message' => 'Un code de réinitialisation vous a été envoyé par email.',
        ];
    }

    /**
     * Vérification de la validité du code OTP
     */
    public function verifyResetCode(string $email, string $code): bool
    {
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record) {
            throw ValidationException::withMessages([
                'code' => ['Aucune demande de réinitialisation trouvée ou le code a expiré.'],
            ]);
        }

        // Validité 15 minutes
        if ($record->created_at && Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            throw ValidationException::withMessages([
                'code' => ['Ce code a expiré. Veuillez faire une nouvelle demande.'],
            ]);
        }

        if (!Hash::check($code, $record->token)) {
            // 5 essais maximum par code, sinon il faut en redemander un
            $attemptsKey = 'reset_code_attempts:' . sha1($email);
            Cache::add($attemptsKey, 0, now()->addMinutes(15));
            $attempts = (int) Cache::increment($attemptsKey);
            if ($attempts >= 5) {
                DB::table('password_reset_tokens')->where('email', $email)->delete();
                Cache::forget($attemptsKey);
                throw ValidationException::withMessages([
                    'code' => ['Trop de tentatives. Veuillez faire une nouvelle demande de code.'],
                ]);
            }

            throw ValidationException::withMessages([
                'code' => ['Le code saisi est incorrect.'],
            ]);
        }

        return true;
    }

    /**
     * Réinitialisation effective du mot de passe
     */
    public function resetPassword(array $data): array
    {
        $this->verifyResetCode($data['email'], $data['code']);

        $user = User::withTrashed()->where('email', $data['email'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Utilisateur introuvable.'],
            ]);
        }

        // Restaurer le compte s'il était soft deleted
        if ($user->trashed()) {
            Log::info('Restauration du compte lors de la réinitialisation du mot de passe', ['email' => $user->email, 'user_id' => $user->id]);
            $user->restore();
            $user->update(['status' => 'active']);
        }

        // Mise à jour du mot de passe
        $user->password = Hash::make($data['password']);

        // Marquer l'email comme vérifié si ce n'était pas fait
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        $user->save();

        // Nettoyage du token
        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        // Révoquer les anciens tokens Sanctum
        $user->tokens()->delete();

        return [
            'message' => 'Votre mot de passe a été réinitialisé avec succès.',
        ];
    }
}

