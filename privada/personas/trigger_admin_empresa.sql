-- Trigger para crear automáticamente contrato de administrador al crear nueva empresa
DELIMITER $$

CREATE TRIGGER tr_contrato_admin_empresa
AFTER INSERT ON empresas
FOR EACH ROW
BEGIN
    -- Insertar contrato para el empleado administrador (empleadoID = 1) en la nueva empresa
    INSERT INTO empleado_empresas (
        empleadoID,
        empresaID,
        rol,
        sueldo,
        fecha_inicio,
        fecha_fin,
        es_titular,
        estado_laboral,
        _fec_insercion,
        _fec_modificacion,
        _usuario,
        _estado
    ) VALUES (
        1,  -- empleadoID del administrador
        NEW.empresaID,  -- ID de la nueva empresa creada
        'ADMINISTRADOR',  -- Rol fijo para administrador
        0.00,  -- Sueldo inicial (puede ajustarse)
        NEW._fec_insercion,  -- Fecha de inicio igual a fecha de creación de empresa
        NULL,  -- Fecha fin NULL (contrato indefinido)
        1,  -- es_titular = 1 (es titular)
        'ACTIVO',  -- estado_laboral siempre ACTIVO
        NEW._fec_insercion,  -- Fecha de inserción
        NEW._fec_insercion,  -- Fecha de modificación inicial
        NEW._usuario,  -- Usuario que crea la empresa
        'A'  -- Estado activo
    );
END$$

DELIMITER ;

-- Comentarios adicionales:
-- Este trigger se ejecuta automáticamente después de cada INSERT en la tabla empresas
-- Crea un registro en empleado_empresas para el empleado con ID = 1 (administrador)
-- El contrato se crea con estado ACTIVO y rol ADMINISTRADOR
-- La fecha de inicio coincide con la fecha de creación de la empresa
-- El sueldo se establece en 0.00 y puede ser modificado posteriormente
