@extends('layouts.app')

@section('title', 'COMPRAS — SALTEÑAS')

@section('content')
    <div class="space-y-6" x-data="compraForm()">

        <!-- Header Principal: Solo COMPRAS -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-xl shadow-sm">
            <h1 class="text-xl font-black text-slate-900 dark:text-white uppercase flex items-center gap-2">
                <i class="fa-solid fa-cart-shopping text-cyan-600 dark:text-cyan-400"></i> COMPRAS
            </h1>
        </div>

        <!-- KPI Cards: Métricas Rápidas -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest block mb-1">
                        🛒 COMPRAS DE HOY
                    </span>
                    <span class="text-2xl font-black text-rose-600 dark:text-rose-400 tracking-tight">
                        Bs. {{ number_format($totalComprasHoy, 2) }}
                    </span>
                </div>
                <div class="p-3 bg-rose-50 dark:bg-rose-500/10 rounded-xl text-rose-600 dark:text-rose-400">
                    <i class="fa-solid fa-calendar-day text-xl"></i>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest block mb-1">
                        📅 TOTAL ESTE MES
                    </span>
                    <span class="text-2xl font-black text-cyan-600 dark:text-cyan-400 tracking-tight">
                        Bs. {{ number_format($totalComprasMes, 2) }}
                    </span>
                </div>
                <div class="p-3 bg-cyan-50 dark:bg-cyan-500/10 rounded-xl text-cyan-600 dark:text-cyan-400">
                    <i class="fa-solid fa-receipt text-xl"></i>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest block mb-1">
                        📦 INSUMOS REGISTRADOS
                    </span>
                    <span class="text-2xl font-black text-amber-600 dark:text-amber-400 tracking-tight">
                        {{ $insumos->count() }} TIPOS
                    </span>
                </div>
                <div class="p-3 bg-amber-50 dark:bg-amber-500/10 rounded-xl text-amber-600 dark:text-amber-400">
                    <i class="fa-solid fa-dolly text-xl"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Formulario Registrar Compra (Col 8) -->
            <div class="lg:col-span-8">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 space-y-4 shadow-sm">
                    <h2 class="text-xs font-black text-slate-900 dark:text-cyan-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                        <i class="fa-solid fa-cart-plus text-cyan-500"></i> REGISTRAR COMPRA
                    </h2>

                    <form action="{{ route('compras.store') }}" method="POST" class="space-y-4">
                        @csrf

                        <!-- Cabecera de la Compra -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                                    FECHA DE COMPRA <span class="text-amber-500">*</span>
                                </label>
                                <input type="date" name="fecha" required value="{{ date('Y-m-d') }}"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-amber-600 dark:text-amber-400 focus:border-cyan-500 focus:outline-none">
                            </div>

                            <!-- Forma de Pago -->
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                                    FORMA DE PAGO <span class="text-amber-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="cursor-pointer border rounded-lg p-2 flex items-center gap-2 transition-all text-xs font-black uppercase"
                                        :class="metodoPago === 'efectivo' ? 'bg-emerald-50 dark:bg-emerald-500/10 border-emerald-500 text-emerald-700 dark:text-emerald-400' : 'bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-700 text-slate-500'">
                                        <input type="radio" name="metodo_pago" value="efectivo" x-model="metodoPago" class="sr-only">
                                        <i class="fa-solid fa-money-bill-wave text-emerald-500"></i>
                                        <span>💵 EFECTIVO</span>
                                    </label>

                                    <label class="cursor-pointer border rounded-lg p-2 flex items-center gap-2 transition-all text-xs font-black uppercase"
                                        :class="metodoPago === 'qr' ? 'bg-purple-50 dark:bg-purple-500/10 border-purple-500 text-purple-700 dark:text-purple-400' : 'bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-700 text-slate-500'">
                                        <input type="radio" name="metodo_pago" value="qr" x-model="metodoPago" class="sr-only">
                                        <i class="fa-solid fa-qrcode text-purple-500"></i>
                                        <span>📱 QR / BANCO</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Barra de Ingreso Directo de Insumos (Estilo Carrito) -->
                        <div class="bg-slate-50 dark:bg-slate-950 p-4 rounded-xl border border-cyan-500/30 dark:border-cyan-500/20 space-y-2.5 shadow-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black uppercase text-cyan-600 dark:text-cyan-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-cart-arrow-down"></i> AGREGAR INSUMO AL CARRITO
                                </span>
                                <span class="text-[10px] text-slate-400 uppercase font-semibold hidden sm:inline">
                                    Escribe el insumo y presiona Enter o clic en [+]
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-end">
                                <!-- Input del Insumo -->
                                <div class="sm:col-span-5">
                                    <label class="block text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">
                                        INSUMO <span class="text-amber-500">*</span>
                                    </label>
                                    <input type="text" id="input-insumo-nombre" list="datalist-insumos" x-model="inputItem.nombre"
                                        @input="onInputNombreChange()" @change="onInputNombreChange()"
                                        @keydown.enter.prevent="agregarItem()"
                                        placeholder="Escribe el insumo (ej. Harina, Pollo)..."
                                        class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-900 dark:text-white uppercase focus:border-cyan-500 focus:outline-none">
                                </div>

                                <!-- Unidad de Medida:
                                     Si existe -> detecta la unidad automáticamente
                                     Si NO existe -> select para que elija -->
                                <div class="sm:col-span-2">
                                    <label class="block text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">
                                        UNIDAD <span class="text-amber-500">*</span>
                                    </label>
                                    <template x-if="!inputItem.es_nuevo && inputItem.insumo_id">
                                        <div class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1.5 text-xs font-black text-amber-600 dark:text-amber-400 text-center uppercase">
                                            <span x-text="inputItem.unidad_medida.toUpperCase()"></span>
                                        </div>
                                    </template>
                                    <template x-if="inputItem.es_nuevo || !inputItem.insumo_id">
                                        <select x-model="inputItem.unidad_medida"
                                            class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1.5 text-xs font-bold text-slate-900 dark:text-white uppercase focus:border-cyan-500 focus:outline-none">
                                            <option value="kg">KG</option>
                                            <option value="g">G</option>
                                            <option value="quintal">QUINTAL</option>
                                            <option value="libra">LIBRA</option>
                                            <option value="litro">LITRO</option>
                                            <option value="ml">ML</option>
                                            <option value="taza">TAZA</option>
                                            <option value="unidad">UNIDAD</option>
                                            <option value="docena">DOCENA</option>
                                            <option value="paquete">PAQUETE</option>
                                            <option value="caja">CAJA</option>
                                            <option value="bolsa">BOLSA</option>
                                            <option value="lata">LATA</option>
                                            <option value="botella">BOTELLA</option>
                                        </select>
                                    </template>
                                </div>

                                <!-- Cantidad -->
                                <div class="sm:col-span-2">
                                    <label class="block text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">
                                        CANTIDAD <span class="text-amber-500">*</span>
                                    </label>
                                    <input type="number" step="any" min="0.01" x-model="inputItem.cantidad"
                                        @keydown.enter.prevent="agregarItem()" placeholder="1.00"
                                        class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-900 dark:text-white uppercase focus:border-cyan-500 focus:outline-none">
                                </div>

                                <!-- Precio Unitario -->
                                <div class="sm:col-span-2">
                                    <label class="block text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">
                                        P. UNIT (BS.) <span class="text-amber-500">*</span>
                                    </label>
                                    <input type="number" step="any" min="0.01" x-model="inputItem.precio_unitario"
                                        @keydown.enter.prevent="agregarItem()" placeholder="0.00"
                                        class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-900 dark:text-white uppercase focus:border-cyan-500 focus:outline-none">
                                </div>

                                <!-- Botón [+] para agregar al carrito -->
                                <div class="sm:col-span-1">
                                    <button type="button" @click="agregarItem()"
                                        class="w-full py-2 bg-cyan-600 hover:bg-cyan-500 text-white rounded-lg text-xs font-black uppercase transition-all shadow-sm flex items-center justify-center gap-1"
                                        title="Agregar al Carrito">
                                        <i class="fa-solid fa-plus text-sm"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Feedback de detección y error -->
                            <div class="flex items-center justify-between text-[10px]">
                                <div>
                                    <template x-if="inputItem.insumo_id && !inputItem.es_nuevo">
                                        <span class="font-extrabold text-emerald-600 dark:text-emerald-400 uppercase flex items-center gap-1">
                                            <i class="fa-solid fa-circle-check"></i> Insumo Registrado (Unidad detectada: <span x-text="inputItem.unidad_medida.toUpperCase()"></span>)
                                        </span>
                                    </template>
                                    <template x-if="inputItem.es_nuevo && inputItem.nombre.trim().length > 0">
                                        <span class="font-extrabold text-cyan-600 dark:text-cyan-400 uppercase flex items-center gap-1">
                                            <i class="fa-solid fa-circle-plus"></i> Insumo Nuevo (Elige su unidad en el selector)
                                        </span>
                                    </template>
                                </div>
                                <div x-show="inputError" class="font-bold text-rose-500 uppercase flex items-center gap-1" x-text="inputError"></div>
                            </div>
                        </div>

                        <!-- Carrito: Lista de Ítems Agregados -->
                        <div class="space-y-3 bg-slate-50 dark:bg-slate-950 p-4 rounded-xl border border-slate-200 dark:border-slate-800">
                            <div class="flex items-center justify-between pb-1 border-b border-slate-200 dark:border-slate-800">
                                <span class="text-xs font-black uppercase text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                    <i class="fa-solid fa-cart-shopping text-cyan-600"></i> INSUMOS EN EL CARRITO (<span x-text="rows.length"></span>)
                                </span>
                            </div>

                            <!-- Estado Vacío: Sin Ítems todavía -->
                            <div x-show="rows.length === 0" class="py-6 text-center space-y-2">
                                <div class="text-2xl text-slate-300 dark:text-slate-600">🛒</div>
                                <p class="text-xs font-bold text-slate-400 uppercase">
                                    El carrito de compras está vacío. Escribe arriba el insumo que deseas comprar para agregarlo.
                                </p>
                            </div>

                            <!-- Tabla del Carrito con Ítems Agregados -->
                            <div x-show="rows.length > 0" class="overflow-x-auto">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 text-[10px] font-black uppercase text-slate-500 dark:text-slate-400">
                                            <th class="py-2.5 px-3">INSUMO</th>
                                            <th class="py-2.5 px-3 text-center">UNIDAD</th>
                                            <th class="py-2.5 px-3 text-center">CANTIDAD</th>
                                            <th class="py-2.5 px-3 text-right">PRECIO UNIT.</th>
                                            <th class="py-2.5 px-3 text-right">SUBTOTAL</th>
                                            <th class="py-2.5 px-2 text-center">QUITAR</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                                        <template x-for="(row, idx) in rows" :key="idx">
                                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                                <td class="py-2.5 px-3">
                                                    <!-- Inputs ocultos para el envío del formulario -->
                                                    <input type="hidden" :name="'items[' + idx + '][insumo_id]'" :value="row.insumo_id">
                                                    <input type="hidden" :name="'items[' + idx + '][nombre]'" :value="row.nombre">
                                                    <input type="hidden" :name="'items[' + idx + '][unidad_medida]'" :value="row.unidad_medida">

                                                    <div class="font-black text-slate-900 dark:text-white uppercase flex items-center gap-1.5">
                                                        <span>📦</span>
                                                        <span x-text="row.nombre"></span>
                                                    </div>
                                                    <template x-if="row.es_nuevo">
                                                        <span class="text-[9px] font-extrabold text-cyan-600 dark:text-cyan-400 uppercase block">
                                                            ★ Nuevo (Se registrará en catálogo)
                                                        </span>
                                                    </template>
                                                </td>

                                                <td class="py-2.5 px-3 text-center font-black text-amber-600 dark:text-amber-400 uppercase">
                                                    <span class="px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-[10px]"
                                                        x-text="row.unidad_medida.toUpperCase()"></span>
                                                </td>

                                                <td class="py-2.5 px-3 text-center">
                                                    <input type="number" step="any" min="0.01" :name="'items[' + idx + '][cantidad]'"
                                                        x-model="row.cantidad" @input="calcSubtotal(idx)"
                                                        class="w-20 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-xs font-bold text-center text-slate-900 dark:text-white focus:border-cyan-500 focus:outline-none">
                                                </td>

                                                <td class="py-2.5 px-3 text-right">
                                                    <input type="number" step="any" min="0.01" :name="'items[' + idx + '][precio_unitario]'"
                                                        x-model="row.precio_unitario" @input="calcSubtotal(idx)"
                                                        class="w-24 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-xs font-bold text-right text-slate-900 dark:text-white focus:border-cyan-500 focus:outline-none">
                                                </td>

                                                <td class="py-2.5 px-3 text-right font-black text-rose-600 dark:text-rose-400">
                                                    Bs. <span x-text="row.subtotal"></span>
                                                </td>

                                                <td class="py-2.5 px-2 text-center">
                                                    <button type="button" @click="removeRow(idx)"
                                                        class="p-1.5 text-slate-400 hover:text-rose-500 transition-colors"
                                                        title="Quitar del carrito">
                                                        <i class="fa-solid fa-trash-can text-sm"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Total General de la Compra -->
                            <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                                <span class="text-xs font-black uppercase text-slate-600 dark:text-slate-400">
                                    TOTAL A PAGAR:
                                </span>
                                <span class="text-xl font-black text-rose-600 dark:text-rose-400">
                                    Bs. <span x-text="grandTotal">0.00</span>
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                                OBSERVACIONES / DETALLES ADICIONALES (OPCIONAL)
                            </label>
                            <textarea name="observaciones" rows="2" placeholder="EJ. HARINA COMPRADA A BUEN PRECIO POR QUINTAL..."
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-medium text-slate-900 dark:text-slate-200 focus:border-cyan-500 focus:outline-none uppercase"></textarea>
                        </div>

                        <button type="submit" :disabled="rows.length === 0"
                            :class="rows.length === 0 ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-cyan-600 hover:bg-cyan-500'"
                            class="w-full py-3 px-4 rounded-lg text-white font-black text-xs uppercase tracking-wider shadow-sm transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-cart-check text-sm"></i> REGISTRAR COMPRA
                        </button>
                    </form>
                </div>
            </div>

            <!-- Panel Lateral: Stock Actual de Insumos (Col 4) -->
            <div class="lg:col-span-4 space-y-4">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
                    <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <h2 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-boxes-stacked text-amber-500"></i> STOCK EN ALMACÉN
                        </h2>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $insumos->count() }} ÍTEMS</span>
                    </div>

                    <div class="max-h-[500px] overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/60 text-xs">
                        @forelse($insumos as $ins)
                            @php
                                $stock = (float)$ins->stock_actual;
                                $ultimoPrecio = $ins->ultimo_precio;
                            @endphp
                            <div class="p-3.5 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white uppercase">
                                        {{ $ins->nombre }}
                                    </div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 uppercase mt-0.5">
                                        Último: <span class="font-bold text-slate-700 dark:text-slate-300">Bs. {{ number_format($ultimoPrecio, 2) }}</span> / {{ strtoupper($ins->unidad_medida) }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if($stock > 0)
                                        <span class="px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400 font-black text-[11px] uppercase">
                                            {{ number_format($stock, 2) }} {{ strtoupper($ins->unidad_medida) }}
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full bg-rose-100 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30 text-rose-700 dark:text-rose-400 font-bold text-[10px] uppercase">
                                            0.00
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-slate-400 uppercase text-xs font-bold">
                                No hay insumos registrados aún.
                            </div>
                        @endforelse
                    </div>

                    <div class="p-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40 text-center">
                        <a href="{{ route('productos.index', ['tab' => 'insumos']) }}"
                            class="text-[11px] font-black uppercase text-cyan-600 dark:text-cyan-400 hover:underline">
                            Administrar Insumos &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla Historial de Compras (Full Width) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h2 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-cyan-500"></i> HISTORIAL DE COMPRAS
                </h2>
                <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase">
                    TOTAL: {{ $compras->total() }} COMPRAS
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 tracking-wider">
                            <th class="py-3 px-4">FECHA</th>
                            <th class="py-3 px-4">FORMA DE PAGO</th>
                            <th class="py-3 px-4">INSUMOS ADQUIRIDOS</th>
                            <th class="py-3 px-4 text-right">TOTAL PAGADO</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($compras as $c)
                            @php
                                $esQr = ($c->metodo_pago === 'qr');
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-3 px-4 font-bold text-slate-900 dark:text-white uppercase">
                                    {{ \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') }}
                                </td>

                                <td class="py-3 px-4">
                                    @if($esQr)
                                        <span class="px-2.5 py-1 rounded-full bg-purple-100 dark:bg-purple-500/10 border border-purple-200 dark:border-purple-500/30 text-purple-700 dark:text-purple-400 font-black text-[10px] uppercase inline-flex items-center gap-1">
                                            <i class="fa-solid fa-qrcode"></i> QR / BANCO
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400 font-black text-[10px] uppercase inline-flex items-center gap-1">
                                            <i class="fa-solid fa-money-bill-wave"></i> EFECTIVO
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3 px-4">
                                    <div class="space-y-1">
                                        @foreach($c->detalles as $det)
                                            <div class="text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase flex items-center gap-1">
                                                <span class="text-cyan-500">•</span>
                                                <span>{{ strtoupper($det->insumo->nombre ?? 'INSUMO') }}:</span>
                                                <span class="text-slate-900 dark:text-white">{{ number_format($det->cantidad, 2) }} {{ strtoupper($det->insumo->unidad_medida ?? '') }}</span>
                                                <span class="text-slate-400 text-[10px]">x Bs. {{ number_format($det->precio_unitario, 2) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if($c->observaciones)
                                        <div class="text-[10px] text-slate-500 italic mt-1 uppercase">
                                            "{{ $c->observaciones }}"
                                        </div>
                                    @endif
                                </td>

                                <td class="py-3 px-4 text-right font-black text-rose-600 dark:text-rose-400 text-sm uppercase">
                                    - Bs. {{ number_format($c->monto_total, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-10 text-center text-slate-400 font-bold uppercase text-xs">
                                    NO HAY COMPRAS REGISTRADAS AÚN.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800">
                {{ $compras->links() }}
            </div>
        </div>

        <!-- Datalist para Autocompletar Insumos en el Carrito -->
        <datalist id="datalist-insumos">
            @foreach($insumos as $ins)
                <option value="{{ $ins->nombre }}">{{ $ins->nombre }} ({{ strtoupper($ins->unidad_medida) }})</option>
            @endforeach
        </datalist>

    </div>
@endsection

@section('scripts')
<script>
    function compraForm() {
        return {
            metodoPago: 'efectivo',
            insumosList: {!! json_encode($insumosList ?? []) !!},
            rows: [],
            inputError: '',
            inputItem: {
                insumo_id: '',
                nombre: '',
                unidad_medida: 'kg',
                cantidad: '1',
                precio_unitario: '',
                es_nuevo: false
            },

            onInputNombreChange() {
                const val = (this.inputItem.nombre || '').trim();
                this.inputError = '';
                if (!val) {
                    this.inputItem.insumo_id = '';
                    this.inputItem.unidad_medida = 'kg';
                    this.inputItem.es_nuevo = false;
                    return;
                }
                const match = this.insumosList.find(i => i.nombre.toLowerCase() === val.toLowerCase());
                if (match) {
                    this.inputItem.insumo_id = match.id;
                    this.inputItem.nombre = match.nombre;
                    this.inputItem.unidad_medida = match.unidad_medida.toLowerCase();
                    this.inputItem.es_nuevo = false;
                    if (!this.inputItem.precio_unitario && match.ultimo_precio > 0) {
                        this.inputItem.precio_unitario = match.ultimo_precio;
                    }
                } else {
                    this.inputItem.insumo_id = 'nuevo';
                    this.inputItem.es_nuevo = true;
                    if (!this.inputItem.unidad_medida) {
                        this.inputItem.unidad_medida = 'kg';
                    }
                }
            },

            agregarItem() {
                const nom = (this.inputItem.nombre || '').trim();
                const cant = parseFloat(this.inputItem.cantidad) || 0;
                const prec = parseFloat(this.inputItem.precio_unitario) || 0;
                const unid = (this.inputItem.unidad_medida || '').trim();

                if (!nom) {
                    this.inputError = 'Escribe el nombre del insumo.';
                    document.getElementById('input-insumo-nombre')?.focus();
                    return;
                }
                if (!unid) {
                    this.inputError = 'Selecciona la unidad de medida.';
                    return;
                }
                if (cant <= 0) {
                    this.inputError = 'La cantidad debe ser mayor a 0.';
                    return;
                }
                if (prec <= 0) {
                    this.inputError = 'El precio unitario debe ser mayor a 0.';
                    return;
                }

                const subtotal = (cant * prec).toFixed(2);
                this.rows.push({
                    insumo_id: this.inputItem.insumo_id,
                    nombre: nom,
                    unidad_medida: unid,
                    cantidad: cant,
                    precio_unitario: prec,
                    subtotal: subtotal,
                    es_nuevo: this.inputItem.es_nuevo
                });

                // Resetear campos para escribir el siguiente insumo inmediatamente
                this.inputItem = {
                    insumo_id: '',
                    nombre: '',
                    unidad_medida: 'kg',
                    cantidad: '1',
                    precio_unitario: '',
                    es_nuevo: false
                };
                this.inputError = '';
                document.getElementById('input-insumo-nombre')?.focus();
            },

            calcSubtotal(idx) {
                const cant = parseFloat(this.rows[idx].cantidad) || 0;
                const prec = parseFloat(this.rows[idx].precio_unitario) || 0;
                this.rows[idx].subtotal = (cant * prec).toFixed(2);
            },

            removeRow(idx) {
                this.rows.splice(idx, 1);
            },

            get grandTotal() {
                return this.rows.reduce((sum, r) => {
                    const cant = parseFloat(r.cantidad) || 0;
                    const prec = parseFloat(r.precio_unitario) || 0;
                    return sum + (cant * prec);
                }, 0).toFixed(2);
            }
        };
    }
</script>
@endsection
