<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once("../../conexion.php");

$ci = isset($_GET['ci']) ? $_GET['ci'] : '';

if (empty($ci)) {
    echo json_encode(['status' => 'ERROR', 'message' => 'C.I. vacío']);
    exit;
}

try {
    // 1. Buscar si la Empleado existe
    $sql = "SELECT * FROM EMPLEADOS WHERE ci = ? AND estado = 'ACTIVO'";
    $stmt = $db->prepare($sql);
    $stmt->execute([$ci]);
    $Empleado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($Empleado) {
        // 2. Si existe la Empleado, buscar si ya es empleado
        $sql_emp = "SELECT * FROM empleados WHERE EmpleadoID = ? AND estado = 'ACTIVO'";
        $stmt_emp = $db->prepare($sql_emp);
        $stmt_emp->execute([$Empleado['EmpleadoID']]);
        $empleado = $stmt_emp->fetch(PDO::FETCH_ASSOC);

        if ($empleado) {
            // Ya es empleado, devolver datos para mostrar contrato/usuario
            echo json_encode([
                'status' => 'FOUND_EMPLOYEE',
                'Empleado' => $Empleado,
                'empleado' => $empleado
            ]);
        } else {
            // Existe como Empleado pero no es empleado, autocompletar formulario de registro
            echo json_encode([
                'status' => 'FOUND_PERSON',
                'Empleado' => $Empleado,
                'message' => 'La Empleado existe pero no es empleado aún.'
            ]);
        }
    } else {
        // No existe la Empleado, mostrar formulario vacío
        echo json_encode([
            'status' => 'NOT_FOUND',
            'message' => 'No se encontró ninguna Empleado con ese C.I.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
}
