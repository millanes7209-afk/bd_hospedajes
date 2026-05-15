<?php
session_start();
require_once("conexion.php");

if ((isset($_POST["accion"])) and ($_POST["accion"] == "Ingresar")) {
    $nick = $_POST["nick"];
    $password = $_POST["password"];

    // 1. Buscamos la clave y el ID en la tabla 'usuarios'
    $sql1 = "SELECT usuarioID, clave FROM usuarios WHERE usuario = ? AND _estado <> 'X'";
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

        if ($rs2) {
            // Guardamos datos básicos del usuario y empleado
            $_SESSION["sesion_id_usuario"] = $rs1[0]["usuarioID"] ?? 0;
            $_SESSION["sesion_usuario"] = $nick;
            $_SESSION["sesion_nom_completo"] = $nom_completo;
            $_SESSION["sesion_id_empleado"] = $rs2[0]["empleadoID"];

            // Verificamos si es administrador global para el selector de empresas
            $sql_admin = "SELECT 1 FROM usuarios_roles ur 
                         INNER JOIN roles r ON ur.rolID = r.rolID 
                         WHERE ur.usuarioID = ? AND r.rol = 'ADMINISTRADOR' AND ur._estado <> 'X'";
            $res_admin = $db->obtenerFila($sql_admin, [$_SESSION["sesion_id_usuario"]]);
            $_SESSION["sesion_es_admin"] = $res_admin ? true : false;
            
            // Vamos directo al selector de empresas
            header("Location: selector_empresa.php");
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