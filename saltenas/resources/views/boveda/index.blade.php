@extends('layouts.app')

@section('title', 'Bóveda Central (Caja Única) — Salteñas')

@section('content')
    <div class="space-y-6">

        <!-- Header Principal -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-xl shadow-sm">
            <h1 class="text-xl font-black text-slate-900 dark:text-white uppercase flex items-center gap-2">
                <i class="fa-solid fa-vault text-amber-500"></i> BÓVEDA CENTRAL (CAJA ÚNICA)
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 uppercase">
                CONSOLIDA TODO EL DINERO REAL RECAUDADO POR LOS CARRITOS (EFECTIVO + QR) Y DESCUENTA LAS COMPRAS DE MATERIA PRIMA.
            </p>
        </div>

        <!-- KPI Cards: Total, Efectivo y QR -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Saldo Total Consolidado -->
            <div class="bg-white dark:bg-slate-900 border-2 border-amber-400 dark:border-amber-500/40 p-5 rounded-xl shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest block mb-1">
                        💰 SALDO TOTAL CONSOLIDADO
                    </span>
                    <span class="text-3xl font-black {{ $saldoActual >= 0 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400' }} tracking-tight">
                        Bs. {{ number_format($saldoActual, 2) }}
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-1 uppercase font-semibold">Efectivo + Dinero en Banco (QR)</span>
                </div>
                <div class="p-3 bg-amber-50 dark:bg-amber-500/10 rounded-xl text-amber-600 dark:text-amber-400">
                    <i class="fa-solid fa-vault text-2xl"></i>
                </div>
            </div>

            <!-- Efectivo Físico en Caja -->
            <div class="bg-white dark:bg-slate-900 border border-emerald-200 dark:border-emerald-500/30 p-5 rounded-xl shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-widest block mb-1">
                        💵 EFECTIVO FÍSICO EN CAJA
                    </span>
                    <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">
                        Bs. {{ number_format($saldoEfectivo, 2) }}
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-1 uppercase font-semibold">Dinero en mano (descontando compras)</span>
                </div>
                <div class="p-3 bg-emerald-50 dark:bg-emerald-500/10 rounded-xl text-emerald-600 dark:text-emerald-400">
                    <i class="fa-solid fa-money-bill-wave text-2xl"></i>
                </div>
            </div>

            <!-- Dinero en Banco (Cobros QR) -->
            <div class="bg-white dark:bg-slate-900 border border-purple-200 dark:border-purple-500/30 p-5 rounded-xl shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black text-purple-700 dark:text-purple-400 uppercase tracking-widest block mb-1">
                        📱 DINERO EN BANCO (PAGOS QR)
                    </span>
                    <span class="text-3xl font-black text-purple-600 dark:text-purple-400 tracking-tight">
                        Bs. {{ number_format($saldoQr, 2) }}
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-1 uppercase font-semibold">Cobros acreditados vía cuenta bancaria</span>
                </div>
                <div class="p-3 bg-purple-50 dark:bg-purple-500/10 rounded-xl text-purple-600 dark:text-purple-400">
                    <i class="fa-solid fa-qrcode text-2xl"></i>
                </div>
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
                            <th class="py-2.5 px-4">ORIGEN / DETALLE</th>
                            <th class="py-2.5 px-4 text-center">DESGLOSE (EFECTIVO / QR)</th>
                            <th class="py-2.5 px-4 text-right">MONTO TOTAL</th>
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
                                            RECAUDACIÓN REAL LIQUIDADA DEL TURNO
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

                                <td class="py-3 px-4 text-center">
                                    @if($mov->tipo === 'ingreso')
                                        <div class="inline-flex items-center gap-1 text-[11px] font-bold">
                                            <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                                                💵 Ef: Bs. {{ number_format($mov->dinero_efectivo, 2) }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-500/20">
                                                📱 QR: Bs. {{ number_format($mov->dinero_qr, 2) }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-[10px] font-semibold uppercase">Efectivo de Caja</span>
                                    @endif
                                </td>

                                <td class="py-3 px-4 text-right font-black text-sm {{ $mov->tipo === 'ingreso' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ $mov->tipo === 'ingreso' ? '+' : '-' }} BS. {{ number_format($mov->monto, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 text-xs font-bold uppercase">
                                    NO HAY MOVIMIENTOS EN LA BÓVEDA AÚN. LOS INGRESOS SE GENERARÁN AUTOMÁTICAMENTE AL APROBAR CIERRES DIARIOS Y LOS EGRESOS AL REGISTRAR COMPRAS.
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
