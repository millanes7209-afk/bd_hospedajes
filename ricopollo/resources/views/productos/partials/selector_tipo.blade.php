<div class="space-y-4 pt-4 border-t border-white/10">
    <h3
        class="text-xs font-black uppercase text-[#FFE66D] tracking-wider flex items-center gap-2 border-b border-white/5 pb-2">
        <span
            class="w-5 h-5 rounded-full bg-[#FFE66D] text-black text-[11px] flex items-center justify-center font-black">2</span>
        TIPO DE PRODUCTO Y PRESENTACIÓN
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Opción Simple -->
        <div id="card-tipo-simple" onclick="selectTipoProducto('simple')"
            class="type-card admin-subcard p-4 rounded-xl cursor-pointer relative overflow-hidden flex flex-col justify-between group">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-[#FFE66D] block mb-1">Opción
                        A</span>
                    <h4 class="text-sm font-black uppercase admin-text-main">Precio Único</h4>
                    <p class="text-[11px] admin-text-muted mt-1 leading-snug">
                        Ideal para platos o productos con una sola presentación estándar.
                    </p>
                </div>
                <div id="badge-simple"
                    class="w-6 h-6 rounded-full bg-[#FFE66D] text-black flex items-center justify-center text-xs shadow-lg">
                    <i class="fa-solid fa-check font-black"></i>
                </div>
            </div>
        </div>

        <!-- Opción Variantes -->
        <div id="card-tipo-variantes" onclick="selectTipoProducto('variantes')"
            class="type-card admin-subcard p-4 rounded-xl cursor-pointer relative overflow-hidden flex flex-col justify-between group">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-[#FFE66D] block mb-1">Opción
                        B</span>
                    <h4 class="text-sm font-black uppercase admin-text-main">Varias Presentaciones</h4>
                    <p class="text-[11px] admin-text-muted mt-1 leading-snug">
                        Ej: Porciones, Tamaños (1/4, 1/2, Entero) o Cantidades múltiples.
                    </p>
                </div>
                <div id="badge-variantes"
                    class="w-6 h-6 rounded-full bg-[#FFE66D] text-black flex items-center justify-center text-xs shadow-lg hidden">
                    <i class="fa-solid fa-check font-black"></i>
                </div>
            </div>
        </div>
    </div>
</div>