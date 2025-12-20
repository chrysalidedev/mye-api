<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(array $data): array
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Email ou mot de passe incorrect'],
            ]);
        }

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

        if (!Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email ou mot de passe incorrect'],
            ]);
        }

        // Supprimer anciens tokens
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }
}
