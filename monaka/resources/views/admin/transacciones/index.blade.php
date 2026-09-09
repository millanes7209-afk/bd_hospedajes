<!doctype html>
<html lang="es" class="dark-mode">

<head>
    <meta charset="utf-8">
    <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TRANSACCIONES - SALTEÑERÍA MONAKA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { primary: '#FFE66D', accent: '#E23E1A', dark: '#09090c' } } } }</script>
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="min-h-screen" style="background-color:var(--color-bg);color:var(--color-text);">

    <!-- Navbar Unificada -->
    @include('layouts.admin_navbar')

    <div class="max-w-7xl mx-auto px-4 py-6">
        <!-- Encabezado y Filtro -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <h2 class="text-xl md:text-2xl font-black uppercase flex items-center gap-2"
                style="color:var(--color-text)">
                <i class="fa-solid fa-receipt text-amber-500"></i>TRANSACCIONES Y VENTAS REALIZADAS
            </h2>

            <!-- Filtro de Fechas -->
            <form action="{{ route('admin.transacciones') }}" method="GET"
                class="flex items-center gap-2 admin-box p-2 rounded-xl border shadow-sm">
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}"
                    class="admin-input border rounded-lg p-2 text-xs font-bold">
                <span class="text-xs font-bold admin-text-muted">AL</span>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}"
                    class="admin-input border rounded-lg p-2 text-xs font-bold">
                <button type="submit"
                    class="bg-amber-500 hover:bg-amber-400 text-black font-black text-xs p-2.5 rounded-lg uppercase shadow cursor-pointer transition-all">
                    <i class="fa-solid fa-filter mr-1"></i>FILTRAR
                </button>
            </form>
        </div>

        <!-- Tarjetas Resumen -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div
                class="admin-box p-5 border border-amber-500/30 rounded-2xl bg-amber-500/10 shadow-sm flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold admin-text-muted uppercase mb-1">TOTAL RECAUDADO EN EL PERÍODO</div>
                    <div class="text-3xl font-black text-amber-600 dark:text-amber-300">
                        Bs. {{ number_format($totalRecaudado, 2) }}
                    </div>
                </div>
                <i class="fa-solid fa-money-bill-wave text-4xl opacity-30 text-amber-500"></i>
            </div>

            <div
                class="admin-box p-5 border border-emerald-500/30 rounded-2xl bg-emerald-500/10 shadow-sm flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold admin-text-muted uppercase mb-1">TOTAL DE VENTAS REGISTRADAS</div>
                    <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400">
                        {{ $totalTransacciones }} <span class="text-sm font-bold">TRANSACCIONES</span>
                    </div>
                </div>
                <i class="fa-solid fa-cash-register text-4xl opacity-30 text-emerald-500"></i>
            </div>
        </div>

        <!-- Tabla Detallada de Transacciones -->
        <div class="admin-box glass-card border rounded-2xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="uppercase bg-black/10 dark:bg-white/5 border-b border-white/10"
                        style="color:var(--color-text-muted,#9ca3af);">
                        <tr>
                            <th class="py-3.5 px-4 font-black">FECHA / HORA</th>
                            <th class="py-3.5 px-4 font-black">CANAL</th>
                            <th class="py-3.5 px-4 font-black">PRODUCTOS Y DETALLE</th>
                            <th class="py-3.5 px-4 font-black">PAGO</th>
                            <th class="py-3.5 px-4 font-black">CAJERO</th>
                            <th class="py-3.5 px-4 font-black text-right">TOTAL</th>
                            <th class="py-3.5 px-4 font-black text-center">TICKET</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse ($ventas as $v)
                                                <?php
                            $pago = $v->pagos->first();
                            $metodo = strtolower($pago->metodo_pago ?? 'efectivo');
                            $cajero = $v->usuarioApertura->nombre ?? ($v->usuarioApertura->username ?? 'SISTEMA');
                                                    ?>
                                                <tr class="hover:bg-amber-500/5 transition-colors">
                                                    <!-- Fecha y Hora -->
                                                    <td class="py-3 px-4 font-extrabold whitespace-nowrap">
                                                        <div class="text-amber-600 dark:text-amber-400 font-black text-xs">
                                                            {{ \Carbon\Carbon::parse($v->fecha_apertura)->format('d/m/Y H:i') }}
                                                        </div>
                                                    </td>

                                                    <!-- Canal de venta -->
                                                    <td class="py-3 px-4 whitespace-nowrap">
                                                        <span
                                                            class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase border {{ $v->origen === 'local' ? 'bg-amber-500/15 border-amber-500/40 text-amber-700 dark:text-amber-300' : 'bg-emerald-500/15 border-emerald-500/40 text-emerald-700 dark:text-emerald-300' }}">
                                                            {{ $v->origen === 'local' ? '🏪 LOCAL' : '📱 WHATSAPP' }}
                                                        </span>
                                                    </td>

                                                    <!-- Detalle de Productos -->
                                                    <td class="py-3 px-4">
                                                        <div class="space-y-1">
                                                            @foreach ($v->items as $item)
                                                                <div class="flex items-center gap-1.5 font-bold">
                                                                    <span
                                                                        class="bg-black/20 dark:bg-white/10 text-amber-600 dark:text-amber-400 px-1.5 py-0.5 rounded text-[10px] font-black">
                                                                        {{ $item->cantidad }}x
                                                                    </span>
                                                                    <span class="uppercase text-[11px]">
                                                                        {{ $item->nombre_producto }}
                                                                        @if($item->nombre_variante)
                                                                            <span
                                                                                class="text-amber-600 dark:text-amber-400">({{ $item->nombre_variante }})</span>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </td>

                                                    <!-- Método de Pago -->
                                                    <td class="py-3 px-4 whitespace-nowrap">
                                                        <span
                                                            class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase border {{ $metodo === 'qr' ? 'bg-blue-500/15 border-blue-500/40 text-blue-700 dark:text-blue-300' : 'bg-emerald-500/15 border-emerald-500/40 text-emerald-700 dark:text-emerald-300' }}">
                                                            {{ $metodo === 'qr' ? '📱 PAGO QR' : '💵 EFECTIVO' }}
                                                        </span>
                                                    </td>

                                                    <!-- Cajero -->
                                                    <td class="py-3 px-4 font-bold uppercase text-[11px] opacity-80 whitespace-nowrap">
                                                        <i class="fa-solid fa-user-gear mr-1 opacity-50"></i>{{ $cajero }}
                                                    </td>

                                                    <!-- Total -->
                                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                                        <span class="text-sm font-black text-amber-600 dark:text-amber-400">
                                                            Bs. {{ number_format($v->monto_total, 2) }}
                                                        </span>
                                                    </td>

                                                    <!-- Acción Ticket -->
                                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                                        <a href="{{ route('ticket.show', $v->id) }}" target="_blank"
                                                            class="px-2.5 py-1 bg-amber-500 hover:bg-amber-400 text-black text-[11px] font-black uppercase rounded-lg shadow transition-all inline-flex items-center gap-1">
                                                            <i class="fa-solid fa-print"></i>TICKET
                                                        </a>
                                                    </td>
                                                </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-xs opacity-60 italic">
                                    No se encontraron transacciones registradas en este período.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>

</html>