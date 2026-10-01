<!doctype html>
<html lang="es" class="dark-mode">

<head>
    <meta charset="utf-8">
    <script>(function () { var s = localStorage.getItem('theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Iniciar Sesión</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FFE66D',
                        accent: '#E23E1A',
                        dark: '#09090c'
                    }
                }
            }
        }
    </script>
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

        :root {
            --color-bg: #09090c;
            --color-bg-alt: #101015;
            --color-primary: #FFE66D;
            --color-primary-rgb: 255, 230, 109;
            --color-accent: #E23E1A;
            --color-card: #15151e;
            --color-card-border: rgba(255, 255, 255, 0.07);
            --color-text: #f3f4f6;
            --color-text-muted: #9ca3af;
            --color-input-bg: rgba(9, 9, 12, 0.6);
            --color-input-border: rgba(255, 255, 255, 0.1);
        }

        .light-mode {
            --color-bg: #e2e8f0;
            --color-bg-alt: #cbd5e1;
            --color-primary: #b45309;
            --color-primary-rgb: 180, 83, 9;
            --color-accent: #c52c0c;
            --color-card: #f8fafc;
            --color-card-border: rgba(0, 0, 0, 0.12);
            --color-text: #1e293b;
            --color-text-muted: #64748b;
            --color-input-bg: #f1f5f9;
            --color-input-border: rgba(0, 0, 0, 0.15);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--color-bg);
            color: var(--color-text);
            min-height: 100vh;
            margin: 0;
            -webkit-font-smoothing: antialiased;
            transition: background-color 0.3s, color 0.3s;
        }

        .mode-toggle-btn {
            background: var(--color-card);
            border: 1px solid var(--color-card-border);
            color: var(--color-text);
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .mode-toggle-btn:hover {
            transform: scale(1.1);
        }

        .glass-card {
            background: var(--color-card);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--color-card-border);
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.1);
            border-radius: 16px;
            transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease, background 0.3s;
        }

        .glass-card:hover {
            border-color: rgba(var(--color-primary-rgb), 0.5);
        }

        .form-input {
            background: var(--color-input-bg);
            border: 1.5px solid var(--color-input-border);
            color: var(--color-text);
            border-radius: 10px;
            padding: 10px 14px;
            width: 100%;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--color-primary);
        }

        .form-input::placeholder {
            color: var(--color-text-muted);
            opacity: 0.5;
        }

        .btn-primary {
            background: var(--color-primary);
            color: #09090c;
            font-weight: 700;
            padding: 10px 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            filter: brightness(1.1);
        }

        html.light-mode .admin-subcard {
            background-color: #f8fafc !important;
            border-color: #e2e8f0 !important;
        }

        html.dark-mode .admin-subcard {
            background-color: rgba(0, 0, 0, 0.3) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        html.light-mode .admin-text-main {
            color: #111827 !important;
        }

        html.dark-mode .admin-text-main {
            color: #ffffff !important;
        }

        html.light-mode .admin-text-gold {
            color: #b45309 !important;
        }

        html.dark-mode .admin-text-gold {
            color: #ffe66d !important;
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4">

    <!-- Botón toggle modo (fijo esquina sup-der) -->
    <button id="modeToggle" class="mode-toggle-btn" style="position:fixed;top:16px;right:16px;z-index:50;"
        title="Cambiar modo">
        <span id="modeIcon">☀️</span>
    </button>

    <div class="w-full max-w-md mx-auto">
        <!-- Logo / Icon Header Container -->
        <div class="flex flex-col items-center mb-8">
            <div class="w-32 h-32 mb-2 hover:scale-105 transition-transform duration-300 admin-subcard rounded-2xl p-4 border flex items-center justify-center shadow-lg"
                style="border-color:var(--color-card-border)">
                <i class="fa-solid fa-user-shield text-5xl admin-text-gold"></i>
            </div>
        </div>

        <!-- Glassmorphism Login Card -->
        <div class="glass-card p-8">
            <h2 class="text-xl font-bold text-center mb-6 tracking-wide admin-text-main uppercase border-b pb-4"
                style="border-color:var(--color-card-border)">
                <i class="fa-solid fa-lock admin-text-gold mr-2"></i>INICIAR SESIÓN
            </h2>

            @if ($errors->any())
                <div
                    class="mb-5 p-3.5 bg-red-950/40 border border-red-500/50 rounded-xl text-red-200 text-sm font-semibold space-y-1">
                    @foreach ($errors->all() as $error)
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-exclamation text-red-500 text-base"></i>
                            <span>{{ strtoupper($error) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">
                        CORREO ELECTRÓNICO
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-3.5 text-gray-500">
                            <i class="fa-regular fa-envelope"></i>
                        </span>
                        <input type="email" name="email" required autofocus class="form-input pl-10"
                            value="{{ old('email') }}" placeholder="admin@ejemplo.com" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">
                        CONTRASEÑA
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-3.5 text-gray-500">
                            <i class="fa-solid fa-key"></i>
                        </span>
                        <input id="contrasena" type="password" name="password" required class="form-input pl-10 pr-10"
                            placeholder="••••••••" />
                        <button type="button" id="togglePass"
                            class="absolute right-3 top-3.5 text-gray-400 hover:text-white transition-colors"
                            aria-label="Mostrar contraseña">
                            <i id="eyeIcon" class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="btn-primary px-6 py-3 w-full justify-center text-sm flex items-center justify-center gap-1.5 uppercase font-bold">
                        <i class="fa-solid fa-right-to-bracket"></i>INICIAR SESIÓN
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const toggle = document.getElementById('togglePass');
        const pass = document.getElementById('contrasena');
        const eyeIcon = document.getElementById('eyeIcon');
        if (toggle) {
            toggle.addEventListener('click', () => {
                if (pass.type === 'password') {
                    pass.type = 'text';
                    eyeIcon.classList.remove('fa-regular', 'fa-eye');
                    eyeIcon.classList.add('fa-solid', 'fa-eye-slash');
                } else {
                    pass.type = 'password';
                    eyeIcon.classList.remove('fa-solid', 'fa-eye-slash');
                    eyeIcon.classList.add('fa-regular', 'fa-eye');
                }
            });
        }

        // ─── TEMA OSCURO / CLARO ───────────────────────────────────────
        const htmlNode = document.documentElement;
        const modeBtn = document.getElementById('modeToggle');
        const modeIcon = document.getElementById('modeIcon');
        function applyTheme(theme) {
            if (theme === 'light') {
                htmlNode.className = 'light-mode';
                modeIcon.textContent = '🌙';
            } else {
                htmlNode.className = 'dark-mode';
                modeIcon.textContent = '☀️';
            }
            localStorage.setItem('theme', theme);
        }
        applyTheme(localStorage.getItem('theme') || 'dark');
        if (modeBtn) {
            modeBtn.addEventListener('click', function () {
                applyTheme(htmlNode.classList.contains('light-mode') ? 'dark' : 'light');
            });
        }
    </script>
</body>

</html>