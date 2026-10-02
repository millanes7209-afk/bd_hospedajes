@extends('layouts.app')

@section('title', 'Usuarios — Sistema Salteñas')

@section('content')
    <div class="space-y-6" x-data="{ nuevoUsuarioModal: false }">

        <!-- Header Section -->
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
            <div>
                <h1
                    class="text-base font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-users text-amber-500 text-lg"></i>
                    GESTIÓN DE USUARIOS Y MI PERFIL
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 uppercase">
                    ADMINISTRA LOS USUARIOS DEL SISTEMA Y CAMBIA TU CONTRASEÑA DE ACCESO.
                </p>
            </div>
            <button type="button" @click="nuevoUsuarioModal = true"
                class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-user-plus"></i>
                <span>NUEVO USUARIO</span>
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Columna Izquierda: Mi Perfil y Cambio de Contraseña (Col 5) -->
            <div class="lg:col-span-5 space-y-6">

                <!-- Card de Usuario Autenticado -->
                <div
                    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm space-y-4">
                    <div class="flex items-center gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
                        <div
                            class="w-12 h-12 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-300 text-slate-950 font-black text-xl flex items-center justify-center shadow-md">
                            {{ strtoupper(substr($currentUser->name, 0, 1)) }}
                        </div>
                        <div>
                            <span
                                class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-widest block">MI
                                SESIÓN ACTUAL</span>
                            <h2 class="text-sm font-black text-slate-900 dark:text-white uppercase">{{ $currentUser->name }}
                            </h2>
                            <span
                                class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ $currentUser->email }}</span>
                        </div>
                    </div>

                    <!-- Formulario de Cambio de Contraseña -->
                    <form action="{{ route('usuarios.password.update') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="space-y-1">
                            <h3
                                class="text-xs font-black text-slate-900 dark:text-white uppercase flex items-center gap-1.5">
                                <i class="fa-solid fa-lock text-amber-500"></i> CAMBIAR MI CONTRASEÑA
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 uppercase">
                                INGRESA TU CLAVE ACTUAL Y LA NUEVA CONTRASEÑA DE ACCESO.
                            </p>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-700 dark:text-slate-300 uppercase mb-1">
                                CONTRASEÑA ACTUAL <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="current_password" required
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none pl-9">
                                <i class="fa-solid fa-key absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-700 dark:text-slate-300 uppercase mb-1">
                                NUEVA CONTRASEÑA <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="password" required minlength="6"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none pl-9">
                                <i class="fa-solid fa-lock absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-700 dark:text-slate-300 uppercase mb-1">
                                CONFIRMAR NUEVA CONTRASEÑA <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" required minlength="6"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none pl-9">
                                <i class="fa-solid fa-check-double absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full py-2.5 px-4 bg-slate-900 dark:bg-amber-500 hover:bg-slate-800 dark:hover:bg-amber-400 text-white dark:text-slate-950 font-black text-xs uppercase rounded-xl transition-all shadow-sm flex items-center justify-center gap-2">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span>ACTUALIZAR MI CONTRASEÑA</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Columna Derecha: Lista de Usuarios Registrados (Col 7) -->
            <div class="lg:col-span-7">
                <div
                    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
                    <div
                        class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <h2
                            class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-user-shield text-emerald-500"></i> USUARIOS DEL SISTEMA
                            ({{ count($users) }})
                        </h2>
                    </div>

                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr
                                class="bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 text-[10px] font-black uppercase text-slate-500 dark:text-slate-400">
                                <th class="py-3 px-4">USUARIO</th>
                                <th class="py-3 px-4">CORREO ELECTRÓNICO</th>
                                <th class="py-3 px-4 text-center">FECHA REGISTRO</th>
                                <th class="py-3 px-4 text-center">ESTADO</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse($users as $u)
                                <tr
                                    class="{{ $u->id === $currentUser->id ? 'bg-amber-50/50 dark:bg-amber-500/5' : '' }} hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <div
                                                class="w-7 h-7 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-black text-xs flex items-center justify-center uppercase">
                                                {{ substr($u->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <span
                                                    class="font-black text-slate-900 dark:text-white uppercase block">{{ $u->name }}</span>
                                                @if($u->id === $currentUser->id)
                                                    <span
                                                        class="text-[9px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-wide">(TÚ
                                                        MISM@)</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 dark:text-slate-300 font-bold">{{ $u->email }}</td>
                                    <td class="py-3 px-4 text-center text-slate-500 dark:text-slate-400 font-bold text-[11px]">
                                        {{ \Carbon\Carbon::parse($u->created_at)->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span
                                            class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-black text-[10px] uppercase">
                                            ACTIVO
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-400 font-bold uppercase">NO HAY OTROS
                                        USUARIOS REGISTRADOS.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MODAL REGISTRO NUEVO USUARIO -->
        <div x-show="nuevoUsuarioModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div @click.away="nuevoUsuarioModal = false"
                class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5">

                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase flex items-center gap-2">
                        <i class="fa-solid fa-user-plus text-amber-500"></i> REGISTRAR NUEVO USUARIO
                    </h3>
                    <button type="button" @click="nuevoUsuarioModal = false"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-white">✕</button>
                </div>

                <form action="{{ route('usuarios.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-slate-700 dark:text-slate-300 uppercase mb-1">
                            NOMBRE COMPLETO <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" required placeholder="EJ. CARLOS MENDOZA"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none uppercase">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-700 dark:text-slate-300 uppercase mb-1">
                            CORREO ELECTRÓNICO <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" name="email" required placeholder="usuario@saltenas.com"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-700 dark:text-slate-300 uppercase mb-1">
                            CONTRASEÑA <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" name="password" required minlength="6" placeholder="MÍNIMO 6 CARACTERES"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-700 dark:text-slate-300 uppercase mb-1">
                            CONFIRMAR CONTRASEÑA <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" name="password_confirmation" required minlength="6"
                            placeholder="REPITE LA CONTRASEÑA"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="nuevoUsuarioModal = false"
                            class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs uppercase">
                            CANCELAR
                        </button>
                        <button type="submit"
                            class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase shadow-sm">
                            GUARDAR USUARIO
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection