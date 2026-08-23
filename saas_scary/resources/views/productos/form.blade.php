<?php
// Variables pasadas por el controlador:
// $producto - producto actual (null si es nuevo)
// $variantes - array de variantes existentes
// $categorias - array de categorías disponibles
// $error - mensaje de error si existe

$isEdit = !empty($producto);
$tipoActual = $producto['tipo'] ?? (!empty($variantes) ? 'variantes' : 'simple');
$diaPromoActual = strtolower($producto['dia_promo'] ?? '');
?>
<!doctype html>
<html lang="es" class="dark-mode">

<head>
  <meta charset="utf-8">
  <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo $isEdit ? 'EDITAR' : 'CREAR'; ?> PRODUCTO - RICO POLLO</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#FFE66D',
            accent: '#E23E1A',
            dark: '#09090c'
          }
        }
      }
    }
  </script>
  <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .type-card.selected {
      border-color: #FFE66D !important;
      background: rgba(255, 230, 109, 0.08) !important;
      box-shadow: 0 0 20px rgba(255, 230, 109, 0.15);
    }
  </style>
</head>

<body style="background-color:var(--color-bg);color:var(--color-text);" class="min-h-screen">

  <!-- Navbar Unificada -->
  @include('layouts.admin_navbar')

  <div class="max-w-3xl w-full mx-auto p-4 pb-16">
    <div class="glass-card p-6 md:p-8 rounded-2xl border border-white/10 shadow-2xl">
      <div class="flex items-center justify-between pb-4 border-b border-white/10 mb-6">
        <h2 class="text-xl font-black tracking-wide uppercase text-white flex items-center gap-2">
          <i class="fa-solid fa-utensils text-[#FFE66D]"></i>
          <?php echo $isEdit ? 'EDITAR PRODUCTO' : 'CREAR NUEVO PRODUCTO'; ?>
        </h2>
        <a href="{{ route('admin.productos') }}" class="text-xs text-gray-400 hover:text-white uppercase font-bold">
          <i class="fa-solid fa-arrow-left mr-1"></i>VOLVER
        </a>
      </div>

      <?php if (!empty($error)): ?>
        <div class="mb-5 p-3.5 bg-red-950/40 border border-red-500/50 rounded-xl text-red-200 text-sm font-semibold flex items-center gap-3">
          <i class="fa-solid fa-circle-exclamation text-red-500"></i>
          <span><?php echo $error; ?></span>
        </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- ════════════════ PASO 1: DATOS BÁSICOS ════════════════ -->
        <div class="space-y-4">
          <h3 class="text-xs font-black uppercase text-[#FFE66D] tracking-wider flex items-center gap-2 border-b border-white/5 pb-2">
            <span class="w-5 h-5 rounded-full bg-[#FFE66D] text-black text-[11px] flex items-center justify-center font-black">1</span>
            DATOS BÁSICOS DEL PLATILLO / BEBIDA
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Categoría -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5">Categoría *</label>
              <select name="categoriaID" required class="form-input focus:bg-gray-900">
                <option value="" disabled <?php echo (!isset($producto) || empty($producto['categoriaID'])) ? 'selected' : ''; ?>>
                  -- SELECCIONE CATEGORÍA --
                </option>
                <?php foreach ($cats as $c): ?>
                  <option value="<?php echo $c['categoriaID']; ?>"
                    <?php echo (isset($producto) && $producto['categoriaID'] == $c['categoriaID']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars(strtoupper($c['nombre'])); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Nombre -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5">Nombre del Producto *</label>
              <input type="text" name="nombre" required value="<?php echo htmlspecialchars($producto['nombre'] ?? ''); ?>"
                placeholder="Ej. NUGGETS DE POLLO" class="form-input uppercase font-bold" />
            </div>
          </div>

          <!-- Descripción -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5">Descripción (Opcional)</label>
            <textarea name="descripcion" rows="2" placeholder="Breve detalle de los ingredientes o preparación..."
              class="form-input text-xs"><?php echo htmlspecialchars($producto['descripcion'] ?? ''); ?></textarea>
          </div>

          <!-- Imagen Principal -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5">Imagen Principal (Opcional)</label>
            <input type="file" name="imagen" accept="image/*" class="form-input text-xs" />
            <?php if (!empty($imagenActual) && file_exists(public_path('assets/productos/' . $imagenActual))): ?>
              <div class="mt-2 flex items-center gap-3">
                <img src="{{ asset('assets/productos/' . $imagenActual) }}" alt="IMG" class="w-16 h-12 object-cover rounded-lg border border-white/10" />
                <span class="text-[10px] uppercase text-gray-400 font-bold">Imagen actual cargada</span>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- ════════════════ PASO 2: TIPO DE PRODUCTO ════════════════ -->
        <div class="pt-4 space-y-4">
          <h3 class="text-xs font-black uppercase text-[#FFE66D] tracking-wider flex items-center gap-2 border-b border-white/5 pb-2">
            <span class="w-5 h-5 rounded-full bg-[#FFE66D] text-black text-[11px] flex items-center justify-center font-black">2</span>
            TIPO DE PRECIO Y PRESENTACIONES
          </h3>

          <input type="hidden" name="tipo" id="input_tipo" value="<?php echo $tipoActual; ?>">

          <!-- Tarjetas de Selección Visual -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div id="card-tipo-simple" onclick="selectTipoProducto('simple')"
              class="type-card cursor-pointer p-5 rounded-2xl border-2 border-white/10 transition-all flex flex-col items-center text-center select-none bg-black/40 hover:border-white/30 <?php echo $tipoActual === 'simple' ? 'selected' : ''; ?>">
              <div class="text-3xl mb-2">🍽️</div>
              <h4 class="font-extrabold uppercase text-sm text-white">Precio Único</h4>
              <p class="text-xs text-gray-400 mt-1">Este plato tiene un solo precio de venta<br><span class="text-[10px] text-gray-500">(ej. Ensalada César, Sopa)</span></p>
            </div>

            <div id="card-tipo-variantes" onclick="selectTipoProducto('variantes')"
              class="type-card cursor-pointer p-5 rounded-2xl border-2 border-white/10 transition-all flex flex-col items-center text-center select-none bg-black/40 hover:border-white/30 <?php echo $tipoActual === 'variantes' ? 'selected' : ''; ?>">
              <div class="text-3xl mb-2">📦</div>
              <h4 class="font-extrabold uppercase text-sm text-white">Varias Presentaciones</h4>
              <p class="text-xs text-gray-400 mt-1">Viene en distintos tamaños o unidades<br><span class="text-[10px] text-gray-500">(ej. Nuggets 3/6/12 und, Gaseosas)</span></p>
            </div>
          </div>

          <!-- ── BLOQUE SI ES PRECIO ÚNICO ── -->
          <div id="section-simple" class="space-y-4 pt-3 border-t border-white/5 <?php echo $tipoActual === 'variantes' ? 'hidden' : ''; ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5">Precio Normal (Bs.) *</label>
                <div class="relative">
                  <span class="absolute left-3.5 top-3.5 text-[#FFE66D] font-bold text-sm">Bs.</span>
                  <input type="number" step="0.01" name="precio" id="precio_simple"
                    value="<?php echo htmlspecialchars($producto['precio'] ?? ''); ?>"
                    placeholder="0.00" class="form-input pl-12 font-bold text-base text-[#FFE66D]" />
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5">Stock Disponible (Opcional)</label>
                <input type="number" name="stock" value="<?php echo htmlspecialchars($producto['stock'] ?? ''); ?>"
                  placeholder="Ilimitado si está vacío" class="form-input text-sm" />
              </div>
            </div>

            <!-- Descuento por día (opcional) -->
            <div class="bg-black/30 p-4 rounded-xl border border-white/5 space-y-3">
              <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" id="toggle_promo_simple" onchange="togglePromoSimpleView()"
                  class="rounded bg-black/60 border-white/20 text-green-400 focus:ring-0 w-4 h-4"
                  <?php echo (!empty($producto['precio_promo'])) ? 'checked' : ''; ?>>
                <span class="text-xs font-bold uppercase text-green-400 flex items-center gap-1.5">
                  <i class="fa-solid fa-tag"></i> ¿Tener precio de descuento en un día especial?
                </span>
              </label>

              <div id="box_promo_simple" class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2 <?php echo empty($producto['precio_promo']) ? 'hidden' : ''; ?>">
                <div>
                  <label class="block text-[11px] font-bold uppercase text-gray-400 mb-1">Día de Promoción</label>
                  <select name="dia_promo" class="form-input text-xs">
                    <option value="">-- Seleccionar Día --</option>
                    <option value="lunes" <?php echo $diaPromoActual === 'lunes' ? 'selected' : ''; ?>>LUNES</option>
                    <option value="martes" <?php echo $diaPromoActual === 'martes' ? 'selected' : ''; ?>>MARTES</option>
                    <option value="miercoles" <?php echo $diaPromoActual === 'miercoles' ? 'selected' : ''; ?>>MIÉRCOLES</option>
                    <option value="jueves" <?php echo $diaPromoActual === 'jueves' ? 'selected' : ''; ?>>JUEVES</option>
                    <option value="viernes" <?php echo $diaPromoActual === 'viernes' ? 'selected' : ''; ?>>VIERNES</option>
                  </select>
                </div>
                <div>
                  <label class="block text-[11px] font-bold uppercase text-gray-400 mb-1">Precio Promocional (Bs.)</label>
                  <input type="number" step="0.01" name="precio_promo" value="<?php echo htmlspecialchars($producto['precio_promo'] ?? ''); ?>"
                    placeholder="Ej. 25.00" class="form-input text-xs text-green-300 font-bold" />
                </div>
              </div>
            </div>
          </div>

          <!-- ── BLOQUE SI ES VARIANTES ── -->
          <div id="section-variantes" class="space-y-4 pt-3 border-t border-white/5 <?php echo $tipoActual === 'simple' ? 'hidden' : ''; ?>">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold uppercase tracking-wider text-gray-300">PRESENTACIONES CONFIGURADAS</span>
              <button type="button" onclick="addVarianteRow()" class="btn-primary text-xs font-black uppercase !py-1.5 !px-3 shadow-md">
                <i class="fa-solid fa-plus mr-1"></i>+ AGREGAR PRESENTACIÓN
              </button>
            </div>

            <div id="variantes-container" class="space-y-3">
              <?php
              if (!empty($variantes)):
                foreach ($variantes as $idx => $v):
                  $vId = $v['varianteID'];
                  $vNombre = htmlspecialchars($v['nombre_variante']);
                  $vCant = htmlspecialchars($v['cantidad'] ?? '');
                  $vUni = htmlspecialchars($v['unidad'] ?? 'und');
                  $vPrecio = htmlspecialchars($v['precio']);
                  $vStock = htmlspecialchars($v['stock'] ?? '');
                  $vPromoPrice = htmlspecialchars($v['precio_promo'] ?? '');
                  $vDiaPromo = strtolower($v['dia_promo'] ?? '');
                  $vDisp = (int) ($v['disponible'] ?? 1);
                  $vAct = (int) ($v['activo'] ?? 1);
              ?>
                <div class="variante-row bg-black/40 p-4 rounded-xl border border-white/10 space-y-3 relative">
                  <input type="hidden" name="variantes[<?php echo $idx; ?>][varianteID]" value="<?php echo $vId; ?>">
                  <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    <!-- Nombre presentación -->
                    <div class="sm:col-span-4">
                      <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Nombre Presentación *</label>
                      <input type="text" name="variantes[<?php echo $idx; ?>][nombre_variante]" value="<?php echo $vNombre; ?>"
                        placeholder="Ej. 6 Unidades" class="form-input text-xs font-bold" required />
                    </div>
                    <!-- Cantidad num -->
                    <div class="sm:col-span-2">
                      <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Cantidad</label>
                      <input type="number" step="0.01" name="variantes[<?php echo $idx; ?>][cantidad]" value="<?php echo $vCant; ?>"
                        placeholder="Ej. 6" class="form-input text-xs" />
                    </div>
                    <!-- Unidad -->
                    <div class="sm:col-span-2">
                      <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Unidad</label>
                      <select name="variantes[<?php echo $idx; ?>][unidad]" class="form-input text-xs">
                        <option value="und" <?php echo $vUni === 'und' ? 'selected' : ''; ?>>und</option>
                        <option value="ml" <?php echo $vUni === 'ml' ? 'selected' : ''; ?>>ml</option>
                        <option value="lt" <?php echo $vUni === 'lt' ? 'selected' : ''; ?>>lt</option>
                        <option value="gr" <?php echo $vUni === 'gr' ? 'selected' : ''; ?>>gr</option>
                        <option value="kg" <?php echo $vUni === 'kg' ? 'selected' : ''; ?>>kg</option>
                        <option value="porcion" <?php echo $vUni === 'porcion' ? 'selected' : ''; ?>>porción</option>
                      </select>
                    </div>
                    <!-- Precio -->
                    <div class="sm:col-span-3">
                      <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Precio (Bs.) *</label>
                      <input type="number" step="0.01" name="variantes[<?php echo $idx; ?>][precio]" value="<?php echo $vPrecio; ?>"
                        placeholder="0.00" class="form-input text-xs font-bold text-[#FFE66D]" required />
                    </div>
                    <!-- Eliminar -->
                    <div class="sm:col-span-1 flex justify-end">
                      <button type="button" onclick="removeVarianteRow(this)" class="text-red-400 hover:text-red-300 text-sm p-2">
                        <i class="fa-solid fa-trash-can"></i>
                      </button>
                    </div>
                  </div>

                  <!-- Fila secundaria: Stock + Promo por día + Disponibilidad -->
                  <div class="pt-2 border-t border-white/5 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-3 flex-wrap">
                      <span class="text-[10px] font-bold uppercase text-gray-500">Stock:</span>
                      <input type="number" name="variantes[<?php echo $idx; ?>][stock]" value="<?php echo $vStock; ?>" placeholder="Ilimitado" class="form-input !py-1 !px-2 w-24 text-xs" />

                      <!-- Promo variante -->
                      <button type="button" onclick="toggleVarPromoBox(this)" class="text-[10px] font-bold uppercase text-green-400 hover:underline flex items-center gap-1">
                        <i class="fa-solid fa-tag"></i> Descuento por día
                      </button>
                    </div>

                    <div class="flex items-center gap-4">
                      <!-- Toggle Disponible -->
                      <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                        <input type="checkbox" name="variantes[<?php echo $idx; ?>][disponible]" value="1" <?php echo $vDisp ? 'checked' : ''; ?>
                          class="rounded bg-black/60 border-white/20 text-green-500 focus:ring-0 w-3.5 h-3.5">
                        <span class="text-[10px] font-bold uppercase text-gray-300">Disponible ahora</span>
                      </label>

                      <!-- Toggle Activo -->
                      <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                        <input type="checkbox" name="variantes[<?php echo $idx; ?>][activo]" value="1" <?php echo $vAct ? 'checked' : ''; ?>
                          class="rounded bg-black/60 border-white/20 text-blue-400 focus:ring-0 w-3.5 h-3.5">
                        <span class="text-[10px] font-bold uppercase text-gray-400">Activo</span>
                      </label>
                    </div>
                  </div>

                  <!-- Bloque Oculto de Promo Variante -->
                  <div class="var-promo-box pt-2 grid grid-cols-1 sm:grid-cols-2 gap-3 bg-black/20 p-3 rounded-lg border border-white/5 <?php echo empty($vPromoPrice) ? 'hidden' : ''; ?>">
                    <div>
                      <label class="block text-[9px] font-bold uppercase text-gray-400 mb-1">Día Promoción</label>
                      <select name="variantes[<?php echo $idx; ?>][dia_promo]" class="form-input !py-1 text-xs">
                        <option value="">-- Sin Día --</option>
                        <option value="lunes" <?php echo $vDiaPromo === 'lunes' ? 'selected' : ''; ?>>LUNES</option>
                        <option value="martes" <?php echo $vDiaPromo === 'martes' ? 'selected' : ''; ?>>MARTES</option>
                        <option value="miercoles" <?php echo $vDiaPromo === 'miercoles' ? 'selected' : ''; ?>>MIÉRCOLES</option>
                        <option value="jueves" <?php echo $vDiaPromo === 'jueves' ? 'selected' : ''; ?>>JUEVES</option>
                        <option value="viernes" <?php echo $vDiaPromo === 'viernes' ? 'selected' : ''; ?>>VIERNES</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-[9px] font-bold uppercase text-gray-400 mb-1">Precio Promocional (Bs.)</label>
                      <input type="number" step="0.01" name="variantes[<?php echo $idx; ?>][precio_promo]" value="<?php echo $vPromoPrice; ?>"
                        placeholder="Ej. 28.00" class="form-input !py-1 text-xs text-green-300 font-bold" />
                    </div>
                  </div>
                </div>
              <?php endforeach; endif; ?>
            </div>
          </div>
        </div>

        <!-- ════════════════ PASO 3: SWITCHES DE ESTADO GENERAL ════════════════ -->
        <div class="pt-4 border-t border-white/10 space-y-4">
          <h3 class="text-xs font-black uppercase text-[#FFE66D] tracking-wider">ESTADO DEL PRODUCTO</h3>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Switch DISPONIBLE -->
            <div class="glass-card p-4 rounded-xl border border-white/10 flex items-center justify-between">
              <div>
                <span class="block text-xs font-black uppercase text-white">✅ DISPONIBLE AHORA</span>
                <span class="text-[10px] text-gray-400">Control operativo diario (Cajero/Cocina)</span>
              </div>
              <label class="inline-flex items-center cursor-pointer select-none">
                <input type="checkbox" name="disponible" value="1" class="sr-only peer"
                  <?php echo (!isset($producto) || ($producto['disponible'] ?? 1) == 1) ? 'checked' : ''; ?>>
                <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500 relative"></div>
              </label>
            </div>

            <!-- Switch ACTIVO -->
            <div class="glass-card p-4 rounded-xl border border-white/10 flex items-center justify-between">
              <div>
                <span class="block text-xs font-black uppercase text-white">📁 ACTIVO EN CATÁLOGO</span>
                <span class="text-[10px] text-gray-400">Control administrativo (Visibilidad general)</span>
              </div>
              <label class="inline-flex items-center cursor-pointer select-none">
                <input type="checkbox" name="activo" value="1" class="sr-only peer"
                  <?php echo (!isset($producto) || ($producto['activo'] ?? 1) == 1) ? 'checked' : ''; ?>>
                <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500 relative"></div>
              </label>
            </div>
          </div>
        </div>

        <!-- Acciones principales -->
        <div class="pt-6 border-t border-white/10 flex items-center justify-between">
          <a href="{{ route('admin.productos') }}" class="btn-outline text-xs font-bold uppercase !py-2.5 !px-5">
            <i class="fa-solid fa-xmark mr-1.5"></i>CANCELAR
          </a>
          <button type="submit" class="btn-primary text-xs font-black uppercase !py-2.5 !px-6 shadow-xl">
            <i class="fa-solid fa-floppy-disk mr-1.5"></i><?php echo $isEdit ? 'GUARDAR CAMBIOS' : 'CREAR PRODUCTO'; ?>
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    let varianteIdxCounter = <?php echo !empty($variantes) ? count($variantes) : 0; ?>;

    function selectTipoProducto(tipo) {
      document.getElementById('input_tipo').value = tipo;
      
      const cardSimple = document.getElementById('card-tipo-simple');
      const cardVariantes = document.getElementById('card-tipo-variantes');
      const sectionSimple = document.getElementById('section-simple');
      const sectionVariantes = document.getElementById('section-variantes');
      const inputPrecioSimple = document.getElementById('precio_simple');

      if (tipo === 'simple') {
        cardSimple.classList.add('selected');
        cardVariantes.classList.remove('selected');
        sectionSimple.classList.remove('hidden');
        sectionVariantes.classList.add('hidden');
        if (inputPrecioSimple) inputPrecioSimple.setAttribute('required', 'required');
      } else {
        cardVariantes.classList.add('selected');
        cardSimple.classList.remove('selected');
        sectionVariantes.classList.remove('hidden');
        sectionSimple.classList.add('hidden');
        if (inputPrecioSimple) inputPrecioSimple.removeAttribute('required');

        // Si no hay filas de variantes, agregar una automáticamente
        if (document.querySelectorAll('.variante-row').length === 0) {
          addVarianteRow();
        }
      }
    }

    function togglePromoSimpleView() {
      const checked = document.getElementById('toggle_promo_simple').checked;
      const box = document.getElementById('box_promo_simple');
      if (checked) {
        box.classList.remove('hidden');
      } else {
        box.classList.add('hidden');
      }
    }

    function toggleVarPromoBox(btn) {
      const row = btn.closest('.variante-row');
      const box = row.querySelector('.var-promo-box');
      if (box) {
        box.classList.toggle('hidden');
      }
    }

    function addVarianteRow() {
      const container = document.getElementById('variantes-container');
      const idx = varianteIdxCounter++;

      const html = `
        <div class="variante-row bg-black/40 p-4 rounded-xl border border-white/10 space-y-3 relative">
          <input type="hidden" name="variantes[${idx}][varianteID]" value="">
          <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <div class="sm:col-span-4">
              <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Nombre Presentación *</label>
              <input type="text" name="variantes[${idx}][nombre_variante]" placeholder="Ej. 6 Unidades" class="form-input text-xs font-bold" required />
            </div>
            <div class="sm:col-span-2">
              <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Cantidad</label>
              <input type="number" step="0.01" name="variantes[${idx}][cantidad]" placeholder="Ej. 6" class="form-input text-xs" />
            </div>
            <div class="sm:col-span-2">
              <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Unidad</label>
              <select name="variantes[${idx}][unidad]" class="form-input text-xs">
                <option value="und">und</option>
                <option value="ml">ml</option>
                <option value="lt">lt</option>
                <option value="gr">gr</option>
                <option value="kg">kg</option>
                <option value="porcion">porción</option>
              </select>
            </div>
            <div class="sm:col-span-3">
              <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Precio (Bs.) *</label>
              <input type="number" step="0.01" name="variantes[${idx}][precio]" placeholder="0.00" class="form-input text-xs font-bold text-[#FFE66D]" required />
            </div>
            <div class="sm:col-span-1 flex justify-end">
              <button type="button" onclick="removeVarianteRow(this)" class="text-red-400 hover:text-red-300 text-sm p-2">
                <i class="fa-solid fa-trash-can"></i>
              </button>
            </div>
          </div>

          <div class="pt-2 border-t border-white/5 flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-3 flex-wrap">
              <span class="text-[10px] font-bold uppercase text-gray-500">Stock:</span>
              <input type="number" name="variantes[${idx}][stock]" placeholder="Ilimitado" class="form-input !py-1 !px-2 w-24 text-xs" />
              <button type="button" onclick="toggleVarPromoBox(this)" class="text-[10px] font-bold uppercase text-green-400 hover:underline flex items-center gap-1">
                <i class="fa-solid fa-tag"></i> Descuento por día
              </button>
            </div>
            <div class="flex items-center gap-4">
              <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                <input type="checkbox" name="variantes[${idx}][disponible]" value="1" checked
                  class="rounded bg-black/60 border-white/20 text-green-500 focus:ring-0 w-3.5 h-3.5">
                <span class="text-[10px] font-bold uppercase text-gray-300">Disponible ahora</span>
              </label>
              <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                <input type="checkbox" name="variantes[${idx}][activo]" value="1" checked
                  class="rounded bg-black/60 border-white/20 text-blue-400 focus:ring-0 w-3.5 h-3.5">
                <span class="text-[10px] font-bold uppercase text-gray-400">Activo</span>
              </label>
            </div>
          </div>

          <div class="var-promo-box hidden pt-2 grid grid-cols-1 sm:grid-cols-2 gap-3 bg-black/20 p-3 rounded-lg border border-white/5">
            <div>
              <label class="block text-[9px] font-bold uppercase text-gray-400 mb-1">Día Promoción</label>
              <select name="variantes[${idx}][dia_promo]" class="form-input !py-1 text-xs">
                <option value="">-- Sin Día --</option>
                <option value="lunes">LUNES</option>
                <option value="martes">MARTES</option>
                <option value="miercoles">MIÉRCOLES</option>
                <option value="jueves">JUEVES</option>
                <option value="viernes">VIERNES</option>
              </select>
            </div>
            <div>
              <label class="block text-[9px] font-bold uppercase text-gray-400 mb-1">Precio Promocional (Bs.)</label>
              <input type="number" step="0.01" name="variantes[${idx}][precio_promo]" placeholder="Ej. 28.00" class="form-input !py-1 text-xs text-green-300 font-bold" />
            </div>
          </div>
        </div>
      `;

      container.insertAdjacentHTML('beforeend', html);
    }

    function removeVarianteRow(btn) {
      const row = btn.closest('.variante-row');
      row.remove();
    }
  </script>
</body>

</html>
