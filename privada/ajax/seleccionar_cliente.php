<?php
session_start();
header('Content-Type: application/json');
require_once("../../conexion.php");

$clienteID = $_POST['clienteID'] ?? 0;

$response = [];

try {
    if ($clienteID) {
        $sql = $db->Prepare("SELECT ci, nombres, apellidos FROM clientes WHERE clienteID = ?");
        $result = $db->GetRow($sql, array($clienteID));

        if ($result) {
            $response = [
                'clienteID' => $clienteID,
                'ci' => $result['ci'],
                'nombre_completo' => $result['nombres'] . ' ' . $result['apellidos']
            ];
        } else {
            // Si no se encuentra el cliente, envía una respuesta apropiada
            $response = ['error' => 'No se encontró el cliente con el ID especificado.'];
        }
    } else {
        // Si no se envió un clienteID válido
        $response = ['error' => 'ID de cliente no válido.'];
    }
} catch (Exception $e) {
    $response = ['error' => 'Error al buscar el cliente: ' . $e->getMessage()];
}

echo json_encode($response);
?>
