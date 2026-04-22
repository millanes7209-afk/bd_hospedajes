<?php
session_start();
header('Content-Type: application/json');
require_once("../../conexion.php");

$ci = $_POST['ci'] ?? '';
$response = [];

try {
    if ($ci) {
        $sql = $db->Prepare("SELECT clienteID, ci, nombres, apellidos FROM clientes WHERE ci LIKE ? AND _estado <> 'X'");
        $result = $db->GetAll($sql, array($ci . "%"));

        foreach ($result as $row) {
            $response[] = [
                'clienteID' => $row['clienteID'],
                'ci' => $row['ci'],
                'nombre_completo' => $row['nombres'] . ' ' . $row['apellidos']
            ];
        }
    }

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error al buscar el cliente: ' . $e->getMessage()
    ]);
}
