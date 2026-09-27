@extends('layouts.app')

@section('title', 'Dashboard — Sistema Salteñas')

@section('content')
    <div class="space-y-6">

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            <!-- Bóveda Saldo -->
            <a href="{{ route('boveda.index') }}"
                class="bg-slate-900 border border-amber-500/30 hover:border-amber-500/60 rounded-2xl p-5 flex items-center gap-4 group transition-all shadow-lg shadow-amber-500/5">
                <div
                    class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center group-hover:bg-amber-500/20 transition-all">
                    <i class="fa-solid fa-vault text-amber-400 text-xl"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Saldo Bóveda</span>
                    <span
                        class="text-2xl font-black {{ $saldoBoveda >= 0 ? 'text-amber-400' : 'text-rose-400' }} tracking-tight">
                        Bs. {{ number_format($saldoBoveda, 2) }}
                    </span>
                </div>
            </a>

            <!-- Carritos Activos -->
            <a href="{{ route('carritos.index') }}"
                class="bg-slate-900 border border-purple-500/30 hover:border-purple-500/60 rounded-2xl p-5 flex items-center gap-4 group transition-all">
                <div
                    class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center group-hover:bg-purple-500/20 transition-all">
                    <i class="fa-solid fa-store text-purple-400 text-xl"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Carritos
                        Activos</span>
                    <span class="text-2xl font-black text-white tracking-tight">{{ $totalCarritos }}</span>
                </div>
            </a>

            <!-- Cierres con Inconsistencias -->
            <a href="{{ route('cierres.index') }}"
                class="bg-slate-900 border border-rose-500/30 hover:border-rose-500/60 rounded-2xl p-5 flex items-center gap-4 group transition-all">
                <div
                    class="w-12 h-12 rounded-xl bg-rose-500/10 flex items-center justify-center group-hover:bg-rose-500/20 transition-all">
                    <i class="fa-solid fa-triangle-exclamation text-rose-400 text-xl"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Cierres
                        Inconsistentes</span>
                    <span
                        class="text-2xl font-black {{ $totalCierresInconsistentes > 0 ? 'text-rose-400' : 'text-emerald-400' }} tracking-tight">
                        {{ $totalCierresInconsistentes }}
                    </span>
                </div>
            </a>

            <!-- Compras Registradas -->
            <a href="{{ route('compras.index') }}"
                class="bg-slate-900 border border-cyan-500/30 hover:border-cyan-500/60 rounded-2xl p-5 flex items-center gap-4 group transition-all">
                <div
                    class="w-12 h-12 rounded-xl bg-cyan-500/10 flex items-center justify-center group-hover:bg-cyan-500/20 transition-all">
                    <i class="fa-solid fa-cart-shopping text-cyan-400 text-xl"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Compras
                        Totales</span>
                    <span class="text-2xl font-black text-white tracking-tight">{{ $totalUltimasCompras }}</span>
                </div>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Últimos Cierres (Col 7) -->
            <div class="lg:col-span-7">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                    <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                        <h2 class="text-xs font-black text-white uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-emerald-400"></i> Últimos 5 Cierres Diarios
                        </h2>
                        <a href="{{ route('cierres.index') }}"
                            class="text-[10px] font-black text-amber-400 hover:underline uppercase">Ver Todos</a>
                    </div>

                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr
                                class="bg-slate-950/70 border-b border-slate-800 text-[10px] font-black uppercase text-slate-400">
                                <th class="py-3 px-4">Fecha</th>
                                <th class="py-3 px-4">Carrito</th>
                                <th class="py-3 px-4 text-right">Estimado</th>
                                <th class="py-3 px-4 text-right">Real</th>
                                <th class="py-3 px-4 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @forelse($ultimosCierres as $c)
                                <tr class="{{ $c->inconsistente ? 'bg-rose-500/5' : '' }} transition-colors">
                                    <td class="py-3 px-4 font-bold text-white">
                                        {{ \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') }}</td>
                                    <td class="py-3 px-4 text-purple-400 font-bold">{{ $c->carrito->nombre ?? '—' }}</td>
                                    <td class="py-3 px-4 text-right text-slate-300 font-bold">Bs.
                                        {{ number_format($c->monto_estimado, 2) }}</td>
                                    <td class="py-3 px-4 text-right text-white font-black">Bs.
                                        {{ number_format($c->monto_real, 2) }}</td>
                                    <td class="py-3 px-4 text-center">
                                        @if(!$c->inconsistente)
                                            <span
                                                class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 font-black text-[10px]">✓</span>
                                        @else
                                            <span
                                                class="px-2 py-0.5 rounded bg-rose-500/10 text-rose-400 font-black text-[10px] animate-pulse">⚠</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-500 font-bold uppercase">Sin cierres
                                        registrados aún.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Panel de Alertas / Inconsistencias (Col 5) -->
            <div class="lg:col-span-5">
                <div
                    class="bg-slate-900 border {{ $totalCierresInconsistentes > 0 ? 'border-rose-500/40' : 'border-slate-800' }} rounded-2xl overflow-hidden shadow-xl h-full">
                    <div
                        class="px-6 py-4 border-b {{ $totalCierresInconsistentes > 0 ? 'border-rose-500/30 bg-rose-500/5' : 'border-slate-800' }} flex items-center justify-between">
                        <h2
                            class="text-xs font-black {{ $totalCierresInconsistentes > 0 ? 'text-rose-400' : 'text-white' }} uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved"></i>
                            {{ $totalCierresInconsistentes > 0 ? '🚨 Alertas de Inconsistencia' : '✅ Sin Alertas' }}
                        </h2>
                    </div>

                    @if($totalCierresInconsistentes == 0)
                        <div class="p-8 text-center">
                            <i class="fa-solid fa-shield-check text-emerald-500 text-5xl mb-3"></i>
                            <p class="font-black text-emerald-400 text-sm uppercase">Todo cuadra</p>
                            <p class="text-xs text-slate-400 mt-1">Todos los cierres registrados tienen consistencia matemática.
                            </p>
                        </div>
                    @else
                        <div class="divide-y divide-slate-800/80">
                            @foreach($cierresInconsistentesList as $ci)
                                <div class="p-4 hover:bg-rose-500/5 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <span class="font-black text-white text-xs">{{ $ci->carrito->nombre ?? '—' }}</span>
                                        <span
                                            class="text-[10px] font-bold text-slate-400">{{ \Carbon\Carbon::parse($ci->fecha)->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="mt-1 flex items-center gap-2">
                                        <span class="text-[10px] font-bold text-slate-400">Diferencia:</span>
                                        <span class="text-xs font-black text-rose-400">
                                            Bs. {{ number_format(abs($ci->diferencia), 2) }}
                                            {{ $ci->diferencia > 0 ? '(Faltante)' : '(Sobrante)' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="px-4 py-3 border-t border-slate-800">
                            <a href="{{ route('cierres.index') }}"
                                class="text-[10px] font-black text-rose-400 hover:underline uppercase flex items-center gap-1">
                                <i class="fa-solid fa-arrow-right"></i> Ver Todos los Cierres Inconsistentes
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection