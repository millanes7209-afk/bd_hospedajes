<?php
$pPrecio = htmlspecialchars($producto['precio'] ?? '');
$pStock = htmlspecialchars($producto['stock'] ?? '');
$pPromoPrice = htmlspecialchars($producto['precio_promo'] ?? '');
$pDiaPromo = strtolower($producto['dia_promo'] ?? ($producto['dias_promo'] ?? ''));
$hasPromo = !empty($pPromoPrice) || !empty($pDiaPromo);
?>
<div id="section-simple" class="space-y-4 pt-4 border-t border-white/10">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
        <!-- Precio Principal -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider admin-text-muted mb-1.5">Precio Venta (Bs.)
                *</label>
            <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-bold text-xs admin-text-muted">Bs.</span>
                <input type="number" step="0.01" name="precio" id="precio_simple" value="<?php echo $pPrecio; ?>"
                    placeholder="0.00" class="form-input pl-12 font-bold text-base text-[#FFE66D]" />
            </div>
        </div>

        <!-- Stock -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider admin-text-muted mb-1.5">Stock Disponible
                (Opcional)</label>
            <input type="number" name="stock" value="<?php echo $pStock; ?>" placeholder="Ilimitado (Dejar vacío)"
                class="form-input text-xs" />
            <span class="text-[10px] admin-text-muted mt-1 block">Si no manejas inventario para este producto, déjalo
                vacío.</span>
        </div>
    </div>

    <!-- Toggle Descuento Especial -->
    <div class="pt-2 flex flex-col sm:flex-row gap-4 sm:items-center justify-between">
        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
            <input type="checkbox" id="toggle_promo_simple" onchange="togglePromoSimpleView()" <?php echo $hasPromo ? 'checked' : ''; ?> class="rounded bg-black/60 border-white/20 text-[#FFE66D] focus:ring-0 w-4 h-4">
            <span class="text-xs font-bold uppercase admin-text-main flex items-center gap-1.5">
                <i class="fa-solid fa-tag text-green-400"></i> ¿Descuento especial por día?
            </span>
        </label>

        <!-- Toggle Solo Local -->
        <?php $pSoloLocal = (int) ($variantes[0]['solo_local'] ?? ($producto['solo_local'] ?? 0)); ?>
        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
            <input type="checkbox" name="solo_local" value="1" <?php echo $pSoloLocal ? 'checked' : ''; ?>
                class="rounded bg-black/60 border-amber-500/50 text-amber-500 focus:ring-0 w-4 h-4">
            <span class="text-xs font-bold uppercase text-amber-500 flex items-center gap-1.5">
                <i class="fa-solid fa-store"></i> Solo en el local
            </span>
        </label>
    </div>

    <!-- Bloque Oculto / Descuento Simple -->
    <div id="box_promo_simple"
        class="admin-subcard p-4 rounded-xl border border-white/10 grid grid-cols-1 md:grid-cols-2 gap-4 <?php echo $hasPromo ? '' : 'hidden'; ?>">
        <div>
            <label class="block text-xs font-bold uppercase admin-text-muted mb-1.5">Día de la Oferta</label>
            <select name="dia_promo" class="form-input text-xs font-bold uppercase">
                <option value="">-- SELECCIONE DÍA --</option>
                <option value="lunes" <?php echo $pDiaPromo === 'lunes' ? 'selected' : ''; ?>>LUNES DE OFERTA</option>
                <option value="martes" <?php echo $pDiaPromo === 'martes' ? 'selected' : ''; ?>>MARTES DE OFERTA</option>
                <option value="miercoles" <?php echo $pDiaPromo === 'miercoles' ? 'selected' : ''; ?>>MIÉRCOLES DE OFERTA
                </option>
                <option value="jueves" <?php echo $pDiaPromo === 'jueves' ? 'selected' : ''; ?>>JUEVES DE OFERTA</option>
                <option value="viernes" <?php echo $pDiaPromo === 'viernes' ? 'selected' : ''; ?>>VIERNES DE OFERTA
                </option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold uppercase admin-text-muted mb-1.5">Precio de Oferta (Bs.)</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 font-bold text-xs text-green-400">Bs.</span>
                <input type="number" step="0.01" name="precio_promo" value="<?php echo $pPromoPrice; ?>"
                    placeholder="Ej. 25.00" class="form-input pl-10 text-xs text-green-400 font-bold" />
            </div>
        </div>
    </div>
</div>