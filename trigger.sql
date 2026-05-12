-- TRIGER PARA CREAR un nuEVO ACCESO A LA NUEVA OPCIÓN QUE SE CREE ----------------------------------------------------------------

DELIMITER //

CREATE TRIGGER after_insert_opciones
AFTER INSERT ON opciones
FOR EACH ROW
BEGIN
    INSERT INTO accesos (_fec_insercion, _fec_modificacion, _usuario, _estado, id_rol, id_opcion)
    VALUES (NEW._fec_insercion, NEW._fec_modificacion, NEW._usuario, NEW._estado, 1, NEW.id_opcion);
END //

DELIMITER ;

-- TRIGER PARA CREAR UN NUEVO ROL CUANDO SE CREA UN NUEVO CARGO ----------------------------------------------------------------


DELIMITER $$

CREATE TRIGGER after_cargo_insert
AFTER INSERT ON cargos
FOR EACH ROW
BEGIN
    -- Inserta un nuevo rol con el mismo nombre que el cargo recién creado
    INSERT INTO roles (_fec_insercion, _usuario, _estado, rol)
    VALUES (NEW._fec_insercion, NEW._usuario, NEW._estado, NEW.cargo);
END$$

DELIMITER ;

-- TRIGER PARA ASIGNAR UN ROL A UN USUARIO----------------------------------------------------------------

DELIMITER $$

CREATE TRIGGER asignar_rol_al_crear_usuario
AFTER INSERT ON usuarios
FOR EACH ROW
BEGIN
    DECLARE rol_id INT;

    -- Buscar el rol asociado al cargo de la persona
    SELECT r.id_rol 
    INTO rol_id
    FROM personas p
    JOIN cargos c ON p.cargoID = c.cargoID
    JOIN roles r ON r.rol = c.cargo
    WHERE p.id_persona = NEW.id_persona
      AND p._estado = 'A'
      AND r._estado = 'A';

    -- Si el rol fue encontrado, insertarlo en la tabla usuarios_roles
    IF rol_id IS NOT NULL THEN
        INSERT INTO usuarios_roles (_fec_insercion, _fec_modificacion, _usuario, _estado, id_rol, id_usuario)
        VALUES (NOW(), NOW(), NEW._usuario, 'A', rol_id, NEW.id_usuario);
    END IF;
END$$

DELIMITER ;

-- TRIGER PARA LAS RESERVAS --------------------------------------------------------------------------------------------------------
DELIMITER //

CREATE TRIGGER actualizar_estado_habitacion_reservada
AFTER INSERT ON reservas
FOR EACH ROW
BEGIN
    -- Actualiza el estado de la habitación a 'RESERVADA' cuando se inserta una nueva reserva
    UPDATE habitaciones
    SET estado = 'RESERVADA'
    WHERE habitacionID = NEW.habitacionID;
END//

DELIMITER ;


-- TRIGER aCTUALIZACIÖN DE ESTADO DE HABITACIÓN DESPUÉS DE UN HOSPEDAJE--------------------------------------------------------------------------------------------------------
DELIMITER //

CREATE TRIGGER actualizar_estado_habitacion_ocupada
AFTER INSERT ON hospedajes
FOR EACH ROW
BEGIN
    -- Actualiza el estado de la habitación a 'OCUPADA' cuando se inserta un nuevo hospedaje
    UPDATE habitaciones
    SET estado = 'OCUPADA'
    WHERE habitacionID = NEW.habitacionID;
END//

DELIMITER ;


-- TRIGGER PARa ACTUALIZAR LA TABLA INGRESOS CUANDO SE MODIFICA UN HOSPEDAJE--------------------------------------------------------------------------------------------------------

DELIMITER //

CREATE TRIGGER actualizar_ingreso
AFTER UPDATE ON hospedajes
FOR EACH ROW
BEGIN
    -- Verificar si el usuario que modifica el hospedaje es el mismo
    IF OLD._usuario = NEW._usuario THEN
        -- Si el monto pendiente cambió, actualizamos monto y forma de pago
        IF OLD.monto_pendiente <> NEW.monto_pendiente THEN
            UPDATE ingresos
            SET monto = NEW.monto_pendiente, formaPagoID = NEW.formaPagoID
            WHERE hospedajeID = NEW.hospedajeID;
        -- Si solo cambió la forma de pago, actualizamos solo la forma de pago
        ELSEIF OLD.formaPagoID <> NEW.formaPagoID THEN
            UPDATE ingresos
            SET formaPagoID = NEW.formaPagoID
            WHERE hospedajeID = NEW.hospedajeID;
        END IF;
    END IF;
END //

DELIMITER ;

-- TRIGGER PARa ACTUALIZAR LA TABLA movimientos_caja CUANDO SE MODIFICA UN ingreso--------------------------------------------------------------------------------------------------------


DELIMITER //

CREATE TRIGGER actualizar_movimimiento_caja
AFTER UPDATE ON ingresos
FOR EACH ROW
BEGIN
    -- Si el monto cambió, actualiza el monto y la forma de pago
    IF OLD.monto <> NEW.monto THEN
        UPDATE movimientos_caja
        SET monto = NEW.monto, formaPagoID = NEW.formaPagoID
        WHERE ingresoID = NEW.ingresoID;
    -- Si solo cambió la forma de pago, actualiza solo la forma de pago
    ELSEIF OLD.formaPagoID <> NEW.formaPagoID THEN
        UPDATE movimientos_caja
        SET formaPagoID = NEW.formaPagoID
        WHERE ingresoID = NEW.ingresoID;
    END IF;
END //

DELIMITER ;


-- TRIGGER MODIFICAR EL ESTADO DE LAS HABITACIONES UN VEZ CAMBIADA LA HABITACIÓN --------------------------------------------------------------------------------------------------------

DELIMITER //

CREATE TRIGGER after_hospedaje_update
AFTER UPDATE ON hospedajes
FOR EACH ROW
BEGIN
    -- Solo proceder si el ID de la habitación ha cambiado
    IF OLD.habitacionID <> NEW.habitacionID THEN
        -- Cambiar el estado de la nueva habitación a 'OCUPADA'
        UPDATE habitaciones
        SET estado = 'OCUPADA'
        WHERE habitacionID = NEW.habitacionID;

        -- Cambiar el estado de la antigua habitación a 'LIMPIEZA'
        UPDATE habitaciones
        SET estado = 'LIMPIEZA'
        WHERE habitacionID = OLD.habitacionID;
    END IF;
END;
//

DELIMITER ;


-- TRIGER PARA INSERCIÓN DE INGRESOS CUANDO SE INSERTA UNA NUEVA RESERVA --------------------------------------------------------------------------------------------------------
DELIMITER //

CREATE TRIGGER registrar_ingreso_reserva 
AFTER INSERT ON reservas 
FOR EACH ROW 
BEGIN 
    IF (NEW.monto_pagado > 0) THEN 
        -- Insertar el ingreso usando el monto_pagado de la tabla reservas 
        INSERT INTO ingresos (_fec_insercion, _fec_modificacion, _usuario, _estado, monto, formaPagoID, reservaID) 
        VALUES (NOW(), NOW(), NEW._usuario, 'A', NEW.monto_pagado, NEW.formaPagoID, NEW.reservaID); 
    END IF; 
END //

DELIMITER ;

-- EVENTO PARA ACTUALIZACIÓN DE ESTADOS CHECKOUT --------------------------------------------------------------------------------------------------------
CREATE EVENT revisar_habitaciones_vencidas
ON SCHEDULE EVERY 1 MINUTE
DO
    UPDATE habitaciones h
    JOIN hospedajes hos ON h.habitacionID = hos.habitacionID
    SET h.estado = 'DEUDA'
    WHERE TIMESTAMPDIFF(SECOND, hos.checkout, NOW()) > 0 -- Verifica que la hora actual sea mayor al checkout
    AND hos.estado = 'ACTIVO'  -- Solo cambiar si el hospedaje está ACTIVO
    AND h.estado = 'OCUPADA';  -- Solo habitaciones actualmente ocupadas


-- EVENTO PARA ACTUALIZACIÓN DE ESTADOS CHECKOUT --------------------------------------------------------------------------------------------------------
CREATE EVENT revisar_habitaciones_novencidas
ON SCHEDULE EVERY 1 MINUTE
DO
    UPDATE habitaciones h
    JOIN hospedajes hos ON h.habitacionID = hos.habitacionID
    SET h.estado = 'OCUPADA'
    WHERE TIMESTAMPDIFF(SECOND, hos.checkout, NOW()) < 0 -- Verifica que la hora actual sea mayor al checkout
    AND hos.estado = 'ACTIVO'  -- Solo cambiar si el hospedaje está ACTIVO
    AND h.estado = 'DEUDA';  -- Solo habitaciones actualmente ocupadas


-- TRIGGER QUE REGISTRA UN INGRESO EN LA TABLA movimiento_cajas --------------------------------------------------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER after_ingreso_insert 
AFTER INSERT ON ingresos 
FOR EACH ROW
BEGIN
    INSERT INTO movimientos_caja (_fec_insercion, _usuario, _estado,cajaID, tipo_movimiento, descripcion, monto, fecha_hora,formaPagoID,ingresoID)
    VALUES (NOW(), NEW._usuario, 'A', NEW.cajaID, 'INGRESO', NEW.tipo, NEW.monto, NOW(),NEW.formaPagoID,NEW.ingresoID);
END $$
DELIMITER ;


-- TRIGGER QUE REGISTRA UN EGRESO EN LA TABLA movimiento_cajas --------------------------------------------------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER after_egreso_insert AFTER INSERT ON egresos FOR EACH ROW
BEGIN
    INSERT INTO movimientos_caja (_fec_insercion, _usuario, _estado,cajaID, tipo_movimiento, descripcion, monto, fecha_hora,egresoID,formaPagoID)
    VALUES (NOW(), NEW._usuario, 'A', NEW.cajaID, 'EGRESO', NEW.descripcion, NEW.monto, NOW(),NEW.egresoID,NEW.formaPagoID);
END $$
DELIMITER ;



-- registras visitas cuando se registran hospedajes(indirectametne desde hospedajes clientes) --------------------------------
DELIMITER //

CREATE TRIGGER after_hospedaje_cliente_insert
AFTER INSERT ON hospedajes_clientes
FOR EACH ROW
BEGIN
    INSERT INTO visitas (clienteID, hospedajeID, fecha,_fec_insercion, _fec_modificacion, _usuario, _estado)
    VALUES (NEW.clienteID, NEW.hospedajeID, NOW(),NEW._fec_insercion, NEW._fec_modificacion, NEW._usuario, NEW._estado);
END //

DELIMITER ;


-- TRIGER aCTUALIZACIÖN DE ESTADO DE HABITACIÓN DESPUÉS ELIMINAR UN HOSPEDAJE--------------------------------------------------------------------------------------------------------
DELIMITER //

CREATE TRIGGER actualizar_estado_habitacion_limpieza
AFTER UPDATE ON hospedajes
FOR EACH ROW
BEGIN
    -- Solo proceder si el hospedaje ha sido eliminado (borrado lógico)
    IF OLD._estado = 'A' AND NEW._estado = 'X' THEN
        -- Actualiza el estado de la habitación a 'LIMPIEZA'
        UPDATE habitaciones
        SET estado = 'LIMPIEZA'
        WHERE habitacionID = NEW.habitacionID;
    END IF;
END//

DELIMITER ;


-- vistas ----------------------------------------------------------------------------------------------------------------------------------------------------------------
CREATE VIEW     vista_empresa AS 
SELECT          logo_agencia,nombre
FROM            empresa
WHERE           _estado = 'A';

-- notificaciones opcionales ----------------------------------------------------------------
/*
DELIMITER $$

CREATE EVENT generar_notificaciones_factura_diaria
ON SCHEDULE EVERY 1 DAY
STARTS '2025-01-07 10:00:00'  -- La primera vez que se ejecuta el evento (10 AM)
DO
BEGIN
    DECLARE hora1 TIME;
    DECLARE hora2 TIME;
    DECLARE hora3 TIME;
    
    -- Generar tres horas aleatorias entre las 10 AM y 10 PM
    SET hora1 = ADDTIME('10:00:00', SEC_TO_TIME(FLOOR(RAND() * (12 * 60 * 60))));  -- Hora aleatoria entre 10 AM y 10 PM
    SET hora2 = ADDTIME('10:00:00', SEC_TO_TIME(FLOOR(RAND() * (12 * 60 * 60))));
    SET hora3 = ADDTIME('10:00:00', SEC_TO_TIME(FLOOR(RAND() * (12 * 60 * 60))));

    -- Insertar las tres notificaciones con las horas generadas
    INSERT INTO notificaciones (mensaje, fecha_programada, estado, tipo)
    VALUES 
        ('Emitir factura', CONCAT(CURDATE(), ' ', hora1), 'pendiente', 'emitir_factura'),
        ('Emitir factura', CONCAT(CURDATE(), ' ', hora2), 'pendiente', 'emitir_factura'),
        ('Emitir factura', CONCAT(CURDATE(), ' ', hora3), 'pendiente', 'emitir_factura');
END$$

DELIMITER ;
*/