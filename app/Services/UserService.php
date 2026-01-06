<?php

namespace App\Services;

use App\Models\User;

class UserService
{
   // Liste des utilisateurs
public function list(): array
{
    $currentUserId = auth()->id(); // Ou Auth::id()

    return User::where('role', '!=', 'admin')
               ->where('id', '!=', $currentUserId)
               ->get()
               ->toArray();
}

   //get user by id
   public function getById(int $id): ?User{
       return User::find($id);
   }
}
