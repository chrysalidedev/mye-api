<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscription;
use App\Models\UserMatch;
use App\Models\Connection;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Affiche le tableau de bord avec l'ensemble des KPIs et graphiques.
     */
    public function index()
    {
        // --- 1. STATISTIQUES UTILISATEURS ---
        $totalUsers = User::count();
        $totalWorkers = User::where('role', 'worker')->count();
        $totalManagers = User::where('role', 'manager')->count();
        $totalAdmins = User::where('role', 'admin')->count();

        $activeUsers = User::where('status', 'active')->count();
        $blockedUsers = User::where('status', 'blocked')->count();

        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        $verificationRate = $totalUsers > 0 ? round(($verifiedUsers / $totalUsers) * 100, 1) : 0;

        $newUsersThisMonth = User::where('created_at', '>=', now()->startOfMonth())->count();
        $newUsersLastMonth = User::whereBetween('created_at', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth()
        ])->count();
        
        $userGrowth = $newUsersLastMonth > 0 
            ? round((($newUsersThisMonth - $newUsersLastMonth) / $newUsersLastMonth) * 100, 1)
            : ($newUsersThisMonth > 0 ? 100 : 0);

        // --- 2. STATISTIQUES FINANCES & ABONNEMENTS ---
        $totalRevenue = Subscription::where('status', 'active')->sum('amount');
        $revenueThisMonth = Subscription::where('status', 'active')
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('amount');

        $activeSubscriptions = Subscription::where('status', 'active')
            ->where('expires_at', '>', now())
            ->count();
        $pendingSubscriptions = Subscription::where('status', 'pending')->count();
        $expiredSubscriptions = Subscription::where('status', 'expired')
            ->orWhere(function ($q) {
                $q->where('status', 'active')->where('expires_at', '<=', now());
            })->count();

        // --- 3. STATISTIQUES RÉSEAU & INTERACTIONS ---
        $totalMatches = UserMatch::where('is_mutual', true)->count();
        $totalSwipes = UserMatch::count();
        $acceptedConnections = Connection::where('status', 'accepted')->count();
        $pendingConnections = Connection::where('status', 'pending')->count();
        $rejectedConnections = Connection::where('status', 'rejected')->count();

        $totalMessages = class_exists(Message::class) ? Message::count() : 0;

        // --- 4. DONNÉES GRAPHIQUES (CHART.JS) ---

        // Inscriptions sur les 30 derniers jours
        $registrationLabels = [];
        $registrationData = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateString = $date->format('Y-m-d');
            $registrationLabels[] = $date->locale('fr')->isoFormat('DD MMM');
            
            $count = User::whereDate('created_at', $dateString)->count();
            $registrationData[] = $count;
        }

        // Revenus mensuels sur les 6 derniers mois
        $monthlyRevenueLabels = [];
        $monthlyRevenueData = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyRevenueLabels[] = ucfirst($month->locale('fr')->isoFormat('MMMM YYYY'));

            $rev = Subscription::where('status', 'active')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');

            $monthlyRevenueData[] = (float) $rev;
        }

        // --- 5. DONNÉES RÉCENTES ---
        $recentUsers = User::latest()->take(6)->get();
        $recentSubscriptions = Subscription::with('user')->latest()->take(6)->get();
        $recentMatches = UserMatch::with(['user1', 'user2'])
            ->where('is_mutual', true)
            ->latest('matched_at')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalWorkers',
            'totalManagers',
            'totalAdmins',
            'activeUsers',
            'blockedUsers',
            'verifiedUsers',
            'verificationRate',
            'newUsersThisMonth',
            'userGrowth',
            'totalRevenue',
            'revenueThisMonth',
            'activeSubscriptions',
            'pendingSubscriptions',
            'expiredSubscriptions',
            'totalMatches',
            'totalSwipes',
            'acceptedConnections',
            'pendingConnections',
            'rejectedConnections',
            'totalMessages',
            'registrationLabels',
            'registrationData',
            'monthlyRevenueLabels',
            'monthlyRevenueData',
            'recentUsers',
            'recentSubscriptions',
            'recentMatches'
        ));
    }
}
