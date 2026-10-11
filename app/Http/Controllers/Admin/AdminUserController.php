<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    /**
     * Liste et filtrage des utilisateurs.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Recherche textuelle
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('profession', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Filtre par rôle
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        // Filtre par statut
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filtre par email vérifié
        if ($request->filled('verified')) {
            if ($request->input('verified') === '1') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->input('verified') === '0') {
                $query->whereNull('email_verified_at');
            }
        }

        // Statistiques globales pour les compteurs
        $totalCount = User::count();
        $workerCount = User::where('role', 'worker')->count();
        $managerCount = User::where('role', 'manager')->count();
        $blockedCount = User::where('status', 'blocked')->count();

        // Pagination avec conservation des filtres
        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact(
            'users',
            'totalCount',
            'workerCount',
            'managerCount',
            'blockedCount'
        ));
    }

    /**
     * Basculer le statut d'un utilisateur (Actif <-> Bloqué).
     */
    public function toggleStatus(User $user)
    {
        // Empêcher l'admin connecté de se bloquer lui-même
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas modifier le statut de votre propre compte.');
        }

        $newStatus = $user->status === 'blocked' ? 'active' : 'blocked';
        $user->update(['status' => $newStatus]);

        $message = $newStatus === 'blocked'
            ? "Le compte de {$user->name} a été suspendu/bloqué."
            : "Le compte de {$user->name} a été réactivé.";

        return back()->with('success', $message);
    }

    /**
     * Valider ou retirer la vérification d'entreprise d'un manager.
     */
    public function toggleVerifyCompany(User $user)
    {
        if (!$user->isManager()) {
            return back()->with('error', 'Cette action ne s\'applique qu\'aux entreprises/managers.');
        }

        $newVerified = !$user->company_verified;
        $user->update(['company_verified' => $newVerified]);

        $message = $newVerified
            ? "L'entreprise {$user->company_name} a été certifiée/vérifiée."
            : "La certification de {$user->company_name} a été révoquée.";

        return back()->with('success', $message);
    }
}
