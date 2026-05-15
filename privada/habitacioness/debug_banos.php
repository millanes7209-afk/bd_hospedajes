<?php
session_start();
$base_dir = dirname(dirname(dirname(__FILE__)));
require_once($base_dir . "/conexion.php");

echo "<h3>Depuración de Sesión y Baños</h3>";
echo "Empresa ID en Sesión: " . ($_SESSION['empresaID'] ?? 'NO SET') . "<br>";
echo "Caja ID en Sesión: " . ($_SESSION['caja_abierta_id'] ?? 'NO SET') . "<br>";
echo "Usuario ID en Sesión: " . ($_SESSION['sesion_id_usuario'] ?? 'NO SET') . "<br>";

echo "<h4>Últimos 5 registros en tabla 'banos':</h4>";
try {
    $res = $db->obtenerTodo("SELECT * FROM banos ORDER BY banoID DESC LIMIT 5");
    if (!$res) {
        echo "No hay registros en la tabla 'banos'.";
    } else {
        echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
        echo "<tr><th>ID</th><th>Empresa</th><th>Caja</th><th>Usuario</th><th>Monto</th><th>Tipo</th><th>Fecha</th></tr>";
        foreach($res as $r) {
            echo "<tr>
                    <td>{$r['banoID']}</td>
                    <td>{$r['empresaID']}</td>
                    <td>{$r['cajaID']}</td>
                    <td>{$r['usuarioID']}</td>
                    <td>{$r['monto']}</td>
                    <td>{$r['tipo']}</td>
                    <td>{$r['fecha']}</td>
                  </tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "Error al consultar la tabla: " . $e->getMessage();
}
?>
