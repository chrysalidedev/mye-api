<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\UserService;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(UserService $userService )
    {
       try {
            return response()->json([
                'message' => 'Liste des utilisateurs',
                'data'    => $userService->list(),
                'success' => true
            ], 200);
       } catch (Exception $e) {
            return response()->json([
                'message' => "Impossible de lister les utilisateurs ".$e->getMessage(),
            ], 500);
       }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, UserService $userService)
    {
        try {
            $user = $userService->getById($id);
            if (!$user) {
                return response()->json([
                    'message' => 'Utilisateur non trouvé',
                    'success' => false
                ], 404);
            }

            return response()->json([
                'message' => 'Utilisateur trouvé',
                'data'    => $user,
                'success' => true
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => "Impossible de récupérer l'utilisateur: " . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
