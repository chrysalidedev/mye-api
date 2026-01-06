<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Services\RegisterService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request, AuthService $authService)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
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
                'success' => true
            ], 200);

        } catch (ValidationException $e) {

            return response()->json([
                'message' => 'Échec de connexion',
                'errors'  => $e->errors(),
            ], 401);
        }
    }


   public function register(Request $request, RegisterService $registerService)
{
    $validator = Validator::make($request->all(), [
        'name'      => 'required|string|max:255',
        'email'     => 'required|email|unique:users,email',
        'phone'     => 'nullable|string|unique:users,phone',
        'password'  => 'nullable|min:8|confirmed',
        'role'      => 'required|in:worker,manager',
        'google_id' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        Log::alert('Validation errors: ' . $validator->errors());
        return response()->json([
            'message' => 'Erreur de validation',
            'errors'  => $validator->errors(),
        ], 422);
    }

    try {
        $data = $registerService->register($validator->validated());

        $message = $data['email_verification_required']
            ? 'Inscription réussie. Veuillez vérifier votre email.'
            : 'Inscription réussie';

        return response()->json([
            'message' => $message,
            'data'    => $data,
            'success' => true
        ], 201);

    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Erreur lors de l\'inscription',
            'errors'  => $e->errors(),
        ], 422);
    }
}

public function googleLogin(Request $request, AuthService $authService)
{
    $validator = Validator::make($request->all(), [
        'name'      => 'required|string',
        'email'     => 'required|email',
        'google_id' => 'required|string',
        'role'      => 'nullable|in:worker,manager',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Erreur de validation',
            'errors'  => $validator->errors(),
        ], 422);
    }

    try {
        $data = $authService->googleLogin($validator->validated());

        return response()->json([
            'message' => 'Connexion Google réussie',
            'data'    => $data,
        ], 200);

    } catch (ValidationException $e) {

        return response()->json([
            'message' => 'Connexion refusée',
            'errors'  => $e->errors(),
        ], 401);
    }
}


}
