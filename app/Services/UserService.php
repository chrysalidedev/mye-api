<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserService
{
    // Liste des utilisateurs
    public function list(): array
    {
        $currentUserId = auth()->id(); // Ou Auth::id()

        return User::where('role', '!=', 'admin')
                   ->where('id', '!=', $currentUserId)
                   ->where('status', '!=', 'blocked')
                   ->get()
                   ->toArray();
    }

    // get user by id (exclut les comptes bloqués sauf si c'est l'utilisateur lui-même)
    public function getById(int $id): ?User
    {
        $user = User::find($id);
        if (!$user) {
            return null;
        }

        // Si le compte est bloqué et qu'il ne s'agit pas de l'utilisateur connecté
        if ($user->status === 'blocked' && auth()->id() !== $user->id) {
            return null;
        }

        return $user;
    }

   public function updateUser(User $user, array $data): User{
       $user->update($data);
       return $user;
   }
}
