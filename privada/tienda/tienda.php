<?php
session_start();
require_once("../../conexion.php");

// Verificar sesión
if (!isset($_SESSION['sesion_id_usuario'])) {
    header("Location: ../../index.php");
    exit;
}

$empresaID = $_SESSION['empresaID'] ?? null;
$rol = $_SESSION['rol'] ?? null;

$sql = "SELECT MIN(formapagoID) AS formapagoID, tipo AS nombre_forma_pago FROM formas_pago WHERE _estado<>'X' GROUP BY tipo";
if (method_exists($db, 'obtenerTodo')) {
    $formas_pago = $db->obtenerTodo($sql);
} else {
    $db->query($sql);
    $formas_pago = $db->resultset();
}

require_once("../../libreria_menu.php");
?>

<link rel="stylesheet" href="css/tienda.css?v=<?= time() ?>">

<div class="container-fluid pt-4 pb-5 px-4 mb-5 pb-5">


    <!-- Panel de Caja de Tienda -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap px-2">
                <div class="d-flex align-items-center">
                    <div class="text-center px-4">
                        <span class="text-muted d-block small fw-bold text-uppercase">Caja Efectivo</span>
                        <h4 class="mb-0 text-success fw-bold">Bs. <span id="cajaEfectivo">0.00</span></h4>
                    </div>
                    <div style="width: 2px; height: 40px; background-color: #cbd5e1; margin: 0 15px;"></div>
                    <div class="text-center px-4">
                        <span class="text-muted d-block small fw-bold text-uppercase">Caja QR/Transf.</span>
                        <h4 class="mb-0 text-primary fw-bold">Bs. <span id="cajaQR">0.00</span></h4>
                    </div>
                </div>
                <?php
                $userRol = strtoupper($_SESSION["sesion_rol"] ?? $_SESSION["rol"] ?? '');
                if (empty($userRol) || in_array($userRol, ['ADMINISTRADOR', 'PROPIETARIO', 'ADMIN'])):
                    ?>
                    <div class="mt-3 mt-md-0">
                        <button type="button" class="btn btn-outline-danger fw-bold shadow-sm"
                            onclick="mostrarModalRetiro()">
                            <i class="fas fa-hand-holding-usd"></i> Retirar Ganancias
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row gx-4">
        <!-- Panel Izquierdo: Productos Grid -->
        <div class="col-lg-7 col-xl-8 mb-4">
            <div class="card shadow border-0 bg-light h-100">
                <div
                    class="card-header bg-transparent border-0 pt-4 pb-2 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="fw-bold mb-0" style="color:#000;">
                        <i class="fas fa-box-open text-warning"></i> Inventario de Productos
                    </h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success btn-sm fw-bold shadow-sm"
                            onclick="mostrarModalNuevoProducto()">
                            <i class="fas fa-plus"></i> Nuevo
                        </button>
                        <a href="transacciones.php?auth=<?= urlencode($_GET['auth'] ?? 'habitaciones.php') ?>"
                            class="btn btn-secondary btn-sm fw-bold shadow-sm">
                            <i class="fas fa-list"></i> Historial
                        </a>
                    </div>
                </div>
                <div class="card-body p-4 pt-3">
                    <div class="row g-3" id="productosGrid">
                        <!-- Productos rellenados por JS -->
                    </div>

                    <h5 class="fw-bold mb-3 border-bottom pb-2 mt-5" style="color:#000;"><i
                            class="fas fa-exclamation-triangle text-danger"></i> Productos Agotados</h5>
                    <div class="row g-3" id="sinStockGrid">
                        <!-- Productos agotados rellenados por JS -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Derecho: Carrito POS -->
        <div class="col-lg-5 col-xl-4 h-100">
            <div class="card shadow border-0" style="position: sticky; top: 80px;">
                <!-- Header Carrito (Tabs Venta/Compra) -->
                <div class="card-header bg-white p-3 border-bottom flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-bold" style="color:#000;">
                            <i class="fas fa-shopping-cart text-primary"></i> Caja / Carrito
                        </h5>
                        <span class="badge bg-primary rounded-pill d-none" id="cartBadge">0</span>
                    </div>
                    <div class="d-flex bg-light p-1 rounded gap-1 w-100">
                        <button class="btn btn-sm btn-venta flex-fill" id="btnModoVenta" onclick="cambiarModo('venta')"
                            style="font-weight: bold; background-color:#198754; color:white;">
                            <i class="fas fa-cash-register"></i> VENTA
                        </button>
                        <button class="btn btn-sm btn-outline-secondary flex-fill" id="btnModoCompra"
                            onclick="cambiarModo('compra')" style="font-weight: bold; color:#000;">
                            COMPRA
                        </button>
                    </div>
                </div>

                <!-- Cuerpo Carrito -->
                <div class="card-body p-0" style="max-height: 45vh; overflow-y: auto;">
                    <div id="carritoItems" class="p-3">
                        <!-- Relleno por JS -->
                    </div>
                </div>

                <!-- Resumen y Pago -->
                <div class="card-footer bg-white p-3 border-top pb-5">
                    <div class="d-flex justify-content-between align-items-center mb-2 px-2">
                        <h6 class="fw-bold mb-0">TOTAL</h6>
                        <div class="input-group input-group-sm" style="width: 150px;">
                            <span class="input-group-text bg-light fw-bold text-dark border-end-0">Bs.</span>
                            <input type="number" id="totalCarrito"
                                class="form-control text-end fw-bold fs-5 border-start-0" value="0.00" step="0.5"
                                oninput="actualizarTotalManual(this.value)">
                        </div>
                    </div>

                    <!-- SECCIÓN DE PAGOS HÍBRIDOS (Sólo visible en MODO VENTA) -->
                    <div id="metodoPagoSection" class="mt-3 p-3 bg-light rounded-3 shadow-sm border border-secondary">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-wallet"></i> Pagos del Cliente</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 fw-bold"
                                onclick="agregarFilaPago()">+ Forma</button>
                        </div>

                        <div id="alertaPagoTienda" class="alert alert-danger py-1 px-2 mb-2 small fw-bold"
                            style="display:none;font-size:0.8rem;"></div>

                        <div id="contenedorPagos" class="mb-2">
                            <!-- Filas dinámicas -->
                        </div>

                        <!-- Elemento Oculto (Template Row) -->
                        <div class="d-none" id="templateFormaPago">
                            <option value="">Forma de pago...</option>
                            <?php foreach ($formas_pago as $fp): ?>
                                <option value="<?= htmlspecialchars($fp['formapagoID'] ?? '') ?>">
                                    <?= htmlspecialchars($fp['nombre_forma_pago'] ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </div>

                    </div>

                    <!-- Boton Principal -->
                    <div class="mt-3">
                        <button id="btnConfirmar"
                            class="btn btn-success w-100 py-3 fw-bold fs-6 shadow d-flex align-items-center justify-content-center gap-2"
                            onclick="confirmarOperacion()">
                            <i class="fas fa-check-circle fs-4"></i> CONFIRMAR VENTA
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo Producto -->
<div class="modal fade" id="modalNuevoProducto" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div
                class="modal-header bg-dark text-white border-0 py-2 px-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold small text-uppercase" style="letter-spacing: 0.5px;"><i
                        class="fas fa-plus-circle me-1"></i> Registrar Nuevo Producto</span>
                <button type="button" class="border-0 bg-transparent text-white p-0 shadow-none" data-bs-dismiss="modal"
                    aria-label="Cerrar"
                    style="background:transparent; border:none; color:#fff; font-size:1.4rem; line-height:1; cursor:pointer; opacity:0.8;">&times;</button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark">Nombre del Producto *</label>
                    <input type="text" id="nuevoNombre" class="form-control bg-white" placeholder="Ej. Coca Cola 2L"
                        onfocus="setTimeout(() => this.select(), 50)" onclick="setTimeout(() => this.select(), 50)">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <label class="form-label fw-bold text-dark">Medida / Litros</label>
                        <input type="text" id="nuevaMedida" class="form-control bg-white" placeholder="Ej. 2"
                            onfocus="setTimeout(() => this.select(), 50)" onclick="setTimeout(() => this.select(), 50)">
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-bold text-dark">Precio Venta (Bs) *</label>
                        <input type="number" id="nuevoPrecio" class="form-control bg-white" step="0.5" value="0"
                            onfocus="setTimeout(() => this.select(), 50)" onclick="setTimeout(() => this.select(), 50)">
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-bold text-dark">Stock Inicial *</label>
                        <input type="number" id="nuevoStock" class="form-control bg-white" min="0" value="0"
                            onfocus="setTimeout(() => this.select(), 50)" onclick="setTimeout(() => this.select(), 50)">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark">Imagen del Producto *</label>
                    <input type="file" id="nuevaImagen" class="form-control bg-white" accept="image/*">
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary fw-bold" onclick="crearProducto()"><i
                        class="fas fa-save"></i> Guardar Producto</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Retiro Ganancias -->
<div class="modal fade" id="modalRetiro" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 6px; overflow: hidden;">
            <div
                class="modal-header bg-dark text-white border-0 py-2 px-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold small text-uppercase" style="letter-spacing: 0.5px;"><i
                        class="fas fa-hand-holding-usd me-1"></i> Retiro de Ganancias</span>
                <button type="button" class="border-0 bg-transparent text-white p-0 shadow-none" data-bs-dismiss="modal"
                    aria-label="Cerrar"
                    style="background:transparent; border:none; color:#fff; font-size:1.4rem; line-height:1; cursor:pointer; opacity:0.8;">&times;</button>
            </div>
            <div class="modal-body p-3 bg-white">
                <div class="p-2 mb-3 bg-light rounded text-muted small border">
                    <i class="fas fa-info-circle me-1 text-primary"></i> Registra la salida de dinero (parcial o total)
                    hacia caja central o gastos.
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-bold small text-success mb-1">Retirar Efectivo (Bs.)</label>
                        <input type="number" id="retiroEfectivo"
                            class="form-control form-control-sm fw-bold text-end text-success" step="0.5" min="0"
                            value="0" onfocus="setTimeout(() => this.select(), 50)"
                            onclick="setTimeout(() => this.select(), 50)">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold small text-primary mb-1">Retirar QR (Bs.)</label>
                        <input type="number" id="retiroQR"
                            class="form-control form-control-sm fw-bold text-end text-primary" step="0.5" min="0"
                            value="0" onfocus="setTimeout(() => this.select(), 50)"
                            onclick="setTimeout(() => this.select(), 50)">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark mb-1">Motivo de Retiro *</label>
                    <textarea id="retiroMotivo" class="form-control form-control-sm" rows="2"
                        placeholder="Ej. Traspaso a central"></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-2 px-3">
                <button type="button" class="btn btn-secondary btn-sm fw-bold" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger btn-sm fw-bold" onclick="registrarRetiro()"><i
                        class="fas fa-check me-1"></i> Ejecutar Retiro</button>
            </div>
        </div>
    </div>
</div>

<!-- TOAST NOTIFICACIONES -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="toastNotificacion" class="toast text-white bg-dark border-0 shadow" role="alert" aria-live="assertive"
        aria-atomic="true">
        <div class="toast-header border-0 bg-dark text-white">
            <i id="toastIcon" class="fas fa-info-circle me-2"></i>
            <strong class="me-auto" id="toastTitulo">Notificación</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body fw-bold" id="toastMensaje" style="font-size: 1.1em;">
            Mensaje de prueba
        </div>
    </div>
</div>

<script src="js/tienda.js?v=<?= time() ?>"></script>
</body>

</html>