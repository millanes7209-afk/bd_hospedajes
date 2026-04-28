<?php
/**
 * FASE 1 - ULALA Migration
 * Creación de tablas nuevas: cuentas, ingresos, ingreso_pagos, egresos, egreso_pagos
 */
require 'conexion.php';

$sqls = [

// -------------------------------------------------------
// TABLA: cuentas
// -------------------------------------------------------
"CREATE TABLE cuentas (
    _fec_insercion    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    _fec_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    _usuario          INT NOT NULL,
    _estado           CHAR(1) NOT NULL DEFAULT 'A',
    cuentaID          INT AUTO_INCREMENT PRIMARY KEY,
    empresaID         INT NOT NULL,
    codigo            VARCHAR(10) NOT NULL,
    nombre            VARCHAR(100) NOT NULL,
    tipo              ENUM('INGRESO','EGRESO') NOT NULL,
    FOREIGN KEY (empresaID) REFERENCES empresa(empresaID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// -------------------------------------------------------
// TABLA: ingresos
// -------------------------------------------------------
"CREATE TABLE ingresos (
    _fec_insercion    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    _fec_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    _usuario          INT NOT NULL,
    _estado           CHAR(1) NOT NULL DEFAULT 'A',
    ingresoID         INT AUTO_INCREMENT PRIMARY KEY,
    empresaID         INT NOT NULL,
    cajaID            INT NOT NULL,
    cuentaID          INT NOT NULL,
    usuarioID         INT NOT NULL,
    monto_total       DECIMAL(10,2) NOT NULL,
    concepto          VARCHAR(200) NULL,
    entregado         TINYINT NOT NULL DEFAULT 0,
    fecha_entrega     TIMESTAMP NULL,
    recaudacionID     INT NULL,
    fecha             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresaID)     REFERENCES empresa(empresaID),
    FOREIGN KEY (cajaID)        REFERENCES cajas(cajaID),
    FOREIGN KEY (cuentaID)      REFERENCES cuentas(cuentaID),
    FOREIGN KEY (recaudacionID) REFERENCES recaudaciones(recaudacionID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// -------------------------------------------------------
// TABLA: ingreso_pagos
// -------------------------------------------------------
"CREATE TABLE ingreso_pagos (
    ingresopagoID INT AUTO_INCREMENT PRIMARY KEY,
    ingresoID     INT NOT NULL,
    formapagoID   INT NOT NULL,
    monto         DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (ingresoID)   REFERENCES ingresos(ingresoID),
    FOREIGN KEY (formapagoID) REFERENCES formas_pago(formapagoID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// -------------------------------------------------------
// TABLA: egresos
// -------------------------------------------------------
"CREATE TABLE egresos (
    _fec_insercion    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    _fec_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    _usuario          INT NOT NULL,
    _estado           CHAR(1) NOT NULL DEFAULT 'A',
    egresoID          INT AUTO_INCREMENT PRIMARY KEY,
    empresaID         INT NOT NULL,
    cajaID            INT NOT NULL,
    cuentaID          INT NOT NULL,
    usuarioID         INT NOT NULL,
    monto_total       DECIMAL(10,2) NOT NULL,
    concepto          VARCHAR(200) NULL,
    fecha             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresaID) REFERENCES empresa(empresaID),
    FOREIGN KEY (cajaID)    REFERENCES cajas(cajaID),
    FOREIGN KEY (cuentaID)  REFERENCES cuentas(cuentaID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// -------------------------------------------------------
// TABLA: egreso_pagos
// -------------------------------------------------------
"CREATE TABLE egreso_pagos (
    egresopagoID INT AUTO_INCREMENT PRIMARY KEY,
    egresoID     INT NOT NULL,
    formapagoID  INT NOT NULL,
    monto        DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (egresoID)    REFERENCES egresos(egresoID),
    FOREIGN KEY (formapagoID) REFERENCES formas_pago(formapagoID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

];

$exitosos = 0;
foreach ($sqls as $sql) {
    // Extraer nombre de tabla del SQL para el log
    preg_match('/CREATE TABLE (\w+)/', $sql, $m);
    $tabla = $m[1] ?? '?';
    $resultado = $db->ejecutar($sql);
    if ($resultado !== false) {
        echo "✅ Tabla '$tabla' creada correctamente.\n";
        $exitosos++;
    } else {
        echo "❌ Error al crear tabla '$tabla'.\n";
    }
}

echo "\n--- Resultado: $exitosos/" . count($sqls) . " tablas creadas ---\n";
