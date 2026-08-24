<!doctype html>
<html lang="es" class="dark-mode">

<head>
    <meta charset="utf-8">
    <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>REPORTES Y ESTADÍSTICAS - SALTEÑERÍA MONAKA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { primary: '#FFE66D', accent: '#E23E1A', dark: '#09090c' } } } }</script>
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="min-h-screen" style="background-color:var(--color-bg);color:var(--color-text);">

    <!-- Navbar Unificada -->
    @include('layouts.admin_navbar')

    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <h2 class="text-2xl font-black uppercase flex items-center gap-2" style="color:var(--color-text)">
                <i class="fa-solid fa-chart-pie text-amber-400"></i>REPORTES DE VENTAS Y MÉTRICAS
            </h2>

            <!-- Filtro de Fechas -->
            <form action="{{ route('admin.reportes') }}" method="GET"
                class="flex items-center gap-2 admin-box p-2 rounded-xl border shadow-sm">
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}"
                    class="admin-input border rounded-lg p-2 text-xs font-bold">
                <span class="text-xs font-bold admin-text-muted">AL</span>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}"
                    class="admin-input border rounded-lg p-2 text-xs font-bold">
                <button type="submit"
                    class="bg-amber-500 hover:bg-amber-400 text-black font-black text-xs p-2.5 rounded-lg uppercase shadow cursor-pointer">
                    <i class="fa-solid fa-filter mr-1"></i>FILTRAR
                </button>
            </form>
        </div>

        <!-- Tarjetas de Métricas Clave -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="admin-box p-6 border border-amber-500/30 rounded-2xl bg-amber-500/10 shadow-sm">
                <div class="text-xs font-bold admin-text-muted uppercase mb-1">TOTAL INGRESOS RECAUDADOS</div>
                <div class="text-3xl font-black text-amber-500 dark:text-amber-300">
                    Bs.{{ number_format($totalIngresos, 2) }}</div>
                <div class="text-[10px] admin-text-muted mt-2 uppercase">Período:
                    {{ date('d/m/Y', strtotime($fechaInicio)) }} - {{ date('d/m/Y', strtotime($fechaFin)) }}
                </div>
            </div>

            <div class="admin-box p-6 border border-emerald-500/30 rounded-2xl bg-emerald-500/10 shadow-sm">
                <div class="text-xs font-bold admin-text-muted uppercase mb-1">TOTAL VENTAS COMPLETADAS</div>
                <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ $cantidadVentas }} <span
                        class="text-xs font-bold">VENTAS</span></div>
                <div class="text-[10px] admin-text-muted mt-2 uppercase">Cuentas cerradas satisfactoriamente</div>
            </div>

            <div class="admin-box p-6 border border-blue-500/30 rounded-2xl bg-blue-500/10 shadow-sm">
                <div class="text-xs font-bold admin-text-muted uppercase mb-1">TICKET PROMEDIO POR VENTA</div>
                <div class="text-3xl font-black text-blue-600 dark:text-blue-400">
                    Bs.{{ number_format($cantidadVentas > 0 ? $totalIngresos / $cantidadVentas : 0, 2) }}
                </div>
                <div class="text-[10px] admin-text-muted mt-2 uppercase">Promedio de consumo por orden</div>
            </div>
        </div>

        <!-- Tablas Desglose -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Ventas por Canal (Local vs WhatsApp) -->
            <div class="admin-box glass-card p-6 border rounded-2xl">
                <h3
                    class="text-base font-black uppercase text-amber-500 dark:text-amber-400 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-store"></i>VENTAS POR CANAL DE ORIGEN
                </h3>
                <div class="space-y-3">
                    @forelse ($ventasPorOrigen as $origen)
                        <div class="flex items-center justify-between p-3 rounded-xl admin-subcard border">
                            <div>
                                <span
                                    class="font-black uppercase text-sm {{ $origen->origen === 'local' ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    {{ $origen->origen === 'local' ? '🏪 VENTA LOCAL (MESAS / LLEVAR)' : '📱 PEDIDOS WHATSAPP' }}
                                </span>
                                <span class="block text-xs admin-text-muted font-semibold">{{ $origen->total_pedidos }}
                                    ÓRDENES REALIZADAS</span>
                            </div>
                            <div class="text-lg font-black admin-text-main">
                                Bs.{{ number_format($origen->total_monto, 2) }}
                            </div>
                        </div>
                    @empty
                        <p class="text-xs admin-text-muted italic text-center py-4">Sin datos de ventas en este rango de
                            fechas.</p>
                    @endforelse
                </div>
            </div>

            <!-- Productos Más Vendidos -->
            <div class="admin-box glass-card p-6 border rounded-2xl">
                <h3
                    class="text-base font-black uppercase text-amber-500 dark:text-amber-400 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-utensils"></i>PRODUCTOS MÁS VENDIDOS
                </h3>
                <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                    @forelse ($productosVendidos as $prod)
                        <div class="flex items-center justify-between text-xs p-2.5 rounded-lg admin-subcard border">
                            <div>
                                <span class="font-extrabold admin-text-main">{{ strtoupper($prod->nombre_producto) }}</span>
                                @if($prod->nombre_variante)
                                    <span
                                        class="text-amber-600 dark:text-amber-400 font-bold">({{ strtoupper($prod->nombre_variante) }})</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-4">
                                <span
                                    class="font-black bg-amber-500/20 text-amber-700 dark:text-amber-300 px-2 py-0.5 rounded-full border border-amber-500/40">
                                    {{ $prod->total_cantidad }} Unid.
                                </span>
                                <span
                                    class="font-extrabold text-amber-600 dark:text-amber-400">Bs.{{ number_format($prod->total_ingreso, 2) }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs admin-text-muted italic text-center py-4">Sin datos de productos vendidos.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Ventas por Hora del Día (Horas Pico) -->
        <div class="admin-box glass-card p-6 border rounded-2xl">
            <h3 class="text-base font-black uppercase text-amber-500 dark:text-amber-400 mb-4 flex items-center gap-2">
                <i class="fa-regular fa-clock"></i>DISTRIBUCIÓN DE VENTAS POR HORAS PICO (00:00 - 23:00)
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                @foreach ($ventasPorHora as $vh)
                    <div class="p-3 rounded-xl admin-subcard border text-center">
                        <div class="text-xs font-black text-amber-600 dark:text-amber-400">
                            {{ sprintf('%02d:00', $vh->hora) }}</div>
                        <div class="text-sm font-black admin-text-main mt-1">Bs.{{ number_format($vh->total_monto, 2) }}
                        </div>
                        <div class="text-[10px] admin-text-muted font-bold mt-0.5">{{ $vh->total_ventas }} ventas</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</body>

</html>