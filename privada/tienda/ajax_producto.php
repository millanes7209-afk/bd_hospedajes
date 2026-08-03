<?php
session_start();
require_once("../../conexion.php");

header('Content-Type: application/json');

$accion = $_GET['accion'] ?? '';
$empresaID = $_SESSION['empresaID'] ?? null;
$usuarioID = $_SESSION['sesion_id_usuario'] ?? null;

if (!$empresaID || !$usuarioID) {
    echo json_encode(['status' => 'error', 'mensaje' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $nombre = mb_strtoupper(trim($_POST['nombre'] ?? ''), 'UTF-8');
        $medida = trim($_POST['medida'] ?? '1');
        $stock = (int) ($_POST['stock'] ?? 0);
        $precio_venta = (float) ($_POST['precio_venta'] ?? 0);

        if (empty($nombre)) {
            echo json_encode(['status' => 'error', 'mensaje' => 'El nombre es obligatorio']);
            exit;
        }

        // Default non-null image string to avoid MySQL strict mode "Column 'imagen' cannot be null" error on host
        $imagen = 'img/tienda/default.jpg';
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $baseDir = __DIR__ . '/../../img/tienda/';
            if (!file_exists($baseDir)) {
                @mkdir($baseDir, 0777, true);
            }

            $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $filename = uniqid() . '.' . $extension;
            $filepath = $baseDir . $filename;

            if (@move_uploaded_file($_FILES['imagen']['tmp_name'], $filepath)) {
                $imagen = 'img/tienda/' . $filename;
            }
        }

        try {
            $sql = "INSERT INTO productos (empresaID, nombre, medida, stock, precio_venta, imagen, _estado, _fec_insercion, _usuario)
                    VALUES (?, ?, ?, ?, ?, ?, 'A', NOW(), ?)";
            $db->ejecutar($sql, [$empresaID, $nombre, $medida, $stock, $precio_venta, $imagen, $usuarioID]);

            echo json_encode(['status' => 'ok', 'mensaje' => 'Producto creado exitosamente']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Error al crear producto: ' . $e->getMessage()]);
        }
    }
} elseif ($accion === 'listar') {
    try {
        $sql = "SELECT productoID, nombre, medida, stock, precio_venta, imagen
                FROM productos
                WHERE empresaID = ? AND _estado = 'A'
                ORDER BY nombre";
        $productos = $db->obtenerTodo($sql, [$empresaID]);

        echo json_encode(['status' => 'ok', 'data' => $productos]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'mensaje' => 'Error al listar productos: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'mensaje' => 'Acción no válida']);
}
