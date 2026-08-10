<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

$incidenteID = $_GET['id'];
$empresaID = $_SESSION['empresaID'];

$sql = "SELECT i.*, CONCAT_WS(' ', c.nombres, c.apellido1, c.apellido2) as cliente_nombre, c.ci
        FROM incidentes i
        INNER JOIN clientes c ON i.clienteID = c.clienteID
        WHERE i.incidenteID = ? AND i.empresaID = ?";

$incidente = $db->obtenerFila($sql, [$incidenteID, $empresaID]);

if (!$incidente) {
    echo "Incidente no encontrado o sin permisos.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Modificar Incidente</title>
    <link rel="stylesheet" href="utils/hospedajes_estilos.css">
    <style>
        .form-container {
            max-width: 650px;
            margin: 20px auto;
            background: #fff;
            padding: 25px;
            border: 2px solid #000;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h3 class="border-bottom pb-2 mb-3 text-uppercase fw-bold">Actualizar Incidente</h3>

        <form action="incidente_actualizar.php" method="POST">
            <input type="hidden" name="auth" value="hospedajes.php">
            <input type="hidden" name="incidenteID" value="<?= $incidente['incidenteID'] ?>">

            <div class="alert alert-secondary py-2">
                <small class="fw-bold d-block">CLIENTE:</small>
                <span>
                    <?= htmlspecialchars($incidente['cliente_nombre']) ?> (CI:
                    <?= $incidente['ci'] ?>)
                </span>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Fecha y Hora:</label>
                    <input type="datetime-local" name="fecha" class="form-control"
                        value="<?= date('Y-m-d\TH:i', strtotime($incidente['fecha'])) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Estado Actual:</label>
                    <select name="estado" class="form-select fw-bold">
                        <option value="PENDIENTE" <?= $incidente['estado'] == 'PENDIENTE' ? 'selected' : '' ?>>PENDIENTE
                        </option>
                        <option value="ACEPTADO" <?= $incidente['estado'] == 'ACEPTADO' ? 'selected' : '' ?>>ACEPTADO
                            (Cerrado)</option>
                        <option value="RECHAZADO" <?= $incidente['estado'] == 'RECHAZADO' ? 'selected' : '' ?>>RECHAZADO
                        </option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Descripción del Incidente:</label>
                <textarea name="descripcion" class="form-control" rows="3"
                    required><?= htmlspecialchars($incidente['descripcion']) ?></textarea>
            </div>

            <div class="mb-3 p-3" style="background: #f8f9fa; border-left: 5px solid #000;">
                <label class="form-label fw-bold text-primary">SOLUCIÓN / ACCIÓN TOMADA:</label>
                <textarea name="solucion" class="form-control" rows="3"
                    placeholder="Si el incidente se resolvió, detalle cómo..."><?= htmlspecialchars($incidente['solucion'] ?? '') ?></textarea>
                <small class="text-muted">Si rellena este campo, la fecha de atención se actualizará
                    automáticamente.</small>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-dark fw-bold">ACTUALIZAR DATOS</button>
                <a href="incidentes.php" class="btn btn-outline-secondary">CANCELAR</a>
            </div>
        </form>
    </div>
</body>

</html>