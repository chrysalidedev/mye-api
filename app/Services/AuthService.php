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

    //Retouner les info de l'utilisateur connecté
    public function user()
    {
        return auth()->user();
    }

    public function googleLogin(array $data): array
{
    $user = User::where('email', $data['email'])->first();

    if ($user) {
        // Compte bloqué
        if ($user->status === 'blocked') {
            throw ValidationException::withMessages([
                'account' => ['Votre compte est bloqué'],
            ]);
        }

        // Lier google_id si absent
        if (is_null($user->google_id)) {
            $user->update([
                'google_id' => $data['google_id'],
                'avatar'    => $data['avatar'] ?? $user->avatar,
            ]);
        }

    } else {
        // Création nouvel utilisateur
        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'google_id' => $data['google_id'],
            'avatar'    => $data['avatar'] ?? null,
            'role'      => $data['role'] ?? 'worker',
            'phone'     => $data['phone'] ?? null,
            'email_verified_at' => now(),
            'status'    => 'active',
            'password'  => null,
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



}
