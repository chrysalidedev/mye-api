@extends('admin.layouts.app')

@section('title', 'Matchs & Relations')
@section('page-title', 'Supervision du Réseau & Matchings')

@section('content')
<div class="space-y-6">

    <!-- 1. KPIS RÉSEAU -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl bg-mye-card border border-mye-cardBorder">
            <span class="text-xs text-pink-400 font-medium uppercase tracking-wider">Matchs Mutuels</span>
            <div class="text-2xl font-bold text-white mt-1">{{ number_format($totalMatches, 0, ',', ' ') }}</div>
        </div>
        <div class="p-4 rounded-xl bg-mye-card border border-mye-cardBorder">
            <span class="text-xs text-slate-400 font-medium uppercase tracking-wider">Swipes & Likes</span>
            <div class="text-2xl font-bold text-white mt-1">{{ number_format($totalSwipes, 0, ',', ' ') }}</div>
        </div>
        <div class="p-4 rounded-xl bg-mye-card border border-mye-cardBorder">
            <span class="text-xs text-purple-400 font-medium uppercase tracking-wider">Relations Connectées</span>
            <div class="text-2xl font-bold text-white mt-1">{{ number_format($acceptedConnections, 0, ',', ' ') }}</div>
        </div>
        <div class="p-4 rounded-xl bg-mye-card border border-mye-cardBorder">
            <span class="text-xs text-amber-400 font-medium uppercase tracking-wider">Demandes en Attente</span>
            <div class="text-2xl font-bold text-white mt-1">{{ number_format($pendingConnections, 0, ',', ' ') }}</div>
        </div>
    </div>

    <!-- 2. ONGLET SWITCHER -->
    <div class="flex items-center gap-2 p-1.5 rounded-2xl bg-mye-card border border-mye-cardBorder w-fit">
        <a 
            href="{{ route('admin.matches.index', ['tab' => 'matches']) }}" 
            class="px-5 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $tab === 'matches' ? 'bg-gradient-to-r from-mye-goldDark via-mye-gold to-mye-goldLight text-black shadow-lg shadow-mye-gold/20' : 'text-slate-400 hover:text-white' }}"
        >
            <i data-lucide="heart" class="w-4 h-4"></i>
            <span>Matchs Géolocalisés</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $tab === 'matches' ? 'bg-black/20 text-black' : 'bg-slate-800 text-slate-300' }}">
                {{ $totalMatches }}
            </span>
        </a>

        <a 
            href="{{ route('admin.matches.index', ['tab' => 'connections']) }}" 
            class="px-5 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $tab === 'connections' ? 'bg-gradient-to-r from-mye-goldDark via-mye-gold to-mye-goldLight text-black shadow-lg shadow-mye-gold/20' : 'text-slate-400 hover:text-white' }}"
        >
            <i data-lucide="users" class="w-4 h-4"></i>
            <span>Demandes & Relations</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $tab === 'connections' ? 'bg-black/20 text-black' : 'bg-slate-800 text-slate-300' }}">
                {{ $acceptedConnections }}
            </span>
        </a>
    </div>

    <!-- 3. CONTENU SELON L'ONGLET -->
    @if($tab === 'matches')
        <!-- FILTRES MATCHS -->
        <div class="p-5 rounded-2xl bg-mye-card border border-mye-cardBorder shadow-lg">
            <form method="GET" action="{{ route('admin.matches.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <input type="hidden" name="tab" value="matches">

                <div class="lg:col-span-2 relative">
                    <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-3.5"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Rechercher par nom de membre ou profession..."
                        class="w-full pl-10 pr-3 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-mye-gold"
                    >
                </div>

                <div>
                    <select name="filter" class="w-full py-2.5 px-3 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-mye-gold">
                        <option value="">Tous les matchs</option>
                        <option value="mutual" {{ request('filter') === 'mutual' ? 'selected' : '' }}>Matchs Mutuels uniquement</option>
                        <option value="one_way" {{ request('filter') === 'one_way' ? 'selected' : '' }}>Swipes simples</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2.5 px-3 bg-mye-gold hover:bg-mye-goldDark text-black font-semibold rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5 shadow-md shadow-mye-gold/20">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        Filtrer
                    </button>
                    @if(request()->hasAny(['search', 'filter']))
                        <a href="{{ route('admin.matches.index', ['tab' => 'matches']) }}" class="p-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs transition-colors" title="Réinitialiser">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- TABLEAU MATCHS -->
        <div class="rounded-2xl bg-mye-card border border-mye-cardBorder shadow-xl overflow-hidden">
            <div class="p-5 border-b border-mye-cardBorder flex items-center justify-between">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4 text-pink-400"></i>
                    Correspondances Géolocalisées (Tinder-like Matching)
                </h3>
                <span class="text-xs text-slate-400">{{ $matches->total() }} résultat(s)</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-900/50 border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                            <th class="py-3 px-4">Membre 1</th>
                            <th class="py-3 px-4 text-center">Interactions Croisées</th>
                            <th class="py-3 px-4">Membre 2</th>
                            <th class="py-3 px-4 text-center">Affinité</th>
                            <th class="py-3 px-4 text-center">Distance</th>
                            <th class="py-3 px-4 text-right">Date Match</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($matches as $m)
                            <tr class="hover:bg-slate-800/25 transition-colors">
                                <!-- Membre 1 -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <img 
                                            src="{{ $m->user1->avatar ?: 'https://cdn-icons-png.flaticon.com/512/149/149071.png' }}" 
                                            alt="{{ $m->user1->name ?? 'Membre' }}" 
                                            class="w-8 h-8 rounded-full object-cover border border-slate-700 bg-slate-800 shrink-0"
                                            onerror="this.src='https://cdn-icons-png.flaticon.com/512/149/149071.png'"
                                        >
                                        <div>
                                            <div class="font-bold text-white">{{ $m->user1->name ?? 'Inconnu' }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $m->user1->profession ?? $m->user1->company_name ?? 'Membre' }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Interaction centrale -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($m->is_mutual)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-pink-500/15 text-pink-400 border border-pink-500/30">
                                            <i data-lucide="heart" class="w-3.5 h-3.5 fill-current"></i>
                                            Match Mutuel
                                        </span>
                                    @else
                                        <div class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                                            <span class="px-1.5 py-0.5 rounded bg-slate-800 uppercase text-[9px] font-semibold">{{ $m->user1_action }}</span>
                                            <span>&harr;</span>
                                            <span class="px-1.5 py-0.5 rounded bg-slate-800 uppercase text-[9px] font-semibold">{{ $m->user2_action }}</span>
                                        </div>
                                    @endif
                                </td>

                                <!-- Membre 2 -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <img 
                                            src="{{ $m->user2->avatar ?: 'https://cdn-icons-png.flaticon.com/512/149/149071.png' }}" 
                                            alt="{{ $m->user2->name ?? 'Membre' }}" 
                                            class="w-8 h-8 rounded-full object-cover border border-slate-700 bg-slate-800 shrink-0"
                                            onerror="this.src='https://cdn-icons-png.flaticon.com/512/149/149071.png'"
                                        >
                                        <div>
                                            <div class="font-bold text-white">{{ $m->user2->name ?? 'Inconnu' }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $m->user2->profession ?? $m->user2->company_name ?? 'Membre' }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Compatibilité -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($m->compatibility_score)
                                        <span class="font-bold text-emerald-400 px-2 py-0.5 rounded bg-emerald-500/10">
                                            {{ $m->compatibility_score }}%
                                        </span>
                                    @else
                                        <span class="text-slate-600">-</span>
                                    @endif
                                </td>

                                <!-- Distance -->
                                <td class="py-3.5 px-4 text-center text-slate-300">
                                    @if($m->distance)
                                        <span>{{ $m->distance >= 1000 ? round($m->distance / 1000, 1) . ' km' : round($m->distance) . ' m' }}</span>
                                    @else
                                        <span class="text-slate-600">-</span>
                                    @endif
                                </td>

                                <!-- Date -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap text-slate-400">
                                    <div>{{ $m->matched_at ? $m->matched_at->format('d/m/Y H:i') : $m->created_at->format('d/m/Y') }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $m->matched_at ? $m->matched_at->diffForHumans() : $m->created_at->diffForHumans() }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-500">
                                    Aucune correspondance enregistrée pour ces critères.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($matches->hasPages())
                <div class="p-4 border-t border-slate-800 flex items-center justify-between">
                    {{ $matches->links() }}
                </div>
            @endif
        </div>

    @else
        <!-- FILTRES RELATIONS -->
        <div class="p-5 rounded-2xl bg-mye-card border border-mye-cardBorder shadow-lg">
            <form method="GET" action="{{ route('admin.matches.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <input type="hidden" name="tab" value="connections">

                <div class="lg:col-span-2 relative">
                    <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-3.5"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Rechercher émetteur ou récepteur..."
                        class="w-full pl-10 pr-3 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-mye-gold"
                    >
                </div>

                <div>
                    <select name="status" class="w-full py-2.5 px-3 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-mye-gold">
                        <option value="">Tous les statuts</option>
                        <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Acceptée</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejetée</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2.5 px-3 bg-mye-gold hover:bg-mye-goldDark text-black font-semibold rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5 shadow-md shadow-mye-gold/20">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        Filtrer
                    </button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.matches.index', ['tab' => 'connections']) }}" class="p-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs transition-colors" title="Réinitialiser">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- TABLEAU RELATIONS -->
        <div class="rounded-2xl bg-mye-card border border-mye-cardBorder shadow-xl overflow-hidden">
            <div class="p-5 border-b border-mye-cardBorder flex items-center justify-between">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4 text-purple-400"></i>
                    Demandes de Connexion & Réseau Professionnel
                </h3>
                <span class="text-xs text-slate-400">{{ $connections->total() }} relation(s)</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-900/50 border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                            <th class="py-3 px-4">Émetteur (Demandeur)</th>
                            <th class="py-3 px-4 text-center">Direction</th>
                            <th class="py-3 px-4">Destinataire</th>
                            <th class="py-3 px-4 text-center">Statut</th>
                            <th class="py-3 px-4 text-right">Date Demande</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($connections as $conn)
                            <tr class="hover:bg-slate-800/25 transition-colors">
                                <!-- Émetteur -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <img 
                                            src="{{ $conn->sender->avatar ?: 'https://cdn-icons-png.flaticon.com/512/149/149071.png' }}" 
                                            alt="{{ $conn->sender->name ?? 'Demandeur' }}" 
                                            class="w-7 h-7 rounded-full object-cover border border-slate-700 bg-slate-800 shrink-0"
                                            onerror="this.src='https://cdn-icons-png.flaticon.com/512/149/149071.png'"
                                        >
                                        <div>
                                            <div class="font-bold text-white">{{ $conn->sender->name ?? 'Compte supprimé' }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $conn->sender->email ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Flèche -->
                                <td class="py-3.5 px-4 text-center text-slate-500">
                                    <i data-lucide="arrow-right" class="w-4 h-4 mx-auto text-mye-gold"></i>
                                </td>

                                <!-- Destinataire -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <img 
                                            src="{{ $conn->receiver->avatar ?: 'https://cdn-icons-png.flaticon.com/512/149/149071.png' }}" 
                                            alt="{{ $conn->receiver->name ?? 'Destinataire' }}" 
                                            class="w-7 h-7 rounded-full object-cover border border-slate-700 bg-slate-800 shrink-0"
                                            onerror="this.src='https://cdn-icons-png.flaticon.com/512/149/149071.png'"
                                        >
                                        <div>
                                            <div class="font-bold text-white">{{ $conn->receiver->name ?? 'Compte supprimé' }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $conn->receiver->email ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Statut -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($conn->status === 'accepted')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Connectés
                                        </span>
                                    @elseif($conn->status === 'pending')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> En Attente
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-red-500/10 text-red-400 border border-red-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Refusée
                                        </span>
                                    @endif
                                </td>

                                <!-- Date -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap text-slate-400">
                                    <div>{{ $conn->created_at ? $conn->created_at->format('d/m/Y H:i') : '-' }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $conn->created_at ? $conn->created_at->diffForHumans() : '' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-500">
                                    Aucune relation réseau trouvée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($connections->hasPages())
                <div class="p-4 border-t border-slate-800 flex items-center justify-between">
                    {{ $connections->links() }}
                </div>
            @endif
        </div>
    @endif

</div>
@endsection
