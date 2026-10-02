@extends('layouts.app')

@section('title', 'Usuarios — Sistema Salteñas')

@section('content')
    <div class="max-w-4xl mx-auto space-y-4">
        <div
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                <h1 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">
                    👥 USUARIOS DEL SISTEMA
                </h1>
            </div>

            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr
                        class="bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 text-[10px] font-black uppercase text-slate-500 dark:text-slate-400">
                        <th class="py-2.5 px-4">NOMBRE</th>
                        <th class="py-2.5 px-4">CORREO</th>
                        <th class="py-2.5 px-4 text-right">MI CONTRASEÑA</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($users as $u)
                        <tr
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors {{ $u->id === $currentUserId ? 'bg-amber-50/40 dark:bg-amber-500/5' : '' }}">
                            <td class="py-3 px-4 font-black text-slate-900 dark:text-white uppercase">
                                {{ $u->name }}
                                @if($u->id === $currentUserId)
                                    <span
                                        class="ml-1 text-[9px] font-black text-amber-600 dark:text-amber-400 uppercase">(TÚ)</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300 font-bold">
                                {{ $u->email }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if($u->id === $currentUserId)
                                    <form action="{{ route('usuarios.password.update') }}" method="POST"
                                        class="flex items-center justify-end gap-2">
                                        @csrf
                                        <input type="password" name="password" required placeholder="Nueva contraseña" minlength="4"
                                            class="bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1 text-xs font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none w-44">
                                        <button type="submit"
                                            class="px-3 py-1 bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-[11px] uppercase rounded-lg shadow-sm transition-all">
                                            GUARDAR
                                        </button>
                                    </form>
                                @else
                                    <span class="text-slate-400 font-bold text-[11px]">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection