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

        /* ── FLOATING CART (EXACTLY LIKE CLIENT MENU) ── */
        #cart-fab {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 999;
            transition: transform 0.2s ease, opacity 0.2s ease;
        }

        #cart-fab.hidden-fab {
            transform: scale(0.7);
            opacity: 0;
            pointer-events: none;
        }

        #cart-fab-btn {
            background: #E23E1A;
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 14px 22px;
            font-size: 0.85rem;
            font-weight: 900;
            letter-spacing: 0.05em;
            box-shadow: 0 8px 30px rgba(226, 62, 26, 0.55);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            text-transform: uppercase;
            transition: transform 0.15s, box-shadow 0.15s;
        }

        #cart-fab-btn:hover {
            transform: scale(1.04);
            box-shadow: 0 10px 38px rgba(226, 62, 26, 0.7);
        }

        #cart-fab-badge {
            background: #FFE66D;
            color: #09090c;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            font-size: 0.7rem;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ── CART PANEL SIDEBAR ── */
        #cart-panel {
            position: fixed;
            top: 0;
            right: 0;
            height: 100%;
            width: 400px;
            max-width: 95vw;
            z-index: 1000;
            background: var(--color-bg, #09090c);
            border-left: 1px solid var(--color-border, rgba(255, 255, 255, 0.12));
            box-shadow: -10px 0 40px rgba(0, 0, 0, 0.6);
            transform: translateX(100%);
            transition: transform 0.32s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }

        #cart-panel.open {
            transform: translateX(0);
        }

        #cart-overlay {
            position: fixed;
            inset: 0;
            z-index: 999;
            background: rgba(0, 0, 0, 0.65);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }

        #cart-overlay.open {
            opacity: 1;
            pointer-events: auto;
        }

        /* EXACT CLIENT MENU CART ROW STYLING WITH LIGHT/DARK MODE SUPPORT */
        .cart-item-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid var(--color-border, rgba(255, 255, 255, 0.1));
        }

        .cart-item-info {
            flex: 1;
            font-size: 0.78rem;
            color: var(--color-text, #ffffff);
            font-weight: 700;
            line-height: 1.4;
        }

        .cart-item-price {
            font-size: 0.78rem;
            color: #d97706;
            font-weight: 900;
            white-space: nowrap;
        }

        .dark-mode .cart-item-price {
            color: #FFE66D;
        }

        .qty-btn {
            width: 26px;
            height: 26px;
            border-radius: 8px;
            border: 1px solid var(--color-border, rgba(255, 255, 255, 0.15));
            background: var(--color-bg-alt, rgba(0, 0, 0, 0.2));
            color: var(--color-text, #ffffff);
            cursor: pointer;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background 0.15s;
        }

        .qty-btn:hover {
            background: rgba(255, 230, 109, 0.15);
            color: #FFE66D;
        }

        .qty-display {
            width: 28px;
            text-align: center;
            font-weight: 900;
            font-size: 0.82rem;
            color: var(--color-text, #ffffff);
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
                <i class="fa-solid fa-bolt text-amber-500"></i>VENTAS
            </h2>
            <div class="flex items-center gap-3">
                <button type="button" onclick="openCart()"
                    class="text-xs font-bold uppercase px-3 py-1.5 rounded-lg bg-amber-500 text-black flex items-center gap-1.5 shadow-sm hover:scale-105 transition-transform">
                    <i class="fa-solid fa-cart-shopping"></i>VER PEDIDO (<span id="nav-cart-count">0</span>)
                </button>
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

        <!-- Filtro por Categorías -->
        <div class="flex items-center gap-2 overflow-x-auto pb-3 mb-4 scrollbar-none">
            <button onclick="filtrarCategoria('todas')" id="cat-btn-todas"
                class="cat-filter-btn px-4 py-2 rounded-xl text-xs font-black uppercase transition-all bg-amber-500 text-black">
                TODAS
            </button>
            @foreach ($categorias as $cat)
                <button onclick="filtrarCategoria('cat-{{ $cat->id ?? $cat->categoria_id }}')"
                    id="cat-btn-cat-{{ $cat->id ?? $cat->categoria_id }}"
                    class="cat-filter-btn px-4 py-2 rounded-xl text-xs font-bold uppercase transition-all pos-card-theme hover:bg-amber-500/20">
                    {{ strtoupper($cat->nombre) }}
                </button>
            @endforeach
        </div>

        <!-- Grid de Productos Pequeños y Adaptables -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
            @foreach ($productos as $p)
                <?php
                    $prodId = $p->id ?? $p->producto_id;
                    $vars = $p->variantes ?? collect([]);
                    $tieneVariantes = count($vars) > 1 || (count($vars) === 1 && !empty($vars[0]->nombre_variante));
                ?>
                <div class="prod-card pos-card-theme p-3 rounded-xl flex flex-col justify-between relative overflow-hidden group border border-white/10"
                    data-categoria="cat-{{ $p->categoria_id }}" data-card-product-id="{{ $prodId }}">

                    <div>
                        <div class="mb-2 overflow-hidden rounded-lg h-24 bg-black/20 flex items-center justify-center relative">
                            <?php
                                $imgPath = null;
                                if (!empty($p->imagen)) {
                                    $imgPath = str_starts_with($p->imagen, 'assets/') ? $p->imagen : 'assets/productos/' . $p->imagen;
                                }
                                $hasImg = !empty($imgPath) && file_exists(public_path($imgPath));
                            ?>
                            @if($hasImg)
                                <img src="{{ asset($imgPath) }}" alt="{{ strtoupper($p->nombre) }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <i class="fa-solid fa-burger text-3xl opacity-30 text-amber-500"></i>
                            @endif
                        </div>

                        <h3 class="font-extrabold text-xs uppercase leading-tight mb-1 truncate" style="color:var(--color-text);">
                            {{ strtoupper($p->nombre) }}
                        </h3>
                        <p class="text-[10px] opacity-70 mb-2 leading-tight line-clamp-1">
                            {{ strtoupper($p->descripcion ?: 'SIN DESCRIPCIÓN') }}
                        </p>
                    </div>

                    <div class="pt-2 border-t border-white/10">
                        @if($tieneVariantes)
                            <!-- Chips de Variantes -->
                            <div class="mb-2">
                                <div class="flex flex-wrap gap-1" id="variant-chips-{{ $prodId }}">
                                    <?php $firstVariant = true; ?>
                                    @foreach($vars as $v)
                                        <?php
                                            $vVarId = $v->id ?? ($v->variante_id ?? $v->varianteID);
                                            $vPrecio = (float) $v->precio;
                                            $vNombreVal = strtoupper($v->nombre_variante);
                                            $vNombreCompleto = strtoupper($p->nombre . ' - ' . $v->nombre_variante);
                                        ?>
                                        <button type="button"
                                            onclick="selectVariant({{ $prodId }}, {{ $vVarId }}, {{ $vPrecio }}, '{{ addslashes($vNombreCompleto) }}')"
                                            class="variant-chip px-2 py-0.5 text-[10px] font-bold rounded-full border transition-all {{ $firstVariant ? 'bg-green-500 border-green-500 text-white' : 'bg-transparent border-white/20 text-gray-300 hover:border-white/40' }}"
                                            data-producto-id="{{ $prodId }}"
                                            data-variante-id="{{ $vVarId }}"
                                            data-precio="{{ $vPrecio }}"
                                            data-nombre-completo="{{ addslashes($vNombreCompleto) }}">
                                            {{ $vNombreVal }}
                                        </button>
                                        <?php $firstVariant = false; ?>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Precio dinámico + Botón AGREGAR -->
                            <div class="flex items-center justify-between">
                                <div class="price-tag text-xs font-black text-amber-500" id="precio-display-{{ $prodId }}">
                                    Bs. {{ number_format($vars[0]->precio, 2) }}
                                </div>
                                <button type="button" onclick="addSelectedVariantToCart({{ $prodId }})"
                                    class="px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-400 text-black text-[11px] font-black uppercase transition-all shadow-sm flex items-center gap-1">
                                    <i class="fa-solid fa-plus text-[9px]"></i>AGREGAR
                                </button>
                            </div>
                        @else
                            <!-- Producto Simple sin Variantes -->
                            <div class="flex items-center justify-between">
                                <div class="price-tag text-xs font-black text-amber-500">
                                    Bs. {{ number_format($p->precio, 2) }}
                                </div>
                                <button type="button"
                                    onclick="addToCart({{ $prodId }}, {{ (float)$p->precio }}, '{{ addslashes(strtoupper($p->nombre)) }}')"
                                    class="px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-400 text-black text-[11px] font-black uppercase transition-all shadow-sm flex items-center gap-1">
                                    <i class="fa-solid fa-plus text-[9px]"></i>AGREGAR
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- ══════════════════════════════ FLOATING CART FAB ════════════════════════════ -->
    <div id="cart-fab" class="hidden-fab">
        <button id="cart-fab-btn" onclick="openCart()">
            <i class="fa-solid fa-cart-shopping"></i>
            <span>VER PEDIDO</span>
            <div id="cart-fab-badge">0</div>
        </button>
    </div>

    <!-- ═════════════════════════════ CART OVERLAY ══════════════════════════ -->
    <div id="cart-overlay" onclick="closeCart()"></div>

    <!-- ═════════════════════════════ TOAST NOTIFICATION ══════════════════════════ -->
    <div id="toast-container"
        class="fixed bottom-24 left-1/2 -translate-x-1/2 z-[2000] flex flex-col items-center gap-2 pointer-events-none w-full max-w-sm px-4">
    </div>

    <!-- ══════════════════════════════ CART PANEL SIDEBAR ═══════════════════════════ -->
    <div id="cart-panel">
        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-white/10">
            <h2 class="text-sm font-black uppercase tracking-wider text-amber-500 flex items-center gap-2">
                <i class="fa-solid fa-receipt text-lg"></i>TICKET DE VENTA
            </h2>
            <button onclick="closeCart()" class="qty-btn text-lg">✕</button>
        </div>

        <!-- Items list -->
        <div id="cart-items-list" class="flex-1 overflow-y-auto px-5 py-2"></div>

        <!-- Empty state -->
        <div id="cart-empty" class="flex-1 flex flex-col items-center justify-center text-center px-6 py-10">
            <i class="fa-solid fa-basket-shopping text-5xl mb-4 text-amber-500/30"></i>
            <p class="text-sm font-bold uppercase text-gray-400">EL CARRITO ESTÁ VACÍO</p>
            <p class="text-xs text-gray-500 mt-1">SELECCIONA PRODUCTOS DEL CATÁLOGO</p>
        </div>

        <!-- Footer Total + Checkout Form -->
        <div class="px-5 pb-5 pt-3 border-t border-white/10 space-y-4">
            <form action="{{ route('admin.pos.venta') }}" method="POST" id="form-pos-venta"
                onsubmit="return validarVenta();">
                @csrf
                <input type="hidden" name="items" id="input-cart-items">
                <input type="hidden" name="monto_total" id="input-cart-total">

                <div class="space-y-3">
                    <!-- Método de Pago -->
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">MÉTODO DE PAGO *</label>
                        <select name="metodo_pago" required
                            class="w-full form-input-pos rounded-xl px-3 py-2 text-xs font-bold uppercase">
                            <option value="efectivo">💵 EFECTIVO</option>
                            <option value="qr">📱 PAGO QR</option>
                        </select>
                    </div>

                    <!-- Display Total -->
                    <div class="flex justify-between items-center py-2 px-3 rounded-xl bg-amber-500/10 border border-amber-500/30">
                        <span class="text-xs font-black uppercase text-gray-300">TOTAL A COBRAR</span>
                        <span class="text-2xl font-black text-amber-500">Bs. <span id="cart-total-display">0.00</span></span>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex gap-2 pt-1">
                        <button type="button" onclick="clearCart()"
                            class="px-3 py-2.5 rounded-xl border border-red-500/40 text-red-400 hover:bg-red-500/20 text-xs font-bold uppercase flex items-center justify-center gap-1">
                            <i class="fa-solid fa-trash-can"></i>VACIAR
                        </button>
                        <button type="submit" id="checkout-btn" disabled
                            class="flex-1 py-2.5 px-4 rounded-xl text-xs font-black uppercase bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 text-white shadow-lg transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-circle-check"></i>COMPLETAR VENTA
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ═════════════════════════════ SCRIPTS ════════════════════════════════ -->
    <script>
        const cart = {};
        const selectedVariants = {};

        // Inicializar variantes seleccionadas por defecto en las tarjetas
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.variant-chip').forEach(chip => {
                const pId = chip.dataset.productoId;
                if (!selectedVariants[pId] && chip.classList.contains('bg-green-500')) {
                    selectedVariants[pId] = {
                        variante_id: chip.dataset.varianteId,
                        precio: parseFloat(chip.dataset.precio),
                        nombreCompleto: chip.dataset.nombreCompleto
                    };
                }
            });
        });

        // ── Selección de chip de variante (No abre nada, solo actualiza la tarjeta)
        function selectVariant(producto_id, variante_id, precio, nombreCompleto) {
            selectedVariants[producto_id] = { variante_id, precio: parseFloat(precio), nombreCompleto };

            // Actualizar botones de chips
            const chips = document.querySelectorAll(`#variant-chips-${producto_id} .variant-chip`);
            chips.forEach(c => {
                if (c.dataset.varianteId == variante_id) {
                    c.className = 'variant-chip px-2.5 py-1 text-[11px] font-bold rounded-full border transition-all bg-green-500 border-green-500 text-white';
                } else {
                    c.className = 'variant-chip px-2.5 py-1 text-[11px] font-bold rounded-full border transition-all bg-transparent border-white/20 text-gray-300 hover:border-white/40';
                }
            });

            // Actualizar display de precio
            const priceEl = document.getElementById(`precio-display-${producto_id}`);
            if (priceEl) {
                priceEl.textContent = `Bs. ${parseFloat(precio).toFixed(2)}`;
            }
        }

        // ── Agregar variante seleccionada al carrito
        function addSelectedVariantToCart(producto_id) {
            const selected = selectedVariants[producto_id];
            if (!selected) {
                showToast('POR FAVOR SELECCIONA UNA VARIANTE', 'error');
                return;
            }

            const key = 'v' + selected.variante_id;
            if (cart[key]) {
                cart[key].qty += 1;
            } else {
                cart[key] = {
                    type: 'variante',
                    producto_id: producto_id,
                    variante_id: selected.variante_id,
                    nombre: selected.nombreCompleto.toUpperCase(),
                    precio: selected.precio,
                    qty: 1
                };
            }

            renderCart();
            showToast('PRODUCTO AGREGADO AL CARRITO', 'success');
        }

        // ── Agregar producto simple al carrito
        function addToCart(producto_id, precio, nombre) {
            const key = 'p' + producto_id;
            if (cart[key]) {
                cart[key].qty += 1;
            } else {
                cart[key] = {
                    type: 'producto',
                    producto_id: producto_id,
                    variante_id: null,
                    nombre: nombre.toUpperCase(),
                    precio: parseFloat(precio),
                    qty: 1
                };
            }

            renderCart();
            showToast('PRODUCTO AGREGADO AL CARRITO', 'success');
        }

        // ── Render del carrito lateral
        function renderCart() {
            const keys = Object.keys(cart);
            const listEl = document.getElementById('cart-items-list');
            const emptyEl = document.getElementById('cart-empty');
            const fab = document.getElementById('cart-fab');
            const badge = document.getElementById('cart-fab-badge');
            const navBadge = document.getElementById('nav-cart-count');
            const totalDisplay = document.getElementById('cart-total-display');
            const btnSubmit = document.getElementById('checkout-btn');

            let total = 0;
            let totalQty = 0;
            let itemsForPos = [];

            if (keys.length === 0) {
                listEl.innerHTML = '';
                listEl.classList.add('hidden');
                emptyEl.classList.remove('hidden');
                fab.classList.add('hidden-fab');
                badge.textContent = '0';
                navBadge.textContent = '0';
                totalDisplay.textContent = '0.00';
                btnSubmit.disabled = true;
                document.getElementById('input-cart-items').value = '';
                document.getElementById('input-cart-total').value = '0';
                return;
            }

            listEl.classList.remove('hidden');
            emptyEl.classList.add('hidden');
            fab.classList.remove('hidden-fab');

            let html = '';
            for (const key of keys) {
                const item = cart[key];
                const lineTotal = item.precio * item.qty;
                total += lineTotal;
                totalQty += item.qty;

                itemsForPos.push({
                    producto_id: item.producto_id,
                    variante_id: item.variante_id || null,
                    nombre_producto: item.nombre,
                    cantidad: item.qty,
                    precio_unitario: item.precio,
                    precio_total: lineTotal
                });

                html += `
                    <div class="cart-item-row">
                        <div class="cart-item-info">
                            <div class="uppercase">${item.nombre}</div>
                            <div style="color:var(--color-text-muted, #9ca3af);font-weight:500;font-size:0.7rem">Bs.${item.precio.toFixed(2)} c/u</div>
                        </div>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <button type="button" class="qty-btn" onclick="changeQty('${key}', -1)"><i class="fa-solid fa-minus text-[10px]"></i></button>
                            <span class="qty-display">${item.qty}</span>
                            <button type="button" class="qty-btn" onclick="changeQty('${key}', 1)"><i class="fa-solid fa-plus text-[10px]"></i></button>
                        </div>
                        <div class="cart-item-price ml-2">Bs.${lineTotal.toFixed(2)}</div>
                    </div>`;
            }

            listEl.innerHTML = html;
            badge.textContent = totalQty;
            navBadge.textContent = totalQty;
            totalDisplay.textContent = total.toFixed(2);
            btnSubmit.disabled = false;

            document.getElementById('input-cart-items').value = JSON.stringify(itemsForPos);
            document.getElementById('input-cart-total').value = total.toFixed(2);
        }

        function changeQty(key, delta) {
            if (!cart[key]) return;
            cart[key].qty += delta;
            if (cart[key].qty <= 0) {
                delete cart[key];
            }
            renderCart();
        }

        function clearCart() {
            for (const k in cart) delete cart[k];
            renderCart();
        }

        function openCart() {
            document.getElementById('cart-panel').classList.add('open');
            document.getElementById('cart-overlay').classList.add('open');
        }

        function closeCart() {
            document.getElementById('cart-panel').classList.remove('open');
            document.getElementById('cart-overlay').classList.remove('open');
        }

        function showToast(msg, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;
            const toast = document.createElement('div');
            const bgClass = type === 'error' ? 'bg-red-600 text-white' : 'bg-green-500 text-black';
            toast.className = `${bgClass} font-black text-xs px-4 py-2.5 rounded-full shadow-2xl uppercase flex items-center gap-2 pointer-events-auto transition-all duration-300 transform translate-y-4 opacity-0`;
            toast.innerHTML = `<i class="fa-solid ${type === 'error' ? 'fa-circle-xmark' : 'fa-circle-check'}"></i> ${msg}`;
            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.remove('translate-y-4', 'opacity-0');
            }, 10);

            setTimeout(() => {
                toast.classList.add('translate-y-4', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 2500);
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
            if (Object.keys(cart).length === 0) {
                alert('DEBES AGREGAR AL MENOS UN PRODUCTO AL CARRITO');
                return false;
            }
            return true;
        }
    </script>
</body>

</html>