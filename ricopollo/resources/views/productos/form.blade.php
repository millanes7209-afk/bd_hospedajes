<?php
$isEdit = isset($id) && $id > 0;
$catsList = $categorias ?? ($cats ?? []);
$tipoActual = $producto['tipo'] ?? 'simple';
?>
<!DOCTYPE html>
<html lang="es" class="<?php echo session('dark_mode', true) ? 'dark-mode' : 'light-mode'; ?>">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    .type-card {
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
      border: 2px solid var(--border-color, rgba(255, 255, 255, 0.12));
    }
    .type-card:hover {
      border-color: rgba(255, 230, 109, 0.4);
    }
    .type-card.selected {
      border-color: #FFE66D !important;
      background: rgba(255, 230, 109, 0.15) !important;
      box-shadow: 0 0 25px rgba(255, 230, 109, 0.25);
      transform: translateY(-2px);
    }
    html.light-mode .type-card.selected {
      border-color: #E23E1A !important;
      background: rgba(226, 62, 26, 0.1) !important;
      box-shadow: 0 4px 20px rgba(226, 62, 26, 0.25);
    }
    select option {
      background-color: var(--color-bg-card, #121218) !important;
      color: var(--color-text, #ffffff) !important;
    }
    html.light-mode .admin-text-main { color: #111827 !important; }
    html.light-mode .admin-text-muted { color: #4b5563 !important; }
    html.light-mode .text-\[\#FFE66D\], html.light-mode [class*="text-[#FFE66D]"] { color: #d97706 !important; }
    html.light-mode .form-input { background-color: #ffffff !important; color: #111827 !important; border-color: #d1d5db !important; }
    html.light-mode .form-input::placeholder { color: #9ca3af !important; }
    html.light-mode .admin-subcard { background-color: #f9fafb !important; border-color: #e5e7eb !important; }
  </style>
</head>

<body style="background-color:var(--color-bg);color:var(--color-text);" class="min-h-screen">

  @include('layouts.admin_navbar')

  <div class="max-w-3xl w-full mx-auto p-4 pb-16">
    <div class="glass-card p-6 md:p-8 rounded-2xl border border-white/10 shadow-2xl">
      <div class="flex items-center justify-between pb-4 border-b border-white/10 mb-6">
        <h2 class="text-xl font-black tracking-wide uppercase admin-text-main flex items-center gap-2">
          <i class="fa-solid fa-utensils text-[#FFE66D]"></i>
          <?php echo $isEdit ? 'EDITAR PRODUCTO' : 'CREAR NUEVO PRODUCTO'; ?>
        </h2>
        <a href="{{ route('admin.productos') }}" class="text-xs admin-text-muted hover:admin-text-main uppercase font-bold">
          <i class="fa-solid fa-arrow-left mr-1"></i>VOLVER
        </a>
      </div>

      <?php if (!empty($error)): ?>
        <div class="mb-4 p-3 bg-red-500/20 border border-red-500/50 rounded-xl text-red-300 text-xs font-bold flex items-center gap-2">
          <i class="fa-solid fa-triangle-exclamation"></i>
          <span><?php  echo htmlspecialchars($error); ?></span>
        </div>
      <?php endif; ?>

      <form action="<?php echo $isEdit ? route('admin.productos.update', $id) : route('admin.productos.store'); ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <input type="hidden" name="tipo" id="input_tipo" value="<?php echo htmlspecialchars($tipoActual); ?>">

        {{-- Paso 1: Datos Básicos --}}
        @include('productos.partials.datos_basicos')

        {{-- Paso 2: Selector de Tipo --}}
        @include('productos.partials.selector_tipo')

        {{-- Paso 3: Sección Precio Único --}}
        @include('productos.partials.seccion_simple')

        {{-- Paso 4: Sección Presentaciones / Variantes --}}
        @include('productos.partials.seccion_variantes')

        {{-- Botón de Acción --}}
        <div class="pt-6 border-t border-white/10 flex items-center justify-end gap-3">
          <a href="{{ route('admin.productos') }}" class="btn-secondary text-xs font-bold uppercase !py-2.5 !px-5">
            CANCELAR
          </a>
          <button type="submit" class="btn-primary text-xs font-black uppercase !py-2.5 !px-6 shadow-xl">
            <i class="fa-solid fa-floppy-disk mr-1.5"></i><?php echo $isEdit ? 'GUARDAR CAMBIOS' : 'CREAR PRODUCTO'; ?>
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="{{ asset('js/productos-form.js') }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      setVarianteCounter(<?php echo !empty($variantes) ? count($variantes) : 0; ?>);
      selectTipoProducto('<?php echo $tipoActual; ?>');
    });
  </script>
</body>

</html>
