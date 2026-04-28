<?php
require_once("conexion.php");

echo "=== INICIANDO OPTIMIZACIÓN DE ÍNDICES ===\n";

$indices_a_crear = [
    // Tabla: Empresa y Funcionalidades
    "empresa" => ["idx_empresa_estado" => "(_estado)"],
    "empresa_funcionalidades" => ["idx_empfunc_emp_func" => "(empresaID, funcionalidadID)"],
    
    // Tabla: Habitaciones
    "habitaciones" => [
        "idx_hab_emp_est" => "(empresaID, _estado)",
        "idx_hab_estado_hab" => "(estado)" // DISPONIBLE, OCUPADA, etc.
    ],
    
    // Tabla: Hospedajes
    "hospedajes" => [
        "idx_hosp_empresa" => "(empresaID)",
        "idx_hosp_habitacion" => "(habitacionID)",
        "idx_hosp_estado" => "(_estado)",
        "idx_hosp_cliente" => "(clienteID)"
    ],
    
    // Tabla: Movimientos de Caja / Pagos
    "movimientos" => [
        "idx_mov_emp_caja" => "(empresaID, cajaID)",
        "idx_mov_hospedaje" => "(hospedajeID)",
        "idx_mov_tipo" => "(tipo)" // INGRESO, EGRESO
    ],
    
    // Tabla: Clientes
    "clientes" => [
        "idx_cli_documento" => "(documento)" // Para búsquedas rápidas por DNI/RUC
    ]
];

foreach ($indices_a_crear as $tabla => $indices) {
    echo "\nRevisando tabla '$tabla'...\n";
    foreach ($indices as $nombre_indice => $columnas) {
        // Verificar si el índice existe (usamos try-catch genérico porque SHOW INDEX puede fallar silenciosamente en PDO simple, es más fácil intentar agregarlo)
        try {
            $db->ejecutar("ALTER TABLE $tabla ADD INDEX $nombre_indice $columnas");
            echo " [+] Índice '$nombre_indice' creado con éxito.\n";
        } catch (Exception $e) {
            echo " [!] El índice '$nombre_indice' ya existe o hubo un error al crearlo (Ignorado).\n";
        }
    }
}

echo "\n=== OPTIMIZACIÓN COMPLETADA ===\n";
?>
