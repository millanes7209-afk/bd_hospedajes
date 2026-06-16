<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

$empresaID = $_SESSION['empresaID'];

// Consulta enfocada en la estética del sistema
$sql = "SELECT i.*, 
               CONCAT_WS(' ', c.apellido1, c.apellido2, c.nombres) as cliente_nombre,
               u.usuario as reportado_por,
               c.ci as cliente_ci
        FROM incidentes i
        INNER JOIN clientes c ON i.clienteID = c.clienteID
        INNER JOIN usuarios u ON i.usuarioID = u.usuarioID
        WHERE i.empresaID = ? AND i._estado <> 'X'
        ORDER BY i.fecha DESC, i.incidenteID DESC";

$incidentes = $db->obtenerTodo($sql, [$empresaID]);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Incidentes</title>
    <link rel="stylesheet" href="utils/hospedajes_estilos.css">
</head>

<body>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0">GESTIÓN DE INCIDENTES</h3>
        </div>

        <div class="card-body">
            <div class="form-group row align-items-end">
                <div class="col-md-9">
                    <label class="small fw-bold">Buscar por Cliente o Descripción:</label>
                    <input type="text" id="buscarTexto" class="form-control form-control-sm"
                        placeholder="Escriba nombre, CI o detalle del incidente..." onkeyup="filtrarIncidentes()">
                </div>
                <div class="col-md-3">
                    <a href="incidente_nuevo.php?auth=hospedajes.php" class="btn btn-primary btn-sm w-100 fw-bold">
                        <i class="fas fa-plus-circle"></i> NUEVO INCIDENTE
                    </a>
                </div>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-striped table-hover" id="tablaIncidentes">
                    <thead>
                        <tr>
                            <th scope="col">Fecha</th>
                            <th scope="col">Cliente / CI</th>
                            <th scope="col">Descripción Novedad</th>
                            <th scope="col">Reportado por</th>
                            <th scope="col" class="text-center">Estado</th>
                            <th colspan="2" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($incidentes): ?>
                            <?php foreach ($incidentes as $i):
                                $colorEstado = ($i['estado'] == 'PENDIENTE') ? 'text-danger' : (($i['estado'] == 'ACEPTADO') ? 'text-success' : 'text-muted');
                                ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($i['fecha'])) ?></td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($i['cliente_nombre']) ?></div>
                                        <small class="text-muted">CI: <?= htmlspecialchars($i['cliente_ci']) ?></small>
                                    </td>
                                    <td style="max-width: 300px;"><?= htmlspecialchars($i['descripcion']) ?></td>
                                    <td><?= htmlspecialchars($i['reportado_por']) ?></td>
                                    <td class="text-center fw-bold <?= $colorEstado ?>"><?= $i['estado'] ?></td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-4">
                                            <a href="incidente_modificar.php?id=<?= $i['incidenteID'] ?>&auth=hospedajes.php"
                                                title="Editar">
                                                <i class="fas fa-pencil-alt fa-lg" style="color: #0d6efd;"></i>
                                            </a>
                                            <a href="javascript:void(0)" onclick="eliminarIncidente(<?= $i['incidenteID'] ?>)"
                                                title="Eliminar">
                                                <i class="fas fa-trash-alt fa-lg" style="color: #dc3545;"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No se encontraron incidentes
                                    registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function filtrarIncidentes() {
            const texto = document.getElementById('buscarTexto').value.toLowerCase();
            const filas = document.querySelectorAll('#tablaIncidentes tbody tr');

            filas.forEach(fila => {
                if (fila.cells.length < 5) return;
                const contenido = fila.innerText.toLowerCase();
                fila.style.display = contenido.includes(texto) ? "" : "none";
            });
        }

        function eliminarIncidente(id) {
            if (confirm('¿Desea eliminar este incidente permanentemente?')) {
                window.location.href = 'incidente_eliminar.php?id=' + id + '&auth=hospedajes.php';
            }
        }
    </script>
</body>

</html>