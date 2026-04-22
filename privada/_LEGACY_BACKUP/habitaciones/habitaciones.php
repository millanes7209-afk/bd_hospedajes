<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");


// Consulta SQL para obtener las habitaciones
$cantidad = isset($_GET['cantidad']) ? intval($_GET['cantidad']) : 5;
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($pagina - 1) * $cantidad;

$sql = $db->Prepare("SELECT h.habitacionID, h.numero, th.tipo, th.precio, h.estado, h.tv, h.bano, h.ventilador
                     FROM habitaciones h
                     JOIN tipo_habitaciones th ON h.tipohabitacionID = th.tipohabitacionID
                     WHERE h._estado <> 'X'
                     AND th._estado <> 'X'
                     ORDER BY h.numero ASC 
                     LIMIT ? OFFSET ?");
$rs = $db->GetAll($sql, array($cantidad, $offset));
//---------------------------------------------------------------------------   
$sqlTotal = $db->Prepare("SELECT COUNT(*) as total
                          FROM habitaciones h
                          JOIN tipo_habitaciones th ON h.tipohabitacionID = th.tipohabitacionID
                          WHERE h._estado <> 'X'
                          AND th._estado <> 'X'");
$totalRegistros = $db->GetRow($sqlTotal)['total'];
$totalPaginas = ceil($totalRegistros / $cantidad);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Habitaciones</title>
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
    /* Estilos básicos del modal */
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
        max-width: 300px;
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
    .hab-link:hover {
        text-decoration: underline !important;
        color: #004085 !important;
        transform: scale(1.05);
        display: inline-block;
    }
</style>
</head>
<body>

    <div class="card">
        <div class="card-header">
           <h3>GESTIÓN HABITACIONES</h3>
        </div>
        <div class="card-body">
        <div id="mensaje"></div>

          <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="habitacion_nuevo.php" class="btn btn-success mb-3" role="button">Añadir Habitación</a>
          </div>
            <label for="cantidad">Mostrar:</label>
            <select id="cantidad" onchange="cargarRegistros()">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="25">25</option>
            </select>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">N°</th>
                            <th scope="col">Número</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">TV</th>
                            <th scope="col">Baño</th>
                            <th scope="col">Ventilador</th>
                            <th scope="col">Estado</th>
                            <th scope="col"><img src='../../imagenes/modificar.gif'></th>
                            <th scope="col"><img src='../../imagenes/borrar.jpeg'></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rs) : ?>
                        <?php 
                        $b = 0;
                        
                        ?>
                        <?php foreach ($rs as $fila) : ?>
                            <tr>
                                <td><?php echo $b; ?></td>
                                <td>
                                    <a href="javascript:void(0)" 
                                       onclick="redirigirHospedaje('<?php echo $fila['numero']; ?>', '<?php echo urlencode($fila['tipo']); ?>', '<?php echo $fila['precio']; ?>', '<?php echo $fila['habitacionID']; ?>')"
                                       class="fw-bold text-primary text-decoration-none hab-link" 
                                       title="Registrar Hospedaje en Hab. <?php echo $fila['numero']; ?>">
                                       <?php echo $fila['numero']; ?>
                                    </a>
                                </td>
                                <td><?php echo $fila['tipo']; ?></td> <!-- Aquí se muestra el tipo de habitación -->
                                <td><?php echo $fila['tv'] ? 'Sí' : 'No'; ?></td>
                                <td><?php echo $fila['bano'] ? 'Sí' : 'No'; ?></td>
                                <td><?php echo $fila['ventilador'] ? 'Sí' : 'No'; ?></td>
                                <td><?php echo $fila['estado']; ?></td>
                                <td>
                                    <form name="formModif<?php echo $fila['habitacionID']; ?>" method="post" action="habitacion_modificar.php" style="display:inline;">
                                        <input type="hidden" name="habitacionID" value="<?php echo $fila['habitacionID']; ?>">
                                        <button type="submit" class="btn btn-sm btn-primary">Modificar</button>
                                    </form>
                                </td>
                                <td>
                                    <!-- Cambiado el botón de eliminación a simple botón con data -->
                                    <button class="btn btn-sm btn-danger btn-accion eliminar-habitacion" data-habitacionid="<?php echo $fila['habitacionID']; ?>" data-numero="<?php echo $fila['numero']; ?>">Eliminar</button>
                                </td>
                        
                            </tr>
                        <?php $b++; ?>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="paginacion">
                <button onclick="cambiarPagina(<?php echo $pagina - 1; ?>)" <?php echo $pagina == 1 ? 'disabled' : ''; ?>>Anterior</button>
                <span>Página <?php echo $pagina; ?></span>
                <button onclick="cambiarPagina(<?php echo $pagina + 1; ?>)" <?php echo $pagina >= $totalPaginas ? 'disabled' : ''; ?>>Siguiente</button>
            </div>
        </div>
    </div>
    <!-- Modal de Confirmación -->
<div class="modal" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmModalLabel">Confirmación de Eliminación</h5>
        <button type="button" class="close" id="closeModalBtn" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        ¿Está seguro de que desea eliminar la habitación número <span id="habitacionNumero"></span>?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="cancelModalBtn">Cancelar</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
      </div>
    </div>
  </div>
</div>
</body>

<script>
document.querySelectorAll('.eliminar-habitacion').forEach(boton => {
    boton.addEventListener('click', function(event) {
        event.preventDefault(); // Evitar el comportamiento por defecto

        const habitacionID = this.getAttribute('data-habitacionid');
        const numero = this.getAttribute('data-numero');

        // Mostrar el número de la habitación en el modal
        document.getElementById('habitacionNumero').textContent = numero;

        // Abrir el modal de confirmación
        $('#confirmModal').modal('show');

        // Cuando se confirme la eliminación en el modal
        document.getElementById('confirmDeleteBtn').onclick = function() {
            fetch('habitacion_eliminar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ habitacionID: habitacionID })
            })
            .then(response => response.json())
            .then(data => {
                // Mostrar mensaje en la página
                const mensajeDiv = document.getElementById('mensaje');
                mensajeDiv.innerHTML = `<div class="alert alert-${data.tipo}">${data.mensaje}</div>`;
                
                // Si fue éxito, recargar la tabla después de unos segundos
                if (data.tipo === 'success') {
                    setTimeout(() => { window.location.reload(); }, 2000);
                }

                // Cerrar el modal
                $('#confirmModal').modal('hide');
            })
            .catch(error => console.error('Error:', error));
        };
    });
});

</script>
<script>
  function showModal() {
    const modal = document.getElementById('confirmModal');
    modal.style.display = 'block';

    // Verificar si ya existe un backdrop antes de agregar uno nuevo
    if (!document.querySelector('.modal-backdrop')) {
        document.body.insertAdjacentHTML('beforeend', '<div class="modal-backdrop"></div>');
    }
}

function hideModal() {
    const modal = document.getElementById('confirmModal');
    modal.style.display = 'none';

    // Eliminar el fondo oscuro del modal (modal-backdrop)
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) {
        backdrop.remove();
    }
}

// Event listeners para el modal
document.querySelectorAll('.eliminar-habitacion').forEach(boton => {
    boton.addEventListener('click', function(event) {
        event.preventDefault(); // Evitar el comportamiento por defecto

        const habitacionID = this.getAttribute('data-habitacionid');
        const numero = this.getAttribute('data-numero');

        // Mostrar el número de la habitación en el modal
        document.getElementById('habitacionNumero').textContent = numero;

        // Abrir el modal de confirmación
        showModal();

        // Cuando se confirme la eliminación en el modal
        document.getElementById('confirmDeleteBtn').onclick = function() {
            fetch('habitacion_eliminar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ habitacionID: habitacionID })
            })
            .then(response => response.json())
            .then(data => {
                // Mostrar mensaje en la página
                const mensajeDiv = document.getElementById('mensaje');
                mensajeDiv.innerHTML = `<div class="alert alert-${data.tipo}">${data.mensaje}</div>`;
                
                // Si fue éxito, recargar la tabla después de unos segundos
                if (data.tipo === 'success') {
                    setTimeout(() => { window.location.reload(); }, 2000);
                }

                // Cerrar el modal y eliminar el fondo oscuro
                hideModal();
            })
            .catch(error => console.error('Error:', error));
        };
    });
});

// Cerrar el modal al hacer clic en el botón de cancelar o la "X"
document.getElementById('closeModalBtn').addEventListener('click', hideModal);
document.getElementById('cancelModalBtn').addEventListener('click', hideModal);

/**
 * REDIRECCIÓN AL NUEVO MÓDULO DE HOSPEDAJE (Usa POST para limpiar la URL)
 */
function redirigirHospedaje(numero, tipo, precio, habitacionID) {
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '../hospedajes/hospedaje_nuevo.php';

    var params = {
        'numero': numero,
        'tipo': decodeURIComponent(tipo),
        'precio': precio,
        'habitacionID': habitacionID
    };

    for (var key in params) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = params[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
}
<script src="js/paginacion.js"></script>



</html>
