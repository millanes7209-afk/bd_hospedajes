<div class="space-y-4">
    <h3
        class="text-xs font-black uppercase text-[#FFE66D] tracking-wider flex items-center gap-2 border-b border-white/5 pb-2">
        <span
            class="w-5 h-5 rounded-full bg-[#FFE66D] text-black text-[11px] flex items-center justify-center font-black">1</span>
        DATOS BÁSICOS DEL PLATILLO / BEBIDA
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Categoría -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider admin-text-muted mb-1.5">Categoría *</label>
            <select name="categoria_id" required class="form-input font-bold uppercase">
                <option value="" disabled <?php echo (!isset($producto) || empty($producto['categoria_id'])) ? 'selected' : ''; ?>>
                    -- SELECCIONE CATEGORÍA --
                </option>
                <?php foreach ($catsList as $c):
    $cId = $c['id'] ?? ($c['categoriaID'] ?? null);
    $cNom = $c['nombre'] ?? '';
    $sel = (isset($producto) && ($producto['categoria_id'] ?? ($producto['categoriaID'] ?? null)) == $cId) ? 'selected' : '';
        ?>
                <option value="<?php    echo $cId; ?>" <?php    echo $sel; ?>>
                    <?php    echo htmlspecialchars(strtoupper($cNom)); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Nombre -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider admin-text-muted mb-1.5">Nombre del Producto
                *</label>
            <input type="text" name="nombre" required value="<?php echo htmlspecialchars($producto['nombre'] ?? ''); ?>"
                oninput="this.value = this.value.toUpperCase();" placeholder="Ej. NUGGETS DE POLLO"
                class="form-input uppercase font-bold" />
        </div>
    </div>

    <!-- Descripción -->
    <div>
        <label class="block text-xs font-bold uppercase tracking-wider admin-text-muted mb-1.5">Descripción
            (Opcional)</label>
        <textarea name="descripcion" rows="2" placeholder="Breve detalle de los ingredientes o preparación..."
            class="form-input text-xs"><?php echo htmlspecialchars($producto['descripcion'] ?? ''); ?></textarea>
    </div>

    <!-- Imagen Principal -->
    <div>
        <label class="block text-xs font-bold uppercase tracking-wider admin-text-muted mb-1.5">Imagen Principal
            (Opcional)</label>
        <input type="file" name="imagen" accept="image/*" class="form-input text-xs" />
        <?php if (!empty($imagenActual)): ?>
        <div class="mt-2 flex items-center gap-2 text-xs admin-text-muted">
            <span>Imagen actual:</span>
            <img src="<?php    echo asset('assets/productos/' . $imagenActual); ?>"
                class="w-10 h-10 object-cover rounded border border-white/20">
        </div>
        <?php endif; ?>
    </div>
</div>