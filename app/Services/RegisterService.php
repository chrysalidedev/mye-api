<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\ValidationException;

class RegisterService
{
    public function register(array $data): array
    {
        // Password obligatoire si pas Google
        if (empty($data['google_id']) && empty($data['password'])) {
            throw ValidationException::withMessages([
                'password' => ['Mot de passe requis sans Google'],
            ]);
        }

        try {
            DB::beginTransaction();

            $user = User::create([
                'name'      => $data['name'],
                'email'     => $data['email'],
                'phone'     => $data['phone'] ?? null,
                'role'      => $data['role'],
                'status'    => 'active',
                'password'  => isset($data['password'])
                    ? Hash::make($data['password'])
                    : null,
                'google_id' => $data['google_id'] ?? null,
                // Si connexion Google, marquer email comme vérifié
                'email_verified_at' => isset($data['google_id']) ? now() : null,
            ]);

            // Supprimer anciens tokens
            $user->tokens()->delete();

            $token = $user->createToken('auth_token')->plainTextToken;

            // Envoyer email de vérification seulement si pas Google
            if (empty($data['google_id'])) {
                event(new Registered($user));
            }

            DB::commit();

            return [
                'user'  => $user,
                'token' => $token,
                'email_verification_required' => empty($data['google_id']),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
