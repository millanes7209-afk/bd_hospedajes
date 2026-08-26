<!doctype html>
<html lang="es" class="dark-mode">


<head>
  <meta charset="utf-8">
  <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>CONFIRMAR PEDIDO - MONAKA</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config = { theme: { extend: { colors: { primary: '#FFE66D', accent: '#E23E1A', dark: '#09090c' } } } }</script>
  <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <style>
    .order-item-row {
      padding: 11px 0;
      border-bottom: 1px solid var(--color-card-border);
      display: flex;
      align-items: flex-start;
      gap: 12px;
    }

    .qty-btn {
      width: 28px;
      height: 28px;
      border-radius: 8px;
      border: 1px solid var(--color-card-border);
      background: var(--color-bg-alt);
      color: var(--color-text);
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
      width: 30px;
      text-align: center;
      font-weight: 900;
      font-size: 0.85rem;
      color: var(--color-text);
    }

    #gps-map {
      height: 250px;
      border-radius: 8px;
      margin-top: 12px;
    }

    html.light-mode .order-price-accent,
    html.light-mode .cart-accent-icon {
      color: #E23E1A;
    }

    html.dark-mode .order-price-accent,
    html.dark-mode .cart-accent-icon {
      color: #FFE66D;
    }
  </style>
</head>

<body class="min-h-screen" style="background-color:var(--color-bg);color:var(--color-text);">

  <button id="modeToggle" class="mode-toggle-btn" style="position:fixed;top:16px;right:16px;z-index:50;"
    title="Cambiar modo">
    <span id="modeIcon">☀️</span>
  </button>

  <div class="max-w-2xl mx-auto px-4 py-10">

    <!-- Back link -->
    <a href="{{ route('menu') }}"
      class="inline-flex items-center gap-2 text-xs font-bold uppercase mb-6 btn-outline px-4 py-2">
      <i class="fa-solid fa-arrow-left"></i>VOLVER AL MENÚ
    </a>

    <h1 class="text-2xl font-extrabold uppercase mb-1 flex items-center gap-2" style="color:var(--color-text)">
      <i class="fa-solid fa-receipt cart-accent-icon"></i>CONFIRMAR PEDIDO
    </h1>
    <p class="text-xs uppercase font-semibold mb-6" style="color:var(--color-text-muted)">REVISA TU SELECCIÓN Y COMPLETA TUS DATOS DE ENTREGA</p>

    <!-- ── INDICADOR DE PASOS ── -->
    <div class="glass-card p-4 mb-6">
      <div class="flex items-center justify-between gap-2 text-center text-[11px] sm:text-xs font-black uppercase">
        <div class="flex-1 flex flex-col items-center gap-1.5">
          <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center text-white bg-[#E23E1A] shadow-md font-extrabold">1</div>
          <span style="color:var(--color-text)">1. Tu Selección</span>
        </div>
        <div class="w-6 sm:w-10 h-0.5 bg-gray-400/30"></div>
        <div class="flex-1 flex flex-col items-center gap-1.5">
          <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center text-white bg-[#E23E1A] shadow-md font-extrabold">2</div>
          <span style="color:var(--color-text)">2. Datos Entrega</span>
        </div>
        <div class="w-6 sm:w-10 h-0.5 bg-gray-400/30"></div>
        <div class="flex-1 flex flex-col items-center gap-1.5">
          <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center border-2 border-green-500 text-green-500 font-extrabold">3</div>
          <span style="color:var(--color-text-muted)">3. Confirmación</span>
        </div>
      </div>
    </div>

    <!-- ── BANNER INFORMATIVO / GUÍA ── -->
    <div class="p-4 rounded-2xl mb-6 flex items-start gap-3 border"
      style="background:rgba(226,62,26,0.06);border-color:rgba(226,62,26,0.25);">
      <i class="fa-solid fa-circle-info text-lg mt-0.5 cart-accent-icon shrink-0"></i>
      <div>
        <h3 class="font-black text-xs sm:text-sm uppercase mb-1" style="color:var(--color-text)">
          ¡Casi listo! Revisa tu pedido y completa tus datos
        </h3>
        <p class="text-xs leading-relaxed" style="color:var(--color-text-muted)">
          Verifica las cantidades abajo, ingresa tu <strong>Nombre</strong>, <strong>Teléfono</strong> y <strong>Dirección de Entrega</strong>. Al hacer clic en <strong>"CONFIRMAR Y ENVIAR PEDIDO"</strong>, registrarás tu pedido para que el restaurante comience a prepararlo.
        </p>
      </div>
    </div>

    <?php if ($error): ?>
    <div class="mb-6 p-4 rounded-xl text-sm font-semibold flex items-center gap-3"
      style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.4);color:#fca5a5">
      <i class="fa-solid fa-circle-exclamation text-red-500"></i><?php  echo $error; ?>
    </div>
    <?php endif; ?>

    <!-- Si el carrito está vacío -->
    <?php if (empty($cartItemsDisplay)): ?>
    <div class="glass-card p-14 text-center">
      <i class="fa-solid fa-cart-shopping text-5xl mb-4 block cart-accent-icon opacity-40"></i>
      <h2 class="text-lg font-bold uppercase mb-3" style="color:var(--color-text)">CARRITO VACÍO</h2>
      <a href="{{ route('menu') }}" class="btn-accent text-sm">
        <i class="fa-solid fa-arrow-left mr-2"></i>IR AL MENÚ
      </a>
    </div>
    <?php else: ?>

    <form action="{{ route('order.confirm') }}" method="POST" id="order-form"
      onsubmit="console.log('🔍 Formulario submit iniciado'); return prepareSubmit()">
      @csrf
      <!-- ── RESUMEN DE PRODUCTOS ── -->
      <div class="glass-card p-5 mb-5">
        <h2 class="text-xs font-extrabold uppercase tracking-wider mb-4 pb-2 border-b flex items-center justify-between"
          style="border-color:var(--color-card-border);color:var(--color-text-muted)">
          <span><i class="fa-solid fa-list-ul mr-1.5 cart-accent-icon"></i>PASO 1: PRODUCTOS SELECCIONADOS</span>
          <a href="{{ route('menu') }}" class="text-[10px] font-bold text-amber-500 hover:underline"><i class="fa-solid fa-plus mr-1"></i>AGREGAR MÁS</a>
        </h2>

        <div id="order-items-list">
          <?php  foreach ($cartItemsDisplay as $lineKey => $line):
    $qty = (int) ($line['qty'] ?? 1);
    $precio = (float) ($line['precio'] ?? 0);
    $nombre = strtoupper($line['nombre'] ?? '');
    $type = $line['type'] ?? 'producto';
    $prodID = (int) ($line['producto_id'] ?? 0);
    $varID = (int) ($line['variante_id'] ?? 0);
    $lineTotal = $precio * $qty;
              ?>
          <div class="order-item-row" id="row_<?php    echo htmlspecialchars($lineKey); ?>">
            <div style="flex:1">
              <div class="font-bold text-sm uppercase" style="color:var(--color-text)">
                <?php    echo htmlspecialchars($nombre); ?>
              </div>
              <div class="text-xs mt-0.5" style="color:var(--color-text-muted)">
                Bs.<?php    echo number_format($precio, 2); ?> c/u</div>
            </div>
            <div class="flex items-center gap-2">
              <button type="button" class="qty-btn"
                onclick="changeOrderQty('<?php    echo htmlspecialchars($lineKey); ?>', -1)">
                <i class="fa-solid fa-minus text-[10px]"></i>
              </button>
              <span class="qty-display"
                id="disp_<?php    echo htmlspecialchars($lineKey); ?>"><?php    echo $qty; ?></span>
              <button type="button" class="qty-btn"
                onclick="changeOrderQty('<?php    echo htmlspecialchars($lineKey); ?>', 1)">
                <i class="fa-solid fa-plus text-[10px]"></i>
              </button>
            </div>
            <div class="text-sm font-extrabold order-price-accent ml-1 min-w-[72px] text-right"
              id="linetotal_<?php    echo htmlspecialchars($lineKey); ?>">
              Bs.<?php    echo number_format($lineTotal, 2); ?>
            </div>
          </div>
          <?php  endforeach; ?>
        </div>

        <!-- Total -->
        <div class="flex justify-between items-center pt-4 mt-2 border-t" style="border-color:var(--color-card-border)">
          <span class="text-sm font-extrabold uppercase" style="color:var(--color-text-muted)">TOTAL GENERAL</span>
          <span class="text-2xl font-black order-price-accent">Bs. <span
              id="grand-total"><?php  echo number_format($displayTotal, 2); ?></span></span>
        </div>
      </div>

      <!-- ── DATOS DEL CLIENTE ── -->
      <div class="glass-card p-5 mb-5 space-y-4">
        <h2 class="text-xs font-extrabold uppercase tracking-wider pb-2 border-b"
          style="border-color:var(--color-card-border);color:var(--color-text-muted)">
          <i class="fa-solid fa-user mr-1.5 cart-accent-icon"></i>PASO 2: TUS DATOS DE ENTREGA
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider mb-1"
              style="color:var(--color-text-muted)">TU NOMBRE *</label>
            <div class="relative">
              <span class="absolute left-3.5 top-3.5" style="color:var(--color-text-subtle)"><i
                  class="fa-solid fa-user text-xs"></i></span>
              <input name="cliente_nombre" required oninput="this.value=this.value.toUpperCase()"
                class="form-input pl-9" placeholder="EJ: JUAN PÉREZ"
                value="<?php  echo htmlspecialchars($_POST['cliente_nombre'] ?? ''); ?>" />
            </div>
            <p class="text-[10px] mt-1" style="color:var(--color-text-subtle)">Para dirigirte a ti al momento de entregar tu pedido.</p>
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider mb-1"
              style="color:var(--color-text-muted)">NÚMERO DE TELÉFONO *</label>
            <div class="relative">
              <span class="absolute left-3.5 top-3.5" style="color:var(--color-text-subtle)"><i
                  class="fa-solid fa-phone text-xs"></i></span>
              <input name="cliente_telefono" required oninput="this.value=this.value.toUpperCase()"
                class="form-input pl-9" placeholder="EJ: 70012345"
                value="<?php  echo htmlspecialchars($_POST['cliente_telefono'] ?? ''); ?>" />
            </div>
            <p class="text-[10px] mt-1" style="color:var(--color-text-subtle)">Te contactaremos a este número para confirmar tu pedido.</p>
          </div>
        </div>

        <!-- Domicilio -->
        <div id="wrapper_direccion">
          <label class="block text-xs font-bold uppercase tracking-wider mb-1"
            style="color:var(--color-text-muted)">DIRECCIÓN DE ENTREGA *</label>
          <div class="relative">
            <span class="absolute left-3.5 top-3.5 pt-0.5" style="color:var(--color-text-subtle)"><i
                class="fa-solid fa-location-dot text-xs"></i></span>
            <input id="direccion_entrega" name="direccion_entrega" oninput="this.value=this.value.toUpperCase()"
              required class="form-input pl-9" placeholder="EJ: AV. PRINCIPAL #123, ENTRE C. BOLÍVAR Y C. SUCRE"
              value="<?php  echo htmlspecialchars($_POST['direccion_entrega'] ?? ''); ?>" />
          </div>
          <p class="text-[10px] mt-1" style="color:var(--color-text-subtle)">Escribe tu calle, número de casa/departamento y referencias de llegada.</p>

          <!-- Botón GPS -->
          <button type="button" onclick="getLocation()"
            class="mt-2.5 text-xs font-bold uppercase btn-outline py-2 px-4 flex items-center gap-2">
            <i class="fa-solid fa-location-crosshairs"></i>USAR MI UBICACIÓN ACTUAL (GPS) 📍
          </button>

          <!-- Contenedor del mapa -->
          <div id="map-container" class="hidden mt-3">
            <div id="gps-map"></div>
            <p class="text-xs mt-2" style="color:var(--color-text-subtle)">
              <i class="fa-solid fa-circle-info mr-1"></i>Arrastra el pin para ajustar tu ubicación exacta
            </p>
          </div>

          <!-- Campos ocultos para coordenadas -->
          <input type="hidden" id="latitud" name="latitud"
            value="<?php  echo htmlspecialchars($_POST['latitud'] ?? ''); ?>" />
          <input type="hidden" id="longitud" name="longitud"
            value="<?php  echo htmlspecialchars($_POST['longitud'] ?? ''); ?>" />
        </div>

        <!-- Información de Método de Pago diferido -->
        <div class="p-3.5 rounded-xl border border-white/10 bg-black/20 text-xs">
          <div class="flex items-center gap-2 font-bold uppercase mb-1" style="color:var(--color-text)">
            <i class="fa-solid fa-circle-info cart-accent-icon"></i>MÉTODO DE PAGO
          </div>
          <p style="color:var(--color-text-muted)" class="text-[11px] uppercase">
            Elegirás pagar en EFECTIVO o por QR una vez que el restaurante confirme la recepción de tu pedido.
          </p>
          <input type="hidden" name="metodo_pago" value="ninguno">
        </div>

        <!-- Nota -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider mb-1"
            style="color:var(--color-text-muted)">NOTAS / INSTRUCCIONES ADICIONALES (OPCIONAL)</label>
          <textarea name="nota" rows="2" oninput="this.value=this.value.toUpperCase()" class="form-input uppercase"
            placeholder="EJ: SIN CEBOLLA, ENTREGAR EN PORTÓN NEGRO..."><?php  echo htmlspecialchars($_POST['nota'] ?? ''); ?></textarea>
        </div>
      </div>

      <!-- ── CAMPOS OCULTOS DEL CARRITO (se llenan en JS antes de submit) ── -->
      <div id="cart-hidden-inputs"></div>

      <!-- Bandera para indicar que es confirmación final -->
      <input type="hidden" name="confirmar_pedido" value="1">

      <!-- ── BOTÓN CONFIRMAR ── -->
      <div class="flex items-center gap-4">
        <a href="menu.php" class="btn-outline text-sm flex items-center gap-2 px-5 py-3">
          <i class="fa-solid fa-arrow-left"></i>EDITAR
        </a>
        <button type="submit"
          class="btn-accent flex-1 py-3 text-sm font-black flex items-center justify-center gap-2 uppercase">
          <i class="fa-solid fa-circle-check text-base"></i>CONFIRMAR Y ENVIAR PEDIDO
        </button>
      </div>

    </form>

    <?php endif; ?>
  </div>

  <script>
    // ── Carrito local para edición en esta página
    const cartData = <?php echo json_encode($cartItemsDisplay, JSON_UNESCAPED_UNICODE); ?>;

    // ── Variables GPS
    let map = null;
    let marker = null;

    // ── Obtener ubicación GPS
    function getLocation() {
      if (!navigator.geolocation) {
        alert('TU NAVEGADOR NO SOPORTA GEOLOCALIZACIÓN');
        return;
      }

      const btn = event.target;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>OBTENIENDO UBICACIÓN...';
      btn.disabled = true;

      navigator.geolocation.getCurrentPosition(
        (position) => {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;

          document.getElementById('latitud').value = lat;
          document.getElementById('longitud').value = lng;

          initMap(lat, lng);

          btn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i>USAR MI UBICACIÓN ACTUAL 📍';
          btn.disabled = false;
        },
        (error) => {
          console.error('Error GPS:', error);
          let errorMsg = 'NO SE PUDO OBTENER TU UBICACIÓN';
          if (error.code === 1) {
            errorMsg = 'PERMISO DE UBICACIÓN DENEGADO';
          } else if (error.code === 2) {
            errorMsg = 'UBICACIÓN NO DISPONIBLE';
          } else if (error.code === 3) {
            errorMsg = 'TIEMPO DE ESPERA AGOTADO';
          }
          alert(errorMsg + '. PUEDES CONTINUAR CON LA DIRECCIÓN ESCRITA.');
          btn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i>USAR MI UBICACIÓN ACTUAL 📍';
          btn.disabled = false;
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
      );
    }

    // ── Inicializar mapa Leaflet
    function initMap(lat, lng) {
      const mapContainer = document.getElementById('map-container');
      mapContainer.classList.remove('hidden');

      if (map) {
        map.remove();
      }

      map = L.map('gps-map').setView([lat, lng], 16);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
      }).addTo(map);

      // Pin arrastrable
      marker = L.marker([lat, lng], { draggable: true }).addTo(map);

      // Actualizar coordenadas al mover el pin
      marker.on('dragend', function (e) {
        const position = marker.getLatLng();
        document.getElementById('latitud').value = position.lat;
        document.getElementById('longitud').value = position.lng;
      });
    }

    // ── Cambiar cantidad en pantalla de revisión
    function changeOrderQty(key, delta) {
      if (!cartData[key]) return;
      cartData[key].qty = (cartData[key].qty || 1) + delta;
      if (cartData[key].qty <= 0) {
        delete cartData[key];
        const row = document.getElementById('row_' + key);
        if (row) row.remove();
      } else {
        const disp = document.getElementById('disp_' + key);
        const lt = document.getElementById('linetotal_' + key);
        if (disp) disp.textContent = cartData[key].qty;
        if (lt) lt.textContent = 'Bs.' + (cartData[key].precio * cartData[key].qty).toFixed(2);
      }
      updateGrandTotal();
    }

    function updateGrandTotal() {
      let total = 0;
      for (const k in cartData) total += (cartData[k].precio * cartData[k].qty);
      const el = document.getElementById('grand-total');
      if (el) el.textContent = total.toFixed(2);
    }

    // ── Prevenir doble envío por doble clic
    let isSubmittingOrder = false;

    // ── Preparar inputs ocultos del carrito antes de submit
    function prepareSubmit() {
      try {
        if (isSubmittingOrder) {
          console.warn('⚠️ Pedido ya en proceso de envío.');
          return false;
        }

        const keys = Object.keys(cartData);

        if (keys.length === 0) {
          alert('NO QUEDAN PRODUCTOS EN TU PEDIDO. VUELVE AL MENÚ.');
          return false;
        }

        const container = document.getElementById('cart-hidden-inputs');
        container.innerHTML = '';

        for (const [k, item] of Object.entries(cartData)) {
          const add = (name, value) => {
            const inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = name; inp.value = value;
            container.appendChild(inp);
          };
          add('cart_items_final[' + k + '][type]', item.type || 'producto');
          add('cart_items_final[' + k + '][producto_id]', item.producto_id || '');
          if (item.variante_id) add('cart_items_final[' + k + '][variante_id]', item.variante_id);
          add('cart_items_final[' + k + '][nombre]', item.nombre || '');
          add('cart_items_final[' + k + '][precio]', item.precio || 0);
          add('cart_items_final[' + k + '][qty]', item.qty || 1);
        }

        // Marcar bandera y deshabilitar botón submit para evitar doble clic
        isSubmittingOrder = true;
        const btnSubmit = document.querySelector('#order-form button[type="submit"]');
        if (btnSubmit) {
          btnSubmit.disabled = true;
          btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-base"></i> ENVIANDO PEDIDO...';
          btnSubmit.style.opacity = '0.65';
          btnSubmit.style.pointerEvents = 'none';
        }

        // Limpiar el carrito de localStorage al confirmar
        localStorage.removeItem('rp_cart');

        return true;
      } catch (error) {
        alert('ERROR AL PROCESAR EL PEDIDO: ' + error.message);
        isSubmittingOrder = false;
        return false;
      }
    }

    // No se necesita JS para cambiar tipo de pedido

    // ── Tema
    const html2 = document.documentElement;
    const modeBtn2 = document.getElementById('modeToggle');
    const modeIcon2 = document.getElementById('modeIcon');
    function applyTheme2(t) {
      html2.className = t === 'light' ? 'light-mode' : 'dark-mode';
      modeIcon2.textContent = t === 'light' ? '🌙' : '☀️';
      localStorage.setItem('rp_theme', t);
    }
    applyTheme2(localStorage.getItem('rp_theme') || 'dark');
    modeBtn2.addEventListener('click', () => applyTheme2(html2.classList.contains('light-mode') ? 'dark' : 'light'));
  </script>
</body>

</html>