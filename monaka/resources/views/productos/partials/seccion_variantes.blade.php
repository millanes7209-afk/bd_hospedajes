<div id="section-variantes" class="space-y-4 pt-4 border-t border-white/10 hidden">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xs font-black uppercase text-[#FFE66D] tracking-wider">PRESENTACIONES / VARIANTES</h3>
            <p class="text-[11px] admin-text-muted">Configura los distintos tamaños, porciones o sabores con sus precios
                correspondientes.</p>
        </div>
        <button type="button" onclick="addVarianteRow()"
            class="btn-primary text-xs font-bold uppercase !py-1.5 !px-3 flex items-center gap-1.5">
            <i class="fa-solid fa-plus"></i> AGREGAR PRESENTACIÓN
        </button>
    </div>

    <div id="variantes-container" class="space-y-3">
        <?php 
      if (!empty($variantes) && is_array($variantes)):
    foreach ($variantes as $idx => $v):
        $vId = $v['id'] ?? ($v['varianteID'] ?? ($v['variante_id'] ?? ''));
        $vNombre = htmlspecialchars(strtoupper($v['nombre_variante'] ?? ($v['nombre'] ?? '')));
        $vCant = htmlspecialchars($v['cantidad'] ?? '');
        $vUni = htmlspecialchars($v['unidad'] ?? 'und');
        $vPrecio = htmlspecialchars($v['precio'] ?? '0');
        $vStock = htmlspecialchars($v['stock'] ?? '');
        $vPromoPrice = htmlspecialchars($v['precio_promo'] ?? '');
        $vDiaPromo = strtolower($v['dia_promo'] ?? '');
        $vDisp = (int) ($v['disponible'] ?? 1);
    ?>
        <div class="variante-row admin-subcard p-4 rounded-xl border border-white/10 space-y-3 relative">
            <input type="hidden" name="variantes[<?php        echo $idx; ?>][variante_id]" value="<?php        echo $vId; ?>">
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                <!-- Nombre presentación -->
                <div class="sm:col-span-4">
                    <label class="block text-[10px] font-bold uppercase admin-text-muted mb-1">Nombre Presentación
                        *</label>
                    <input type="text" name="variantes[<?php        echo $idx; ?>][nombre_variante]"
                        value="<?php        echo $vNombre; ?>" oninput="this.value = this.value.toUpperCase();"
                        placeholder="Ej. 6 UNIDADES"
                        class="form-input text-xs font-bold uppercase variante-nombre-input" />
                </div>
                <!-- Cantidad num -->
                <div class="sm:col-span-2">
                    <label class="block text-[10px] font-bold uppercase admin-text-muted mb-1">Cantidad</label>
                    <input type="number" step="0.01" name="variantes[<?php        echo $idx; ?>][cantidad]"
                        value="<?php        echo $vCant; ?>" placeholder="Ej. 6" class="form-input text-xs" />
                </div>
                <!-- Unidad -->
                <div class="sm:col-span-2">
                    <label class="block text-[10px] font-bold uppercase admin-text-muted mb-1">Unidad</label>
                    <select name="variantes[<?php        echo $idx; ?>][unidad]" class="form-input text-xs">
                        <option value="und" <?php        echo $vUni === 'und' ? 'selected' : ''; ?>>und</option>
                        <option value="ml" <?php        echo $vUni === 'ml' ? 'selected' : ''; ?>>ml</option>
                        <option value="lt" <?php        echo $vUni === 'lt' ? 'selected' : ''; ?>>lt</option>
                        <option value="gr" <?php        echo $vUni === 'gr' ? 'selected' : ''; ?>>gr</option>
                        <option value="kg" <?php        echo $vUni === 'kg' ? 'selected' : ''; ?>>kg</option>
                        <option value="porcion" <?php        echo $vUni === 'porcion' ? 'selected' : ''; ?>>porción</option>
                    </select>
                </div>
                <!-- Precio -->
                <div class="sm:col-span-3">
                    <label class="block text-[10px] font-bold uppercase admin-text-muted mb-1">Precio (Bs.) *</label>
                    <input type="number" step="0.01" name="variantes[<?php        echo $idx; ?>][precio]"
                        value="<?php        echo $vPrecio; ?>" placeholder="0.00"
                        class="form-input text-xs font-bold text-[#FFE66D] variante-precio-input" />
                </div>
                <!-- Eliminar -->
                <div class="sm:col-span-1 flex justify-end">
                    <button type="button" onclick="removeVarianteRow(this)"
                        class="text-red-400 hover:text-red-300 text-sm p-2">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>

            <!-- Fila secundaria: Stock + Promo por día + Disponibilidad -->
            <div class="pt-2 border-t border-white/5 flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="text-[10px] font-bold uppercase admin-text-muted">Stock:</span>
                    <input type="number" name="variantes[<?php        echo $idx; ?>][stock]" value="<?php        echo $vStock; ?>"
                        placeholder="Ilimitado" class="form-input !py-1 !px-2 w-24 text-xs" />

                    <!-- Promo variante -->
                    <button type="button" onclick="toggleVarPromoBox(this)"
                        class="text-[10px] font-bold uppercase text-green-400 hover:underline flex items-center gap-1">
                        <i class="fa-solid fa-tag"></i> Descuento por día
                    </button>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Toggle Disponible -->
                    <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                        <input type="checkbox" name="variantes[<?php        echo $idx; ?>][disponible]" value="1" <?php        echo $vDisp ? 'checked' : ''; ?>
                            class="rounded bg-black/60 border-white/20 text-green-500 focus:ring-0 w-3.5 h-3.5">
                        <span class="text-[10px] font-bold uppercase admin-text-main">Disponible ahora</span>
                    </label>
                </div>
            </div>

            <!-- Bloque Oculto de Promo Variante -->
            <div
                class="var-promo-box pt-2 grid grid-cols-1 sm:grid-cols-2 gap-3 admin-subcard p-3 rounded-lg border border-white/5 <?php        echo empty($vPromoPrice) ? 'hidden' : ''; ?>">
                <div>
                    <label class="block text-[9px] font-bold uppercase admin-text-muted mb-1">Día Promoción</label>
                    <select name="variantes[<?php        echo $idx; ?>][dia_promo]" class="form-input !py-1 text-xs">
                        <option value="">-- Sin Día --</option>
                        <option value="lunes" <?php        echo $vDiaPromo === 'lunes' ? 'selected' : ''; ?>>LUNES</option>
                        <option value="martes" <?php        echo $vDiaPromo === 'martes' ? 'selected' : ''; ?>>MARTES</option>
                        <option value="miercoles" <?php        echo $vDiaPromo === 'miercoles' ? 'selected' : ''; ?>>MIÉRCOLES
                        </option>
                        <option value="jueves" <?php        echo $vDiaPromo === 'jueves' ? 'selected' : ''; ?>>JUEVES</option>
                        <option value="viernes" <?php        echo $vDiaPromo === 'viernes' ? 'selected' : ''; ?>>VIERNES</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[9px] font-bold uppercase admin-text-muted mb-1">Precio Promocional
                        (Bs.)</label>
                    <input type="number" step="0.01" name="variantes[<?php        echo $idx; ?>][precio_promo]"
                        value="<?php        echo $vPromoPrice; ?>" placeholder="Ej. 28.00"
                        class="form-input !py-1 text-xs text-green-400 font-bold" />
                </div>
            </div>
        </div>
        <?php 
        endforeach;
endif; 
    ?>
    </div>
</div>