<!doctype html>
<html lang="es" class="dark-mode">

<head>
    <meta charset="utf-8">
    <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>VENTAS - SALTEÑERÍA MONAKA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { primary: '#FFE66D', accent: '#E23E1A', dark: '#09090c' } } } }</script>
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .pos-card-theme {
            background-color: var(--color-bg-alt, rgba(255, 255, 255, 0.05));
            color: var(--color-text, #ffffff);
            border: 1px solid var(--color-border, rgba(255, 255, 255, 0.12));
        }

        .form-input-pos {
            background-color: var(--color-bg, rgba(0, 0, 0, 0.2));
            color: var(--color-text, #ffffff);
            border: 1px solid var(--color-border, rgba(255, 255, 255, 0.15));
        }

        .form-input-pos:focus {
            border-color: #FFE66D;
            outline: none;
        }
    </style>
</head>

<body class="min-h-screen" style="background-color:var(--color-bg);color:var(--color-text);">

    <!-- Navbar Unificada -->
    @include('layouts.admin_navbar')

    <div class="max-w-7xl mx-auto px-4 py-4">
        <!-- Banner Encabezado -->
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl md:text-2xl font-black uppercase flex items-center gap-2">
                <i class="fa-solid fa-bolt text-amber-500"></i>VENTAS (PARA LLEVAR)
            </h2>
            <div
                class="text-xs font-bold uppercase px-3 py-1.5 rounded-lg bg-amber-500/10 text-amber-500 border border-amber-500/30">
                <i class="fa-solid fa-store mr-1"></i>MOSTRADOR / VENTAS DIRECTAS
            </div>
        </div>

        @if (session('success'))
            <div
                class="mb-4 p-4 rounded-xl bg-green-500/20 border border-green-500/50 text-green-300 font-bold text-xs uppercase flex items-center justify-between">
                <span><i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}</span>
                @if(session('ticket_venta_id'))
                    <a href="{{ route('ticket.show', session('ticket_venta_id')) }}" target="_blank"
                        class="px-3 py-1 bg-green-600 hover:bg-green-500 text-white rounded-lg text-xs font-black">
                        <i class="fa-solid fa-print mr-1"></i>IMPRIMIR TICKET
                    </a>
                @endif
            </div>
        @endif

        @if (session('error'))
            <div
                class="mb-4 p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-300 font-bold text-xs uppercase">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i>{{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- COLUMNA IZQUIERDA: CATÁLOGO DE PRODUCTOS (8 cols) -->
            <div class="lg:col-span-7 xl:col-span-8 space-y-4">
                <!-- Filtro por Categorías -->
                <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                    <button onclick="filtrarCategoria('todas')" id="cat-btn-todas"
                        class="cat-filter-btn px-4 py-2 rounded-xl text-xs font-black uppercase transition-all bg-amber-500 text-black">
                        TODAS
                    </button>
                    @foreach ($categorias as $cat)
                        <button onclick="filtrarCategoria('cat-{{ $cat->categoria_id }}')"
                            id="cat-btn-cat-{{ $cat->categoria_id }}"
                            class="cat-filter-btn px-4 py-2 rounded-xl text-xs font-bold uppercase transition-all pos-card-theme hover:bg-amber-500/20">
                            {{ strtoupper($cat->nombre) }}
                        </button>
                    @endforeach
                </div>

                <!-- Grid de Productos -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 xl:grid-cols-4 gap-3">
                    @foreach ($productos as $p)
                                        <div class="prod-card pos-card-theme p-3 rounded-2xl flex flex-col justify-between hover:border-amber-500/50 transition-all cursor-pointer shadow-sm group"
                                            data-categoria="cat-{{ $p->categoria_id }}"
                                            onclick="handleSelectProducto({{ json_encode($p) }})">
                                            <div>
                                                <div
                                                    class="w-full h-24 rounded-xl mb-2 flex items-center justify-center overflow-hidden bg-black/10 relative">
                                                    <?php
                        $imgPath = null;
                        if (!empty($p->imagen)) {
                            $imgPath = str_starts_with($p->imagen, 'assets/') ? $p->imagen : 'assets/productos/' . $p->imagen;
                        }
                        $hasImg = !empty($imgPath) && file_exists(public_path($imgPath));
                                                        ?>
                                                    @if($hasImg)
                                                        <img src="{{ asset($imgPath) }}" alt="{{ $p->nombre }}"
                                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                                    @else
                                                        <i class="fa-solid fa-burger text-3xl opacity-40 text-amber-500"></i>
                                                    @endif
                                                </div>
                                                <h4 class="font-extrabold text-xs uppercase leading-tight line-clamp-2"
                                                    style="color:var(--color-text);">{{ strtoupper($p->nombre) }}</h4>
                                            </div>

                                            <div class="mt-3 flex items-center justify-between">
                                                <span class="text-xs font-black text-amber-500">
                                                    {{ $p->tipo === 'simple' ? 'Bs. ' . number_format($p->precio, 2) : 'VARIAS OPCIONES' }}
                                                </span>
                                                <span
                                                    class="w-7 h-7 rounded-lg bg-amber-500 text-black flex items-center justify-center text-xs font-black shadow-sm group-hover:scale-110 transition-transform">
                                                    <i class="fa-solid fa-plus"></i>
                                                </span>
                                            </div>
                                        </div>
                    @endforeach
                </div>
            </div>

            <!-- COLUMNA DERECHA: CARRITO DE VENTA (4 cols) -->
            <div class="lg:col-span-5 xl:col-span-4">
                <div class="pos-card-theme p-5 rounded-2xl sticky top-20 shadow-xl space-y-4">
                    <div class="flex items-center justify-between border-b pb-3"
                        style="border-color:var(--color-border, rgba(255,255,255,0.1))">
                        <h3 class="font-black text-sm uppercase flex items-center gap-2">
                            <i class="fa-solid fa-receipt text-amber-500"></i>TICKET MOSTRADOR
                        </h3>
                        <button onclick="vaciarCarrito()"
                            class="text-[11px] text-red-400 hover:text-red-300 font-bold uppercase">
                            <i class="fa-solid fa-trash mr-1"></i>VACIAR
                        </button>
                    </div>

                    <!-- Lista de Ítems en Carrito -->
                    <div id="pos-cart-container" class="space-y-2 max-h-72 overflow-y-auto pr-1">
                        <div id="cart-empty-msg" class="py-8 text-center text-gray-400">
                            <i class="fa-solid fa-basket-shopping text-3xl mb-2 opacity-30"></i>
                            <p class="text-xs font-bold uppercase">CARRITO VACÍO</p>
                            <p class="text-[10px] text-gray-500">Haz clic en un producto para agregarlo</p>
                        </div>
                    </div>

                    <!-- Resumen y Formulario de Cobro -->
                    <form action="{{ route('admin.pos.venta') }}" method="POST" id="form-pos-venta"
                        onsubmit="return validarVenta();">
                        @csrf
                        <input type="hidden" name="items" id="input-cart-items">
                        <input type="hidden" name="monto_total" id="input-cart-total">

                        <div class="space-y-3 pt-3 border-t"
                            style="border-color:var(--color-border, rgba(255,255,255,0.1))">
                            <!-- Nombre de Cliente (Opcional) -->
                            <div>
                                <label class="block text-[11px] font-bold uppercase mb-1"
                                    style="color:var(--color-text-muted, #9ca3af);">CLIENTE (OPCIONAL)</label>
                                <input type="text" name="cliente_nombre" placeholder="CLIENTE MOSTRADOR"
                                    oninput="this.value = this.value.toUpperCase();" style="text-transform: uppercase;"
                                    class="w-full form-input-pos rounded-xl px-3 py-2 text-xs font-bold">
                            </div>

                            <!-- Método de Pago -->
                            <div>
                                <label class="block text-[11px] font-bold uppercase mb-1"
                                    style="color:var(--color-text-muted, #9ca3af);">MÉTODO DE PAGO *</label>
                                <select name="metodo_pago" required
                                    class="w-full form-input-pos rounded-xl px-3 py-2 text-xs font-bold uppercase">
                                    <option value="efectivo">💵 EFECTIVO</option>
                                    <option value="qr">📱 PAGO QR</option>
                                </select>
                            </div>

                            <!-- Total del Ticket -->
                            <div
                                class="p-4 rounded-xl flex items-center justify-between bg-amber-500/10 border border-amber-500/30">
                                <div>
                                    <span class="text-[10px] font-extrabold uppercase block"
                                        style="color:var(--color-text-muted, #9ca3af);">TOTAL A COBRAR</span>
                                    <span class="text-2xl font-black text-amber-500" id="cart-total-text">Bs.
                                        0.00</span>
                                </div>
                            </div>

                            <button type="submit" id="btn-completar-venta" disabled
                                class="w-full py-3 px-4 rounded-xl text-xs font-black uppercase bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 text-white shadow-lg transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-circle-check text-sm"></i>COMPLETAR VENTA
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL SELECCIÓN DE VARIANTE -->
    <div id="modal-variantes"
        class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="pos-card-theme rounded-2xl p-5 max-w-sm w-full shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b pb-2" style="border-color:var(--color-border)">
                <h3 class="text-sm font-black uppercase text-amber-500" id="modal-prod-title">SELECCIONAR TAMAÑO /
                    OPCIÓN</h3>
                <button type="button" onclick="cerrarModalVariantes()" class="text-gray-400 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div id="modal-variantes-list" class="space-y-2 max-h-60 overflow-y-auto"></div>
        </div>
    </div>

    <script>
        let cart = [];

        function handleSelectProducto(prod) {
            if (prod.tipo === 'variantes' && prod.variantes && prod.variantes.length > 0) {
                mostrarModalVariantes(prod);
            } else {
                agregarAlCarrito(prod.producto_id, null, prod.nombre, null, parseFloat(prod.precio));
            }
        }

        function mostrarModalVariantes(prod) {
            const container = document.getElementById('modal-variantes-list');
            document.getElementById('modal-prod-title').textContent = prod.nombre.toUpperCase();
            container.innerHTML = '';

            prod.variantes.forEach(v => {
                const btn = document.createElement('button');
                btn.className = 'w-full pos-card-theme hover:border-amber-500 p-3 rounded-xl flex items-center justify-between text-xs font-bold uppercase transition-all';
                btn.innerHTML = `
                    <span>${v.nombre_variante.toUpperCase()}</span>
                    <span class="text-amber-500 font-extrabold">Bs. ${parseFloat(v.precio).toFixed(2)}</span>
                `;
                btn.onclick = () => {
                    agregarAlCarrito(prod.producto_id, v.variante_id, prod.nombre, v.nombre_variante, parseFloat(v.precio));
                    cerrarModalVariantes();
                };
                container.appendChild(btn);
            });

            document.getElementById('modal-variantes').style.display = 'flex';
        }

        function cerrarModalVariantes() {
            document.getElementById('modal-variantes').style.display = 'none';
        }

        function agregarAlCarrito(producto_id, variante_id, nombreProducto, nombreVariante, precioUnitario) {
            const key = `${producto_id}_${variante_id || 0}`;
            const existing = cart.find(item => item.key === key);

            if (existing) {
                existing.cantidad++;
                existing.precio_total = existing.cantidad * existing.precio_unitario;
            } else {
                cart.push({
                    key: key,
                    producto_id: producto_id,
                    variante_id: variante_id,
                    nombre_producto: nombreProducto,
                    nombre_variante: nombreVariante,
                    cantidad: 1,
                    precio_unitario: precioUnitario,
                    precio_total: precioUnitario
                });
            }
            renderCarrito();
        }

        function cambiarCantidad(key, delta) {
            const item = cart.find(i => i.key === key);
            if (!item) return;

            item.cantidad += delta;
            if (item.cantidad <= 0) {
                cart = cart.filter(i => i.key !== key);
            } else {
                item.precio_total = item.cantidad * item.precio_unitario;
            }
            renderCarrito();
        }

        function vaciarCarrito() {
            cart = [];
            renderCarrito();
        }

        function renderCarrito() {
            const container = document.getElementById('pos-cart-container');
            const emptyMsg = document.getElementById('cart-empty-msg');
            const totalText = document.getElementById('cart-total-text');
            const btnSubmit = document.getElementById('btn-completar-venta');

            let totalSum = 0;

            if (cart.length === 0) {
                container.innerHTML = '';
                container.appendChild(emptyMsg);
                totalText.textContent = 'Bs. 0.00';
                btnSubmit.disabled = true;
                document.getElementById('input-cart-items').value = '';
                document.getElementById('input-cart-total').value = 0;
                return;
            }

            container.innerHTML = '';
            cart.forEach(item => {
                totalSum += item.precio_total;
                const div = document.createElement('div');
                div.className = 'pos-card-theme p-2.5 rounded-xl flex items-center justify-between text-xs';
                div.innerHTML = `
                    <div class="flex-1 pr-2">
                        <div class="font-extrabold uppercase leading-tight">${item.nombre_producto}</div>
                        ${item.nombre_variante ? `<div class="text-[10px] text-amber-500 font-bold">${item.nombre_variante}</div>` : ''}
                        <div class="text-[10px] opacity-70">Bs. ${item.precio_unitario.toFixed(2)} c/u</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1 rounded-lg p-1 bg-black/10">
                            <button type="button" onclick="cambiarCantidad('${item.key}', -1)" class="w-5 h-5 rounded flex items-center justify-center font-black hover:bg-amber-500 hover:text-black">-</button>
                            <span class="px-1.5 font-bold">${item.cantidad}</span>
                            <button type="button" onclick="cambiarCantidad('${item.key}', 1)" class="w-5 h-5 rounded flex items-center justify-center font-black hover:bg-amber-500 hover:text-black">+</button>
                        </div>
                        <span class="font-black text-amber-500 w-16 text-right">Bs. ${item.precio_total.toFixed(2)}</span>
                    </div>
                `;
                container.appendChild(div);
            });

            totalText.textContent = `Bs. ${totalSum.toFixed(2)}`;
            btnSubmit.disabled = false;

            document.getElementById('input-cart-items').value = JSON.stringify(cart);
            document.getElementById('input-cart-total').value = totalSum.toFixed(2);
        }

        function filtrarCategoria(catClass) {
            document.querySelectorAll('.cat-filter-btn').forEach(b => {
                b.className = 'cat-filter-btn px-4 py-2 rounded-xl text-xs font-bold uppercase transition-all pos-card-theme hover:bg-amber-500/20';
            });

            const activeBtn = document.getElementById('cat-btn-' + catClass.replace('cat-', ''));
            if (activeBtn) {
                activeBtn.className = 'cat-filter-btn px-4 py-2 rounded-xl text-xs font-black uppercase transition-all bg-amber-500 text-black';
            }

            document.querySelectorAll('.prod-card').forEach(card => {
                if (catClass === 'todas' || card.dataset.categoria === catClass) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function validarVenta() {
            if (cart.length === 0) {
                alert('Debes agregar al menos un producto al carrito');
                return false;
            }
            return true;
        }
    </script>
</body>

</html>