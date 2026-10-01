@extends('layouts.app')

@section('title', 'Bóveda Central (Caja Única) — Salteñas')

@section('content')
    <div class="space-y-6">

        <!-- Header & Saldo KPI -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-xl shadow-sm">
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-white uppercase flex items-center gap-2">
                    <i class="fa-solid fa-vault text-amber-500"></i> BÓVEDA CENTRAL (CAJA ÚNICA)
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 uppercase">CONSOLIDA TODO EL DINERO REAL RECAUDADO POR LOS CARRITOS Y DESCUENTA LAS COMPRAS GENERALES DE MATERIA PRIMA.</p>
            </div>

            <div class="bg-slate-50 dark:bg-slate-950 border border-amber-300 dark:border-amber-500/30 p-4 rounded-xl text-right shrink-0">
                <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest block">SALDO ACTUAL EN BÓVEDA</span>
                <span class="text-3xl font-black {{ $saldoActual >= 0 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400' }} tracking-tight">
                    Bs. {{ number_format($saldoActual, 2) }}
                </span>
            </div>
        </div>

        <!-- Historial de Movimientos -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h2 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-amber-500"></i> HISTORIAL DE MOVIMIENTOS DE BÓVEDA
                </h2>
                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase">TOTAL: {{ $movimientos->total() }} REGISTROS</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 tracking-wider">
                            <th class="py-2.5 px-4">FECHA</th>
                            <th class="py-2.5 px-4">TIPO</th>
                            <th class="py-2.5 px-4">ORIGEN / ORIGEN DE DINERO</th>
                            <th class="py-2.5 px-4 text-right">MONTO</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs font-medium text-slate-700 dark:text-slate-300">
                        @forelse($movimientos as $mov)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}
                                </td>

                                <td class="py-3 px-4">
                                    @if($mov->tipo === 'ingreso')
                                        <span class="px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400 font-black text-[10px] uppercase inline-flex items-center gap-1">
                                            <i class="fa-solid fa-arrow-down-left"></i> INGRESO
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full bg-rose-100 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30 text-rose-700 dark:text-rose-400 font-black text-[10px] uppercase inline-flex items-center gap-1">
                                            <i class="fa-solid fa-arrow-up-right"></i> EGRESO
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3 px-4">
                                    @if($mov->cierreDiario)
                                        <span class="font-bold text-amber-600 dark:text-amber-400">
                                            🛒 CIERRE DIARIO — {{ strtoupper($mov->cierreDiario->carrito->nombre ?? 'CARRITO') }}
                                        </span>
                                        <span class="block text-[10px] text-slate-500 dark:text-slate-400 uppercase">
                                            RECAUDACIÓN REAL ENTREGADA DE LA JORNADA
                                        </span>
                                    @elseif($mov->compra)
                                        <span class="font-bold text-cyan-600 dark:text-cyan-400">
                                            🛍️ COMPRA DE INSUMOS #{{ $mov->compra->id }}
                                        </span>
                                        <span class="block text-[10px] text-slate-500 dark:text-slate-400 uppercase">
                                            @foreach($mov->compra->detalles as $det)
                                                {{ strtoupper($det->insumo->nombre ?? 'INSUMO') }} ({{ $det->cantidad }} {{ strtoupper($det->insumo->unidad_medida ?? '') }}),
                                            @endforeach
                                        </span>
                                    @else
                                        <span class="text-slate-600 dark:text-slate-400 font-bold uppercase">AJUSTE DIRECTO</span>
                                    @endif
                                </td>

                                <td class="py-3 px-4 text-right font-black text-sm {{ $mov->tipo === 'ingreso' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ $mov->tipo === 'ingreso' ? '+' : '-' }} BS. {{ number_format($mov->monto, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400 text-xs font-bold uppercase">
                                    NO HAY MOVIMIENTOS EN LA BÓVEDA AÚN. LOS INGRESOS SE GENERARÁN AUTOMÁTICAMENTE AL REGISTRAR CIERRES DIARIOS Y LOS EGRESOS AL REGISTRAR COMPRAS.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800">
                {{ $movimientos->links() }}
            </div>
        </div>
    </div>
@endsection