<?php
session_start();
require_once("../conexion.php");

// Verificar si hay sesión activa
if (!isset($_SESSION["sesion_id_usuario"])) {
    header("Location: ../../index.php");
    exit();
}

// Verificar si es administrador
if (strtoupper($_SESSION['sesion_rol']) !== 'ADMINISTRADOR') {
    header("Location: ../../selector_empresa.php");
    exit();
}

// Obtener colores de la primera empresa (para el diseño)
$sql_color = "SELECT color_primario, color_secundario FROM empresa LIMIT 1";
$stmt_color = $db->prepare($sql_color);
$stmt_color->execute();
$colores = $stmt_color->fetch();
$color_primario = $colores['color_primario'] ?? '#059669';
$color_secundario = $colores['color_secundario'] ?? '#ffffff';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nombre = $_POST['nombre'] ?? '';
        $color_primario = $_POST['color_primario'] ?? '#059669';
        $color_secundario = $_POST['color_secundario'] ?? '#ffffff';
        
        if (empty($nombre)) {
            throw new Exception("El nombre de la empresa es obligatorio");
        }
        
        // Insertar nueva empresa
        $sql = "INSERT INTO empresa (nombre, color_primario, color_secundario, _fec_insercion, _usuario, _estado) 
                VALUES (?, ?, ?, NOW(), ?, 'A')";
        
        $stmt = $db->prepare($sql);
        $resultado = $stmt->execute([$nombre, $color_primario, $color_secundario, $_SESSION['sesion_id_usuario']]);
        
        if ($resultado) {
            $empresaID = $db->lastInsertId();
            
            // El trigger automáticamente creará el contrato para el administrador (empleadoID = 1)
            
            // Redirigir al selector de empresas con mensaje de éxito
            header("Location: ../../selector_empresa.php?mensaje=Empresa creada exitosamente");
            exit();
        } else {
            throw new Exception("Error al crear la empresa");
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Nueva Empresa - Sistema Web Dulces Sueños</title>
    <link href='../../bootstrap/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .header-section {
            background-color: <?php echo $color_primario; ?>;
            color: <?php echo $color_secundario; ?>;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .form-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 5px solid <?php echo $color_primario; ?>;
        }
        .btn-primary {
            background-color: <?php echo $color_primario; ?>;
            border-color: <?php echo $color_primario; ?>;
            color: <?php echo $color_secundario; ?>;
        }
        .btn-primary:hover {
            opacity: 0.9;
        }
        .color-preview {
            width: 40px;
            height: 40px;
            border-radius: 5px;
            border: 2px solid #dee2e6;
            display: inline-block;
            vertical-align: middle;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header-section text-center'>
            <h1 class='display-4'><i class='fas fa-building me-3'></i>Nueva Empresa</h1>
            <p class='lead mb-0'>Crea una nueva empresa en el sistema</p>
        </div>

        <div class='row justify-content-center'>
            <div class='col-lg-6'>
                <div class='form-card'>
                    <?php if (isset($error)): ?>
                        <div class='alert alert-danger'>
                            <h5 class='alert-heading'><i class='fas fa-exclamation-circle me-2'></i>Error</h5>
                            <p class='mb-0'><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    <?php endif; ?>

                    <form method='post' action=''>
                        <div class='mb-3'>
                            <label for='nombre' class='form-label'>
                                <i class='fas fa-building me-2'></i>Nombre de la Empresa <span class='text-danger'>*</span>
                            </label>
                            <input type='text' class='form-control' id='nombre' name='nombre' 
                                   value='<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>' 
                                   required maxlength='100'>
                            <small class='text-muted'>Ingresa el nombre completo de la empresa</small>
                        </div>

                        <div class='row'>
                            <div class='col-md-6'>
                                <div class='mb-3'>
                                    <label for='color_primario' class='form-label'>
                                        <i class='fas fa-palette me-2'></i>Color Primario
                                    </label>
                                    <div class='d-flex align-items-center'>
                                        <input type='color' class='form-control form-control-color' id='color_primario' 
                                               name='color_primario' value='<?php echo htmlspecialchars($_POST['color_primario'] ?? '#059669'); ?>'>
                                        <span class='color-preview' id='preview_primario' style='background-color: <?php echo htmlspecialchars($_POST['color_primario'] ?? '#059669'); ?>;'></span>
                                    </div>
                                    <small class='text-muted'>Color principal para la interfaz</small>
                                </div>
                            </div>
                            <div class='col-md-6'>
                                <div class='mb-3'>
                                    <label for='color_secundario' class='form-label'>
                                        <i class='fas fa-palette me-2'></i>Color Secundario
                                    </label>
                                    <div class='d-flex align-items-center'>
                                        <input type='color' class='form-control form-control-color' id='color_secundario' 
                                               name='color_secundario' value='<?php echo htmlspecialchars($_POST['color_secundario'] ?? '#ffffff'); ?>'>
                                        <span class='color-preview' id='preview_secundario' style='background-color: <?php echo htmlspecialchars($_POST['color_secundario'] ?? '#ffffff'); ?>;'></span>
                                    </div>
                                    <small class='text-muted'>Color para textos y elementos secundarios</small>
                                </div>
                            </div>
                        </div>

                        <div class='alert alert-info'>
                            <h6 class='alert-heading'><i class='fas fa-info-circle me-2'></i>Información importante</h6>
                            <p class='mb-2'>
                                <i class='fas fa-user-shield me-2'></i>Al crear la empresa, el administrador (empleadoID = 1) 
                                será asignado automáticamente con un contrato activo.
                            </p>
                            <p class='mb-0'>
                                <i class='fas fa-cog me-2'></i>El trigger se encargará de crear el contrato automáticamente 
                                con rol "ADMINISTRADOR" y estado "ACTIVO".
                            </p>
                        </div>

                        <div class='d-grid gap-2 d-md-flex justify-content-md-end'>
                            <a href='../../selector_empresa.php' class='btn btn-secondary'>
                                <i class='fas fa-arrow-left me-2'></i>Cancelar
                            </a>
                            <button type='submit' class='btn btn-primary'>
                                <i class='fas fa-save me-2'></i>Crear Empresa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Actualizar vistas previas de colores
        document.getElementById('color_primario').addEventListener('input', function() {
            document.getElementById('preview_primario').style.backgroundColor = this.value;
        });

        document.getElementById('color_secundario').addEventListener('input', function() {
            document.getElementById('preview_secundario').style.backgroundColor = this.value;
        });

        // Validación de formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre').value.trim();
            
            if (nombre === '') {
                e.preventDefault();
                alert('Por favor, ingresa el nombre de la empresa');
                return false;
            }
            
            if (nombre.length < 3) {
                e.preventDefault();
                alert('El nombre de la empresa debe tener al menos 3 caracteres');
                return false;
            }
        });
    </script>
</body>
</html>
