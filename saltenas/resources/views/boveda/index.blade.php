@extends('layouts.app')

@section('title', 'Bóveda Central (Caja Única) — Salteñas')

@section('content')
    <div class="space-y-6">

        <!-- Header & Saldo KPI -->
        <div
            class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-6 rounded-2xl">
            <div>
                <h1 class="text-2xl font-black text-white uppercase flex items-center gap-2">
                    <i class="fa-solid fa-vault text-amber-500"></i> Bóveda Central (Caja Única)
                </h1>
                <p class="text-xs text-slate-400 mt-1">Consolida todo el dinero real recaudado por los carritos y descuenta
                    las compras generales de materia prima.</p>
            </div>

            <div class="bg-slate-950 border border-amber-500/30 p-4 rounded-xl text-right">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Saldo Actual en
                    Bóveda</span>
                <span class="text-3xl font-black text-amber-400 tracking-tight">
                    Bs. {{ number_format($saldoActual, 2) }}
                </span>
            </div>
        </div>

        <!-- Historial de Movimientos -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
            <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                <h2 class="text-xs font-black text-white uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-amber-500"></i> Historial de Movimientos de Bóveda
                </h2>
                <span class="text-[10px] font-extrabold text-slate-400 uppercase">Total: {{ $movimientos->total() }}
                    registros</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-slate-950/70 border-b border-slate-800 text-[10px] font-black uppercase text-slate-400 tracking-wider">
                            <th class="py-3 px-4">Fecha</th>
                            <th class="py-3 px-4">Tipo</th>
                            <th class="py-3 px-4">Origen / Origen de Dinero</th>
                            <th class="py-3 px-4 text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-xs font-medium text-slate-300">
                        @forelse($movimientos as $mov)
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="py-3.5 px-4 font-bold text-white">
                                    {{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}
                                </td>

                                <td class="py-3.5 px-4">
                                    @if($mov->tipo === 'ingreso')
                                        <span
                                            class="px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-black text-[10px] uppercase inline-flex items-center gap-1">
                                            <i class="fa-solid fa-arrow-down-left"></i> INGRESO
                                        </span>
                                    @else
                                        <span
                                            class="px-2.5 py-1 rounded-full bg-rose-500/10 border border-rose-500/30 text-rose-400 font-black text-[10px] uppercase inline-flex items-center gap-1">
                                            <i class="fa-solid fa-arrow-up-right"></i> EGRESO
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    @if($mov->cierreDiario)
                                        <span class="font-bold text-amber-400">
                                            🛒 Cierre Diario — {{ $mov->cierreDiario->carrito->nombre ?? 'Carrito' }}
                                        </span>
                                        <span class="block text-[10px] text-slate-400">
                                            Recaudación real entregada de la jornada
                                        </span>
                                    @elseif($mov->compra)
                                        <span class="font-bold text-cyan-400">
                                            🛍️ Compra de Insumos #{{ $mov->compra->id }}
                                        </span>
                                        <span class="block text-[10px] text-slate-400">
                                            @foreach($mov->compra->detalles as $det)
                                                {{ $det->insumo->nombre ?? 'Insumo' }} ({{ $det->cantidad }}
                                                {{ $det->insumo->unidad_medida ?? '' }}),
                                            @endforeach
                                        </span>
                                    @else
                                        <span class="text-slate-400 font-bold">Ajuste Directo</span>
                                    @endif
                                </td>

                                <td
                                    class="py-3.5 px-4 text-right font-black text-sm {{ $mov->tipo === 'ingreso' ? 'text-emerald-400' : 'text-rose-400' }}">
                                    {{ $mov->tipo === 'ingreso' ? '+' : '-' }} Bs. {{ number_format($mov->monto, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-500 text-xs font-bold uppercase">
                                    No hay movimientos en la Bóveda aún. Los ingresos se generarán automáticamente al registrar
                                    cierres diarios y los egresos al registrar compras.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-3 border-t border-slate-800">
                {{ $movimientos->links() }}
            </div>
        </div>
    </div>
@endsection