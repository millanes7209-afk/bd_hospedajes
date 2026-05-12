<?php
session_start();
require_once("conexion.php");

if ((isset($_POST["accion"])) and ($_POST["accion"] == "Ingresar")) {
    $nick = $_POST["nick"];
    $password = $_POST["password"];

    // 1. Buscamos la clave en la tabla 'usuarios' (antes era password, ahora es clave)
    $sql1 = "SELECT clave FROM usuarios WHERE usuario = ? AND _estado <> 'X'";
    $rs1 = $db->obtenerTodo($sql1, array($nick));

    if ($rs1) {
        $clave_bd = $rs1[0]["clave"];
    } else {
        $clave_bd = 0;
    }

    // Verificar la contraseña (password_verify asume que está encriptada en la BD)
    if (password_verify($password, $clave_bd)) {
        
        // 2. Obtener datos del EMPLEADO (antes era 'personas')
        // Ajustado a: empleadoID, nombres, apellidos
        $sql2 = "SELECT e.* FROM empleados e, usuarios u 
                              WHERE u.usuario = ? 
                              AND u.empleadoID = e.empleadoID 
                              AND e._estado <> 'X' 
                              AND u._estado <> 'X'";
        $rs2 = $db->obtenerTodo($sql2, array($nick));
        
        if ($rs2) {
            $nombres = $rs2[0]["nombres"];
            $apellidos = $rs2[0]["apellidos"];
            $nom_completo = $nombres . " " . $apellidos;
        } else {
            $nom_completo = 'Usuario Sistema';
        }

        // 3. Obtener los roles (Ajustado a: usuarioID, rolID, usuario_roles)
        $sql ="SELECT u.*, ur.rolID, r.rol 
                             FROM usuarios u 
                             INNER JOIN usuarios_roles ur ON u.usuarioID = ur.usuarioID 
                             INNER JOIN roles r ON ur.rolID = r.rolID 
                             WHERE u.usuario = ? 
                             AND u._estado <> 'X' 
                             AND ur._estado <> 'X' 
                             AND r._estado <> 'X'";
        $rs = $db->obtenerTodo($sql, array($nick));

        if ($rs) {
            // Guardamos datos comunes
            $_SESSION["sesion_id_usuario"] = $rs[0]["usuarioID"];
            $_SESSION["sesion_usuario"] = $rs[0]["usuario"];
            $_SESSION["sesion_nom_completo"] = $nom_completo;
            $_SESSION["sesion_id_empleado"] = $rs[0]["empleadoID"];
            
            // Guardamos la lista de todos los roles disponibles
            $_SESSION["sesion_roles_disponibles"] = $rs;

            if (count($rs) > 1) {
                // Múltiples roles: Al selector
                header("Location: selector_rol.php");
            } else {
                // Un solo rol: Seteamos y entramos
                $_SESSION["sesion_id_rol"] = $rs[0]["rolID"];
                $_SESSION["sesion_rol"] = $rs[0]["rol"];
                header("Location: selector_empresa.php");
            }
            exit();
        }
    } else {
        // Error de login
        $_SESSION['mensaje'] = array('tipo' => 'danger', 'texto' => 'Usuario o contraseña incorrectos.');
        header("Location: index.php");
        exit();
    }
} else {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>