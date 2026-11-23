<?php
session_start();
require_once("../../conexion.php");

// ============================================================
// 1. MANEJO DE ACCIONES POST (Modificar Cliente)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    $accion = $_POST['accion'];
    $usuarioID = $_SESSION['sesion_id_usuario'] ?? 1;
    $ahora = date('Y-m-d H:i:s');

    if ($accion === 'modificar') {
        $clienteID = isset($_POST['clienteID']) ? (int)$_POST['clienteID'] : 0;
        $ci = isset($_POST['ci']) ? strtoupper(trim($_POST['ci'])) : '';
        $paisID = isset($_POST['paisID']) ? (int)$_POST['paisID'] : 0;
        $nombres = isset($_POST['nombres']) ? strtoupper(trim($_POST['nombres'])) : '';
        $apellido1 = isset($_POST['apellido1']) ? strtoupper(trim($_POST['apellido1'])) : '';
        $apellido2 = isset($_POST['apellido2']) ? strtoupper(trim($_POST['apellido2'])) : null;
        $fecha_nacimiento = isset($_POST['fecha_nacimiento']) ? trim($_POST['fecha_nacimiento']) : '';
        $lugar_nacimiento = isset($_POST['lugar_nacimiento']) ? strtoupper(trim($_POST['lugar_nacimiento'])) : '';

        // Validar campos obligatorios
        if (!$clienteID || empty($ci) || !$paisID || empty($nombres) || empty($apellido1) || empty($fecha_nacimiento) || empty($lugar_nacimiento)) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Por favor complete todos los campos obligatorios (*).']);
            exit;
        }

        // Validar restricción única (ci, paisID) excluyendo el cliente actual
        $sql_check = "SELECT clienteID FROM clientes WHERE ci = ? AND paisID = ? AND clienteID <> ? AND _estado <> 'X'";
        $duplicado = $db->obtenerFila($sql_check, [$ci, $paisID, $clienteID]);
        if ($duplicado) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Ya existe otro cliente registrado con este C.I. y País.']);
            exit;
        }

        // Actualizar datos del cliente
        $sql_update = "UPDATE clientes SET 
                        ci = ?,
                        paisID = ?,
                        nombres = ?,
                        apellido1 = ?,
                        apellido2 = ?,
                        fecha_nacimiento = ?,
                        lugar_nacimiento = ?,
                        _fec_modificacion = ?,
                        _usuario = ?
                       WHERE clienteID = ?";
        $res = $db->ejecutar($sql_update, [
            $ci,
            $paisID,
            $nombres,
            $apellido1,
            $apellido2 !== '' ? $apellido2 : null,
            $fecha_nacimiento,
            $lugar_nacimiento,
            $ahora,
            $usuarioID,
            $clienteID
        ]);

        if ($res) {
            echo json_encode(['status' => 'ok', 'mensaje' => 'Datos del cliente actualizados correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'mensaje' => 'Ocurrió un error al actualizar los datos en la base de datos.']);
        }
        exit;
    }

    echo json_encode(['status' => 'error', 'mensaje' => 'Acción no permitida.']);
    exit;
}

// ============================================================
// 2. MANEJO DE CONSULTA AJAX (Búsqueda y Paginación Rápida)
// ============================================================
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json; charset=utf-8');

    $ci = isset($_GET['ci']) ? trim($_GET['ci']) : '';
    $nombres = isset($_GET['nombres']) ? trim($_GET['nombres']) : '';
    $apellidos = isset($_GET['apellidos']) ? trim($_GET['apellidos']) : '';
    $paisID = isset($_GET['paisID']) && $_GET['paisID'] !== '' ? (int)$_GET['paisID'] : null;
    $pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
    $limite = 20;
    $offset = ($pagina - 1) * $limite;

    $where = "WHERE c._estado <> 'X'";
    $params = [];

    if (!empty($ci)) {
        $where .= " AND c.ci LIKE ?";
        $params[] = "%$ci%";
    }
    if (!empty($nombres)) {
        $where .= " AND c.nombres LIKE ?";
        $params[] = "%$nombres%";
    }
    if (!empty($apellidos)) {
        $where .= " AND (c.apellido1 LIKE ? OR c.apellido2 LIKE ?)";
        $params[] = "%$apellidos%";
        $params[] = "%$apellidos%";
    }
    if ($paisID) {
        $where .= " AND c.paisID = ?";
        $params[] = $paisID;
    }

    // Contar total de coincidencias
    $sql_count = "SELECT COUNT(*) as total FROM clientes c $where";
    $countRow = $db->obtenerFila($sql_count, $params);
    $total_registros = (int)($countRow['total'] ?? 0);
    $total_paginas = max(1, (int)ceil($total_registros / $limite));

    // Obtener los clientes paginados
    $sql_datos = "SELECT c.clienteID, c.ci, c.nombres, c.apellido1, c.apellido2, 
                         c.fecha_nacimiento, c.lugar_nacimiento, c.paisID,
                         COALESCE(p.nombre, 'DESCONOCIDO') AS pais_nombre
                  FROM clientes c
                  LEFT JOIN paises p ON c.paisID = p.paisID
                  $where
                  ORDER BY c.clienteID DESC
                  LIMIT $limite OFFSET $offset";

    $clientes = $db->obtenerTodo($sql_datos, $params);

    echo json_encode([
        'status' => 'ok',
        'clientes' => $clientes ?: [],
        'pagina' => $pagina,
        'total_paginas' => $total_paginas,
        'total_registros' => $total_registros,
        'limite' => $limite
    ]);
    exit;
}

// ============================================================
// 3. RENDERIZADO DE LA PÁGINA PRINCIPAL
// ============================================================
require_once("../../libreria_menu.php");
require_once("utils/hospedajes_utilidades.php");

verificarSesion();

// Obtener países para el formulario de edición y selector
$paises = $db->obtenerTodo("SELECT paisID, nombre FROM paises WHERE _estado <> 'X' ORDER BY nombre ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes - Hospedajes</title>
    <link rel="stylesheet" href="utils/hospedajes_estilos.css">
    <style>
        .card {
            margin: 20px;
        }
        thead {
            color: #000;
            background: #b5b5b5;
        }
        .modal-dialog-custom {
            max-width: 650px !important;
            margin: 2rem auto !important;
            top: auto !important;
        }
        .badge-age {
            font-size: 0.75rem;
            background-color: #e2e8f0;
            color: #334155;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .btn-edit-action {
            background: none;
            border: none;
            color: #0d6efd;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background 0.2s;
        }
        .btn-edit-action:hover {
            background: #e0f2fe;
            color: #0369a1;
        }
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
    </style>
</head>
<body>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-address-book text-primary me-2"></i>CLIENTES</h3>
                <small class="text-muted">Listado general de clientes registrados y modificación de datos</small>
            </div>
            <div>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cargarClientes(1)" title="Refrescar lista">
                    <i class="fas fa-sync-alt me-1"></i> Actualizar
                </button>
            </div>
        </div>

        <div class="card-body">
            <!-- FILTROS DE BÚSQUEDA RÁPIDA -->
            <div class="card bg-light border-0 mb-3 m-0">
                <div class="card-body py-3 px-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3 col-sm-6">
                            <label class="small fw-bold text-secondary mb-1">C.I.:</label>
                            <input type="text" id="filtroCI" class="form-control form-control-sm" placeholder="Buscar por C.I...." oninput="debounceBuscar()">
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <label class="small fw-bold text-secondary mb-1">Nombres:</label>
                            <input type="text" id="filtroNombres" class="form-control form-control-sm" placeholder="Buscar por nombres..." oninput="debounceBuscar()">
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <label class="small fw-bold text-secondary mb-1">Apellidos:</label>
                            <input type="text" id="filtroApellidos" class="form-control form-control-sm" placeholder="Buscar por apellidos..." oninput="debounceBuscar()">
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <label class="small fw-bold text-secondary mb-1">País:</label>
                            <select id="filtroPais" class="form-control form-control-sm" onchange="cargarClientes(1)">
                                <option value="">-- Todos los países --</option>
                                <?php foreach ($paises as $p): ?>
                                    <option value="<?= $p['paisID'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CONTENEDOR TABLA -->
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0" id="tablaClientes">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th style="width: 120px;">C.I.</th>
                            <th style="width: 140px;">País</th>
                            <th>Nombres</th>
                            <th>1er Apellido</th>
                            <th>2do Apellido</th>
                            <th>Fecha Nacimiento</th>
                            <th>Lugar Nacimiento / Procedencia</th>
                            <th class="text-center" style="width: 90px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyClientes">
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <span class="spinner-border spinner-border-sm me-2 text-primary"></span> Cargando clientes...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- PAGINACIÓN Y RESUMEN -->
            <div class="pagination-container mt-3 px-1">
                <div class="small text-muted" id="infoPaginacion">
                    Mostrando 0 registros
                </div>
                <div class="btn-group btn-group-sm" id="botonesPaginacion" role="group">
                    <!-- Botones generados dinámicamente -->
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR CLIENTE -->
    <div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-labelledby="modalEditarClienteLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-custom modal-lg">
            <div class="modal-content shadow border-0">
                <div class="modal-header bg-primary text-white py-2 px-3">
                    <h5 class="modal-title fs-6 fw-bold" id="modalEditarClienteLabel">
                        <i class="fas fa-user-edit me-2"></i>MODIFICAR DATOS DEL CLIENTE
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditarCliente" onsubmit="guardarModificacionCliente(event)">
                    <div class="modal-body p-3">
                        <input type="hidden" name="accion" value="modificar">
                        <input type="hidden" name="clienteID" id="edit_clienteID">

                        <div id="alertaModal" class="alert alert-danger py-2 px-3 small fw-bold" style="display:none;"></div>

                        <div class="row g-2">
                            <!-- C.I. y País -->
                            <div class="col-md-6">
                                <label class="small fw-bold mb-1">(*) C.I.:</label>
                                <input type="text" name="ci" id="edit_ci" class="form-control form-control-sm" required onkeyup="this.value=this.value.toUpperCase()">
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold mb-1">(*) País / Nacionalidad:</label>
                                <select name="paisID" id="edit_paisID" class="form-control form-control-sm" required>
                                    <option value="">-- Seleccionar País --</option>
                                    <?php foreach ($paises as $p): ?>
                                        <option value="<?= $p['paisID'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Nombres -->
                            <div class="col-12">
                                <label class="small fw-bold mb-1">(*) Nombres:</label>
                                <input type="text" name="nombres" id="edit_nombres" class="form-control form-control-sm" required onkeyup="this.value=this.value.toUpperCase()">
                            </div>

                            <!-- Apellidos -->
                            <div class="col-md-6">
                                <label class="small fw-bold mb-1">(*) Primer Apellido (Paterno):</label>
                                <input type="text" name="apellido1" id="edit_apellido1" class="form-control form-control-sm" required onkeyup="this.value=this.value.toUpperCase()">
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold mb-1">Segundo Apellido (Materno):</label>
                                <input type="text" name="apellido2" id="edit_apellido2" class="form-control form-control-sm" onkeyup="this.value=this.value.toUpperCase()">
                            </div>

                            <!-- Fecha Nacimiento y Lugar -->
                            <div class="col-md-6">
                                <label class="small fw-bold mb-1">(*) Fecha de Nacimiento:</label>
                                <input type="date" name="fecha_nacimiento" id="edit_fecha_nacimiento" class="form-control form-control-sm" required onchange="actualizarEdadModal()">
                                <div id="edit_edad_calculada" class="text-primary small fw-bold mt-1"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold mb-1">(*) Lugar Nacimiento / Procedencia:</label>
                                <input type="text" name="lugar_nacimiento" id="edit_lugar_nacimiento" class="form-control form-control-sm" required onkeyup="this.value=this.value.toUpperCase()" placeholder="Ej: TARIJA, LA PAZ, etc.">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-3">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </button>
                        <button type="submit" id="btnGuardarModificacion" class="btn btn-primary btn-sm fw-bold">
                            <i class="fas fa-save me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT DEL MÓDULO -->
    <script>
        let paginaActual = 1;
        let timerBusqueda = null;
        let modalEditarInstance = null;
        let clientesCache = [];

        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('modalEditarCliente');
            if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                modalEditarInstance = new bootstrap.Modal(modalEl);
            }
            cargarClientes(1);
        });

        // Búsqueda con debounce para no saturar con cada letra
        function debounceBuscar() {
            clearTimeout(timerBusqueda);
            timerBusqueda = setTimeout(function() {
                cargarClientes(1);
            }, 300);
        }

        // Carga AJAX de clientes con filtros y paginación
        function cargarClientes(pagina = 1) {
            paginaActual = pagina;
            const ci = document.getElementById('filtroCI').value.trim();
            const nombres = document.getElementById('filtroNombres').value.trim();
            const apellidos = document.getElementById('filtroApellidos').value.trim();
            const paisID = document.getElementById('filtroPais').value;

            const tbody = document.getElementById('tbodyClientes');
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">
                        <span class="spinner-border spinner-border-sm me-2 text-primary"></span> Cargando clientes...
                    </td>
                </tr>
            `;

            const params = new URLSearchParams({
                ajax: '1',
                pagina: paginaActual,
                ci: ci,
                nombres: nombres,
                apellidos: apellidos,
                paisID: paisID
            });

            fetch('clientes.php?' + params.toString())
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'ok') {
                        clientesCache = data.clientes || [];
                        renderizarTabla(clientesCache, data.total_registros, data.pagina, data.total_paginas, data.limite);
                    } else {
                        tbody.innerHTML = `<tr><td colspan="9" class="text-center text-danger py-4">Error al obtener clientes</td></tr>`;
                    }
                })
                .catch(() => {
                    tbody.innerHTML = `<tr><td colspan="9" class="text-center text-danger py-4">Error de conexión al cargar datos</td></tr>`;
                });
        }

        // Calcular edad en años
        function calcularEdad(fechaStr) {
            if (!fechaStr || fechaStr === '0000-00-00') return '-';
            const fNac = new Date(fechaStr + 'T00:00:00');
            const hoy = new Date();
            let edad = hoy.getFullYear() - fNac.getFullYear();
            const mes = hoy.getMonth() - fNac.getMonth();
            if (mes < 0 || (mes === 0 && hoy.getDate() < fNac.getDate())) {
                edad--;
            }
            return isNaN(edad) ? '-' : (edad + ' años');
        }

        // Renderizar la tabla y los controles de paginación
        function renderizarTabla(clientes, total, pagina, totalPaginas, limite) {
            const tbody = document.getElementById('tbodyClientes');
            if (!clientes || clientes.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="fas fa-info-circle me-1"></i> No se encontraron clientes con los filtros ingresados.
                        </td>
                    </tr>
                `;
                document.getElementById('infoPaginacion').innerText = 'Mostrando 0 de 0 registros';
                document.getElementById('botonesPaginacion').innerHTML = '';
                return;
            }

            let html = '';
            const offset = (pagina - 1) * limite;
            clientes.forEach((c, idx) => {
                const edadStr = calcularEdad(c.fecha_nacimiento);
                const ap2 = c.apellido2 ? c.apellido2 : '<span class="text-muted">-</span>';
                html += `
                    <tr>
                        <td class="text-center text-muted small">${offset + idx + 1}</td>
                        <td class="fw-bold">${escapeHtml(c.ci)}</td>
                        <td><span class="badge bg-light text-dark border">${escapeHtml(c.pais_nombre)}</span></td>
                        <td class="fw-semibold">${escapeHtml(c.nombres)}</td>
                        <td>${escapeHtml(c.apellido1)}</td>
                        <td>${ap2}</td>
                        <td>
                            <span>${escapeHtml(c.fecha_nacimiento)}</span>
                            <span class="badge-age ms-1">${edadStr}</span>
                        </td>
                        <td>${escapeHtml(c.lugar_nacimiento)}</td>
                        <td class="text-center">
                            <button type="button" class="btn-edit-action" title="Modificar Cliente" onclick="abrirModalEditar(${c.clienteID})">
                                <i class="fas fa-pencil-alt fa-lg"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;

            // Información de paginación
            const desde = offset + 1;
            const hasta = Math.min(offset + clientes.length, total);
            document.getElementById('infoPaginacion').innerText = `Mostrando ${desde} a ${hasta} de ${total.toLocaleString()} registros`;

            // Botones de paginación
            renderizarBotonesPaginacion(pagina, totalPaginas);
        }

        // Renderizar botones de paginación
        function renderizarBotonesPaginacion(paginaActual, totalPaginas) {
            const container = document.getElementById('botonesPaginacion');
            if (totalPaginas <= 1) {
                container.innerHTML = '';
                return;
            }

            let html = '';
            // Botón anterior
            html += `<button type="button" class="btn btn-outline-secondary ${paginaActual <= 1 ? 'disabled' : ''}" onclick="cargarClientes(${paginaActual - 1})"><i class="fas fa-chevron-left"></i></button>`;

            // Rango de páginas a mostrar (máximo 5 botones)
            let start = Math.max(1, paginaActual - 2);
            let end = Math.min(totalPaginas, start + 4);
            if (end - start < 4) {
                start = Math.max(1, end - 4);
            }

            if (start > 1) {
                html += `<button type="button" class="btn btn-outline-secondary" onclick="cargarClientes(1)">1</button>`;
                if (start > 2) html += `<button type="button" class="btn btn-outline-secondary disabled">...</button>`;
            }

            for (let p = start; p <= end; p++) {
                html += `<button type="button" class="btn ${p === paginaActual ? 'btn-primary active' : 'btn-outline-secondary'}" onclick="cargarClientes(${p})">${p}</button>`;
            }

            if (end < totalPaginas) {
                if (end < totalPaginas - 1) html += `<button type="button" class="btn btn-outline-secondary disabled">...</button>`;
                html += `<button type="button" class="btn btn-outline-secondary" onclick="cargarClientes(${totalPaginas})">${totalPaginas}</button>`;
            }

            // Botón siguiente
            html += `<button type="button" class="btn btn-outline-secondary ${paginaActual >= totalPaginas ? 'disabled' : ''}" onclick="cargarClientes(${paginaActual + 1})"><i class="fas fa-chevron-right"></i></button>`;

            container.innerHTML = html;
        }

        // Abrir modal de edición con datos cargados
        function abrirModalEditar(clienteID) {
            const cliente = clientesCache.find(c => c.clienteID == clienteID);
            if (!cliente) return;

            document.getElementById('edit_clienteID').value = cliente.clienteID;
            document.getElementById('edit_ci').value = cliente.ci || '';
            document.getElementById('edit_paisID').value = cliente.paisID || '';
            document.getElementById('edit_nombres').value = cliente.nombres || '';
            document.getElementById('edit_apellido1').value = cliente.apellido1 || '';
            document.getElementById('edit_apellido2').value = cliente.apellido2 || '';
            document.getElementById('edit_fecha_nacimiento').value = cliente.fecha_nacimiento || '';
            document.getElementById('edit_lugar_nacimiento').value = cliente.lugar_nacimiento || '';

            actualizarEdadModal();
            document.getElementById('alertaModal').style.display = 'none';

            if (modalEditarInstance) {
                modalEditarInstance.show();
            } else {
                // Fallback con jQuery si bootstrap.Modal no estuviera activo
                $('#modalEditarCliente').modal('show');
            }
        }

        // Actualizar cálculo de edad en el modal
        function actualizarEdadModal() {
            const fechaVal = document.getElementById('edit_fecha_nacimiento').value;
            const contenedor = document.getElementById('edit_edad_calculada');
            if (fechaVal) {
                contenedor.innerText = 'Edad calculada: ' + calcularEdad(fechaVal);
            } else {
                contenedor.innerText = '';
            }
        }

        // Guardar cambios por AJAX
        function guardarModificacionCliente(e) {
            e.preventDefault();

            const form = document.getElementById('formEditarCliente');
            const alerta = document.getElementById('alertaModal');
            const btn = document.getElementById('btnGuardarModificacion');

            alerta.style.display = 'none';
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

            const formData = new FormData(form);

            fetch('clientes.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Cambios';

                if (data.status === 'ok') {
                    if (modalEditarInstance) {
                        modalEditarInstance.hide();
                    } else {
                        $('#modalEditarCliente').modal('hide');
                    }
                    // Recargar página actual de la lista
                    cargarClientes(paginaActual);
                } else {
                    alerta.innerText = data.mensaje || 'Error al guardar los cambios.';
                    alerta.style.display = 'block';
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Cambios';
                alerta.innerText = 'Error de conexión con el servidor al guardar.';
                alerta.style.display = 'block';
            });
        }

        // Utilidad para escapar texto HTML
        function escapeHtml(text) {
            if (text === null || text === undefined) return '';
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    </script>
</body>
</html>
