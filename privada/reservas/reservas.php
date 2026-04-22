<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");
//$db->debug=true;
// Consulta para obtener los datos de las reservas
$sql = $db->Prepare("\n    SELECT r.reservaID, CONCAT_WS(' ', c.apellidos, c.nombres) as cliente, r.checkin, r.fecha_reserva,\n           h.numero AS habitacion_numero, r.monto_reserva, r.estado\n    FROM reservas r\n    JOIN clientes c ON r.clienteID = c.clienteID\n    JOIN habitaciones h ON r.habitacionID = h.habitacionID\n    WHERE r._estado <> 'X'\n    ORDER BY r.reservaID DESC\n");

$rs = $db->GetAll($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Reservas</title>
    <style>
        thead {
            color: black;
            background: #b5b5b5;
        }
        .card {
            margin: 20px;
        }
        tr {
            color: black;
        }
        .btn-accion {
            margin-right: 5px;
        }
        /* Estilos del modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            outline: 0;
        }

        .modal-dialog {
            position: relative;
            margin: 10px;
            max-width: 500px;
            margin: auto;
            top: 20%;
        }

        .modal-content {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.3rem;
        }

        .modal-header, .modal-footer {
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
        }

        .modal-body {
            padding: 1rem;
        }

        .modal-header .close {
            border: none;
            background: transparent;
        }

        .btn {
            padding: 0.375rem 0.75rem;
            border-radius: 0.25rem;
            display: inline-block;
            text-align: center;
            cursor: pointer;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn:hover {
            opacity: 0.8;
        }

        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1040;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="card-header">
            <h3>GESTIÓN RESERVAS</h3>
        </div>
    
        <div class="card-body">
            <div id="mensaje"></div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Cliente</th>
                            <th scope="col">Fecha Reserva</th>
                            <th scope="col">Check-in</th>
                            <th scope="col">Habitación</th>
                            <th scope="col">Monto</th>
                            <th scope="col">Estado</th>
                            <th scope="col"><img src='../../imagenes/modificar.gif' alt='Modificar'></th>
                            <th scope="col"><img src='../../imagenes/borrar.jpeg' alt='Eliminar'></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($rs) : ?>
                    <?php foreach ($rs as $fila) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($fila['cliente']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($fila['fecha_reserva'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($fila['checkin'])); ?></td>
                            <td style="text-align: center;"><?php echo htmlspecialchars($fila['habitacion_numero']); ?></td>
                            <td style="text-align: center;"><?php echo htmlspecialchars($fila['monto_reserva']); ?></td>
                            <td><?php echo htmlspecialchars($fila['estado']); ?></td>
                            <td>
                                <form name="formModif<?php echo $fila['reservaID']; ?>" method="post" action="reserva_modificar.php" style="display:inline;">
                                    <input type="hidden" name="reservaID" value="<?php echo $fila['reservaID']; ?>">
                                    <button type="submit" class="btn btn-sm btn-primary btn-accion">Modificar</button>
                                </form>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-danger btn-accion eliminar-reserva" data-reservaid="<?php echo $fila['reservaID']; ?>" data-cliente="<?php echo htmlspecialchars($fila['cliente']); ?>">Eliminar</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación -->
    <div class="modal" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <!-- Contenido del modal -->
          <div class="modal-header">
            <h5 class="modal-title">Confirmación de Eliminación</h5>
            <button type="button" class="close" id="closeModalBtn" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            ¿Está seguro de que desea eliminar la reserva de <span id="clienteNombre"></span>?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="cancelModalBtn">Cancelar</button>
            <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
          </div>
        </div>
      </div>
    </div>

    <script>
    function showModal() {
        const modal = document.getElementById('confirmModal');
        modal.style.display = 'block';

        if (!document.querySelector('.modal-backdrop')) {
            document.body.insertAdjacentHTML('beforeend', '<div class="modal-backdrop"></div>');
        }
    }

    function hideModal() {
        const modal = document.getElementById('confirmModal');
        modal.style.display = 'none';

        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
    }

    document.querySelectorAll('.eliminar-reserva').forEach(boton => {
        boton.addEventListener('click', function(event) {
            event.preventDefault();

            const reservaID = this.getAttribute('data-reservaid');
            const cliente = this.getAttribute('data-cliente');

            document.getElementById('clienteNombre').textContent = cliente;

            showModal();

            document.getElementById('confirmDeleteBtn').onclick = function() {
                fetch('reserva_eliminar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ reservaID: reservaID })
                })
                .then(response => response.json())
                .then(data => {
                    const mensajeDiv = document.getElementById('mensaje');
                    mensajeDiv.innerHTML = `<div class="alert alert-${data.tipo}">${data.mensaje}</div>`;
                    
                    if (data.tipo === 'success') {
                        setTimeout(() => { window.location.reload(); }, 2000);
                    }

                    hideModal();
                })
                .catch(error => console.error('Error:', error));
            };
        });
    });

    document.getElementById('closeModalBtn').addEventListener('click', hideModal);
    document.getElementById('cancelModalBtn').addEventListener('click', hideModal);
    </script>

</body>
</html>
