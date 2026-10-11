<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;

class AdminSubscriptionController extends Controller
{
    /**
     * Liste et filtrage des abonnements et transactions.
     */
    public function index(Request $request)
    {
        $query = Subscription::with('user');

        // Recherche par client ou transaction
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('cinetpay_transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par statut
        if ($status = $request->input('status')) {
            if ($status === 'expired') {
                $query->where(function ($q) {
                    $q->where('status', 'expired')
                      ->orWhere(function ($sq) {
                          $sq->where('status', 'active')->where('expires_at', '<=', now());
                      });
                });
            } else {
                $query->where('status', $status);
            }
        }

        // Filtre par formule (plan)
        if ($plan = $request->input('plan')) {
            $query->where('plan', $plan);
        }

        // Métriques
        $totalRevenue = Subscription::where('status', 'active')->sum('amount');
        $activeCount = Subscription::where('status', 'active')->where('expires_at', '>', now())->count();
        $pendingCount = Subscription::where('status', 'pending')->count();
        $totalCount = Subscription::count();

        $subscriptions = $query->latest()->paginate(15)->withQueryString();

        return view('admin.subscriptions.index', compact(
            'subscriptions',
            'totalRevenue',
            'activeCount',
            'pendingCount',
            'totalCount'
        ));
    }
}
