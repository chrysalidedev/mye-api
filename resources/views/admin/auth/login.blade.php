<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Connexion Administration - Mye</title>

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
                        }
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0A0E17;
        }
    </style>
</head>
<body class="min-h-screen bg-mye-bg text-slate-100 flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Background glowing ambient spheres -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-mye-gold/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-mye-goldDark/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <div class="inline-flex w-16 h-16 rounded-2xl bg-gradient-to-tr from-mye-goldDark via-mye-gold to-mye-goldLight items-center justify-center shadow-xl shadow-mye-gold/20 mb-4 transform hover:scale-105 transition-all">
                <span class="text-black font-extrabold text-3xl tracking-wider">M</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Administration Mye</h1>
            <p class="text-sm text-slate-400 mt-1">Plateforme de mise en relation & gestion</p>
        </div>

        <!-- Login Card -->
        <div class="bg-mye-card/90 backdrop-blur-xl border border-mye-cardBorder rounded-2xl p-8 shadow-2xl relative">
            <!-- Top Gold accent border -->
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-transparent via-mye-gold to-transparent rounded-t-2xl"></div>

            @if(session('error'))
                <div class="mb-5 p-3.5 rounded-xl bg-red-950/60 border border-red-800/50 text-red-300 text-xs flex items-center gap-2.5">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-red-400 shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="mb-5 p-3.5 rounded-xl bg-blue-950/60 border border-blue-800/50 text-blue-300 text-xs flex items-center gap-2.5">
                    <i data-lucide="info" class="w-4 h-4 text-blue-400 shrink-0"></i>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-5">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Adresse Email
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            placeholder="nom@exemple.com"
                            class="w-full pl-10 pr-4 py-3 bg-slate-900/80 border {{ $errors->has('email') ? 'border-red-500' : 'border-slate-800 focus:border-mye-gold' }} rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-mye-gold/20 transition-all"
                        >
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Mot de passe
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            required
                            placeholder="••••••••"
                            class="w-full pl-10 pr-10 py-3 bg-slate-900/80 border {{ $errors->has('password') ? 'border-red-500' : 'border-slate-800 focus:border-mye-gold' }} rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-mye-gold/20 transition-all"
                        >
                        <button
                            type="button"
                            onclick="togglePasswordVisibility()"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-200"
                        >
                            <i data-lucide="eye" id="eye-icon" class="w-4 h-4"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-400 hover:text-slate-200">
                        <input
                            type="checkbox"
                            name="remember"
                            id="remember"
                            class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-mye-gold focus:ring-mye-gold/40 focus:ring-offset-0"
                        >
                        <span>Se souvenir de moi</span>
                    </label>
                    <span class="text-mye-gold text-[11px] font-medium flex items-center gap-1">
                        <i data-lucide="shield-check" class="w-3 h-3"></i> Accès sécurisé
                    </span>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="w-full py-3.5 px-4 bg-gradient-to-r from-mye-goldDark via-mye-gold to-mye-goldLight hover:from-mye-gold hover:to-mye-goldDark text-black font-bold rounded-xl text-sm shadow-lg shadow-mye-gold/20 transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2"
                >
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Accéder au Dashboard</span>
                </button>
            </form>
        </div>

        <!-- Footer note -->
        <p class="text-center text-xs text-slate-500 mt-6">
            &copy; {{ date('Y') }} Mye Inc. Accès strictement réservé aux administrateurs.
        </p>
    </div>

    <script>
        lucide.createIcons();

        function togglePasswordVisibility() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                pwd.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }
    </script>
</body>
</html>
