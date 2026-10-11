<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Tableau de bord') - Administration Mye</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        mye: {
                            gold: '#CBA948',
                            goldLight: '#E5D187',
                            goldDark: '#A8872B',
                            bg: '#0A0E17',
                            card: '#111827',
                            cardBorder: '#1F293D',
                            sidebar: '#0D131F',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0A0E17;
            color: #E2E8F0;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0A0E17;
        }
        ::-webkit-scrollbar-thumb {
            background: #1F293D;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #CBA948;
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen bg-mye-bg text-slate-100 flex overflow-x-hidden">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-mye-sidebar border-r border-mye-cardBorder min-h-screen flex flex-col fixed inset-y-0 left-0 z-40 transition-transform duration-300" id="sidebar">
        <!-- Logo Header -->
        <div class="h-20 flex items-center justify-between px-6 border-b border-mye-cardBorder/60 bg-mye-sidebar/50 backdrop-blur-md">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-mye-goldDark via-mye-gold to-mye-goldLight flex items-center justify-center shadow-lg shadow-mye-gold/20 group-hover:scale-105 transition-all">
                    <span class="text-black font-extrabold text-xl tracking-wider">M</span>
                </div>
                <div>
                    <h1 class="text-lg font-bold tracking-tight text-white flex items-center gap-1.5">
                        MYE <span class="text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded bg-mye-gold/20 text-mye-gold border border-mye-gold/30">ADMIN</span>
                    </h1>
                    <p class="text-[11px] text-slate-400">Plateforme de mise en relation</p>
                </div>
            </a>
        </div>

        <!-- Navigation Links -->
        <div class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
            <div class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                Menu Principal
            </div>

            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-mye-gold/20 to-transparent text-mye-gold border-l-4 border-mye-gold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">
                <i data-lucide="layout-dashboard" class="w-5 h-5 {{ request()->routeIs('admin.dashboard') ? 'text-mye-gold' : 'text-slate-400' }}"></i>
                <span>Tableau de bord</span>
            </a>

            <div class="pt-5 px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                Supervision
            </div>

            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.users.*') ? 'bg-gradient-to-r from-mye-gold/20 to-transparent text-mye-gold border-l-4 border-mye-gold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">
                <i data-lucide="users" class="w-5 h-5 {{ request()->routeIs('admin.users.*') ? 'text-mye-gold' : 'text-slate-400' }}"></i>
                <span>Utilisateurs & Profils</span>
            </a>

            <a href="{{ route('admin.subscriptions.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.subscriptions.*') ? 'bg-gradient-to-r from-mye-gold/20 to-transparent text-mye-gold border-l-4 border-mye-gold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">
                <i data-lucide="credit-card" class="w-5 h-5 {{ request()->routeIs('admin.subscriptions.*') ? 'text-mye-gold' : 'text-slate-400' }}"></i>
                <span>Abonnements & Finances</span>
            </a>

            <a href="{{ route('admin.matches.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.matches.*') ? 'bg-gradient-to-r from-mye-gold/20 to-transparent text-mye-gold border-l-4 border-mye-gold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">
                <i data-lucide="sparkles" class="w-5 h-5 {{ request()->routeIs('admin.matches.*') ? 'text-mye-gold' : 'text-slate-400' }}"></i>
                <span>Matchs & Relations</span>
            </a>

            <div class="pt-5 px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                Système
            </div>

            <a href="{{ url('/') }}" target="_blank" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/40 transition-all">
                <i data-lucide="external-link" class="w-5 h-5 text-slate-400"></i>
                <span>Site public Mye</span>
            </a>
        </div>

        <!-- Admin Profile Footer in Sidebar -->
        <div class="p-4 border-t border-mye-cardBorder/60 bg-mye-sidebar/80">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-800 border border-mye-gold/30 flex items-center justify-center font-bold text-mye-gold">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name ?? 'Administrateur' }}</p>
                        <p class="text-[11px] text-mye-gold truncate">Super Admin</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                    @csrf
                    <button type="submit" title="Se déconnecter" class="p-2 text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT WRAPPER -->
    <div class="flex-1 flex flex-col pl-64 min-h-screen">

        <!-- TOPBAR -->
        <header class="h-20 bg-mye-card/60 backdrop-blur-xl border-b border-mye-cardBorder px-8 flex items-center justify-between sticky top-0 z-30">
            <!-- Left: Title & Live Clock -->
            <div class="flex items-center gap-4">
                <button type="button" class="lg:hidden text-slate-400 hover:text-white" onclick="document.getElementById('sidebar').classList.toggle('-translate-x-full')">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <div>
                    <h2 class="text-lg font-bold text-white tracking-tight">@yield('page-title', 'Vue d\'ensemble')</h2>
                    <p class="text-xs text-slate-400">Tableau de bord d'administration et métriques globales</p>
                </div>
            </div>

            <!-- Right: Status & Admin Quick actions -->
            <div class="flex items-center gap-4">
                <!-- System status pill -->
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    API & Plateforme En Ligne
                </div>

                <!-- Live time display -->
                <div class="hidden md:flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 text-xs font-mono" id="live-clock">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-mye-gold"></i>
                    <span id="current-time">--:--:--</span>
                </div>

                <!-- Logout Button -->
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500 hover:text-white transition-all">
                        <i data-lucide="power" class="w-3.5 h-3.5"></i>
                        <span>Déconnexion</span>
                    </button>
                </form>
            </div>
        </header>

        <!-- FLASH NOTIFICATIONS -->
        <div class="px-8 pt-6">
            @if(session('success'))
                <div class="p-4 mb-4 rounded-xl bg-emerald-950/60 border border-emerald-600/40 text-emerald-300 flex items-center justify-between backdrop-blur-md">
                    <div class="flex items-center gap-3">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-400"></i>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 mb-4 rounded-xl bg-red-950/60 border border-red-600/40 text-red-300 flex items-center justify-between backdrop-blur-md">
                    <div class="flex items-center gap-3">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-red-400"></i>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-white">&times;</button>
                </div>
            @endif

            @if(session('info'))
                <div class="p-4 mb-4 rounded-xl bg-blue-950/60 border border-blue-600/40 text-blue-300 flex items-center justify-between backdrop-blur-md">
                    <div class="flex items-center gap-3">
                        <i data-lucide="info" class="w-5 h-5 text-blue-400"></i>
                        <span class="text-sm font-medium">{{ session('info') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-blue-400 hover:text-white">&times;</button>
                </div>
            @endif
        </div>

        <!-- MAIN BODY -->
        <main class="flex-1 px-8 py-4">
            @yield('content')
        </main>

        <!-- FOOTER -->
        <footer class="px-8 py-5 border-t border-mye-cardBorder/60 text-center sm:text-left flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500">
            <div>&copy; {{ date('Y') }} Mye - Plateforme de mise en relation & recrutement. Tous droits réservés.</div>
            <div class="mt-2 sm:mt-0 flex items-center gap-4">
                <span>Version 1.1.0</span>
                <span>&bull;</span>
                <span class="text-mye-gold">Sécurité Niveau 4</span>
            </div>
        </footer>
    </div>

    <!-- Init icons and clock -->
    <script>
        lucide.createIcons();

        function updateClock() {
            const clockEl = document.getElementById('current-time');
            if (clockEl) {
                const now = new Date();
                clockEl.textContent = now.toLocaleTimeString('fr-FR');
            }
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
    @stack('scripts')
</body>
</html>
