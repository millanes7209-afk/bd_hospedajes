<?php
// Variables pasadas por el controlador:
// $productos - array de productos
// $variantesMap - array de variantes agrupadas por producto_id
// $categorias - array de categorias
$categorias = $categorias ?? [];
?>
<!doctype html>
<html lang="es" class="dark-mode">

<head>
  <meta charset="utf-8">
  <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>PRODUCTOS Y CATEGORÍAS - MONAKA</title>
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
    .prod-card-theme {
      background-color: var(--color-bg-alt, rgba(255, 255, 255, 0.05));
      color: var(--color-text, #ffffff);
      border: 1px solid var(--color-border, rgba(255, 255, 255, 0.12));
    }

    .form-input-prod {
      background-color: var(--color-bg, rgba(0, 0, 0, 0.2));
      color: var(--color-text, #ffffff);
      border: 1px solid var(--color-border, rgba(255, 255, 255, 0.15));
    }

    .form-input-prod:focus {
      border-color: #FFE66D;
      outline: none;
    }
  </style>
</head>

<body style="background-color:var(--color-bg);color:var(--color-text);" class="min-h-screen">

  <!-- Navbar Unificada -->
  @include('layouts.admin_navbar')

  <div class="max-w-7xl mx-auto px-4 py-6">

    @if (session('success'))
      <div
        class="mb-6 p-4 rounded-xl bg-green-500/20 border border-green-500/50 text-green-300 font-bold text-sm flex items-center gap-2">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
      </div>
    @endif

    <!-- Encabezado con Pestañas (PRODUCTOS / CATEGORÍAS) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 border-b pb-4"
      style="border-color:var(--color-card-border)">
      <div>
        <h2 class="text-xl md:text-2xl font-black uppercase flex items-center gap-2">
          <i class="fa-solid fa-utensils text-amber-500"></i> CATÁLOGOS Y MENÚ
        </h2>
        <p class="text-xs admin-text-muted mt-1">Administra los productos, variaciones y categorías del menú.</p>
      </div>

      <!-- Selector de Pestañas -->
      <div class="flex items-center gap-2">
        <button type="button" onclick="switchTab('productos')" id="tab-btn-productos"
          class="px-4 py-2 rounded-xl text-xs font-black uppercase transition-all bg-amber-500 text-black shadow">
          <i class="fa-solid fa-burger mr-1.5"></i>PRODUCTOS
        </button>
        <button type="button" onclick="switchTab('categorias')" id="tab-btn-categorias"
          class="px-4 py-2 rounded-xl text-xs font-bold uppercase transition-all prod-card-theme hover:bg-amber-500/20">
          <i class="fa-solid fa-tags mr-1.5"></i>CATEGORÍAS ({{ count($categorias) }})
        </button>
      </div>
    </div>

    <!-- PESTAÑA 1: GESTIÓN DE PRODUCTOS -->
    <div id="tab-content-productos" class="space-y-4">
      <div class="flex items-center justify-between mb-4">
        <span class="text-xs font-bold uppercase text-gray-400">LISTA DE PRODUCTOS Y PRESENTACIONES</span>
        <a href="{{ route('admin.productos.create') }}"
          class="btn-primary text-xs font-black uppercase !py-2.5 !px-5 shadow-lg">
          <i class="fa-solid fa-plus-circle mr-1.5"></i>NUEVO PRODUCTO
        </a>
      </div>

      <div class="glass-card p-5 border border-white/10 rounded-2xl">
        <?php if (empty($productos)): ?>
        <div class="py-12 text-center text-gray-500">
          <i class="fa-solid fa-burger text-5xl mb-4 block text-amber-500/40"></i>
          <p class="text-sm font-bold uppercase">No hay productos en el catálogo</p>
          <p class="text-xs text-gray-400 mt-1">Agrega tu primer platillo o bebida para comenzar.</p>
          <div class="mt-4">
            <a href="{{ route('admin.productos.create') }}" class="btn-primary text-xs uppercase">
              <i class="fa-solid fa-plus mr-1"></i>Crear Producto
            </a>
          </div>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
          <table class="custom-table w-full text-left">
            <thead>
              <tr class="text-xs uppercase admin-text-muted border-b" style="border-color:var(--color-card-border)">
                <th class="py-3 px-3">Imagen</th>
                <th class="py-3 px-3 text-center">Disponibilidad</th>
                <th class="py-3 px-3">Platillo / Categoría</th>
                <th class="py-3 px-3">Tipo / Precio</th>
                <th class="py-3 px-3 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y text-sm" style="border-color:var(--color-card-border)">
              <?php  foreach ($productos as $p): ?>
              <?php
    $pId = $p['id'];
    $tipo = $p['tipo'] ?? 'simple';
    $tieneVariantes = !empty($variantesMap[$pId]);
    $disponible = (int) ($p['disponible'] ?? 1);
    $activo = (int) ($p['activo'] ?? 1);
              ?>
              <tr class="align-middle hover:bg-white/[0.04]">
                <td class="w-16 py-3 px-3">
                  <?php    if (!empty($p['imagen']) && file_exists(public_path($p['imagen']))): ?>
                  <img src="{{ asset($p['imagen']) }}" alt="IMG" class="w-12 h-10 object-cover rounded-lg border"
                    style="border-color:var(--color-card-border)" />
                  <?php    else: ?>
                  <div
                    class="w-12 h-10 rounded-lg prod-card-theme flex items-center justify-center text-[9px] font-bold text-gray-400 uppercase">
                    SIN IMG
                  </div>
                  <?php    endif; ?>
                </td>

                <td class="py-3 px-3 text-center">
                  <button type="button" onclick="toggleDisponibleAjax('producto', <?php    echo $pId; ?>, null, this)"
                    class="btn-toggle-disp inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black uppercase transition-all border shadow-sm <?php    echo $disponible ? 'border-green-500/50 bg-green-500/10 text-green-500 hover:bg-green-500/20' : 'border-red-500/50 bg-red-500/10 text-red-500 hover:bg-red-500/20'; ?>">
                    <i
                      class="fa-solid <?php    echo $disponible ? 'fa-toggle-on text-green-500' : 'fa-toggle-off text-red-500'; ?> text-base"></i>
                    <span><?php    echo $disponible ? 'DISPONIBLE' : 'NO DISPONIBLE'; ?></span>
                  </button>
                </td>

                <td class="py-3 px-3">
                  <div class="font-black flex items-center gap-2">
                    <?php    echo htmlspecialchars(strtoupper($p['nombre'])); ?>
                    <?php    if (!$activo): ?>
                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-gray-500/20 text-gray-400 border"
                      style="border-color:var(--color-card-border)">Inactivo</span>
                    <?php    endif; ?>
                  </div>
                  <div class="text-[10px] text-gray-400 uppercase font-semibold mt-0.5 flex items-center gap-1">
                    <i class="fa-solid fa-tag text-[9px] text-amber-500"></i>
                    <?php    echo htmlspecialchars(strtoupper($p['categoria_nombre'] ?: 'SIN CATEGORÍA')); ?>
                  </div>
                </td>

                <td class="py-3 px-3">
                  <?php    if ($tipo === 'variantes' || $tieneVariantes): ?>
                  <span
                    class="inline-block px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-purple-500/20 text-purple-400 border border-purple-500/30">
                    📦 <?php      echo count($variantesMap[$pId] ?? []); ?> PRESENTACIONES
                  </span>
                  <?php    else: ?>
                  <div class="font-black text-amber-500">Bs. <?php      echo number_format($p['precio'] ?? 0, 2); ?>
                  </div>
                  <?php    endif; ?>
                </td>

                <td class="py-3 px-3 text-right">
                  <div class="inline-flex gap-1.5">
                    <a href="{{ route('admin.productos.edit', $pId) }}"
                      class="btn-outline text-xs !py-1.5 !px-2.5 hover:!border-amber-500 hover:!text-amber-500"
                      title="Editar producto">
                      <i class="fa-solid fa-pen-to-square"></i><span class="hidden sm:inline ml-1">Editar</span>
                    </a>
                    <a href="{{ route('admin.productos.destroy', $pId) }}"
                      onclick="return confirm('¿ELIMINAR ESTE PRODUCTO Y SUS VARIANTES?');"
                      class="btn-outline text-xs border-red-500/30 text-red-400 hover:bg-red-950/20 hover:border-red-500 hover:!text-white !py-1.5 !px-2.5"
                      title="Eliminar producto">
                      <i class="fa-regular fa-trash-can"></i><span class="hidden sm:inline ml-1">Eliminar</span>
                    </a>
                  </div>
                </td>
              </tr>

              <!-- Filas de Variantes -->
              <?php    if ($tieneVariantes): ?>
              <?php      foreach ($variantesMap[$pId] as $v): ?>
              <?php
        $vId = $v['id'];
        $vDisp = (int) ($v['disponible'] ?? 1);
              ?>
              <tr class="prod-card-theme border-l-4 border-l-amber-500/60 text-xs">
                <td class="w-16 py-2 px-3 text-center text-gray-400">
                  <i class="fa-solid fa-level-up-alt fa-rotate-90 text-amber-500"></i>
                </td>
                <td class="py-2 px-3 text-center">
                  <button type="button"
                    onclick="toggleDisponibleAjax('variante', <?php        echo $pId; ?>, <?php        echo $vId; ?>, this)"
                    class="btn-toggle-disp inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase transition-all border <?php        echo $vDisp ? 'border-green-500/40 bg-green-500/10 text-green-500 hover:bg-green-500/20' : 'border-red-500/40 bg-red-500/10 text-red-500 hover:bg-red-500/20'; ?>">
                    <i
                      class="fa-solid <?php        echo $vDisp ? 'fa-toggle-on text-green-500' : 'fa-toggle-off text-red-500'; ?>"></i>
                    <span><?php        echo $vDisp ? 'DISPONIBLE' : 'NO DISPONIBLE'; ?></span>
                  </button>
                </td>
                <td class="py-2 px-3 font-bold uppercase">
                  <?php        echo htmlspecialchars($v['nombre_variante']); ?>
                </td>
                <td class="py-2 px-3 font-bold text-amber-500">
                  Bs. <?php        echo number_format($v['precio'], 2); ?>
                </td>
                <td class="py-2 px-3 text-right"></td>
              </tr>
              <?php      endforeach; ?>
              <?php    endif; ?>

              <?php  endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- PESTAÑA 2: GESTIÓN DE CATEGORÍAS EN SÍNDROME DE PESTAÑA -->
    <div id="tab-content-categorias" class="hidden space-y-6">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Formulario Crear Categoría -->
        <div class="lg:col-span-4">
          <div class="prod-card-theme p-5 rounded-2xl space-y-4">
            <h3 class="font-black text-sm uppercase text-amber-500 flex items-center gap-2">
              <i class="fa-solid fa-plus-circle"></i>NUEVA CATEGORÍA
            </h3>

            <form action="{{ route('admin.categorias.store') }}" method="POST" class="space-y-3">
              @csrf
              <div>
                <label class="block text-[11px] font-bold uppercase mb-1 text-gray-400">NOMBRE DE CATEGORÍA *</label>
                <input type="text" name="nombre" required placeholder="EJ. SALTEÑAS, BEBIDAS"
                  oninput="this.value = this.value.toUpperCase();"
                  class="w-full form-input-prod rounded-xl px-3 py-2 text-xs font-bold uppercase">
              </div>
              <button type="submit"
                class="w-full py-2.5 px-4 rounded-xl text-xs font-black uppercase bg-amber-500 hover:bg-amber-400 text-black shadow transition-all">
                CREAR CATEGORÍA
              </button>
            </form>
          </div>
        </div>

        <!-- Lista de Categorías Existentes -->
        <div class="lg:col-span-8">
          <div class="glass-card p-5 border border-white/10 rounded-2xl">
            <h3 class="font-black text-sm uppercase text-amber-500 mb-4 flex items-center gap-2">
              <i class="fa-solid fa-list"></i>CATEGORÍAS REGISTRADAS
            </h3>

            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead>
                  <tr class="uppercase text-gray-400 border-b" style="border-color:var(--color-card-border)">
                    <th class="py-2.5 px-3"># ID</th>
                    <th class="py-2.5 px-3">Nombre Categoría</th>
                    <th class="py-2.5 px-3 text-center">Estado</th>
                    <th class="py-2.5 px-3 text-right">Acciones</th>
                  </tr>
                </thead>
                <tbody class="divide-y" style="border-color:var(--color-card-border)">
                  @foreach($categorias as $cat)
                    <tr class="hover:bg-white/[0.04]">
                      <td class="py-3 px-3 font-mono font-bold text-gray-400">#{{ $cat['id'] }}</td>
                      <td class="py-3 px-3 font-black uppercase text-amber-500">{{ $cat['nombre'] }}</td>
                      <td class="py-3 px-3 text-center">
                        <a href="{{ route('admin.categorias.estado', $cat['id']) }}"
                          class="px-2.5 py-1 rounded-lg font-extrabold uppercase border text-[10px] {{ ($cat['activo'] ?? 1) ? 'border-green-500/50 text-green-400 bg-green-500/10' : 'border-red-500/50 text-red-400 bg-red-500/10' }}">
                          {{ ($cat['activo'] ?? 1) ? 'ACTIVA' : 'INACTIVA' }}
                        </a>
                      </td>
                      <td class="py-3 px-3 text-right">
                        <button type="button" onclick="editarCategoria('{{ $cat['id'] }}', '{{ $cat['nombre'] }}')"
                          class="px-2.5 py-1 rounded-lg border border-amber-500/30 text-amber-500 hover:bg-amber-500/20 font-bold text-[10px] uppercase">
                          <i class="fa-solid fa-pen mr-1"></i>EDITAR
                        </button>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function switchTab(tab) {
      const btnProd = document.getElementById('tab-btn-productos');
      const btnCat = document.getElementById('tab-btn-categorias');
      const contentProd = document.getElementById('tab-content-productos');
      const contentCat = document.getElementById('tab-content-categorias');

      if (tab === 'productos') {
        btnProd.className = 'px-4 py-2 rounded-xl text-xs font-black uppercase transition-all bg-amber-500 text-black shadow';
        btnCat.className = 'px-4 py-2 rounded-xl text-xs font-bold uppercase transition-all prod-card-theme hover:bg-amber-500/20';
        contentProd.classList.remove('hidden');
        contentCat.classList.add('hidden');
      } else {
        btnCat.className = 'px-4 py-2 rounded-xl text-xs font-black uppercase transition-all bg-amber-500 text-black shadow';
        btnProd.className = 'px-4 py-2 rounded-xl text-xs font-bold uppercase transition-all prod-card-theme hover:bg-amber-500/20';
        contentCat.classList.remove('hidden');
        contentProd.classList.add('hidden');
      }

      const url = new URL(window.location);
      url.searchParams.set('tab', tab);
      window.history.replaceState({}, '', url);
    }

    document.addEventListener('DOMContentLoaded', () => {
      const urlParams = new URLSearchParams(window.location.search);
      const activeTab = urlParams.get('tab') || '{{ request("tab", "productos") }}';
      if (activeTab === 'categorias') {
        switchTab('categorias');
      }
    });

    function editarCategoria(id, nombre) {
      const nuevoNombre = prompt('EDITAR NOMBRE DE LA CATEGORÍA:', nombre);
      if (nuevoNombre && nuevoNombre.trim() !== '') {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/categorias/update/${id}`;

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'nombre';
        input.value = nuevoNombre.trim().toUpperCase();
        form.appendChild(input);

        document.body.appendChild(form);
        form.submit();
      }
    }

    async function toggleDisponibleAjax(type, productoId, varianteId, btn) {
      const originalHtml = btn.innerHTML;
      btn.disabled = true;
      btn.style.opacity = '0.6';

      let url = type === 'producto'
        ? `/admin/productos/toggle/${productoId}`
        : `/admin/productos/variantes/toggle/${productoId}/${varianteId}`;

      try {
        const res = await fetch(url, {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        });

        const data = await res.json();
        if (data.success) {
          const isAvailable = !!data.disponible;
          if (isAvailable) {
            btn.className = type === 'producto'
              ? 'btn-toggle-disp inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black uppercase transition-all border shadow-sm border-green-500/50 bg-green-500/10 text-green-500 hover:bg-green-500/20'
              : 'btn-toggle-disp inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase transition-all border border-green-500/40 bg-green-500/10 text-green-500 hover:bg-green-500/20';
            btn.innerHTML = type === 'producto'
              ? '<i class="fa-solid fa-toggle-on text-green-500 text-base"></i><span>DISPONIBLE</span>'
              : '<i class="fa-solid fa-toggle-on text-green-500"></i><span>DISPONIBLE</span>';
          } else {
            btn.className = type === 'producto'
              ? 'btn-toggle-disp inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black uppercase transition-all border shadow-sm border-red-500/50 bg-red-500/10 text-red-500 hover:bg-red-500/20'
              : 'btn-toggle-disp inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase transition-all border border-red-500/40 bg-red-500/10 text-red-500 hover:bg-red-500/20';
            btn.innerHTML = type === 'producto'
              ? '<i class="fa-solid fa-toggle-off text-red-500 text-base"></i><span>NO DISPONIBLE</span>'
              : '<i class="fa-solid fa-toggle-off text-red-500"></i><span>NO DISPONIBLE</span>';
          }
        }
      } catch (err) {
        console.error("Error al conmutar disponibilidad:", err);
        btn.innerHTML = originalHtml;
      } finally {
        btn.disabled = false;
        btn.style.opacity = '1';
      }
    }
  </script>
</body>

</html>