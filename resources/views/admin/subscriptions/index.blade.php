@extends('admin.layouts.app')

@section('title', 'Abonnements & Finances')
@section('page-title', 'Suivi des Abonnements & Revenus')

@section('content')
<div class="space-y-6">

    <!-- 1. KPIS FINANCES -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl bg-mye-card border border-mye-cardBorder">
            <span class="text-xs text-mye-gold font-medium uppercase tracking-wider">Chiffre d'Affaires</span>
            <div class="text-2xl font-bold text-white mt-1">{{ number_format($totalRevenue, 0, ',', ' ') }} <span class="text-xs text-mye-gold font-normal">FCFA</span></div>
        </div>
        <div class="p-4 rounded-xl bg-mye-card border border-mye-cardBorder">
            <span class="text-xs text-emerald-400 font-medium uppercase tracking-wider">Abonnements Actifs</span>
            <div class="text-2xl font-bold text-white mt-1">{{ number_format($activeCount, 0, ',', ' ') }}</div>
        </div>
        <div class="p-4 rounded-xl bg-mye-card border border-mye-cardBorder">
            <span class="text-xs text-amber-400 font-medium uppercase tracking-wider">En Attente</span>
            <div class="text-2xl font-bold text-white mt-1">{{ number_format($pendingCount, 0, ',', ' ') }}</div>
        </div>
        <div class="p-4 rounded-xl bg-mye-card border border-mye-cardBorder">
            <span class="text-xs text-slate-400 font-medium uppercase tracking-wider">Total Transactions</span>
            <div class="text-2xl font-bold text-white mt-1">{{ number_format($totalCount, 0, ',', ' ') }}</div>
        </div>
    </div>

    <!-- 2. RECHERCHE ET FILTRES -->
    <div class="p-5 rounded-2xl bg-mye-card border border-mye-cardBorder shadow-lg">
        <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            
            <!-- Recherche textuelle -->
            <div class="lg:col-span-2 relative">
                <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-3.5"></i>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Transaction CinetPay, nom client, email..."
                    class="w-full pl-10 pr-3 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-mye-gold"
                >
            </div>

            <!-- Filtre Statut -->
            <div>
                <select name="status" class="w-full py-2.5 px-3 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-mye-gold">
                    <option value="">Tous les statuts</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif (Payé)</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expiré</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                </select>
            </div>

            <!-- Filtre Formule -->
            <div>
                <select name="plan" class="w-full py-2.5 px-3 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-mye-gold">
                    <option value="">Toutes les formules</option>
                    <option value="monthly" {{ request('plan') === 'monthly' ? 'selected' : '' }}>Mensuel</option>
                    <option value="yearly" {{ request('plan') === 'yearly' ? 'selected' : '' }}>Annuel</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 px-3 bg-mye-gold hover:bg-mye-goldDark text-black font-semibold rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5 shadow-md shadow-mye-gold/20">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    Filtrer
                </button>
                @if(request()->hasAny(['search', 'status', 'plan']))
                    <a href="{{ route('admin.subscriptions.index') }}" class="p-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs transition-colors" title="Réinitialiser">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- 3. TABLEAU DES TRANSACTIONS -->
    <div class="rounded-2xl bg-mye-card border border-mye-cardBorder shadow-xl overflow-hidden">
        <div class="p-5 border-b border-mye-cardBorder flex items-center justify-between">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <i data-lucide="receipt" class="w-4 h-4 text-emerald-400"></i>
                Historique des Abonnements
            </h3>
            <span class="text-xs text-slate-400">
                {{ $subscriptions->total() }} transaction(s) répertoriée(s)
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-900/50 border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Transaction</th>
                        <th class="py-3 px-4">Client / Utilisateur</th>
                        <th class="py-3 px-4">Formule</th>
                        <th class="py-3 px-4">Montant</th>
                        <th class="py-3 px-4">Période de Validité</th>
                        <th class="py-3 px-4">Statut</th>
                        <th class="py-3 px-4 text-right">Date Opération</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($subscriptions as $sub)
                        <tr class="hover:bg-slate-800/25 transition-colors">
                            <!-- Transaction ID -->
                            <td class="py-3.5 px-4">
                                <div class="font-mono text-slate-300 font-semibold">
                                    {{ $sub->cinetpay_transaction_id ?: '#SUB-'.$sub->id }}
                                </div>
                                <div class="text-[10px] text-slate-500">Passerelle CinetPay</div>
                            </td>

                            <!-- Client -->
                            <td class="py-3.5 px-4">
                                @if($sub->user)
                                    <div class="flex items-center gap-2.5">
                                        <img 
                                            src="{{ $sub->user->avatar ?: 'https://cdn-icons-png.flaticon.com/512/149/149071.png' }}" 
                                            alt="{{ $sub->user->name }}" 
                                            class="w-7 h-7 rounded-full object-cover border border-slate-700 bg-slate-800 shrink-0"
                                            onerror="this.src='https://cdn-icons-png.flaticon.com/512/149/149071.png'"
                                        >
                                        <div>
                                            <div class="font-bold text-white">{{ $sub->user->name }}</div>
                                            <div class="text-[11px] text-slate-400">{{ $sub->user->email }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-500 italic">Utilisateur supprimé (#{{ $sub->user_id }})</span>
                                @endif
                            </td>

                            <!-- Formule -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($sub->plan === 'yearly')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-purple-500/10 text-purple-400 border border-purple-500/20">
                                        Annuel (12 mois)
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                        Mensuel (30 jours)
                                    </span>
                                @endif
                            </td>

                            <!-- Montant -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="text-sm font-extrabold text-white">
                                    {{ number_format($sub->amount, 0, ',', ' ') }}
                                    <span class="text-[10px] text-mye-gold font-bold">{{ $sub->currency ?: 'XOF' }}</span>
                                </div>
                            </td>

                            <!-- Période -->
                            <td class="py-3.5 px-4 whitespace-nowrap text-slate-300">
                                @if($sub->starts_at && $sub->expires_at)
                                    <div>Du {{ $sub->starts_at->format('d/m/Y') }} au {{ $sub->expires_at->format('d/m/Y') }}</div>
                                    <div class="text-[10px] {{ $sub->expires_at->isFuture() ? 'text-emerald-400' : 'text-slate-500' }}">
                                        {{ $sub->expires_at->isFuture() ? 'Expire ' . $sub->expires_at->diffForHumans() : 'Expiré ' . $sub->expires_at->diffForHumans() }}
                                    </div>
                                @else
                                    <span class="text-slate-600">-</span>
                                @endif
                            </td>

                            <!-- Statut -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($sub->status === 'active' && (!$sub->expires_at || $sub->expires_at->isFuture()))
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Actif & Payé
                                    </span>
                                @elseif($sub->status === 'pending')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> En Attente
                                    </span>
                                @elseif($sub->status === 'expired' || ($sub->expires_at && $sub->expires_at->isPast()))
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-800 text-slate-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span> Expiré
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-red-500/10 text-red-400 border border-red-500/20">
                                        {{ ucfirst($sub->status) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Date Opération -->
                            <td class="py-3.5 px-4 text-right whitespace-nowrap text-slate-400">
                                <div>{{ $sub->created_at ? $sub->created_at->format('d/m/Y H:i') : '-' }}</div>
                                <div class="text-[10px] text-slate-500">{{ $sub->created_at ? $sub->created_at->diffForHumans() : '' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-500">
                                <i data-lucide="credit-card" class="w-10 h-10 mx-auto text-slate-600 mb-2"></i>
                                Aucune transaction ne correspond aux critères sélectionnés.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($subscriptions->hasPages())
            <div class="p-4 border-t border-slate-800 flex items-center justify-between">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
