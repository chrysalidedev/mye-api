@extends('admin.layouts.app')

@section('title', 'Utilisateurs & Profils')
@section('page-title', 'Gestion des Utilisateurs')

@section('content')
<div class="space-y-6">

    <!-- 1. KPIS RAPIDES -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl bg-mye-card border border-mye-cardBorder">
            <span class="text-xs text-slate-400 font-medium">Total Comptes</span>
            <div class="text-2xl font-bold text-white mt-1">{{ number_format($totalCount, 0, ',', ' ') }}</div>
        </div>
        <div class="p-4 rounded-xl bg-mye-card border border-mye-cardBorder">
            <span class="text-xs text-blue-400 font-medium">Travailleurs</span>
            <div class="text-2xl font-bold text-white mt-1">{{ number_format($workerCount, 0, ',', ' ') }}</div>
        </div>
        <div class="p-4 rounded-xl bg-mye-card border border-mye-cardBorder">
            <span class="text-xs text-mye-gold font-medium">Entreprises</span>
            <div class="text-2xl font-bold text-white mt-1">{{ number_format($managerCount, 0, ',', ' ') }}</div>
        </div>
        <div class="p-4 rounded-xl bg-mye-card border border-mye-cardBorder">
            <span class="text-xs text-red-400 font-medium">Comptes Bloqués</span>
            <div class="text-2xl font-bold text-white mt-1">{{ number_format($blockedCount, 0, ',', ' ') }}</div>
        </div>
    </div>

    <!-- 2. BARRE DE RECHERCHE ET FILTRES -->
    <div class="p-5 rounded-2xl bg-mye-card border border-mye-cardBorder shadow-lg">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            
            <!-- Recherche textuelle -->
            <div class="lg:col-span-2 relative">
                <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-3.5"></i>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Nom, email, profession, entreprise, ville..."
                    class="w-full pl-10 pr-3 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-mye-gold"
                >
            </div>

            <!-- Filtre Rôle -->
            <div>
                <select name="role" class="w-full py-2.5 px-3 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-mye-gold">
                    <option value="">Tous les rôles</option>
                    <option value="worker" {{ request('role') === 'worker' ? 'selected' : '' }}>Travailleur</option>
                    <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>Entreprise / Recruteur</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrateur</option>
                </select>
            </div>

            <!-- Filtre Statut -->
            <div>
                <select name="status" class="w-full py-2.5 px-3 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-mye-gold">
                    <option value="">Tous les statuts</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Bloqué / Suspendu</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
                </select>
            </div>

            <!-- Actions Filtrer / Reset -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 px-3 bg-mye-gold hover:bg-mye-goldDark text-black font-semibold rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5 shadow-md shadow-mye-gold/20">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    Filtrer
                </button>
                @if(request()->hasAny(['search', 'role', 'status', 'verified']))
                    <a href="{{ route('admin.users.index') }}" class="p-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs transition-colors" title="Réinitialiser">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- 3. TABLEAU DES UTILISATEURS -->
    <div class="rounded-2xl bg-mye-card border border-mye-cardBorder shadow-xl overflow-hidden">
        <div class="p-5 border-b border-mye-cardBorder flex items-center justify-between">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4 text-mye-gold"></i>
                Liste des Utilisateurs
            </h3>
            <span class="text-xs text-slate-400">
                Affichage de {{ $users->firstItem() ?? 0 }} à {{ $users->lastItem() ?? 0 }} sur {{ $users->total() }} membres
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-900/50 border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Utilisateur</th>
                        <th class="py-3 px-4">Rôle</th>
                        <th class="py-3 px-4">Détails Activité</th>
                        <th class="py-3 px-4">Localisation</th>
                        <th class="py-3 px-4">Statut</th>
                        <th class="py-3 px-4">Inscrit</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-800/25 transition-colors">
                            <!-- Utilisateur -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <img 
                                        src="{{ $user->avatar ?: 'https://cdn-icons-png.flaticon.com/512/149/149071.png' }}" 
                                        alt="{{ $user->name }}" 
                                        class="w-9 h-9 rounded-full object-cover border border-slate-700 bg-slate-800 shrink-0"
                                        onerror="this.src='https://cdn-icons-png.flaticon.com/512/149/149071.png'"
                                    >
                                    <div>
                                        <div class="font-bold text-white flex items-center gap-1.5">
                                            <span>{{ $user->name }}</span>
                                            @if($user->email_verified_at)
                                                <i data-lucide="badge-check" class="w-3.5 h-3.5 text-emerald-400" title="Email vérifié"></i>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400">{{ $user->email }}</div>
                                        @if($user->phone)
                                            <div class="text-[10px] text-slate-500 font-mono">{{ $user->phone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Rôle -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($user->role === 'worker')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                        Travailleur
                                    </span>
                                @elseif($user->role === 'manager')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-mye-gold/15 text-mye-gold border border-mye-gold/30">
                                        Entreprise
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-purple-500/10 text-purple-400 border border-purple-500/20">
                                        Admin
                                    </span>
                                @endif
                            </td>

                            <!-- Détails métier / entreprise -->
                            <td class="py-3.5 px-4">
                                @if($user->isWorker())
                                    <div class="font-medium text-slate-200">{{ $user->profession ?: 'Non renseigné' }}</div>
                                    @if($user->experience_years)
                                        <div class="text-[10px] text-slate-400">{{ $user->experience_years }} ans d'expérience</div>
                                    @endif
                                @elseif($user->isManager())
                                    <div class="font-medium text-slate-200 flex items-center gap-1">
                                        <span>{{ $user->company_name ?: 'Non renseigné' }}</span>
                                        @if($user->company_verified)
                                            <span class="text-[9px] px-1 rounded bg-emerald-500/20 text-emerald-400 font-semibold" title="Entreprise vérifiée">Certifiée</span>
                                        @endif
                                    </div>
                                    <div class="text-[10px] text-slate-400">{{ $user->company_activity }}</div>
                                @else
                                    <span class="text-slate-500 text-[11px]">Accès Super Administrateur</span>
                                @endif
                            </td>

                            <!-- Localisation -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($user->city || $user->country)
                                    <div class="text-slate-300 font-medium">{{ $user->city ?? '' }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $user->country ?? '' }}</div>
                                @else
                                    <span class="text-slate-600">-</span>
                                @endif
                            </td>

                            <!-- Statut -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($user->status === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Actif
                                    </span>
                                @elseif($user->status === 'blocked')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-red-500/10 text-red-400 border border-red-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Bloqué
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-800 text-slate-400">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Date Inscription -->
                            <td class="py-3.5 px-4 whitespace-nowrap text-slate-400">
                                <div>{{ $user->created_at ? $user->created_at->format('d/m/Y') : '-' }}</div>
                                <div class="text-[10px] text-slate-500">{{ $user->created_at ? $user->created_at->diffForHumans() : '' }}</div>
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Valider Entreprise si Manager -->
                                    @if($user->isManager())
                                        <form method="POST" action="{{ route('admin.users.toggle-verify', $user) }}" class="inline">
                                            @csrf
                                            <button 
                                                type="submit" 
                                                title="{{ $user->company_verified ? 'Retirer la certification' : 'Certifier cette entreprise' }}"
                                                class="p-1.5 rounded-lg {{ $user->company_verified ? 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500 hover:text-white' : 'bg-slate-800 text-slate-400 hover:text-emerald-400 hover:bg-slate-700' }} transition-colors"
                                            >
                                                <i data-lucide="award" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Bloquer / Activer compte -->
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="inline" onsubmit="return confirm('Confirmez-vous le changement de statut pour cet utilisateur ?')">
                                            @csrf
                                            <button 
                                                type="submit" 
                                                title="{{ $user->status === 'blocked' ? 'Débloquer le compte' : 'Suspendre/Bloquer le compte' }}"
                                                class="p-1.5 rounded-lg {{ $user->status === 'blocked' ? 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500 hover:text-white' : 'bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white' }} transition-colors"
                                            >
                                                <i data-lucide="{{ $user->status === 'blocked' ? 'check-circle' : 'ban' }}" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-500">
                                <i data-lucide="users" class="w-10 h-10 mx-auto text-slate-600 mb-2"></i>
                                Aucun utilisateur ne correspond aux critères de recherche.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="p-4 border-t border-slate-800 flex items-center justify-between">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
