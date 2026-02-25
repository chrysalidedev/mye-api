<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\UserService;
use Illuminate\Support\Facades\Log;

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
public function updateUser(Request $request, UserService $userService)
{
    try {
            $rules = [
        'name'    => 'required|string|max:255',
        'phone'   => 'nullable|string|max:20',
        'gender'  => 'nullable|in:male,female,other',
        'bio'     => 'nullable|string|max:500',
        'city'    => 'nullable|string|max:100',
        'country' => 'nullable|string|max:100',
        'role'    => 'nullable|in:worker,manager',
    ];

    Log::info("Auth user ID: " . auth()->user()->id);

    $user = $userService->getById(auth()->user()->id);
    if (!$user) {
        return response()->json([
            'message' => 'Utilisateur non trouve',
            'success' => false
        ], 404);
    }

    // Pour la validation : si le rôle est envoyé (ex. complétion profil Google), on valide selon ce rôle
    $effectiveRole = $request->input('role') ?? $user->role;

    if ($effectiveRole === 'worker') {
        $rules = array_merge($rules, [
            'profession'        => 'nullable|string|max:150',
            'skills'            => 'nullable|array',
            'experience_years'  => 'nullable|integer|min:0',
        ]);
    }

    if ($effectiveRole === 'manager') {
        $rules = array_merge($rules, [
            'company_name'      => 'nullable|string|max:255',
            'company_activity'  => 'nullable|string|max:500',
        ]);
    }

    $data = $request->validate($rules);

    if (($data['role'] ?? $user->role) === 'worker') {
        $data['availability'] = $data['availability'] ?? false;
    }
        $userService->updateUser($user, $data);
      
        return response()->json([
            'message' => 'Utilisateur mis à jour',
            'data'    => $user,
            'success' => true
        ], 200);
    } catch (Exception $e) {
            Log::critical("Impossible de mettre à jour l'utilisateur: " . $e->getMessage(),);
        return response()->json([
            'message' => "Impossible de mettre à jour l'utilisateur: " . $e->getMessage(),
            'success' => false
        ], 500);
    }
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
