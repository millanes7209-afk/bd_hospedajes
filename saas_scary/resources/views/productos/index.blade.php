<?php
// Variables pasadas por el controlador:
// $productos - array de productos
// $variantesMap - array de variantes agrupadas por productoID
?>
<!doctype html>
<html lang="es" class="dark-mode">

<head>
  <meta charset="utf-8">
  <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>PRODUCTOS - RICO POLLO</title>
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

    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-black uppercase flex items-center gap-2 admin-text-main">
          <i class="fa-solid fa-utensils text-[#FFE66D]"></i> GESTIÓN DE PRODUCTOS
        </h2>
        <p class="text-xs admin-text-muted mt-1">Conmutadores de disponibilidad en tiempo real para caja y cocina.</p>
      </div>

      <a href="{{ route('admin.productos.create') }}"
        class="btn-primary text-xs font-black uppercase !py-2.5 !px-5 shadow-lg shadow-yellow-900/20">
        <i class="fa-solid fa-plus-circle mr-1.5"></i>NUEVO PRODUCTO
      </a>
    </div>

    <!-- Product Table Wrapper -->
    <div class="glass-card p-5 border border-white/10 rounded-2xl">
      <?php if (empty($productos)): ?>
      <div class="py-12 text-center text-gray-500">
        <i class="fa-solid fa-burger text-5xl mb-4 block text-[#FFE66D]/30"></i>
        <p class="text-sm font-bold uppercase admin-text-main">No hay productos en el catálogo</p>
        <p class="text-xs admin-text-muted mt-1">Agrega tu primer platillo o bebida para comenzar.</p>
        <div class="mt-4">
          <a href="{{ route('admin.productos.create') }}" class="btn-primary text-xs uppercase"><i
              class="fa-solid fa-plus mr-1"></i>Crear Producto</a>
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
    $pId = $p['productoID'];
    $tipo = $p['tipo'] ?? 'simple';
    $tieneVariantes = !empty($variantesMap[$pId]);
    $disponible = (int) ($p['disponible'] ?? 1);
    $activo = (int) ($p['activo'] ?? 1);
            ?>
            <tr class="align-middle hover:bg-white/[0.04]">
              <td class="w-16 py-3 px-3">
                <?php    if (!empty($p['imagen']) && file_exists(public_path('assets/productos/' . $p['imagen']))): ?>
                <img src="{{ asset('assets/productos/' . $p['imagen']) }}" alt="IMG"
                  class="w-12 h-10 object-cover rounded-lg border" style="border-color:var(--color-card-border)" />
                <?php    else: ?>
                <div
                  class="w-12 h-10 rounded-lg admin-subcard flex items-center justify-center text-[9px] font-bold admin-text-muted uppercase">
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
                <div class="font-black admin-text-main flex items-center gap-2">
                  <?php    echo htmlspecialchars(strtoupper($p['nombre'])); ?>
                  <?php    if (!$activo): ?>
                  <span
                    class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-gray-500/20 admin-text-muted border"
                    style="border-color:var(--color-card-border)">Inactivo</span>
                  <?php    endif; ?>
                </div>
                <div class="text-[10px] admin-text-muted uppercase font-semibold mt-0.5 flex items-center gap-1">
                  <i class="fa-solid fa-tag text-[9px] text-[#FFE66D]"></i>
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
                <div class="font-black text-[#FFE66D]">Bs. <?php      echo number_format($p['precio'] ?? 0, 2); ?></div>
                <?php      if (!empty($p['precio_promo'])): ?>
                <div class="text-[10px] text-green-500 font-bold mt-0.5 flex items-center gap-1">
                  <i class="fa-solid fa-fire text-amber-500"></i>
                  Bs. <?php        echo number_format($p['precio_promo'], 2); ?>
                  <span
                    class="admin-text-muted font-normal uppercase">(<?php        echo htmlspecialchars($p['dia_promo'] ?? ''); ?>)</span>
                </div>
                <?php      endif; ?>
                <?php    endif; ?>
              </td>

              <td class="py-3 px-3 text-right">
                <div class="inline-flex gap-2">
                  <a href="{{ route('admin.productos.edit', $pId) }}"
                    class="btn-outline text-xs !py-1.5 !px-3 hover:!border-[#FFE66D] hover:!text-[#FFE66D]">
                    <i class="fa-solid fa-pen-to-square mr-1"></i>Editar
                  </a>
                  <a href="{{ route('admin.productos.destroy', $pId) }}"
                    onclick="return confirm('¿ELIMINAR ESTE PRODUCTO Y SUS VARIANTES?');"
                    class="btn-outline text-xs border-red-500/30 text-red-400 hover:bg-red-950/20 hover:border-red-500 hover:!text-white !py-1.5 !px-3">
                    <i class="fa-regular fa-trash-can mr-1"></i>Eliminar
                  </a>
                </div>
              </td>
            </tr>

            <!-- Filas de Variantes -->
            <?php    if ($tieneVariantes): ?>
            <?php      foreach ($variantesMap[$pId] as $v): ?>
            <?php
        $vId = $v['varianteID'];
        $vDisp = (int) ($v['disponible'] ?? 1);
        $vAct = (int) ($v['activo'] ?? 1);
                    ?>
            <tr class="bg-black/30 border-l-4 border-l-[#FFE66D]/60 text-xs">
              <td class="w-16 py-2 px-3 text-center text-gray-500">
                <i class="fa-solid fa-level-up-alt fa-rotate-90 text-[#FFE66D]"></i>
              </td>
              <td class="py-2 px-3">
                <span class="font-bold text-gray-200 uppercase tracking-wide">
                  <?php        echo htmlspecialchars($v['nombre_variante']); ?>
                </span>
                <?php        if (!empty($v['cantidad']) && !empty($v['unidad'])): ?>
                <span class="text-[10px] text-gray-400 ml-1">
                  (<?php          echo $v['cantidad']; ?> <?php          echo htmlspecialchars($v['unidad']); ?>)
                </span>
                <?php        endif; ?>
                <?php        if (!$vAct): ?>
                <span class="ml-2 text-[9px] text-gray-500 font-bold uppercase">(Inactiva)</span>
                <?php        endif; ?>
              </td>

              <td class="py-2 px-3">
                <span class="font-bold text-[#FFE66D]">Bs. <?php        echo number_format($v['precio'], 2); ?></span>
                <?php        if (!empty($v['precio_promo'])): ?>
                <span class="text-[10px] text-green-400 font-bold ml-2">
                  <i class="fa-solid fa-fire text-amber-500"></i> Bs.
                  <?php          echo number_format($v['precio_promo'], 2); ?>
                  (<?php          echo htmlspecialchars($v['dia_promo'] ?? ''); ?>)
                </span>
                <?php        endif; ?>
              </td>

              <td class="py-2 px-3 text-center">
                <button type="button"
                  onclick="toggleDisponibleAjax('variante', <?php        echo $pId; ?>, <?php        echo $vId; ?>, this)"
                  class="btn-toggle-disp inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase transition-all border <?php        echo $vDisp ? 'border-green-500/40 bg-green-500/10 text-green-300 hover:bg-green-500/20' : 'border-red-500/40 bg-red-500/10 text-red-300 hover:bg-red-500/20'; ?>">
                  <i
                    class="fa-solid <?php        echo $vDisp ? 'fa-toggle-on text-green-400' : 'fa-toggle-off text-red-400'; ?>"></i>
                  <span><?php        echo $vDisp ? 'DISPONIBLE' : 'NO DISPONIBLE'; ?></span>
                </button>
              </td>

              <td class="py-2 px-3 text-right">
                <!-- Espacio variante -->
              </td>
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

  <script>
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
              ? 'btn-toggle-disp inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black uppercase transition-all border shadow-sm border-green-500/50 bg-green-500/10 text-green-300 hover:bg-green-500/20'
              : 'btn-toggle-disp inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase transition-all border border-green-500/40 bg-green-500/10 text-green-300 hover:bg-green-500/20';
            btn.innerHTML = type === 'producto'
              ? '<i class="fa-solid fa-toggle-on text-green-400 text-base"></i><span>DISPONIBLE</span>'
              : '<i class="fa-solid fa-toggle-on text-green-400"></i><span>DISPONIBLE</span>';
          } else {
            btn.className = type === 'producto'
              ? 'btn-toggle-disp inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black uppercase transition-all border shadow-sm border-red-500/50 bg-red-500/10 text-red-300 hover:bg-red-500/20'
              : 'btn-toggle-disp inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase transition-all border border-red-500/40 bg-red-500/10 text-red-300 hover:bg-red-500/20';
            btn.innerHTML = type === 'producto'
              ? '<i class="fa-solid fa-toggle-off text-red-400 text-base"></i><span>NO DISPONIBLE</span>'
              : '<i class="fa-solid fa-toggle-off text-red-400"></i><span>NO DISPONIBLE</span>';
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

    // Toggle Menú Hamburguesa en móvil
    document.addEventListener('DOMContentLoaded', () => {
      const btn = document.getElementById('mobile-menu-btn');
      const menu = document.getElementById('mobile-menu');
      if (btn && menu) {
        btn.addEventListener('click', () => {
          menu.classList.toggle('hidden');
        });
      }
    });
  </script>
</body>

</html>