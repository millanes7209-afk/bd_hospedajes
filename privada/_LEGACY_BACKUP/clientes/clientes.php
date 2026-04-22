<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Consulta SQL para obtener los clientes
$sql = "SELECT *, TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) as edad
        FROM clientes
        WHERE _estado <> 'X'
        ORDER BY clienteID DESC";

// Ejecutamos la consulta
$rs = $db->GetAll($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Clientes</title>
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
        .highlight {
            background-color: yellow;
            color: black;
            font-weight: bold;
        }

    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
           <h3>GESTIÓN CLIENTES</h3>
        </div>
        <div class="card-body">
            <div id="mensaje"></div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="cliente_nuevo.php" class="btn btn-success mb-3" role="button">Agregar Cliente</a>
            </div>
            <div class="form-group row">
                <div class="col-md-4">
                    <input type="text" id="buscarNombres" class="form-control" placeholder="Buscar Nombre">
                </div>
                <div class="col-md-4">
                    <input type="text" id="buscarApellidos" class="form-control" placeholder="Buscar Apellidos">
                </div>
                <div class="col-md-4">
                    <input type="text" id="buscarCI" class="form-control" placeholder="Buscar CI">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            
                            <th scope="col">CI</th>
                            <th scope="col">Nombres</th>
                            <th scope="col">Apellidos</th>
                            <th scope="col">Edad</th>
                            <th scope="col">Lugar de Nacimiento</th>
                            <th scope="col"><img src='../../imagenes/modificar.gif'></th>
                            <th scope="col"><img src='../../imagenes/borrar.jpeg'></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rs) : ?>
                            <?php $b = 1; ?>
                            <?php foreach ($rs as $fila) : ?>
                                <tr>
                                    
                                    <td><?php echo $fila['ci']; ?></td>
                                    <td><?php echo $fila['nombres']; ?></td>
                                    <td><?php echo $fila['apellidos']; ?></td>
                                    <td><?php echo $fila['edad']; ?></td>
                                    <td><?php echo $fila['lugar_nacimiento']; ?></td>
                                    <td>
                                        <form name="formModif<?php echo $fila['clienteID']; ?>" method="post" action="cliente_modificar.php" style="display:inline;">
                                            <input type="hidden" name="clienteID" value="<?php echo $fila['clienteID']; ?>">
                                            <button type="submit" class="btn btn-sm btn-primary">Modificar</button>
                                        </form>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger eliminar-cliente" data-clienteid="<?php echo $fila['clienteID']; ?>" data-nombre="<?php echo $fila['nombres'] . ' ' . $fila['apellidos']; ?>">Eliminar</button>
                                    </td>
                                </tr>
                            <?php $b++; ?>
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
            ¿Está seguro de que desea eliminar al cliente <span id="clienteNombre"></span>?
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

        document.querySelectorAll('.eliminar-cliente').forEach(boton => {
            boton.addEventListener('click', function(event) {
                event.preventDefault();

                const clienteID = this.getAttribute('data-clienteid');
                const cliente = this.getAttribute('data-nombre');

                document.getElementById('clienteNombre').textContent = cliente;

                showModal();

                document.getElementById('confirmDeleteBtn').onclick = function() {
                    fetch('cliente_eliminar.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ clienteID: clienteID })
                    })
                    .then(response => response.json())
                    .then(data => {
                        const mensajeDiv = document.getElementById('mensaje');
                        mensajeDiv.innerHTML = `<div class="alert alert-${data.tipo}">${data.mensaje}</div>`;
                        
                        if (data.tipo === 'success') {
                            setTimeout(() => { window.location.reload(); }, 2000);  // Recargar la página después de eliminar
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
    <script src="js/busquedas.js">    </script>
</body>
</html>