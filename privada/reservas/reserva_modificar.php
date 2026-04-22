<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Consulta para obtener los datos de la reserva
$reservaID = $_POST["reservaID"];
$sql = $db->Prepare("
    SELECT r.*, CONCAT_WS(' ', c.apellidos, c.nombres) as cliente, h.numero AS habitacion_numero, th.precio 
    FROM reservas r
    JOIN clientes c ON r.clienteID = c.clienteID
    JOIN habitaciones h ON r.habitacionID = h.habitacionID
    JOIN tipo_habitaciones th ON h.tipohabitacionID = th.tipohabitacionID
    WHERE r.reservaID = ? AND r._estado <> 'X'");
$rs = $db->GetRow($sql, array($reservaID));

// Consulta para obtener habitaciones disponibles, incluyendo la habitación actual
$sqlHabitaciones = $db->Prepare("
    SELECT h.habitacionID, h.numero, th.precio 
    FROM habitaciones h
    JOIN tipo_habitaciones th ON h.tipohabitacionID = th.tipohabitacionID
    WHERE (h.estado = 'DISPONIBLE' OR h.habitacionID = ?) AND h._estado <> 'X'");
$rsHabitaciones = $db->GetAll($sqlHabitaciones, array($rs['habitacionID']));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Modificar Reserva</title>
    <style>
        .form-control {
            border-color: black;
        }
        .card-body {
            padding: 25px;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="form-container">
                    <div class="card">
                        <div class="card-header">
                            <h3>MODIFICAR RESERVA</h3>
                        </div>
                        <div class="card-body">
                            <form class="needs-validation" novalidate action="reserva_modificar1.php" method="post" name="formu">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="cliente" class="form-label">Cliente</label>
                                        <input type="text" class="form-control" name="cliente" id="cliente" required value="<?= htmlspecialchars($rs['cliente']) ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="habitacionID" class="form-label">Habitación</label>
                                        <select name="habitacionID" class="form-control" required onchange="actualizarPrecio(this)">
                                            <?php foreach ($rsHabitaciones as $habitacion) { ?>
                                                <option value="<?= $habitacion['habitacionID'] ?>" 
                                                    <?= $habitacion['habitacionID'] == $rs['habitacionID'] ? 'selected' : '' ?> data-precio="<?= $habitacion['precio'] ?>">
                                                    <?= htmlspecialchars($habitacion['numero']) ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Otros campos necesarios -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="checkin" class="form-label">Check-in</label>
                                        <input type="date" class="form-control" name="checkin" id="checkin" required value="<?= date('Y-m-d', strtotime($rs['checkin'])) ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="monto_reserva" class="form-label">Monto Reserva</label>
                                        <input type="number" class="form-control" name="monto_reserva" id="monto_reserva" required value="<?= htmlspecialchars($rs['monto_reserva']) ?>" step="0.01" min="0" readonly>
                                    </div>
                                </div>

                                <input type="hidden" name="reservaID" value="<?= htmlspecialchars($rs['reservaID']) ?>">

                                <div class="row mb-3 text-center">
                                    <div class="col-md-12">
                                        <button class="btn btn-primary" type="submit">Modificar</button>
                                        <button class="btn btn-secondary" type="button" onclick="window.history.back()">Atrás</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function actualizarPrecio(select) {
            // Obtener el precio de la habitación seleccionada
            var precio = select.options[select.selectedIndex].getAttribute('data-precio');
            // Establecer el valor en el campo monto_reserva
            document.getElementById('monto_reserva').value = precio;
        }
    </script>
</body>
</html>
