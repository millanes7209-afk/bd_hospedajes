<!doctype html>
<html lang="es" class="dark-mode">


<head>
  <meta charset="utf-8">
  <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>CONFIRMAR PEDIDO - RICO POLLO</title>
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

    <h1 class="text-2xl font-extrabold uppercase mb-1" style="color:var(--color-text)">
      <i class="fa-solid fa-receipt mr-2 text-[#FFE66D]"></i>CONFIRMAR PEDIDO
    </h1>
    <p class="text-xs uppercase font-semibold mb-8" style="color:var(--color-text-muted)">REVISA TU SELECCIÓN ANTES DE
      ENVIAR</p>

    <?php if ($error): ?>
    <div class="mb-6 p-4 rounded-xl text-sm font-semibold flex items-center gap-3"
      style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.4);color:#fca5a5">
      <i class="fa-solid fa-circle-exclamation text-red-500"></i><?php  echo $error; ?>
    </div>
    <?php endif; ?>

    <!-- Si el carrito está vacío -->
    <?php if (empty($cartItemsDisplay)): ?>
    <div class="glass-card p-14 text-center">
      <i class="fa-solid fa-cart-shopping text-5xl mb-4 block" style="color:rgba(255,230,109,0.3)"></i>
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
        <h2 class="text-xs font-extrabold uppercase tracking-wider mb-4 pb-2 border-b"
          style="border-color:var(--color-card-border);color:var(--color-text-muted)">
          <i class="fa-solid fa-list-ul mr-1.5 text-[#FFE66D]"></i>PRODUCTOS SELECCIONADOS
        </h2>

        <div id="order-items-list">
          <?php  foreach ($cartItemsDisplay as $lineKey => $line):
    $qty = (int) ($line['qty'] ?? 1);
    $precio = (float) ($line['precio'] ?? 0);
    $nombre = strtoupper($line['nombre'] ?? '');
    $type = $line['type'] ?? 'producto';
    $prodID = (int) ($line['productoID'] ?? 0);
    $varID = (int) ($line['varianteID'] ?? 0);
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
            <div class="text-sm font-extrabold text-[#FFE66D] ml-1 min-w-[72px] text-right"
              id="linetotal_<?php    echo htmlspecialchars($lineKey); ?>">
              Bs.<?php    echo number_format($lineTotal, 2); ?>
            </div>
          </div>
          <?php  endforeach; ?>
        </div>

        <!-- Total -->
        <div class="flex justify-between items-center pt-4 mt-2 border-t" style="border-color:var(--color-card-border)">
          <span class="text-sm font-extrabold uppercase" style="color:var(--color-text-muted)">TOTAL</span>
          <span class="text-2xl font-black text-[#FFE66D]">Bs. <span
              id="grand-total"><?php  echo number_format($displayTotal, 2); ?></span></span>
        </div>
      </div>

      <!-- ── DATOS DEL CLIENTE ── -->
      <div class="glass-card p-5 mb-5 space-y-4">
        <h2 class="text-xs font-extrabold uppercase tracking-wider pb-2 border-b"
          style="border-color:var(--color-card-border);color:var(--color-text-muted)">
          <i class="fa-solid fa-user mr-1.5 text-[#FFE66D]"></i>TUS DATOS
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
              style="color:var(--color-text-muted)">NOMBRE *</label>
            <div class="relative">
              <span class="absolute left-3.5 top-3.5" style="color:var(--color-text-subtle)"><i
                  class="fa-solid fa-user text-xs"></i></span>
              <input name="cliente_nombre" required oninput="this.value=this.value.toUpperCase()"
                class="form-input pl-9" placeholder="EJ: JUAN PÉREZ"
                value="<?php  echo htmlspecialchars($_POST['cliente_nombre'] ?? ''); ?>" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
              style="color:var(--color-text-muted)">TELÉFONO *</label>
            <div class="relative">
              <span class="absolute left-3.5 top-3.5" style="color:var(--color-text-subtle)"><i
                  class="fa-solid fa-phone text-xs"></i></span>
              <input name="cliente_telefono" required oninput="this.value=this.value.toUpperCase()"
                class="form-input pl-9" placeholder="EJ: 70012345"
                value="<?php  echo htmlspecialchars($_POST['cliente_telefono'] ?? ''); ?>" />
            </div>
          </div>
        </div>

        <!-- Domicilio (Siempre visible, ya que todo pedido de la App es Domicilio) -->
        <div id="wrapper_direccion">
          <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
            style="color:var(--color-text-muted)">DIRECCIÓN DE ENTREGA *</label>
          <div class="relative">
            <span class="absolute left-3.5 top-3.5 pt-0.5" style="color:var(--color-text-subtle)"><i
                class="fa-solid fa-location-dot text-xs"></i></span>
            <input id="direccion_entrega" name="direccion_entrega" oninput="this.value=this.value.toUpperCase()"
              required class="form-input pl-9" placeholder="EJ: AV. PRINCIPAL 123, EDIFICIO ABC"
              value="<?php  echo htmlspecialchars($_POST['direccion_entrega'] ?? ''); ?>" />
          </div>

          <!-- Botón GPS -->
          <button type="button" onclick="getLocation()"
            class="mt-2 text-xs font-bold uppercase btn-outline py-2 px-4 flex items-center gap-2">
            <i class="fa-solid fa-location-crosshairs"></i>USAR MI UBICACIÓN ACTUAL 📍
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

        <!-- Información de Método de Pago diferido a la confirmación de stock -->
        <div class="p-3.5 rounded-xl border border-white/10 bg-black/20 text-xs">
          <div class="flex items-center gap-2 font-bold uppercase mb-1" style="color:var(--color-text)">
            <i class="fa-solid fa-circle-info text-[#FFE66D]"></i>MÉTODO DE PAGO
          </div>
          <p style="color:var(--color-text-muted)" class="text-[11px] uppercase">
            EL MÉTODO DE PAGO (EFECTIVO O QR) SE ELEGIRÁ UNA VEZ QUE EL RESTAURANTE CONFIRME LA DISPONIBILIDAD DE TU
            PEDIDO.
          </p>
          <input type="hidden" name="metodo_pago" value="ninguno">
        </div>

        <!-- Nota -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
            style="color:var(--color-text-muted)">NOTAS / INSTRUCCIONES (OPCIONAL)</label>
          <textarea name="nota" rows="2" oninput="this.value=this.value.toUpperCase()" class="form-input uppercase"
            placeholder="EJ: SIN CEBOLLA, SALSA EXTRA..."><?php  echo htmlspecialchars($_POST['nota'] ?? ''); ?></textarea>
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
          add('cart_items_final[' + k + '][productoID]', item.productoID || '');
          if (item.varianteID) add('cart_items_final[' + k + '][varianteID]', item.varianteID);
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