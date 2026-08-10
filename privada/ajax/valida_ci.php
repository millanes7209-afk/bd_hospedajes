<?php
session_start();
header('Content-Type: application/json');
require_once("../../conexion.php");

$ci = $_POST['ci'] ?? '';
$response = ['exists' => false];

try {
    if ($ci) {
        $sql = $db->Prepare("SELECT COUNT(*) as count FROM clientes WHERE ci = ? AND _estado <> 'X'");
        $result = $db->GetRow($sql, array($ci));

        if ($result['count'] > 0) {
            $response['exists'] = true;
        }
    }

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error al validar CI: ' . $e->getMessage()
    ]);
}
