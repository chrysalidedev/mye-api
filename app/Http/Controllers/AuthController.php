<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\AuthService;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request, AuthService $authService)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required', // PAS min ici
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $data = $authService->login($validator->validated());

            return response()->json([
                'message' => 'Connexion réussie',
                'data'    => $data,
            ], 200);

        } catch (ValidationException $e) {

            return response()->json([
                'message' => 'Échec de connexion',
                'errors'  => $e->errors(),
            ], 401);
        }
    }
}
