<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserMatch;
use App\Models\Connection;
use Illuminate\Http\Request;

class AdminMatchController extends Controller
{
    /**
     * Liste et supervision des matchs et relations réseau.
     */
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'matches'); // 'matches' ou 'connections'

        // Compteurs globaux
        $totalMatches = UserMatch::where('is_mutual', true)->count();
        $totalSwipes = UserMatch::count();
        $acceptedConnections = Connection::where('status', 'accepted')->count();
        $pendingConnections = Connection::where('status', 'pending')->count();

        // Récupération selon l'onglet actif
        if ($tab === 'connections') {
            $connQuery = Connection::with(['sender', 'receiver']);

            if ($status = $request->input('status')) {
                $connQuery->where('status', $status);
            }

            if ($search = $request->input('search')) {
                $connQuery->where(function ($q) use ($search) {
                    $q->whereHas('sender', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
                    })->orWhereHas('receiver', function ($rq) use ($search) {
                        $rq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
                    });
                });
            }

            $connections = $connQuery->latest()->paginate(15)->withQueryString();
            $matches = null;
        } else {
            $matchQuery = UserMatch::with(['user1', 'user2']);

            if ($filter = $request->input('filter')) {
                if ($filter === 'mutual') {
                    $matchQuery->where('is_mutual', true);
                } elseif ($filter === 'one_way') {
                    $matchQuery->where('is_mutual', false);
                }
            }

            if ($search = $request->input('search')) {
                $matchQuery->where(function ($q) use ($search) {
                    $q->whereHas('user1', function ($u1) use ($search) {
                        $u1->where('name', 'like', "%{$search}%")->orWhere('profession', 'like', "%{$search}%");
                    })->orWhereHas('user2', function ($u2) use ($search) {
                        $u2->where('name', 'like', "%{$search}%")->orWhere('profession', 'like', "%{$search}%");
                    });
                });
            }

            $matches = $matchQuery->latest('updated_at')->paginate(15)->withQueryString();
            $connections = null;
        }

        return view('admin.matches.index', compact(
            'tab',
            'matches',
            'connections',
            'totalMatches',
            'totalSwipes',
            'acceptedConnections',
            'pendingConnections'
        ));
    }
}
