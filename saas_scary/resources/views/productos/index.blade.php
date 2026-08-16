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
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#FFD700', /* amarillo */
            accent: '#E23E1A',  /* rojo */
            dark: '#09090c'
          }
        }
      }
    }
  </script>
  <!-- Custom CSS Styles -->
  <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
  <!-- FontAwesome icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body style="background-color:var(--color-bg);color:var(--color-text);" class="min-h-screen">
  <!-- Header / Topbar Navigation -->
  <header class="glass-card mb-6 border-b rounded-none px-4 py-3"
    style="border-color:var(--color-card-border);background:var(--color-bg-alt)">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
      <!-- Brand Logo & Title -->
      <div class="flex items-center gap-3">
        <div class="w-10 h-8">
          <img src="{{ asset('assets/logo.svg') }}" alt="LOGO" class="w-full h-full object-contain">
        </div>
        <span class="text-base font-black text-[#FFE66D] tracking-wider uppercase">RICO POLLO</span>
      </div>

      <!-- Right Side Navigation Links (Desktop) -->
      <nav id="nav-menu" class="hidden md:flex items-center gap-1 text-xs font-bold uppercase tracking-wider">
        <a href="{{ route('admin.pedidos') }}"
          class="px-3 py-2 rounded-lg transition-colors flex items-center gap-2 {{ request()->routeIs('admin.pedidos*') ? 'bg-[#FFE66D]/15 text-[#FFE66D] font-extrabold' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
          <i class="fa-solid fa-clipboard-list text-sm"></i>Pedidos
        </a>

        <a href="{{ route('admin.productos') }}"
          class="px-3 py-2 rounded-lg transition-colors flex items-center gap-2 {{ request()->routeIs('admin.productos*') ? 'bg-[#FFE66D]/15 text-[#FFE66D] font-extrabold' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
          <i class="fa-solid fa-utensils text-sm"></i>Productos
        </a>

        @if(Session::get('is_super_admin') || Session::get('rolID') === 'ADMINISTRADOR')
          <a href="{{ route('admin.usuarios') }}"
            class="px-3 py-2 rounded-lg transition-colors flex items-center gap-2 {{ request()->routeIs('admin.usuarios*') ? 'bg-[#FFE66D]/15 text-[#FFE66D] font-extrabold' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
            <i class="fa-solid fa-users text-sm"></i>Usuarios
          </a>
        @endif

        <a href="{{ route('admin.perfil') }}"
          class="px-3 py-2 rounded-lg transition-colors flex items-center gap-2 {{ request()->routeIs('admin.perfil*') ? 'bg-[#FFE66D]/15 text-[#FFE66D] font-extrabold' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
          <i class="fa-solid fa-user-gear text-sm"></i>Mi Perfil
        </a>

        <a href="{{ route('menu') }}" target="_blank"
          class="px-3 py-2 rounded-lg transition-colors text-gray-300 hover:text-white hover:bg-white/5 flex items-center gap-2">
          <i class="fa-solid fa-store text-sm"></i>Ver Tienda
        </a>

        <div class="h-4 w-px bg-white/10 mx-1"></div>

        <form action="{{ route('logout') }}" method="POST" class="inline">
          @csrf
          <button type="submit"
            class="px-3 py-2 rounded-lg text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-colors flex items-center gap-1.5 font-bold uppercase">
            <i class="fa-solid fa-right-from-bracket text-sm"></i>Salir
          </button>
        </form>
      </nav>

      <!-- Mobile Hamburger Button -->
      <button id="mobile-menu-btn" class="md:hidden text-gray-300 hover:text-white p-2 focus:outline-none text-lg">
        <i class="fa-solid fa-bars"></i>
      </button>
    </div>

    <!-- Mobile Dropdown Navigation Menu -->
    <div id="mobile-menu"
      class="hidden md:hidden mt-3 pt-3 border-t border-white/10 flex flex-col gap-1 text-right text-xs font-bold uppercase tracking-wider">
      <a href="{{ route('admin.pedidos') }}"
        class="px-4 py-2.5 rounded-lg flex items-center justify-end gap-2.5 {{ request()->routeIs('admin.pedidos*') ? 'bg-[#FFE66D]/15 text-[#FFE66D]' : 'text-gray-300 hover:bg-white/5' }}">
        <i class="fa-solid fa-clipboard-list text-sm"></i>Pedidos
      </a>

      <a href="{{ route('admin.productos') }}"
        class="px-4 py-2.5 rounded-lg flex items-center justify-end gap-2.5 {{ request()->routeIs('admin.productos*') ? 'bg-[#FFE66D]/15 text-[#FFE66D]' : 'text-gray-300 hover:bg-white/5' }}">
        <i class="fa-solid fa-utensils text-sm"></i>Productos
      </a>

      @if(Session::get('is_super_admin') || Session::get('rolID') === 'ADMINISTRADOR')
        <a href="{{ route('admin.usuarios') }}"
          class="px-4 py-2.5 rounded-lg flex items-center justify-end gap-2.5 {{ request()->routeIs('admin.usuarios*') ? 'bg-[#FFE66D]/15 text-[#FFE66D]' : 'text-gray-300 hover:bg-white/5' }}">
          <i class="fa-solid fa-users text-sm"></i>Usuarios
        </a>
      @endif

      <a href="{{ route('admin.perfil') }}"
        class="px-4 py-2.5 rounded-lg flex items-center justify-end gap-2.5 {{ request()->routeIs('admin.perfil*') ? 'bg-[#FFE66D]/15 text-[#FFE66D]' : 'text-gray-300 hover:bg-white/5' }}">
        <i class="fa-solid fa-user-gear text-sm"></i>Mi Perfil
      </a>

      <a href="{{ route('menu') }}" target="_blank"
        class="px-4 py-2.5 rounded-lg flex items-center justify-end gap-2.5 text-gray-300 hover:bg-white/5">
        <i class="fa-solid fa-store text-sm"></i>Ver Tienda
      </a>

      <form action="{{ route('logout') }}" method="POST" class="mt-1 pt-1 border-t border-white/10">
        @csrf
        <button type="submit"
          class="w-full text-right px-4 py-2.5 rounded-lg text-red-400 hover:bg-red-500/10 flex items-center justify-end gap-2.5 font-bold uppercase">
          <i class="fa-solid fa-right-from-bracket text-sm"></i>Salir
        </button>
      </form>
    </div>
  </header>

  <div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-black uppercase"><i class="fa-solid fa-utensils mr-2 text-[#FFE66D]"></i>INVENTARIO DE
        PRODUCTOS</h2>
      <a href="{{ route('admin.productos.create') }}"
        class="btn-primary text-xs font-black uppercase !py-2 !px-4 bg-green-600 hover:bg-green-500 text-white border-green-500 shadow-lg shadow-green-900/20">
        <i class="fa-solid fa-plus-circle mr-1.5"></i>NUEVO PRODUCTO
      </a>
    </div>

    <!-- Product Table Wrapper -->
    <div class="glass-card p-5">
      <?php if (empty($productos)): ?>
      <div class="py-12 text-center text-gray-500">
        <i class="fa-solid fa-burger text-5xl mb-4 block text-[#FFE66D]/30"></i>
        <p class="text-sm font-semibold uppercase"><?php  echo strtoupper('No hay productos creados'); ?></p>
        <p class="text-xs text-gray-400 mt-1">
          <?php  echo strtoupper('Comienza agregando un nuevo platillo al catálogo.'); ?>
        </p>
        <div class="mt-4">
          <a href="{{ route('admin.productos.create') }}" class="btn-primary text-xs"><i
              class="fa-solid fa-plus mr-1"></i><?php  echo strtoupper('Crear Producto'); ?></a>
        </div>
      </div>
      <?php else: ?>
      <div class="overflow-x-auto">
        <table class="custom-table">
          <thead>
            <tr>
              <th><?php  echo strtoupper('Imagen'); ?></th>
              <th><?php  echo strtoupper('Nombre del platillo'); ?></th>
              <th><?php  echo strtoupper('Precio'); ?></th>
              <th><?php  echo strtoupper('Disponible'); ?></th>
              <th class="text-right"><?php  echo strtoupper('Acciones'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php  foreach ($productos as $p): ?>
            <?php    $tieneVariantes = !empty($variantesMap[$p['productoID']]); ?>
            <tr class="align-middle">
              <td class="w-20">
                <?php    if (!empty($p['imagen']) && file_exists(public_path('assets/productos/' . $p['imagen']))): ?>
                <img src="{{ asset('assets/productos/' . $p['imagen']) }}" alt="IMG"
                  class="w-14 h-10 object-cover rounded-lg border border-white/10" />
                <?php    else: ?>
                <div
                  class="w-14 h-10 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-[10px] uppercase text-gray-400">
                  <?php      echo strtoupper('SIN IMG'); ?>
                </div>
                <?php    endif; ?>
              </td>
              <td>
                <div class="font-bold text-white"><?php    echo htmlspecialchars(strtoupper($p['nombre'])); ?></div>
                <div class="text-[10px] text-gray-400 uppercase font-semibold mt-0.5">
                  <i
                    class="fa-solid fa-list mr-1"></i><?php    echo htmlspecialchars(strtoupper($p['categoria_nombre'] ?: 'SIN CATEGORÍA')); ?>
                </div>
              </td>
              <td>
                <?php    if ($tieneVariantes): ?>
                <div class="text-[10px] text-gray-400 uppercase font-semibold">
                  <?php      echo count($variantesMap[$p['productoID']]); ?> <?php      echo strtoupper('VARIANTES'); ?>
                </div>
                <?php    else: ?>
                <div class="font-bold text-[#FFE66D]">Bs.<?php      echo number_format($p['precio'], 2); ?></div>
                <?php      if (!empty($p['precio_promo'])): ?>
                <div class="text-[10px] text-green-400 font-semibold mt-0.5">
                  <i class="fa-solid fa-tags text-[9px] mr-0.5"></i>
                  Bs.<?php        echo number_format($p['precio_promo'], 2); ?>
                  <span
                    class="text-gray-400 font-normal">(<?php        echo htmlspecialchars($p['dias_promo']); ?>)</span>
                </div>
                <?php      endif; ?>
                <?php    endif; ?>
              </td>
              <td class="text-center">
                <a href="{{ route('admin.productos.toggle', $p['productoID']) }}"
                  class="inline-flex items-center gap-2 btn-outline text-[11px] !py-1 !px-2.5 <?php    echo $p['disponible'] ? 'border-green-500 text-green-300 hover:text-white' : 'border-red-500 text-red-300 hover:text-white'; ?>"
                  title="<?php    echo $p['disponible'] ? 'Marcar como no disponible' : 'Marcar como disponible'; ?>">
                  <i class="fa-solid <?php    echo $p['disponible'] ? 'fa-toggle-on' : 'fa-toggle-off'; ?>"></i>
                  <?php    echo $p['disponible'] ? strtoupper('DISPONIBLE') : strtoupper('NO DISPONIBLE'); ?>
                </a>
              </td>
              <td class="text-right">
                <div class="inline-flex gap-2">
                  <a href="{{ route('admin.productos.edit', $p['productoID']) }}"
                    class="btn-outline text-[11px] !py-1 !px-2.5 hover:!border-[#FFE66D] hover:!text-[#FFE66D]">
                    <i class="fa-solid fa-pen-to-square mr-1"></i><?php    echo strtoupper('Editar'); ?>
                  </a>
                  <a href="{{ route('admin.productos.destroy', $p['productoID']) }}"
                    onclick="return confirm('¿CONFIRMAR BORRADO DE ESTE PRODUCTO?');"
                    class="btn-outline text-[11px] border-red-500/30 text-red-400 hover:bg-red-950/20 hover:border-red-500 hover:!text-white !py-1 !px-2.5">
                    <i class="fa-regular fa-trash-can mr-1"></i><?php    echo strtoupper('Eliminar'); ?>
                  </a>
                </div>
              </td>
            </tr>
            <?php    if ($tieneVariantes): ?>
            <?php      foreach ($variantesMap[$p['productoID']] as $v): ?>
            <tr class="bg-black/40 border-l-[3px] border-l-gray-600">
              <td class="w-20 border-r border-white/5">
                <!-- Efecto de tabulación plana -->
              </td>
              <td colspan="4" class="py-2 pl-4">
                <div class="flex items-center justify-between pr-2">
                  <div class="flex items-center gap-3">
                    <div class="text-gray-500"><i class="fa-solid fa-arrow-turn-up fa-rotate-90"></i></div>

                    <?php        if (!empty($v['imagen']) && file_exists(public_path('assets/productos/' . $v['imagen']))): ?>
                    <img src="{{ asset('assets/productos/' . $v['imagen']) }}" alt="IMG"
                      class="w-10 h-7 object-cover rounded-lg border border-white/10" />
                    <?php        endif; ?>

                    <span class="text-[11px] text-gray-300 uppercase font-bold tracking-wide">
                      <?php        echo htmlspecialchars(strtoupper($v['nombre_variante'])); ?>
                    </span>

                    <span
                      class="text-[11px] text-[#FFE66D] font-bold ml-2">Bs.<?php        echo number_format($v['precio'], 2); ?></span>
                  </div>

                  <a href="{{ route('admin.productos.variantes.toggle', ['producto_id' => $p['productoID'], 'variante_id' => $v['varianteID']]) }}"
                    class="inline-flex items-center gap-2 btn-outline text-[10px] !py-0.5 !px-2 <?php        echo $v['activo'] ? 'border-green-500 text-green-300 hover:text-white' : 'border-red-500 text-red-300 hover:text-white'; ?>"
                    title="<?php        echo $v['activo'] ? 'Desactivar esta variante' : 'Activar esta variante'; ?>">
                    <i class="fa-solid <?php        echo $v['activo'] ? 'fa-toggle-on' : 'fa-toggle-off'; ?>"></i>
                    <?php        echo $v['activo'] ? strtoupper('ACTIVA') : strtoupper('INACTIVA'); ?>
                  </a>
                </div>
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
    document.getElementById('mobile-menu-btn')?.addEventListener('click', function () {
      const menu = document.getElementById('mobile-menu');
      menu?.classList.toggle('hidden');
    });
  </script>
</body>

</html>