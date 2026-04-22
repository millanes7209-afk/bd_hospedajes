<?php
/**
 * Verifica si un usuario tiene una caja abierta en una empresa específica.
 */
function verificarCajaAbierta($db, $usuarioID, $empresaID) {
    
    // 1. LA CONSULTA SQL
    // Seleccionamos la caja que esté 'ABIERTA' para el usuario y la empresa indicados.
    $sql = "SELECT cajaID, empresaID 
            FROM cajas 
            WHERE estado = 'ABIERTA' 
            AND usuarioID = ? 
            AND empresaID = ? 
            AND _estado <> 'X'"; // Evitamos registros eliminados
    
    /**
     * 2. EJECUCIÓN
     * Usamos $usuarioID y $empresaID que vienen de los parámetros de la función.
     */
    $resultado = $db->obtenerFila($sql, array($usuarioID, $empresaID));
    
    // 3. RETORNO
    // Si se encontró una fila, devolvemos el ID de la caja. Si no, devolvemos null.
    return $resultado ? $resultado['cajaID'] : null;
}
?>