<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Obtener lista de países
$sql_paises = "SELECT paisID, nombre FROM paises WHERE _estado <> 'X' ORDER BY nombre ASC";
$paises = $db->obtenerTodo($sql_paises);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Nuevo Incidente</title>
    <link rel="stylesheet" href="utils/hospedajes_estilos.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border: 2px solid #000;
            border-radius: 8px;
        }

        .search-results {
            position: absolute;
            background: white;
            border: 1px solid #ccc;
            width: 95%;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }

        .search-item {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }

        .search-item:hover {
            background: #f0f0f0;
        }

        .info-box {
            background: #e9ecef;
            border-left: 4px solid #000;
            padding: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h3 class="border-bottom pb-2 mb-3 text-uppercase fw-bold">Registrar Incidente</h3>

        <form action="incidente_insertar.php" method="POST" id="formIncidente">
            <input type="hidden" name="auth" value="hospedajes.php">
            <input type="hidden" name="estado" value="PENDIENTE"> <!-- Estado forzado a PENDIENTE -->

            <!-- BUSCADOR DE CLIENTE POR CI Y PAIS -->
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">País Emisor del Documento:</label>
                    <select id="paisID" name="paisID" class="form-control" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($paises as $p): ?>
                            <option value="<?= $p['paisID'] ?>" <?= $p['nombre'] == 'BOLIVIA' ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 position-relative">
                    <label class="form-label small fw-bold">C.I. del Cliente:</label>
                    <div class="input-group">
                        <input type="text" id="ciBusqueda" class="form-control form-control-sm"
                            placeholder="Ingrese CI..." autocomplete="off" required>
                        <button type="button" class="btn btn-dark btn-sm" onclick="buscarPorCI()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div id="resultadosBusqueda" class="search-results"></div>
                </div>
            </div>

            <div id="contenedorInfoCliente" style="display:none;">
                <div class="info-box mb-3">
                    <span class="small fw-bold d-block">CLIENTE SELECCIONADO:</span>
                    <span id="nombreClienteLabel" class="fs-5 fw-bold text-primary"></span>
                    <input type="hidden" name="clienteID" id="clienteID">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold">Fecha y Hora del Incidente:</label>
                <input type="datetime-local" name="fecha_hora" class="form-control" value="<?= date('Y-m-d\TH:i') ?>"
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold">Descripción / Novedad:</label>
                <textarea name="descripcion" class="form-control" rows="4"
                    placeholder="Detalle el comportamiento o daño causado por el cliente..." required></textarea>
            </div>

            <div id="alerta" class="alert alert-info small py-2 mb-3">
                <i class="fas fa-info-circle"></i> El registro se guardará como <strong>PENDIENTE</strong> para revisión
                de gerencia.
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-dark fw-bold">GUARDAR REGISTRO</button>
                <a href="incidentes.php" class="btn btn-outline-secondary">CANCELAR</a>
            </div>
        </form>
    </div>

    <script>
        const inputCI = document.getElementById('ciBusqueda');
        const selPais = document.getElementById('paisID');
        const resultados = document.getElementById('resultadosBusqueda');
        const infoCliente = document.getElementById('contenedorInfoCliente');
        const labelNombre = document.getElementById('nombreClienteLabel');
        const inputID = document.getElementById('clienteID');

        function buscarPorCI() {
            const ci = inputCI.value.trim();
            const pais = selPais.value;

            if (!ci || !pais) {
                alert('Debe ingresar un CI y seleccionar el País.');
                return;
            }

            const data = new FormData();
            data.append('ci', ci);
            data.append('paisID', pais);

            fetch('buscar_cliente_incidente.php', {
                method: 'POST',
                body: data
            })
                .then(r => r.text())
                .then(html => {
                    resultados.innerHTML = html;
                    resultados.style.display = 'block';
                });
        }

        function seleccionarCliente(clienteID, nombre, ci) {
            inputID.value = clienteID;
            labelNombre.innerText = nombre;
            infoCliente.style.display = 'block';
            resultados.style.display = 'none';
            inputCI.value = ci;
        }


        // Permitir búsqueda al presionar Enter en el CI
        inputCI.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarPorCI();
            }
        });

        document.getElementById('formIncidente').onsubmit = function () {
            if (!inputID.value) {
                alert('Debe buscar y seleccionar un cliente primero.');
                return false;
            }
            return true;
        };
    </script>
</body>

</html>