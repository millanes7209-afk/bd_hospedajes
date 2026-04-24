<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

if (!isset($_SESSION["sesion_id_usuario"])) {
    header("Location: ../../index.php");
    exit();
}

$usuarioID = $_SESSION['sesion_id_usuario'];

// Obtener datos completos del perfil
$sql = "SELECT e.*, ee.sueldo, ee.fecha_inicio, ee.fecha_fin, r.rol AS cargo, u.usuario
        FROM empleados e
        INNER JOIN usuarios u ON e.empleadoID = u.empleadoID
        LEFT JOIN empleado_empresas ee ON e.empleadoID = ee.empleadoID AND ee.estado_laboral = 'ACTIVO'
        LEFT JOIN roles r ON ee.rolID = r.rolID
        WHERE u.usuarioID = ? 
        LIMIT 1";

$rs = $db->obtenerTodo($sql, [$usuarioID]);
$datos = $rs[0] ?? null;

if (!$datos) {
    echo "Error: No se encontraron datos de perfil.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - Dulces Sueños</title>
    <style>
        .profile-card {
            max-width: 800px;
            margin: 30px auto;
            border: none;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: bold;
            color: #333;
        }

        .info-label {
            font-weight: bold;
            color: #666;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .info-value {
            font-size: 1.1rem;
            margin-bottom: 15px;
            color: #000;
        }

        .pass-container {
            position: relative;
        }

        .toggle-pass {
            position: absolute;
            right: 25px;
            top: 40px;
            cursor: pointer;
            color: #666;
            z-index: 10;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-user-circle me-2"></i>MI PERFIL DE USUARIO</h4>
            </div>
            <div class="card-body p-4">

                <div class="row">
                    <!-- COLUMNA 1: DATOS PERSONALES -->
                    <div class="col-md-4 border-end">
                        <h5 class="section-title"><i class="fas fa-id-card me-2 text-primary"></i>Datos Personales</h5>
                        <div class="info-label">Nombre Completo</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($datos['apellidos'] . " " . $datos['nombres']); ?>
                        </div>

                        <div class="info-label">C.I. / Documento</div>
                        <div class="info-value"><?php echo htmlspecialchars($datos['ci']); ?></div>

                        <div class="info-label">Teléfono</div>
                        <div class="info-value">
                            <?php echo !empty($datos['telefono']) ? htmlspecialchars($datos['telefono']) : 'No registrado'; ?>
                        </div>
                    </div>

                    <!-- COLUMNA 2: DATOS LABORALES -->
                    <div class="col-md-4 border-end ps-md-4">
                        <h5 class="section-title"><i class="fas fa-briefcase me-2 text-success"></i>Información Laboral
                        </h5>
                        <div class="info-label">Cargo Actual</div>
                        <div class="info-value"><?php echo htmlspecialchars($datos['cargo'] ?? 'Sin cargo asignado'); ?>
                        </div>

                        <div class="info-label">Sueldo</div>
                        <div class="info-value">Bs. <?php echo number_format($datos['sueldo'] ?? 0, 2, ',', '.'); ?>
                        </div>

                        <div class="info-label">Fecha de Ingreso</div>
                        <div class="info-value">
                            <?php echo !empty($datos['fecha_inicio']) ? date("d/m/Y", strtotime($datos['fecha_inicio'])) : '-'; ?>
                        </div>

                        <div class="info-label">Fin de Contrato</div>
                        <div class="info-value">
                            <?php echo !empty($datos['fecha_fin']) ? date("d/m/Y", strtotime($datos['fecha_fin'])) : '<span class="text-success fw-bold">INDEFINIDO</span>'; ?>
                        </div>
                    </div>

                    <!-- COLUMNA 3: SEGURIDAD Y ACCESO -->
                    <div class="col-md-4 ps-md-4">
                        <h5 class="section-title"><i class="fas fa-lock me-2 text-danger"></i>Seguridad y Acceso</h5>

                        <div id="alertPlaceholder"></div>

                        <form id="formCambiarClave">
                            <div class="mb-3">
                                <label class="info-label">Nombre de Usuario</label>
                                <input type="text" class="form-control bg-light"
                                    value="<?php echo htmlspecialchars($datos['usuario']); ?>" readonly>
                            </div>

                            <div class="mb-3 pass-container">
                                <label for="nueva_clave" class="info-label fw-bold">Nueva Contraseña</label>
                                <input type="password" id="nueva_clave" class="form-control border-dark"
                                    placeholder="Ingrese nueva clave...">
                                <i class="fas fa-eye toggle-pass" onclick="toggleVisibility('nueva_clave', this)" style="top: 40px;"></i>
                            </div>

                            <button type="submit" class="btn btn-danger w-100 fw-bold">
                                <i class="fas fa-save me-2"></i>ACTUALIZAR MI CLAVE
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleVisibility(id, icon) {
            const input = document.getElementById(id);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.replace("fa-eye-slash", "fa-eye");
            }
        }

        document.getElementById('formCambiarClave').addEventListener('submit', function (e) {
            e.preventDefault();
            const pass = document.getElementById('nueva_clave').value;
            const placeholder = document.getElementById('alertPlaceholder');

            if (pass.length < 4) {
                placeholder.innerHTML = '<div class="alert alert-warning py-2 mb-3">La contraseña debe tener al menos 4 caracteres.</div>';
                return;
            }

            fetch('ajax_cambiar_mi_clave.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `nueva_clave=${encodeURIComponent(pass)}`
            })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'SUCCESS') {
                        placeholder.innerHTML = '<div class="alert alert-success">¡Contraseña actualizada correctamente!</div>';
                        document.getElementById('formCambiarClave').reset();
                    } else {
                        placeholder.innerHTML = `<div class="alert alert-danger">Error: ${data.message}</div>`;
                    }
                });
        });
    </script>
</body>

</html>