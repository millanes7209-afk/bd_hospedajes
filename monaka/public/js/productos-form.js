let varianteIdxCounter = 0;

function setVarianteCounter(initialValue) {
    varianteIdxCounter = initialValue;
}

function selectTipoProducto(tipo) {
    const inputTipo = document.getElementById('input_tipo');
    if (inputTipo) inputTipo.value = tipo;

    const cardSimple = document.getElementById('card-tipo-simple');
    const cardVariantes = document.getElementById('card-tipo-variantes');
    const badgeSimple = document.getElementById('badge-simple');
    const badgeVariantes = document.getElementById('badge-variantes');
    const sectionSimple = document.getElementById('section-simple');
    const sectionVariantes = document.getElementById('section-variantes');
    const inputPrecioSimple = document.getElementById('precio_simple');

    if (tipo === 'simple') {
        if (cardSimple) cardSimple.classList.add('selected');
        if (cardVariantes) cardVariantes.classList.remove('selected');
        if (badgeSimple) badgeSimple.classList.remove('hidden');
        if (badgeVariantes) badgeVariantes.classList.add('hidden');

        if (sectionSimple) sectionSimple.classList.remove('hidden');
        if (sectionVariantes) sectionVariantes.classList.add('hidden');
        if (inputPrecioSimple) inputPrecioSimple.setAttribute('required', 'required');

        document.querySelectorAll('#section-variantes input, #section-variantes select').forEach(el => {
            el.removeAttribute('required');
        });
    } else {
        if (cardVariantes) cardVariantes.classList.add('selected');
        if (cardSimple) cardSimple.classList.remove('selected');
        if (badgeVariantes) badgeVariantes.classList.remove('hidden');
        if (badgeSimple) badgeSimple.classList.add('hidden');

        if (sectionVariantes) sectionVariantes.classList.remove('hidden');
        if (sectionSimple) sectionSimple.classList.add('hidden');
        if (inputPrecioSimple) inputPrecioSimple.removeAttribute('required');

        document.querySelectorAll('#section-variantes .variante-nombre-input, #section-variantes .variante-precio-input').forEach(el => {
            el.setAttribute('required', 'required');
        });

        if (document.querySelectorAll('.variante-row').length === 0) {
            addVarianteRow();
        }
    }
}

function togglePromoSimpleView() {
    const toggle = document.getElementById('toggle_promo_simple');
    const box = document.getElementById('box_promo_simple');
    if (toggle && box) {
        if (toggle.checked) {
            box.classList.remove('hidden');
        } else {
            box.classList.add('hidden');
        }
    }
}

function toggleVarPromoBox(btn) {
    const row = btn.closest('.variante-row');
    if (row) {
        const box = row.querySelector('.var-promo-box');
        if (box) {
            box.classList.toggle('hidden');
        }
    }
}

function addVarianteRow() {
    const container = document.getElementById('variantes-container');
    if (!container) return;

    const idx = varianteIdxCounter++;
    const currentTipo = document.getElementById('input_tipo')?.value || 'variantes';

    const html = `
    <div class="variante-row admin-subcard p-4 rounded-xl border border-white/10 space-y-3 relative">
      <input type="hidden" name="variantes[${idx}][variante_id]" value="">
      <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
        <div class="sm:col-span-4">
          <label class="block text-[10px] font-bold uppercase admin-text-muted mb-1">Nombre Presentación *</label>
          <input type="text" name="variantes[${idx}][nombre_variante]" oninput="this.value = this.value.toUpperCase();" placeholder="Ej. 6 UNIDADES" class="form-input text-xs font-bold uppercase variante-nombre-input" ${currentTipo === 'variantes' ? 'required' : ''} />
        </div>
        <div class="sm:col-span-2">
          <label class="block text-[10px] font-bold uppercase admin-text-muted mb-1">Cantidad</label>
          <input type="number" step="0.01" name="variantes[${idx}][cantidad]" placeholder="Ej. 6" class="form-input text-xs" />
        </div>
        <div class="sm:col-span-2">
          <label class="block text-[10px] font-bold uppercase admin-text-muted mb-1">Unidad</label>
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
          <label class="block text-[10px] font-bold uppercase admin-text-muted mb-1">Precio (Bs.) *</label>
          <input type="number" step="0.01" name="variantes[${idx}][precio]" placeholder="0.00" class="form-input text-xs font-bold text-[#FFE66D] variante-precio-input" ${currentTipo === 'variantes' ? 'required' : ''} />
        </div>
        <div class="sm:col-span-1 flex justify-end">
          <button type="button" onclick="removeVarianteRow(this)" class="text-red-400 hover:text-red-300 text-sm p-2">
            <i class="fa-solid fa-trash-can"></i>
          </button>
        </div>
      </div>

      <div class="pt-2 border-t border-white/5 flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-3 flex-wrap">
          <span class="text-[10px] font-bold uppercase admin-text-muted">Stock:</span>
          <input type="number" name="variantes[${idx}][stock]" placeholder="Ilimitado" class="form-input !py-1 !px-2 w-24 text-xs" />
          <button type="button" onclick="toggleVarPromoBox(this)" class="text-[10px] font-bold uppercase text-green-400 hover:underline flex items-center gap-1">
            <i class="fa-solid fa-tag"></i> Descuento por día
          </button>
        </div>
        <div class="flex items-center gap-4">
          <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
            <input type="checkbox" name="variantes[${idx}][disponible]" value="1" checked
              class="rounded bg-black/60 border-white/20 text-green-500 focus:ring-0 w-3.5 h-3.5">
            <span class="text-[10px] font-bold uppercase admin-text-main">Disponible ahora</span>
          </label>
        </div>
      </div>

      <div class="var-promo-box hidden pt-2 grid grid-cols-1 sm:grid-cols-2 gap-3 admin-subcard p-3 rounded-lg border border-white/5">
        <div>
          <label class="block text-[9px] font-bold uppercase admin-text-muted mb-1">Día Promoción</label>
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
          <label class="block text-[9px] font-bold uppercase admin-text-muted mb-1">Precio Promocional (Bs.)</label>
          <input type="number" step="0.01" name="variantes[${idx}][precio_promo]" placeholder="Ej. 28.00" class="form-input !py-1 text-xs text-green-400 font-bold" />
        </div>
      </div>
    </div>
  `;

    container.insertAdjacentHTML('beforeend', html);
}

function removeVarianteRow(btn) {
    const row = btn.closest('.variante-row');
    if (row) row.remove();
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function () {
            const tipo = document.getElementById('input_tipo')?.value;
            if (tipo === 'simple') {
                document.querySelectorAll('#section-variantes [required]').forEach(el => el.removeAttribute('required'));
            } else {
                const inputPrecioSimple = document.getElementById('precio_simple');
                if (inputPrecioSimple) inputPrecioSimple.removeAttribute('required');
            }
        });
    }
});
