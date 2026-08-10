$file = 'c:\xampp\htdocs\dulces\sis_segundo_2023\tienda\tienda.php'
$content = [System.IO.File]::ReadAllText($file, [System.Text.Encoding]::UTF8)

# ============================================================
# PATCH 1: Agregar pestañas nav justo después del cierre del row de caja
# Buscar la línea después de la caja (antes de "<!-- Grid de Productos -->")
# ============================================================
$old1 = "        <div class=""row"">" + "`n" +
        "            <!-- Grid de Productos -->"

$new1 = "        <!-- Pestañas de navegación -->" + "`n" +
        "        <ul class=""nav nav-tabs mb-4"" id=""tiendaTabs"" role=""tablist"">" + "`n" +
        "            <li class=""nav-item"" role=""presentation"">" + "`n" +
        "                <button class=""nav-link active"" id=""tab-inventario"" data-bs-toggle=""tab""" + "`n" +
        "                    data-bs-target=""#pane-inventario"" type=""button"" role=""tab"">" + "`n" +
        "                    <i class=""fas fa-boxes me-1""></i> Inventario" + "`n" +
        "                </button>" + "`n" +
        "            </li>" + "`n" +
        "            <li class=""nav-item"" role=""presentation"">" + "`n" +
        "                <button class=""nav-link"" id=""tab-transacciones"" data-bs-toggle=""tab""" + "`n" +
        "                    data-bs-target=""#pane-transacciones"" type=""button"" role=""tab""" + "`n" +
        "                    onclick=""cargarTransacciones()"">" + "`n" +
        "                    <i class=""fas fa-receipt me-1""></i> Transacciones" + "`n" +
        "                </button>" + "`n" +
        "            </li>" + "`n" +
        "        </ul>" + "`n" +
        "`n" +
        "        <div class=""tab-content"">" + "`n" +
        "        <!-- Pestaña 1: Inventario + Carrito -->" + "`n" +
        "        <div class=""tab-pane fade show active"" id=""pane-inventario"" role=""tabpanel"">" + "`n" +
        "        <div class=""row"">" + "`n" +
        "            <!-- Grid de Productos -->"

if ($content.Contains($old1)) {
    Write-Host 'Patch 1 encontrado...'
    $content = $content.Replace($old1, $new1)
} else {
    Write-Host 'Patch 1 NO encontrado'
}

# ============================================================
# PATCH 2: Cerrar la pestaña de inventario y agregar pestaña de transacciones
# El inventario termina después del carrito lateral (cierra </div></div> del row)
# Buscamos el cierre de la sección de inventario/carrito antes de los modales
# ============================================================
$old2 = "    <!-- Modal Nuevo Producto -->"

$new2 = "        </div><!-- /row inventario+carrito -->" + "`n" +
        "        </div><!-- /pane-inventario -->" + "`n" +
        "`n" +
        "        <!-- Pestaña 2: Transacciones -->" + "`n" +
        "        <div class=""tab-pane fade"" id=""pane-transacciones"" role=""tabpanel"">" + "`n" +
        "            <div class=""card border-0 shadow-sm"">" + "`n" +
        "                <div class=""card-body"">" + "`n" +
        "                    <div class=""d-flex justify-content-between align-items-center mb-3"">" + "`n" +
        "                        <h6 class=""fw-bold mb-0""><i class=""fas fa-history me-2 text-success""></i>Historial de Transacciones</h6>" + "`n" +
        "                        <button class=""btn btn-sm btn-outline-secondary"" onclick=""cargarTransacciones()"">" + "`n" +
        "                            <i class=""fas fa-sync-alt""></i> Actualizar" + "`n" +
        "                        </button>" + "`n" +
        "                    </div>" + "`n" +
        "                    <!-- Filtros rápidos -->" + "`n" +
        "                    <div class=""d-flex gap-2 mb-3"">" + "`n" +
        "                        <button class=""btn btn-sm btn-outline-success active"" id=""filtroTodos"" onclick=""filtrarTransacciones('TODOS')"">Todos</button>" + "`n" +
        "                        <button class=""btn btn-sm btn-outline-primary"" id=""filtroVentas"" onclick=""filtrarTransacciones('VENTA')"">Solo Ventas</button>" + "`n" +
        "                        <button class=""btn btn-sm btn-outline-warning"" id=""filtroCompras"" onclick=""filtrarTransacciones('COMPRA')"">Solo Compras</button>" + "`n" +
        "                    </div>" + "`n" +
        "                    <div id=""tablaTransacciones""><!-- cargado por JS --></div>" + "`n" +
        "                </div>" + "`n" +
        "            </div>" + "`n" +
        "        </div><!-- /pane-transacciones -->" + "`n" +
        "        </div><!-- /tab-content -->" + "`n" +
        "`n" +
        "    <!-- Modal Nuevo Producto -->"

if ($content.Contains($old2)) {
    Write-Host 'Patch 2 encontrado...'
    $content = $content.Replace($old2, $new2)
} else {
    Write-Host 'Patch 2 NO encontrado'
}

# ============================================================
# PATCH 3: Eliminar el div row extra que queda huérfano
# (El row de inventario+carrito ahora está dentro de pane-inventario)
# Buscar el cierre que ahora sería doble cierre
# ============================================================
# Buscar si hay </div></div> extra al final del inventario (el que cerraba la sección)
# Esto lo hacemos buscando el patrón que aparece justo antes del primer modal
$old3 = "        </div><!-- /row inventario+carrito -->" + "`n" +
        "        </div><!-- /pane-inventario -->" + "`n" +
        "`n" +
        "        <!-- Pestaña 2: Transacciones -->"

# Verificar si hay un </div> adicional antes (del row original)
$checkPattern = "        </div>`n    </div>`n`n        </div><!-- /row inventario+carrito -->"
if ($content.Contains($checkPattern)) {
    Write-Host 'Encontré div extra, limpiando...'
    $content = $content.Replace("        </div>`n    </div>`n`n        </div><!-- /row inventario+carrito -->",
                                "        </div><!-- /row inventario+carrito -->")
}

[System.IO.File]::WriteAllText($file, $content, [System.Text.Encoding]::UTF8)
Write-Host 'Patches aplicados'
