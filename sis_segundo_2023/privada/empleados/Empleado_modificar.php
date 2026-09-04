<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

if (!isset($_SESSION["sesion_id_usuario"])) {
    header("Location: ../../index.php");
    exit();
}

$empleadoID = $_POST['empleadoID'] ?? null;
if (!$empleadoID) {
    header("Location: empleados.php");
    exit();
}

// Obtener datos del empleado
$sql = "SELECT * FROM empleados WHERE empleadoID = ? AND _estado <> 'X'";
$rs = $db->obtenerTodo($sql, [$empleadoID]);
$reg = $rs[0] ?? null;

if (!$reg) {
    echo "Empleado no encontrado.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Modificar Empleado - Dulces Sueños</title>
    <style>
        .edit-card {
            max-width: 900px;
            margin: 30px auto;
            border: none;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.15);
        }

        .form-label {
            font-weight: bold;
            color: #444;
        }

        .section-title {
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
            margin-bottom: 25px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container">
        <div class="card edit-card">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-user-edit me-2"></i>MODIFICAR FICHA DE EMPLEADO</h4>
                <a href="empleados.php" class="btn btn-light btn-sm fw-bold"><i class="fas fa-arrow-left"></i>
                    VOLVER</a>
            </div>
            <div class="card-body p-4">
                <div id="alertPlaceholder"></div>

                <form id="formModificarEmpleado">
                    <input type="hidden" name="empleadoID" value="<?php echo $empleadoID; ?>">

                    <h5 class="section-title"><i class="fas fa-id-badge me-2"></i>Datos de Identidad</h5>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombres</label>
                            <input type="text" name="nombres" class="form-control"
                                value="<?php echo htmlspecialchars($reg['nombres'] ?? ''); ?>" required
                                onkeyup="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Apellidos</label>
                            <input type="text" name="apellidos" class="form-control"
                                value="<?php echo htmlspecialchars($reg['apellidos'] ?? ''); ?>" required
                                onkeyup="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">C.I. / Documento</label>
                            <input type="text" name="ci" class="form-control fw-bold"
                                value="<?php echo htmlspecialchars($reg['ci'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Género</label>
                            <select name="genero" class="form-control" required>
                                <option value="M" <?php echo ($reg['genero'] == 'M') ? 'selected' : ''; ?>>MASCULINO
                                </option>
                                <option value="F" <?php echo ($reg['genero'] == 'F') ? 'selected' : ''; ?>>FEMENINO
                                </option>
                                <option value="O" <?php echo ($reg['genero'] == 'O') ? 'selected' : ''; ?>>OTRO</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Teléfono / Celular</label>
                            <input type="text" name="telefono" class="form-control"
                                value="<?php echo htmlspecialchars($reg['telefono'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="text-center mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-5 fw-bold btn-lg">
                            <i class="fas fa-save me-2"></i>GUARDAR CAMBIOS EN LA FICHA
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('formModificarEmpleado').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const placeholder = document.getElementById('alertPlaceholder');

            fetch('ajax_empleado_actualizar.php', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'SUCCESS') {
                        placeholder.innerHTML = '<div class="alert alert-success fw-bold"><i class="fas fa-check-circle"></i> ¡Datos actualizados correctamente! Redirigiendo...</div>';
                        setTimeout(() => { window.location.href = 'empleados.php'; }, 1500);
                    } else {
                        placeholder.innerHTML = `<div class="alert alert-danger fw-bold"><i class="fas fa-exclamation-circle"></i> Error: ${data.message}</div>`;
                    }
                })
                .catch(err => {
                    placeholder.innerHTML = '<div class="alert alert-danger fw-bold">Error de conexión al servidor.</div>';
                });
        });
    </script>
</body>

</html>