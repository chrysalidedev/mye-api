@extends('admin.layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Vue d\'ensemble & KPIs')

@section('content')
<div class="space-y-8">

    <!-- 1. BIENVENUE & BANNIÈRE RÉCAPITULATIVE -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-mye-card via-slate-900 to-mye-card border border-mye-cardBorder p-6 sm:p-8 shadow-xl">
        <!-- Accent Glow -->
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-mye-gold/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-mye-gold/15 text-mye-gold border border-mye-gold/30 text-xs font-semibold uppercase tracking-wider mb-3">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    Centre de Contrôle Global
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Bonjour, {{ auth()->user()->name ?? 'Administrateur' }} 👋
                </h2>
                <p class="text-slate-400 text-sm mt-1 max-w-xl">
                    Voici l'état en temps réel de votre plateforme Mye : utilisateurs, interactions, correspondances et transactions financières.
                </p>
            </div>

            <!-- Quick Stats in Welcome Card -->
            <div class="flex items-center gap-3">
                <div class="px-4 py-3 rounded-xl bg-slate-950/60 border border-slate-800 text-center">
                    <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Ce Mois-ci</div>
                    <div class="text-lg font-bold text-mye-gold mt-0.5">+{{ $newUsersThisMonth }} <span class="text-xs text-slate-400 font-normal">inscrits</span></div>
                </div>
                <div class="px-4 py-3 rounded-xl bg-slate-950/60 border border-slate-800 text-center">
                    <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Revenus Mois</div>
                    <div class="text-lg font-bold text-emerald-400 mt-0.5">{{ number_format($revenueThisMonth, 0, ',', ' ') }} <span class="text-xs font-normal">FCFA</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. CARTES KPIS PRINCIPALES -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- KPI 1 : Total Utilisateurs -->
        <a href="{{ route('admin.users.index') }}" class="block rounded-2xl bg-mye-card border border-mye-cardBorder p-5 hover:border-mye-gold/60 transition-all duration-300 group shadow-lg hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider group-hover:text-white transition-colors">Utilisateurs</span>
                <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 border border-blue-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-white">{{ number_format($totalUsers, 0, ',', ' ') }}</span>
                @if($userGrowth >= 0)
                    <span class="text-xs font-bold text-emerald-400 flex items-center">
                        <i data-lucide="trending-up" class="w-3.5 h-3.5 mr-0.5"></i>+{{ $userGrowth }}%
                    </span>
                @else
                    <span class="text-xs font-bold text-red-400 flex items-center">
                        <i data-lucide="trending-down" class="w-3.5 h-3.5 mr-0.5"></i>{{ $userGrowth }}%
                    </span>
                @endif
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                <span>{{ $totalWorkers }} Travailleurs</span>
                <span>&bull;</span>
                <span>{{ $totalManagers }} Entreprises</span>
            </div>
        </a>

        <!-- KPI 2 : Abonnements & Revenus -->
        <a href="{{ route('admin.subscriptions.index') }}" class="block rounded-2xl bg-mye-card border border-mye-cardBorder p-5 hover:border-mye-gold/60 transition-all duration-300 group shadow-lg hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider group-hover:text-white transition-colors">Chiffre d'Affaires</span>
                <div class="w-10 h-10 rounded-xl bg-mye-gold/15 text-mye-gold border border-mye-gold/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold text-white">{{ number_format($totalRevenue, 0, ',', ' ') }}</span>
                <span class="text-xs font-bold text-mye-gold">FCFA</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                <span class="text-emerald-400 font-medium">{{ $activeSubscriptions }} Actifs</span>
                <span>&bull;</span>
                <span class="text-amber-400">{{ $pendingSubscriptions }} En attente</span>
            </div>
        </a>

        <!-- KPI 3 : Matchs Mutuels -->
        <a href="{{ route('admin.matches.index', ['tab' => 'matches']) }}" class="block rounded-2xl bg-mye-card border border-mye-cardBorder p-5 hover:border-pink-500/60 transition-all duration-300 group shadow-lg hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider group-hover:text-white transition-colors">Matchs Réalisés</span>
                <div class="w-10 h-10 rounded-xl bg-pink-500/10 text-pink-400 border border-pink-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i data-lucide="heart" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-white">{{ number_format($totalMatches, 0, ',', ' ') }}</span>
                <span class="text-xs text-pink-400 font-semibold">Mutuels</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                <span>{{ $totalSwipes }} Swipes globaux</span>
                <span class="text-mye-gold font-medium">{{ $totalUsers > 0 ? round(($totalMatches / max(1, $totalUsers)) * 100) : 0 }}% ratio</span>
            </div>
        </a>

        <!-- KPI 4 : Relations & Connexions -->
        <a href="{{ route('admin.matches.index', ['tab' => 'connections']) }}" class="block rounded-2xl bg-mye-card border border-mye-cardBorder p-5 hover:border-purple-500/60 transition-all duration-300 group shadow-lg hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider group-hover:text-white transition-colors">Relations Réseau</span>
                <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 border border-purple-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i data-lucide="user-check" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-white">{{ number_format($acceptedConnections, 0, ',', ' ') }}</span>
                <span class="text-xs text-purple-400 font-semibold">Connectés</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                <span class="text-amber-400">{{ $pendingConnections }} En attente</span>
                <span>&bull;</span>
                <span class="text-slate-400">{{ $totalMessages }} Messages</span>
            </div>
        </a>
    </div>

    <!-- 3. SECTION GRAPHIQUES (CHART.JS) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Graphique 1 : Inscriptions sur 30 jours (2 colonnes sur desktop) -->
        <div class="lg:col-span-2 rounded-2xl bg-mye-card border border-mye-cardBorder p-6 shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <i data-lucide="line-chart" class="w-4 h-4 text-mye-gold"></i>
                        Croissance des Inscriptions (30 Derniers Jours)
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Évolution quotidienne des nouveaux comptes créés sur la plateforme</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-900 border border-slate-800 text-xs font-semibold text-mye-gold">
                        <span class="w-2 h-2 rounded-full bg-mye-gold"></span>
                        Nouveaux membres
                    </span>
                </div>
            </div>
            <div class="h-72 w-full">
                <canvas id="registrationChart"></canvas>
            </div>
        </div>

        <!-- Graphique 2 : Répartition des Rôles (1 colonne sur desktop) -->
        <div class="rounded-2xl bg-mye-card border border-mye-cardBorder p-6 shadow-xl flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <i data-lucide="pie-chart" class="w-4 h-4 text-blue-400"></i>
                        Répartition par Profil
                    </h3>
                </div>
                <p class="text-xs text-slate-400 mb-6">Proportion entre Travailleurs, Entreprises et Administrateurs</p>
                <div class="h-56 relative flex items-center justify-center">
                    <canvas id="roleDistributionChart"></canvas>
                </div>
            </div>
            <!-- Legend summary -->
            <div class="mt-4 pt-4 border-t border-slate-800 grid grid-cols-3 gap-2 text-center text-xs">
                <div>
                    <div class="text-blue-400 font-bold text-sm">{{ $totalWorkers }}</div>
                    <div class="text-[10px] text-slate-400">Travailleurs</div>
                </div>
                <div>
                    <div class="text-mye-gold font-bold text-sm">{{ $totalManagers }}</div>
                    <div class="text-[10px] text-slate-400">Entreprises</div>
                </div>
                <div>
                    <div class="text-purple-400 font-bold text-sm">{{ $totalAdmins }}</div>
                    <div class="text-[10px] text-slate-400">Admins</div>
                </div>
            </div>
        </div>

        <!-- Graphique 3 : Revenus sur 6 mois (2 colonnes) -->
        <div class="lg:col-span-2 rounded-2xl bg-mye-card border border-mye-cardBorder p-6 shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <i data-lucide="bar-chart-3" class="w-4 h-4 text-emerald-400"></i>
                        Évolution des Revenus Abonnements (6 Derniers Mois)
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Montant cumulé des abonnements validés (FCFA)</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-xs font-semibold text-emerald-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    Transactions CinetPay
                </span>
            </div>
            <div class="h-72 w-full">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Graphique 4 : État des Interactions (1 colonne) -->
        <div class="rounded-2xl bg-mye-card border border-mye-cardBorder p-6 shadow-xl flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <i data-lucide="activity" class="w-4 h-4 text-purple-400"></i>
                        Interactions Réseau
                    </h3>
                </div>
                <p class="text-xs text-slate-400 mb-6">Volume comparé des mises en relation et connexions</p>
                <div class="h-56 relative flex items-center justify-center">
                    <canvas id="networkDistributionChart"></canvas>
                </div>
            </div>
            <!-- Legend summary -->
            <div class="mt-4 pt-4 border-t border-slate-800 grid grid-cols-3 gap-2 text-center text-xs">
                <div>
                    <div class="text-pink-400 font-bold text-sm">{{ $totalMatches }}</div>
                    <div class="text-[10px] text-slate-400">Matchs</div>
                </div>
                <div>
                    <div class="text-purple-400 font-bold text-sm">{{ $acceptedConnections }}</div>
                    <div class="text-[10px] text-slate-400">Acceptées</div>
                </div>
                <div>
                    <div class="text-amber-400 font-bold text-sm">{{ $pendingConnections }}</div>
                    <div class="text-[10px] text-slate-400">En attente</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. SECTION TABLEAUX D'ACTIVITÉS RÉCENTES -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" id="users-section">

        <!-- Tableau : Derniers Utilisateurs Inscrits -->
        <div class="rounded-2xl bg-mye-card border border-mye-cardBorder p-6 shadow-xl">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <i data-lucide="user-plus" class="w-4 h-4 text-mye-gold"></i>
                        Derniers Utilisateurs Inscrits
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Les 6 comptes les plus récents sur Mye</p>
                </div>
                <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-slate-900 text-mye-gold border border-slate-800 hover:border-mye-gold/50 hover:bg-slate-800 transition-all flex items-center gap-1.5">
                    <span>Gérer tout ({{ $totalUsers }})</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                            <th class="pb-3">Utilisateur</th>
                            <th class="pb-3">Rôle</th>
                            <th class="pb-3">Statut</th>
                            <th class="pb-3 text-right">Inscrit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($recentUsers as $user)
                            <tr class="hover:bg-slate-800/20 transition-colors">
                                <td class="py-3">
                                    <div class="flex items-center gap-3">
                                        <img 
                                            src="{{ $user->avatar ?: 'https://cdn-icons-png.flaticon.com/512/149/149071.png' }}" 
                                            alt="{{ $user->name }}" 
                                            class="w-8 h-8 rounded-full object-cover border border-slate-700 bg-slate-800"
                                            onerror="this.src='https://cdn-icons-png.flaticon.com/512/149/149071.png'"
                                        >
                                        <div>
                                            <p class="font-semibold text-white truncate max-w-[140px]">{{ $user->name }}</p>
                                            <p class="text-[11px] text-slate-400 truncate max-w-[140px]">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    @if($user->role === 'worker')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                            Travailleur
                                        </span>
                                    @elseif($user->role === 'manager')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-mye-gold/15 text-mye-gold border border-mye-gold/30">
                                            Entreprise
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-500/10 text-purple-400 border border-purple-500/20">
                                            Admin
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    @if($user->status === 'active')
                                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Actif
                                        </span>
                                    @elseif($user->status === 'blocked')
                                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-red-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Bloqué
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> {{ ucfirst($user->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 text-right text-slate-400 whitespace-nowrap">
                                    {{ $user->created_at ? $user->created_at->diffForHumans() : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-500">
                                    Aucun utilisateur trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tableau : Dernières Transactions d'Abonnements -->
        <div class="rounded-2xl bg-mye-card border border-mye-cardBorder p-6 shadow-xl" id="subscriptions-section">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <i data-lucide="receipt" class="w-4 h-4 text-emerald-400"></i>
                        Dernières Transactions
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Historique récent des souscriptions et paiements</p>
                </div>
                <a href="{{ route('admin.subscriptions.index') }}" class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500 hover:text-white transition-all flex items-center gap-1.5">
                    <span>Voir l'historique ({{ $activeSubscriptions }} actifs)</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                            <th class="pb-3">Utilisateur</th>
                            <th class="pb-3">Formule</th>
                            <th class="pb-3">Montant</th>
                            <th class="pb-3">Statut</th>
                            <th class="pb-3 text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($recentSubscriptions as $sub)
                            <tr class="hover:bg-slate-800/20 transition-colors">
                                <td class="py-3">
                                    <p class="font-semibold text-white truncate max-w-[130px]">{{ $sub->user->name ?? 'Utilisateur inconnu' }}</p>
                                    <p class="text-[10px] font-mono text-slate-500 truncate max-w-[130px]">{{ $sub->cinetpay_transaction_id ?: '#'.$sub->id }}</p>
                                </td>
                                <td class="py-3 text-slate-300 font-medium">
                                    {{ $sub->plan === 'yearly' ? 'Annuel' : 'Mensuel' }}
                                </td>
                                <td class="py-3 font-semibold text-white">
                                    {{ number_format($sub->amount, 0, ',', ' ') }} <span class="text-[10px] text-mye-gold">{{ $sub->currency ?: 'XOF' }}</span>
                                </td>
                                <td class="py-3">
                                    @if($sub->status === 'active')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            Payé
                                        </span>
                                    @elseif($sub->status === 'pending')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                            En attente
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-500/10 text-red-400 border border-red-500/20">
                                            {{ ucfirst($sub->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 text-right text-slate-400 whitespace-nowrap">
                                    {{ $sub->created_at ? $sub->created_at->diffForHumans() : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500">
                                    Aucune transaction enregistrée pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 5. SECTION MATCHS RÉCENTS -->
    <div class="rounded-2xl bg-mye-card border border-mye-cardBorder p-6 shadow-xl" id="matches-section">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4 text-pink-400"></i>
                    Dernières Correspondances Mutuelles (Matching)
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Matchs récents confirmés entre candidats et recruteurs</p>
            </div>
            <a href="{{ route('admin.matches.index') }}" class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-pink-500/10 text-pink-400 border border-pink-500/20 hover:bg-pink-500 hover:text-white transition-all flex items-center gap-1.5">
                <span>Tous les matchs ({{ $totalMatches }})</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            @forelse($recentMatches as $match)
                <div class="p-4 rounded-xl bg-slate-900/80 border border-slate-800 hover:border-pink-500/40 transition-all text-center">
                    <div class="flex items-center justify-center -space-x-2 mb-3">
                        <img 
                            src="{{ $match->user1->avatar ?? 'https://cdn-icons-png.flaticon.com/512/149/149071.png' }}" 
                            alt="{{ $match->user1->name ?? 'Utilisateur' }}" 
                            class="w-10 h-10 rounded-full border-2 border-slate-900 object-cover"
                            onerror="this.src='https://cdn-icons-png.flaticon.com/512/149/149071.png'"
                        >
                        <div class="w-7 h-7 rounded-full bg-pink-500 text-white flex items-center justify-center border-2 border-slate-900 z-10">
                            <i data-lucide="heart" class="w-3.5 h-3.5 fill-current"></i>
                        </div>
                        <img 
                            src="{{ $match->user2->avatar ?? 'https://cdn-icons-png.flaticon.com/512/149/149071.png' }}" 
                            alt="{{ $match->user2->name ?? 'Utilisateur' }}" 
                            class="w-10 h-10 rounded-full border-2 border-slate-900 object-cover"
                            onerror="this.src='https://cdn-icons-png.flaticon.com/512/149/149071.png'"
                        >
                    </div>

                    <div class="text-xs font-semibold text-white truncate">
                        {{ $match->user1->name ?? 'Membre 1' }} & {{ $match->user2->name ?? 'Membre 2' }}
                    </div>

                    <div class="mt-2 flex items-center justify-center gap-2 text-[11px]">
                        @if($match->compatibility_score)
                            <span class="px-2 py-0.5 rounded bg-emerald-500/15 text-emerald-400 font-bold">
                                {{ $match->compatibility_score }}% affinité
                            </span>
                        @endif
                    </div>

                    <div class="mt-2 text-[10px] text-slate-500">
                        {{ $match->matched_at ? $match->matched_at->diffForHumans() : $match->created_at->diffForHumans() }}
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-slate-500 text-xs">
                    Aucun match mutuel enregistré pour le moment.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // --- 1. Graphique Inscriptions (30 Jours) ---
    const regCtx = document.getElementById('registrationChart').getContext('2d');
    
    // Gradient pour la courbe
    const regGradient = regCtx.createLinearGradient(0, 0, 0, 260);
    regGradient.addColorStop(0, 'rgba(203, 169, 72, 0.45)');
    regGradient.addColorStop(1, 'rgba(203, 169, 72, 0.0)');

    new Chart(regCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($registrationLabels) !!},
            datasets: [{
                label: 'Inscriptions',
                data: {!! json_encode($registrationData) !!},
                borderColor: '#CBA948',
                backgroundColor: regGradient,
                borderWidth: 3,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#CBA948',
                pointBorderColor: '#0A0E17',
                pointBorderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#111827',
                    titleColor: '#FFFFFF',
                    bodyColor: '#CBA948',
                    borderColor: '#1F293D',
                    borderWidth: 1,
                    padding: 10,
                    displayColors: false
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255, 255, 255, 0.04)' },
                    ticks: { color: '#94A3B8', font: { size: 10 } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255, 255, 255, 0.04)' },
                    ticks: {
                        color: '#94A3B8',
                        font: { size: 10 },
                        precision: 0
                    }
                }
            }
        }
    });

    // --- 2. Graphique Répartition des Rôles (Donut) ---
    const roleCtx = document.getElementById('roleDistributionChart').getContext('2d');
    new Chart(roleCtx, {
        type: 'doughnut',
        data: {
            labels: ['Travailleurs', 'Entreprises', 'Admins'],
            datasets: [{
                data: [{{ $totalWorkers }}, {{ $totalManagers }}, {{ $totalAdmins }}],
                backgroundColor: ['#3B82F6', '#CBA948', '#A855F7'],
                borderColor: '#111827',
                borderWidth: 4,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#111827',
                    titleColor: '#FFFFFF',
                    borderColor: '#1F293D',
                    borderWidth: 1,
                    padding: 10
                }
            }
        }
    });

    // --- 3. Graphique Revenus Mensuels (Barres) ---
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($monthlyRevenueLabels) !!},
            datasets: [{
                label: 'Revenus (FCFA)',
                data: {!! json_encode($monthlyRevenueData) !!},
                backgroundColor: '#10B981',
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#111827',
                    titleColor: '#FFFFFF',
                    bodyColor: '#10B981',
                    borderColor: '#1F293D',
                    borderWidth: 1,
                    padding: 10,
                    callbacks: {
                        label: function(context) {
                            return new Intl.NumberFormat('fr-FR').format(context.raw) + ' FCFA';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#94A3B8', font: { size: 10 } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255, 255, 255, 0.04)' },
                    ticks: {
                        color: '#94A3B8',
                        font: { size: 10 },
                        callback: function(val) {
                            return val >= 1000 ? (val / 1000) + 'k' : val;
                        }
                    }
                }
            }
        }
    });

    // --- 4. Graphique Interactions Réseau (Donut) ---
    const netCtx = document.getElementById('networkDistributionChart').getContext('2d');
    new Chart(netCtx, {
        type: 'doughnut',
        data: {
            labels: ['Matchs Mutuels', 'Relations Acceptées', 'Demandes en Attente'],
            datasets: [{
                data: [{{ $totalMatches }}, {{ $acceptedConnections }}, {{ $pendingConnections }}],
                backgroundColor: ['#EC4899', '#A855F7', '#F59E0B'],
                borderColor: '#111827',
                borderWidth: 4,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#111827',
                    titleColor: '#FFFFFF',
                    borderColor: '#1F293D',
                    borderWidth: 1,
                    padding: 10
                }
            }
        }
    });
</script>
@endpush
