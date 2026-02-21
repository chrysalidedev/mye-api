<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    /**
     * Upload avatar de l'utilisateur
     */
    public function uploadAvatar(Request $request)
    {
        // Validation
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ]);



        try {
            $user = auth()->user();
                      Log::info('User: ' . $user);
        Log::info('Avatar: ' . $user->avatar);

            // Supprimer l'ancien avatar si existe
            if ($user->avatar) {
                $this->deleteOldAvatar($user->avatar);
            }

            // Upload la nouvelle image
            $file = $request->file('avatar');
            $filename = $this->generateUniqueFilename($file);
            
            // Stocker dans le dossier public/avatars
            $path = $file->storeAs('avatars', $filename, 'public');
            
            // Générer l'URL complète
            $avatarUrl = url('storage/' . $path);
            $user->avatar = $avatarUrl;
            $user->save();

            if($user->save()){
                return response()->json([
                    'success' => true,
                    'message' => 'Avatar uploadé avec succès',
                    'data' => [
                        'avatar_url' => $avatarUrl,
                    ],
                ], 200);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'upload',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Générer un nom de fichier unique
     */
    private function generateUniqueFilename($file)
    {
        $extension = $file->getClientOriginalExtension();
        return Str::uuid() . '_' . time() . '.' . $extension;
    }

    /**
     * Supprimer l'ancien avatar
     */
    private function deleteOldAvatar($avatarUrl)
    {
        try {
            // Extraire le chemin du fichier depuis l'URL
            $path = str_replace('/storage/', '', parse_url($avatarUrl, PHP_URL_PATH));
            
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas bloquer l'upload
            \Log::warning('Erreur lors de la suppression de l\'ancien avatar: ' . $e->getMessage());
        }
    }
}
