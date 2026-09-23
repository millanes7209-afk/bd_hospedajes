<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950 text-slate-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — Salteñas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="h-full flex items-center justify-center p-4 bg-slate-950 text-slate-100 font-sans antialiased">

    <div class="w-full max-w-md space-y-6">

        <!-- Logo & Branding -->
        <div class="text-center space-y-2">
            <div
                class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-600 to-amber-400 text-slate-950 font-black text-3xl shadow-xl shadow-amber-500/20 mb-2">
                🥟
            </div>
            <h1 class="text-2xl font-black tracking-tight text-white uppercase">
                SALTEÑAS
            </h1>
            <p class="text-xs text-slate-400 font-medium uppercase tracking-widest">
                Control de Ventas & Analítica
            </p>
        </div>

        <!-- Card Login Form -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl space-y-6">

            @if ($errors->any())
                <div
                    class="p-3.5 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs font-bold space-y-1">
                    @foreach ($errors->all() as $error)
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-exclamation text-sm"></i>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-[11px] font-black uppercase text-slate-400 mb-1.5">
                        Correo Electrónico
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-3 text-slate-500 text-xs">
                            <i class="fa-solid fa-envelope"></i>
                        </span>
                        <input type="email" name="email" required autofocus value="{{ old('email') }}"
                            placeholder="admin@saltenas.com"
                            class="w-full bg-slate-950 border border-slate-700 rounded-xl pl-9 pr-3 py-2.5 text-xs font-bold text-white focus:border-amber-500 focus:outline-none transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-black uppercase text-slate-400 mb-1.5">
                        Contraseña
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-3 text-slate-500 text-xs">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" name="password" required placeholder="••••••••"
                            class="w-full bg-slate-950 border border-slate-700 rounded-xl pl-9 pr-3 py-2.5 text-xs font-bold text-white focus:border-amber-500 focus:outline-none transition-all">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-400 font-medium">
                        <input type="checkbox" name="remember"
                            class="rounded border-slate-700 bg-slate-950 text-amber-500 focus:ring-amber-500">
                        <span>Recordarme</span>
                    </label>
                </div>

                <button type="submit"
                    class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs uppercase tracking-wider shadow-lg shadow-amber-500/20 transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-right-to-bracket text-sm"></i> Iniciar Sesión
                </button>
            </form>
        </div>

        <div class="text-center text-xs text-slate-600 font-bold">
            Sistema Salteñas © {{ date('Y') }}
        </div>
    </div>
</body>

</html>