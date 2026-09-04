<?php
session_start();
require_once("../../conexion.php");

if (!isset($_SESSION['sesion_id_usuario'])) {
    header("Location: ../../index.php");
    exit;
}

$empresaID = $_SESSION['empresaID'] ?? null;
require_once("../../libreria_menu.php");
?>

<link rel="stylesheet" href="css/transacciones.css?v=<?= time() ?>">

<div class="card m-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="mb-0 fw-bold">HISTORIAL DE TRANSACCIONES</h3>
        <a href="tienda.php?auth=<?= urlencode($_GET['auth'] ?? 'habitaciones.php') ?>"
            class="btn btn-secondary btn-sm fw-bold">
            <i class="fas fa-arrow-left"></i> Volver a Tienda
        </a>
    </div>

    <div class="card-body">
        <form id="filterForm" class="row g-2 align-items-end mb-3">
            <div class="col-md-3">
                <label class="small fw-bold">Tipo de Transacción:</label>
                <select class="form-control form-control-sm" id="filtroTipo">
                    <option value="TODAS">TODAS</option>
                    <option value="VENTA">VENTAS</option>
                    <option value="COMPRA">COMPRAS DE STOCK</option>
                    <option value="RETIRO">RETIROS DE GANANCIAS</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold">Desde:</label>
                <input type="date" class="form-control form-control-sm" id="filtroDesde" value="">
            </div>
            <div class="col-md-3">
                <label class="small fw-bold">Hasta:</label>
                <input type="date" class="form-control form-control-sm" id="filtroHasta" value="">
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-primary btn-sm w-100 fw-bold" onclick="cargarTransacciones()">
                    <i class="fas fa-search"></i> BUSCAR
                </button>
            </div>
        </form>

        <div class="table-responsive mt-3">
            <table class="table table-striped table-hover align-middle mb-0" id="tablaTransacciones">
                <thead>
                    <tr>
                        <th scope="col">Tipo</th>
                        <th scope="col">Productos</th>
                        <th scope="col">Total</th>
                        <th scope="col">Método Pago</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Usuario</th>
                        <th scope="col" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="listaTransacciones">
                    <tr>
                        <td colspan="7" class="text-center text-muted p-4">Cargando transacciones...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detalles -->
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 6px; overflow: hidden;">
            <div
                class="modal-header bg-dark text-white border-0 py-2 px-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold small text-uppercase" style="letter-spacing: 0.5px;">Detalle de Transacción #<span
                        id="detTransID"></span></span>
                <button type="button" class="border-0 bg-transparent text-white p-0 shadow-none" data-bs-dismiss="modal"
                    aria-label="Cerrar"
                    style="background:transparent; border:none; color:#fff; font-size:1.4rem; line-height:1; cursor:pointer; opacity:0.8;">&times;</button>
            </div>
            <div class="modal-body p-3 bg-white">
                <table class="tabla-transacciones w-100 mb-0">
                    <thead>
                        <tr>
                            <th>PRODUCTO</th>
                            <th class="text-center">CANT.</th>
                            <th class="text-end">PRECIO U.</th>
                            <th class="text-end">SUBTOTAL</th>
                        </tr>
                    </thead>
                    <tbody id="detItems"></tbody>
                    <tfoot class="fw-bold">
                        <tr style="border-top: 1.5px solid #000;">
                            <td colspan="3" class="text-end pt-2">TOTAL:</td>
                            <td class="text-end text-danger pt-2 fs-6">Bs. <span id="detTotal"></span></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="js/transacciones.js?v=<?= time() ?>"></script>
</body>

</html>