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
                        <div class="bg-slate-50 dark:bg-slate-950 p-4 rounded-xl border border-cyan-500/30 dark:border-cyan-500/20 space-y-3 shadow-xs">
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
                                        placeholder="Escribe el insumo (ej. Harina, Queso)..."
                                        class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-900 dark:text-white uppercase focus:border-cyan-500 focus:outline-none">
                                </div>

                                <!-- Medida (Unidad de Medida del Insumo) -->
                                <div class="sm:col-span-2">
                                    <label class="block text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">
                                        MEDIDA <span class="text-amber-500">*</span>
                                    </label>
                                    <template x-if="!inputItem.es_nuevo && inputItem.insumo_id">
                                        <div class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1.5 text-xs font-black text-amber-600 dark:text-amber-400 text-center uppercase">
                                            <span x-text="inputItem.unidad_medida.toUpperCase()"></span>
                                        </div>
                                    </template>
                                    <template x-if="inputItem.es_nuevo || !inputItem.insumo_id">
                                        <select x-model="inputItem.unidad_medida" @change="onUnidadMedidaChange()"
                                            class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1.5 text-xs font-bold text-slate-900 dark:text-white uppercase focus:border-cyan-500 focus:outline-none">
                                            <option value="kg">KG (KILOGRAMO)</option>
                                            <option value="g">G (GRAMO)</option>
                                            <option value="quintal">QUINTAL</option>
                                            <option value="libra">LIBRA</option>
                                            <option value="litro">LITRO</option>
                                            <option value="ml">ML (MILILITRO)</option>
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

                                <!-- Cantidad / Peso con Switch de sub-unidad (KG vs Gramos, Litro vs ML) -->
                                <div class="sm:col-span-2">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400">
                                            <span x-text="getCantidadLabel()"></span> <span class="text-amber-500">*</span>
                                        </label>
                                        <!-- Selector de modo Kg vs Gramos si la medida base es KG -->
                                        <template x-if="inputItem.unidad_medida === 'kg'">
                                            <div class="inline-flex rounded-md p-0.5 bg-slate-200 dark:bg-slate-800 text-[9px] font-black uppercase">
                                                <button type="button" @click="setUnitMode('kg')"
                                                    :class="inputItem.input_mode === 'kg' ? 'bg-cyan-600 text-white shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-cyan-500'"
                                                    class="px-1.5 py-0.5 rounded transition-all">
                                                    KG
                                                </button>
                                                <button type="button" @click="setUnitMode('g')"
                                                    :class="inputItem.input_mode === 'g' ? 'bg-cyan-600 text-white shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-cyan-500'"
                                                    class="px-1.5 py-0.5 rounded transition-all">
                                                    GRAMOS
                                                </button>
                                            </div>
                                        </template>
                                        <!-- Selector de modo Litro vs ML si la medida base es LITRO -->
                                        <template x-if="inputItem.unidad_medida === 'litro'">
                                            <div class="inline-flex rounded-md p-0.5 bg-slate-200 dark:bg-slate-800 text-[9px] font-black uppercase">
                                                <button type="button" @click="setUnitMode('litro')"
                                                    :class="inputItem.input_mode === 'litro' ? 'bg-cyan-600 text-white shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-cyan-500'"
                                                    class="px-1.5 py-0.5 rounded transition-all">
                                                    L
                                                </button>
                                                <button type="button" @click="setUnitMode('ml')"
                                                    :class="inputItem.input_mode === 'ml' ? 'bg-cyan-600 text-white shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-cyan-500'"
                                                    class="px-1.5 py-0.5 rounded transition-all">
                                                    ML
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                    <input type="number" step="any" min="0.001" x-model="inputItem.raw_cantidad"
                                        @keydown.enter.prevent="agregarItem()"
                                        :placeholder="getCantidadPlaceholder()"
                                        class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-900 dark:text-white uppercase focus:border-cyan-500 focus:outline-none">
                                </div>

                                <!-- Precio en la Medida Base -->
                                <div class="sm:col-span-2">
                                    <label class="block text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">
                                        <span x-text="getPrecioLabel()"></span> <span class="text-amber-500">*</span>
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

                            <!-- Atajos Rápidos de Peso / Fracciones para agilizar la compra -->
                            <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                                <template x-if="inputItem.unidad_medida === 'kg'">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span class="text-[9px] font-black uppercase text-slate-400 mr-1">⚖️ Atajos de Peso:</span>
                                        <button type="button" @click="setQuickWeight('0.25', '250')"
                                            class="px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-950/50 border border-amber-300 dark:border-amber-700 hover:bg-amber-100 text-amber-700 dark:text-amber-300 text-[10px] font-black uppercase transition-all shadow-2xs">
                                            ¼ Kg (250g)
                                        </button>
                                        <button type="button" @click="setQuickWeight('0.5', '500')"
                                            class="px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-950/50 border border-amber-300 dark:border-amber-700 hover:bg-amber-100 text-amber-700 dark:text-amber-300 text-[10px] font-black uppercase transition-all shadow-2xs">
                                            ½ Kg (500g)
                                        </button>
                                        <button type="button" @click="setQuickWeight('0.75', '750')"
                                            class="px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-950/50 border border-amber-300 dark:border-amber-700 hover:bg-amber-100 text-amber-700 dark:text-amber-300 text-[10px] font-black uppercase transition-all shadow-2xs">
                                            ¾ Kg (750g)
                                        </button>
                                        <button type="button" @click="setQuickWeight('1', '1000')"
                                            class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-[10px] font-black uppercase transition-all shadow-2xs">
                                            1 Kg
                                        </button>
                                        <button type="button" @click="setQuickWeight('2', '2000')"
                                            class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-[10px] font-black uppercase transition-all shadow-2xs">
                                            2 Kg
                                        </button>
                                    </div>
                                </template>
                                <template x-if="inputItem.unidad_medida === 'litro'">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span class="text-[9px] font-black uppercase text-slate-400 mr-1">🥤 Atajos de Volumen:</span>
                                        <button type="button" @click="setQuickVolume('0.25', '250')"
                                            class="px-2 py-0.5 rounded bg-purple-50 dark:bg-purple-950/50 border border-purple-300 dark:border-purple-700 hover:bg-purple-100 text-purple-700 dark:text-purple-300 text-[10px] font-black uppercase transition-all shadow-2xs">
                                            ¼ Litro (250ml)
                                        </button>
                                        <button type="button" @click="setQuickVolume('0.5', '500')"
                                            class="px-2 py-0.5 rounded bg-purple-50 dark:bg-purple-950/50 border border-purple-300 dark:border-purple-700 hover:bg-purple-100 text-purple-700 dark:text-purple-300 text-[10px] font-black uppercase transition-all shadow-2xs">
                                            ½ Litro (500ml)
                                        </button>
                                        <button type="button" @click="setQuickVolume('0.75', '750')"
                                            class="px-2 py-0.5 rounded bg-purple-50 dark:bg-purple-950/50 border border-purple-300 dark:border-purple-700 hover:bg-purple-100 text-purple-700 dark:text-purple-300 text-[10px] font-black uppercase transition-all shadow-2xs">
                                            ¾ Litro (750ml)
                                        </button>
                                        <button type="button" @click="setQuickVolume('1', '1000')"
                                            class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-[10px] font-black uppercase transition-all shadow-2xs">
                                            1 Litro
                                        </button>
                                    </div>
                                </template>
                                <template x-if="inputItem.unidad_medida === 'docena'">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span class="text-[9px] font-black uppercase text-slate-400 mr-1">🥚 Atajos de Docena:</span>
                                        <button type="button" @click="setQuickDocena('0.5')"
                                            class="px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-950/50 border border-amber-300 dark:border-amber-700 hover:bg-amber-100 text-amber-700 dark:text-amber-300 text-[10px] font-black uppercase transition-all shadow-2xs">
                                            ½ Docena (6 un.)
                                        </button>
                                        <button type="button" @click="setQuickDocena('1')"
                                            class="px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-950/50 border border-amber-300 dark:border-amber-700 hover:bg-amber-100 text-amber-700 dark:text-amber-300 text-[10px] font-black uppercase transition-all shadow-2xs">
                                            1 Docena (12 un.)
                                        </button>
                                        <button type="button" @click="setQuickDocena('2')"
                                            class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-[10px] font-black uppercase transition-all shadow-2xs">
                                            2 Docenas (24 un.)
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <!-- Explicación en Tiempo Real y Cálculo del Subtotal -->
                            <div class="p-2.5 rounded-lg border flex flex-wrap items-center justify-between gap-2 text-xs transition-all"
                                :class="isLargeKgWarning ? 'bg-amber-50 dark:bg-amber-950/40 border-amber-300 dark:border-amber-700 text-amber-900 dark:text-amber-200' : 'bg-cyan-50 dark:bg-cyan-950/30 border-cyan-200 dark:border-cyan-800/60 text-slate-800 dark:text-slate-200'">
                                <div class="flex items-center gap-2">
                                    <span class="text-base" x-text="isLargeKgWarning ? '⚠️' : '💡'"></span>
                                    <div class="font-bold">
                                        <span x-html="conversionMessage"></span>
                                        <template x-if="isLargeKgWarning">
                                            <button type="button" @click="fixLargeKgToGrams()"
                                                class="ml-2 px-2 py-0.5 rounded bg-amber-600 hover:bg-amber-500 text-white font-black text-[10px] uppercase shadow-xs">
                                                Convertir a <span x-text="inputItem.raw_cantidad"></span> gramos (0.<span x-text="inputItem.raw_cantidad"></span> Kg)
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                <div class="font-black text-rose-600 dark:text-rose-400 uppercase text-xs">
                                    Subtotal: Bs. <span x-text="computedSubtotal">0.00</span>
                                </div>
                            </div>

                            <!-- Feedback de detección y error -->
                            <div class="flex items-center justify-between text-[10px]">
                                <div>
                                    <template x-if="inputItem.insumo_id && !inputItem.es_nuevo">
                                        <span class="font-extrabold text-emerald-600 dark:text-emerald-400 uppercase flex items-center gap-1">
                                            <i class="fa-solid fa-circle-check"></i> Insumo Registrado (Medida: <span x-text="inputItem.unidad_medida.toUpperCase()"></span>)
                                        </span>
                                    </template>
                                    <template x-if="inputItem.es_nuevo && inputItem.nombre.trim().length > 0">
                                        <span class="font-extrabold text-cyan-600 dark:text-cyan-400 uppercase flex items-center gap-1">
                                            <i class="fa-solid fa-circle-plus"></i> Insumo Nuevo (Elige su medida en el selector)
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
                                            <th class="py-2.5 px-3 text-center">MEDIDA</th>
                                            <th class="py-2.5 px-3 text-center">CANTIDAD / PESO</th>
                                            <th class="py-2.5 px-3 text-right">PRECIO BASE</th>
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
                                                    <div class="inline-flex items-center justify-center gap-1">
                                                        <input type="number" step="any" min="0.001" :name="'items[' + idx + '][cantidad]'"
                                                            x-model="row.cantidad" @input="calcSubtotal(idx)"
                                                            class="w-24 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-xs font-black text-center text-slate-900 dark:text-white focus:border-cyan-500 focus:outline-none">
                                                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase" x-text="row.unidad_medida"></span>
                                                    </div>
                                                    <template x-if="getDetailNote(row)">
                                                        <div class="text-[10px] font-black text-cyan-600 dark:text-cyan-400 mt-1 uppercase"
                                                            x-text="getDetailNote(row)"></div>
                                                    </template>
                                                    <template x-if="row.unidad_medida === 'kg' && parseFloat(row.cantidad) >= 50">
                                                        <div class="text-[9px] font-black text-rose-500 mt-0.5 uppercase">
                                                            ⚠️ ¿Pusiste Kilos o Gramos?
                                                        </div>
                                                    </template>
                                                </td>

                                                <td class="py-2.5 px-3 text-right">
                                                    <div class="inline-flex items-center justify-end gap-1">
                                                        <span class="text-[10px] text-slate-400 font-bold">Bs.</span>
                                                        <input type="number" step="any" min="0.01" :name="'items[' + idx + '][precio_unitario]'"
                                                            x-model="row.precio_unitario" @input="calcSubtotal(idx)"
                                                            class="w-24 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-xs font-bold text-right text-slate-900 dark:text-white focus:border-cyan-500 focus:outline-none">
                                                    </div>
                                                    <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 block text-right uppercase mt-0.5">
                                                        x <span x-text="row.unidad_medida.toUpperCase()"></span>
                                                    </span>
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
                                            @if(strtolower($ins->unidad_medida) === 'kg' && $stock < 1)
                                                {{ round($stock * 1000) }} g ({{ number_format($stock, 2) }} KG)
                                            @elseif(strtolower($ins->unidad_medida) === 'litro' && $stock < 1)
                                                {{ round($stock * 1000) }} ml ({{ number_format($stock, 2) }} L)
                                            @else
                                                {{ (float)$stock == (int)$stock ? number_format($stock, 0) : number_format($stock, 2) }} {{ strtoupper($ins->unidad_medida) }}
                                            @endif
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
                                            @php
                                                $detCant = (float)$det->cantidad;
                                                $detUnid = strtolower($det->insumo->unidad_medida ?? 'unidad');
                                            @endphp
                                            <div class="text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase flex items-center gap-1">
                                                <span class="text-cyan-500">•</span>
                                                <span>{{ strtoupper($det->insumo->nombre ?? 'INSUMO') }}:</span>
                                                <span class="text-slate-900 dark:text-white">
                                                    @if($detUnid === 'kg' && $detCant < 1)
                                                        {{ round($detCant * 1000) }} g ({{ number_format($detCant, 2) }} Kg)
                                                    @elseif($detUnid === 'litro' && $detCant < 1)
                                                        {{ round($detCant * 1000) }} ml ({{ number_format($detCant, 2) }} L)
                                                    @else
                                                        {{ (float)$detCant == (int)$detCant ? number_format($detCant, 0) : number_format($detCant, 2) }} {{ strtoupper($detUnid) }}
                                                    @endif
                                                </span>
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
                input_mode: 'kg',
                raw_cantidad: '1',
                precio_unitario: '',
                es_nuevo: false
            },

            // Cantidad numérica real en la unidad base (ej: 250 g -> 0.25 kg)
            get computedBaseCantidad() {
                const raw = parseFloat(this.inputItem.raw_cantidad) || 0;
                if (raw <= 0) return 0;
                if (this.inputItem.unidad_medida === 'kg' && this.inputItem.input_mode === 'g') {
                    return parseFloat((raw / 1000).toFixed(4));
                }
                if (this.inputItem.unidad_medida === 'litro' && this.inputItem.input_mode === 'ml') {
                    return parseFloat((raw / 1000).toFixed(4));
                }
                return raw;
            },

            // Subtotal dinámico del ítem que se está agregando
            get computedSubtotal() {
                const cant = this.computedBaseCantidad;
                const prec = parseFloat(this.inputItem.precio_unitario) || 0;
                return (cant * prec).toFixed(2);
            },

            // Alerta si el usuario puso >= 50 en modo KG (probablemente quiso decir gramos)
            get isLargeKgWarning() {
                if (this.inputItem.unidad_medida === 'kg' && this.inputItem.input_mode === 'kg') {
                    const raw = parseFloat(this.inputItem.raw_cantidad) || 0;
                    return raw >= 50;
                }
                return false;
            },

            fixLargeKgToGrams() {
                this.inputItem.input_mode = 'g';
            },

            getCantidadLabel() {
                const u = (this.inputItem.unidad_medida || '').toLowerCase();
                if (u === 'kg' || u === 'g' || u === 'libra' || u === 'quintal') {
                    return 'PESO';
                }
                if (u === 'litro' || u === 'ml') {
                    return 'VOLUMEN';
                }
                return 'CANTIDAD';
            },

            getCantidadPlaceholder() {
                const u = (this.inputItem.unidad_medida || '').toLowerCase();
                if (u === 'kg') {
                    return this.inputItem.input_mode === 'g' ? 'Ej. 250 (g)' : 'Ej. 0.25 ó 1 (Kg)';
                }
                if (u === 'litro') {
                    return this.inputItem.input_mode === 'ml' ? 'Ej. 500 (ml)' : 'Ej. 0.5 ó 1 (L)';
                }
                return '1.00';
            },

            getPrecioLabel() {
                const u = (this.inputItem.unidad_medida || '').toLowerCase();
                if (u === 'kg') return 'PRECIO X KG (BS.)';
                if (u === 'g') return 'PRECIO X G (BS.)';
                if (u === 'litro') return 'PRECIO X LITRO (BS.)';
                if (u === 'ml') return 'PRECIO X ML (BS.)';
                if (u === 'docena') return 'PRECIO X DOCENA (BS.)';
                if (u === 'quintal') return 'PRECIO X QUINTAL (BS.)';
                if (u === 'libra') return 'PRECIO X LIBRA (BS.)';
                return 'PRECIO UNIT. (BS.)';
            },

            get conversionMessage() {
                const u = (this.inputItem.unidad_medida || '').toLowerCase();
                const raw = parseFloat(this.inputItem.raw_cantidad) || 0;
                const baseCant = this.computedBaseCantidad;

                if (this.isLargeKgWarning) {
                    return `Has ingresado <strong>${raw} Kilogramos (${raw} Kilos)</strong>. Si compraste ${raw} gramos (0.${raw} Kg), pulsa:`;
                }

                if (u === 'kg') {
                    if (this.inputItem.input_mode === 'g') {
                        let desc = '';
                        if (raw === 250) desc = ' (Un cuarto de kilo)';
                        else if (raw === 500) desc = ' (Medio kilo)';
                        else if (raw === 750) desc = ' (Tres cuartos de kilo)';
                        else if (raw === 1000) desc = ' (Un kilo)';
                        return `<strong>${raw} gramos</strong>${desc} = <span class="text-cyan-700 dark:text-cyan-300 font-black">${baseCant} KG</span>. Se sumarán <strong>${baseCant} KG</strong> a almacén.`;
                    } else {
                        let desc = '';
                        if (raw === 0.25) desc = ' = 250 gramos (Un cuarto de kilo)';
                        else if (raw === 0.5) desc = ' = 500 gramos (Medio kilo)';
                        else if (raw === 0.75) desc = ' = 750 gramos (Tres cuartos de kilo)';
                        else if (raw === 1) desc = ' = 1000 gramos (Un kilo)';
                        else if (raw < 1 && raw > 0) desc = ` = ${Math.round(raw * 1000)} gramos`;
                        return `<strong>${raw} KG</strong>${desc}. Se sumarán <strong>${baseCant} KG</strong> a almacén.`;
                    }
                }

                if (u === 'litro') {
                    if (this.inputItem.input_mode === 'ml') {
                        let desc = '';
                        if (raw === 250) desc = ' (Un cuarto de litro)';
                        else if (raw === 500) desc = ' (Medio litro)';
                        else if (raw === 750) desc = ' (Tres cuartos de litro)';
                        else if (raw === 1000) desc = ' (Un litro)';
                        return `<strong>${raw} ml</strong>${desc} = <span class="text-cyan-700 dark:text-cyan-300 font-black">${baseCant} Litro</span>. Se sumarán <strong>${baseCant} L</strong> a almacén.`;
                    } else {
                        let desc = '';
                        if (raw === 0.25) desc = ' = 250 ml (Un cuarto de litro)';
                        else if (raw === 0.5) desc = ' = 500 ml (Medio litro)';
                        else if (raw === 0.75) desc = ' = 750 ml (Tres cuartos de litro)';
                        else if (raw === 1) desc = ' = 1 Litro';
                        else if (raw < 1 && raw > 0) desc = ` = ${Math.round(raw * 1000)} ml`;
                        return `<strong>${raw} Litros</strong>${desc}. Se sumarán <strong>${baseCant} L</strong> a almacén.`;
                    }
                }

                if (u === 'docena') {
                    if (raw === 0.5) return `<strong>0.5 docena</strong> = <span class="text-cyan-700 dark:text-cyan-300 font-black">6 unidades</span> (Media docena).`;
                    if (raw === 1) return `<strong>1 docena</strong> = <span class="text-cyan-700 dark:text-cyan-300 font-black">12 unidades</span>.`;
                    if (raw > 0) return `<strong>${raw} docenas</strong> = <span class="text-cyan-700 dark:text-cyan-300 font-black">${Math.round(raw * 12)} unidades</span>.`;
                }

                return `Cantidad: <strong>${baseCant} ${u.toUpperCase()}</strong>.`;
            },

            getDetailNote(row) {
                const u = (row.unidad_medida || '').toLowerCase();
                const num = parseFloat(row.cantidad) || 0;
                if (u === 'kg') {
                    if (num === 0.25) return '⚖️ 250 gramos (¼ Kg)';
                    if (num === 0.5) return '⚖️ 500 gramos (½ Kg)';
                    if (num === 0.75) return '⚖️ 750 gramos (¾ Kg)';
                    if (num > 0 && num < 1) return `⚖️ ${Math.round(num * 1000)} gramos`;
                    if (num >= 1 && num % 1 !== 0) return `⚖️ ${Math.floor(num)} Kg y ${Math.round((num % 1) * 1000)}g`;
                    if (num >= 1) return `⚖️ ${num} Kg`;
                }
                if (u === 'litro') {
                    if (num === 0.25) return '🥤 250 ml (¼ Litro)';
                    if (num === 0.5) return '🥤 500 ml (½ Litro)';
                    if (num === 0.75) return '🥤 750 ml (¾ Litro)';
                    if (num > 0 && num < 1) return `🥤 ${Math.round(num * 1000)} ml`;
                    if (num >= 1 && num % 1 !== 0) return `🥤 ${Math.floor(num)} L y ${Math.round((num % 1) * 1000)} ml`;
                    if (num >= 1) return `🥤 ${num} Litros`;
                }
                if (u === 'docena') {
                    if (num === 0.5) return '🥚 6 unidades (½ docena)';
                    if (num === 1) return '🥚 12 unidades (1 docena)';
                    if (num > 0) return `🥚 ${Math.round(num * 12)} unidades`;
                }
                return '';
            },

            setUnitMode(mode) {
                if (this.inputItem.input_mode === mode) return;
                const currentRaw = parseFloat(this.inputItem.raw_cantidad) || 0;
                if (this.inputItem.unidad_medida === 'kg') {
                    if (mode === 'g' && this.inputItem.input_mode === 'kg') {
                        this.inputItem.raw_cantidad = currentRaw > 0 ? (currentRaw * 1000).toString() : '250';
                    } else if (mode === 'kg' && this.inputItem.input_mode === 'g') {
                        this.inputItem.raw_cantidad = currentRaw > 0 ? (currentRaw / 1000).toString() : '1';
                    }
                } else if (this.inputItem.unidad_medida === 'litro') {
                    if (mode === 'ml' && this.inputItem.input_mode === 'litro') {
                        this.inputItem.raw_cantidad = currentRaw > 0 ? (currentRaw * 1000).toString() : '500';
                    } else if (mode === 'litro' && this.inputItem.input_mode === 'ml') {
                        this.inputItem.raw_cantidad = currentRaw > 0 ? (currentRaw / 1000).toString() : '1';
                    }
                }
                this.inputItem.input_mode = mode;
            },

            setQuickWeight(kgVal, gVal) {
                if (this.inputItem.input_mode === 'g') {
                    this.inputItem.raw_cantidad = gVal;
                } else {
                    this.inputItem.raw_cantidad = kgVal;
                }
            },

            setQuickVolume(litroVal, mlVal) {
                if (this.inputItem.input_mode === 'ml') {
                    this.inputItem.raw_cantidad = mlVal;
                } else {
                    this.inputItem.raw_cantidad = litroVal;
                }
            },

            setQuickDocena(val) {
                this.inputItem.raw_cantidad = val.toString();
            },

            onInputNombreChange() {
                const val = (this.inputItem.nombre || '').trim();
                this.inputError = '';
                if (!val) {
                    this.inputItem.insumo_id = '';
                    this.inputItem.unidad_medida = 'kg';
                    this.inputItem.input_mode = 'kg';
                    this.inputItem.es_nuevo = false;
                    return;
                }
                const match = this.insumosList.find(i => i.nombre.toLowerCase() === val.toLowerCase());
                if (match) {
                    this.inputItem.insumo_id = match.id;
                    this.inputItem.nombre = match.nombre;
                    const prevUnidad = this.inputItem.unidad_medida;
                    this.inputItem.unidad_medida = match.unidad_medida.toLowerCase();
                    if (prevUnidad !== this.inputItem.unidad_medida) {
                        this.inputItem.input_mode = this.inputItem.unidad_medida === 'kg' ? 'kg' : (this.inputItem.unidad_medida === 'litro' ? 'litro' : 'unidad');
                    }
                    this.inputItem.es_nuevo = false;
                    if (!this.inputItem.precio_unitario && match.ultimo_precio > 0) {
                        this.inputItem.precio_unitario = match.ultimo_precio;
                    }
                } else {
                    this.inputItem.insumo_id = 'nuevo';
                    this.inputItem.es_nuevo = true;
                    if (!this.inputItem.unidad_medida) {
                        this.inputItem.unidad_medida = 'kg';
                        this.inputItem.input_mode = 'kg';
                    }
                }
            },

            onUnidadMedidaChange() {
                this.inputItem.input_mode = this.inputItem.unidad_medida === 'kg' ? 'kg' : (this.inputItem.unidad_medida === 'litro' ? 'litro' : 'unidad');
            },

            agregarItem() {
                const nom = (this.inputItem.nombre || '').trim();
                const cantBase = this.computedBaseCantidad;
                const precBase = parseFloat(this.inputItem.precio_unitario) || 0;
                const unid = (this.inputItem.unidad_medida || '').toLowerCase().trim();

                if (!nom) {
                    this.inputError = 'Escribe el nombre del insumo.';
                    document.getElementById('input-insumo-nombre')?.focus();
                    return;
                }
                if (!unid) {
                    this.inputError = 'Selecciona la unidad o medida.';
                    return;
                }
                if (cantBase <= 0) {
                    this.inputError = 'El peso o cantidad debe ser mayor a 0.';
                    return;
                }
                if (precBase <= 0) {
                    this.inputError = 'El precio debe ser mayor a 0.';
                    return;
                }

                const subtotal = (cantBase * precBase).toFixed(2);
                this.rows.push({
                    insumo_id: this.inputItem.insumo_id,
                    nombre: nom,
                    unidad_medida: unid,
                    cantidad: cantBase,
                    precio_unitario: precBase,
                    subtotal: subtotal,
                    es_nuevo: this.inputItem.es_nuevo
                });

                // Resetear campos para escribir el siguiente insumo inmediatamente
                this.inputItem = {
                    insumo_id: '',
                    nombre: '',
                    unidad_medida: 'kg',
                    input_mode: 'kg',
                    raw_cantidad: '1',
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
