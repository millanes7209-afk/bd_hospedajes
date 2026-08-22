<!doctype html>
<html lang="es" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PANEL CENTRAL SUPERADMIN - SCARY</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: radial-gradient(circle at center, #1a1c29 0%, #08090d 100%);
            color: #f3f4f6;
            font-family: system-ui, -apple-system, sans-serif;
        }

        .glass-panel {
            background: rgba(21, 23, 35, 0.75);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 230, 109, 0.15);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4">

    <div class="glass-panel w-full max-w-md p-8 rounded-3xl relative overflow-hidden">
        <!-- Accent Glow -->
        <div class="absolute -top-12 -left-12 w-32 h-32 bg-amber-500/20 rounded-full blur-3xl pointer-events-none">
        </div>
        <div class="absolute -bottom-12 -right-12 w-32 h-32 bg-red-500/20 rounded-full blur-3xl pointer-events-none">
        </div>

        <div class="text-center mb-8">
            <div
                class="w-16 h-16 bg-gradient-to-br from-amber-400 to-red-500 rounded-2xl mx-auto flex items-center justify-center shadow-lg shadow-amber-500/20 mb-4">
                <i class="fa-solid fa-shield-halved text-2xl text-black"></i>
            </div>
            <h1 class="text-2xl font-black tracking-wider text-white uppercase">PANEL CENTRAL</h1>
            <p class="text-xs font-bold text-amber-400 uppercase tracking-widest mt-1">SCARY CONTROL MULTI-TENANT</p>
        </div>

        @if (session('success'))
            <div
                class="mb-4 p-3.5 rounded-xl bg-green-500/20 border border-green-500/40 text-green-300 font-bold text-xs uppercase flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-sm"></i> {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div
                class="mb-4 p-3.5 rounded-xl bg-red-500/20 border border-red-500/40 text-red-300 font-bold text-xs uppercase flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-sm"></i> {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('superadmin.login.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-black uppercase text-gray-300 mb-1.5">CORREO SUPERADMIN *</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fa-solid fa-envelope text-xs"></i>
                    </span>
                    <input type="email" name="email" required placeholder="admin@scary.com"
                        class="w-full bg-black/50 border border-white/10 text-white rounded-xl py-3 pl-10 pr-4 text-xs font-bold focus:outline-none focus:border-amber-400 transition-colors">
                </div>
            </div>

            <div>
                <label class="block text-xs font-black uppercase text-gray-300 mb-1.5">CONTRASEÑA MAESTRA *</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fa-solid fa-key text-xs"></i>
                    </span>
                    <input type="password" name="password" required placeholder="••••••••"
                        class="w-full bg-black/50 border border-white/10 text-white rounded-xl py-3 pl-10 pr-4 text-xs font-bold focus:outline-none focus:border-amber-400 transition-colors">
                </div>
            </div>

            <button type="submit"
                class="w-full mt-2 py-3.5 px-4 bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-black font-black text-xs uppercase tracking-wider rounded-xl shadow-xl shadow-amber-500/20 transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-right-to-bracket text-sm"></i> INGRESAR AL PANEL
            </button>
        </form>

        <div
            class="mt-8 pt-6 border-t border-white/10 text-center text-[10px] text-gray-500 font-bold uppercase tracking-wider">
            SISTEMA SAAS CONTROL DE EMPRESAS &bull; SCARY MULTI-TENANT
        </div>
    </div>

</body>

</html>