# ESTRUCTURA ACTUAL DE LA BASE DE DATOS

## Tabla: `accesos` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| accesoID | int(11) | NO | PRI |  | auto_increment |
| rolID | int(11) | NO | MUL |  |  |
| opcionID | int(11) | NO | MUL |  |  |

## Tabla: `auditorias` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| auditoriaID | int(11) | NO | PRI |  | auto_increment |
| hospedajeID | int(11) | NO | MUL |  |  |
| tipo_auditoria | enum('MODIFICACION','ELIMINACION') | YES |  | MODIFICACION |  |
| monto_anterior | decimal(15,2) | NO |  |  |  |
| monto_nuevo | decimal(15,2) | NO |  |  |  |
| detalle_original | text | YES |  |  |  |
| detalle_nuevo | text | YES |  |  |  |
| estado_revision | tinyint(4) | YES | MUL | 0 |  |
| empresaID | int(11) | NO | MUL |  |  |
| motivo | text | YES |  |  |  |
| usuarioID | int(11) | NO |  |  |  |
| fecha | datetime | NO |  |  |  |

## Tabla: `cajas` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| cajaID | int(11) | NO | PRI |  | auto_increment |
| empresaID | int(11) | NO | MUL |  |  |
| usuarioID | int(11) | NO | MUL |  |  |
| estado | enum('ABIERTA','CERRADA') | YES |  | CERRADA |  |
| fecha_apertura | datetime | YES |  | current_timestamp() |  |
| fecha_cierre | datetime | YES |  |  |  |
| observaciones | text | YES |  |  |  |

## Tabla: `cierre_cajas` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| cierrecajaID | int(11) | NO | PRI |  | auto_increment |
| cajaID | int(11) | NO | MUL |  |  |
| formapagoID | int(11) | NO | MUL |  |  |
| monto | decimal(12,2) | NO |  |  |  |

## Tabla: `clientes` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| clienteID | int(11) | NO | PRI |  | auto_increment |
| ci | varchar(15) | NO | UNI |  |  |
| nombres | varchar(50) | NO |  |  |  |
| apellido1 | varchar(30) | NO |  |  |  |
| apellido2 | varchar(30) | YES |  |  |  |
| fecha_nacimiento | date | NO |  |  |  |
| lugar_nacimiento | varchar(100) | NO |  |  |  |
| estado_civil | varchar(25) | YES |  |  |  |
| profesion | varchar(100) | YES |  |  |  |
| pais | varchar(100) | YES |  |  |  |
| paisID | int(11) | NO |  |  |  |

## Tabla: `cuentas` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| cuentaID | int(11) | NO | PRI |  | auto_increment |
| empresaID | int(11) | NO | MUL |  |  |
| codigo | varchar(10) | NO |  |  |  |
| nombre | varchar(100) | NO |  |  |  |
| tipo | enum('INGRESO','EGRESO') | NO |  |  |  |

## Tabla: `egreso_pagos` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| egresopagoID | int(11) | NO | PRI |  | auto_increment |
| egresoID | int(11) | NO | MUL |  |  |
| formapagoID | int(11) | NO | MUL |  |  |
| monto | decimal(10,2) | NO |  |  |  |

## Tabla: `egresos` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| egresoID | int(11) | NO | PRI |  | auto_increment |
| empresaID | int(11) | NO | MUL |  |  |
| cajaID | int(11) | NO | MUL |  |  |
| cuentaID | int(11) | NO | MUL |  |  |
| usuarioID | int(11) | NO |  |  |  |
| monto_total | decimal(10,2) | NO |  |  |  |
| concepto | varchar(200) | YES |  |  |  |
| fecha | timestamp | NO |  | current_timestamp() |  |
| entregado | tinyint(1) | YES |  | 0 |  |
| fecha_entrega | datetime | YES |  |  |  |
| recaudacionID | int(11) | YES | MUL |  |  |

## Tabla: `empleado_empresas` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| empleadoempresaID | int(11) | NO | PRI |  | auto_increment |
| empleadoID | int(11) | NO | MUL |  |  |
| rolID | int(11) | NO | MUL |  |  |
| empresaID | int(11) | NO | MUL |  |  |
| sueldo | decimal(15,2) | YES |  |  |  |
| fecha_inicio | date | NO |  |  |  |
| fecha_fin | date | YES |  |  |  |
| es_titular | tinyint(1) | YES |  | 0 |  |
| estado_laboral | enum('ACTIVO','INACTIVO','SUSPENDIDO') | NO |  | ACTIVO |  |

## Tabla: `empleados` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| empleadoID | int(11) | NO | PRI |  | auto_increment |
| ci | varchar(15) | NO | UNI |  |  |
| nombres | varchar(40) | NO |  |  |  |
| apellidos | varchar(40) | NO |  |  |  |
| telefono | varchar(15) | NO |  |  |  |
| genero | char(1) | NO |  |  |  |
| fecha_nacimiento | date | YES |  |  |  |

## Tabla: `empresa` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| empresaID | int(11) | NO | PRI |  | auto_increment |
| nombre | varchar(50) | NO |  |  |  |
| direccion | varchar(200) | NO |  |  |  |
| telefono | varchar(15) | NO |  |  |  |
| logo_agencia | varchar(100) | NO |  |  |  |
| color_primario | varchar(10) | YES |  | #059669 |  |
| color_secundario | varchar(10) | YES |  | #ffffff |  |
| ruc | varchar(20) | YES |  |  |  |
| representante_legal | varchar(100) | YES |  |  |  |

## Tabla: `empresa_funcionalidades` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| empresafuncionID | int(11) | NO | PRI |  | auto_increment |
| empresaID | int(11) | NO | MUL |  |  |
| funcionalidadID | int(11) | NO | MUL |  |  |
| fecha_activacion | date | YES |  | current_timestamp() |  |
| fecha_vencimiento | date | YES |  |  |  |
| estado | enum('ACTIVO','VENCIDO','CANCELADO') | YES |  | ACTIVO |  |

## Tabla: `formas_pago` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| formapagoID | int(11) | NO | PRI |  | auto_increment |
| empresaID | int(11) | NO | MUL |  |  |
| tipo | varchar(20) | NO |  |  |  |
| descripcion | varchar(100) | NO |  |  |  |

## Tabla: `funcionalidades` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| funcionalidadID | int(11) | NO | PRI |  | auto_increment |
| nombre | varchar(50) | NO | UNI |  |  |
| descripcion | text | YES |  |  |  |
| categoria | varchar(30) | NO |  |  |  |
| estado | enum('ACTIVO','INACTIVO') | YES |  | ACTIVO |  |

## Tabla: `grupos` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| grupoID | int(11) | NO | PRI |  | auto_increment |
| grupo | varchar(30) | NO |  |  |  |
| descripcion | varchar(200) | YES |  |  |  |

## Tabla: `habitaciones` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| habitacionID | int(11) | NO | PRI |  | auto_increment |
| tipohabitacionID | int(11) | NO | MUL |  |  |
| empresaID | int(11) | NO | MUL |  |  |
| numero | varchar(10) | NO |  |  |  |
| estado | enum('DISPONIBLE','OCUPADA','LIMPIEZA','MANTENIMIENTO','RESERVADA','MOMENTANEO') | YES | MUL | DISPONIBLE |  |
| descripcion | varchar(255) | YES |  |  |  |
| tv | tinyint(1) | NO |  | 0 |  |
| bano | tinyint(1) | NO |  | 0 |  |
| ventilador | tinyint(1) | NO |  | 0 |  |

## Tabla: `hospedajes` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| hospedajeID | int(11) | NO | PRI |  | auto_increment |
| empresaID | int(11) | NO | MUL |  |  |
| checkin | datetime | YES |  | current_timestamp() |  |
| checkout | datetime | NO |  |  |  |
| monto | decimal(15,2) | NO |  |  |  |
| estado | enum('ACTIVO','INACTIVO') | YES | MUL | ACTIVO |  |
| habitacionID | int(11) | NO | MUL |  |  |
| observaciones | text | YES |  |  |  |
| cajaID | int(11) | YES |  |  |  |
| ingresoID | int(11) | NO | MUL |  |  |

## Tabla: `hospedajes_clientes` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| hospedajeclienteID | int(11) | NO | PRI |  | auto_increment |
| hospedajeID | int(11) | NO | MUL |  |  |
| clienteID | int(11) | NO | MUL |  |  |
| empresaID | int(11) | YES | MUL |  |  |

## Tabla: `incidentes` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| incidenteID | int(11) | NO | PRI |  | auto_increment |
| clienteID | int(11) | NO | MUL |  |  |
| empresaID | int(11) | NO | MUL |  |  |
| descripcion | text | NO |  |  |  |
| fecha | date | NO |  |  |  |
| estado | enum('PENDIENTE','ACEPTADO','RECHAZADO') | YES |  | PENDIENTE |  |
| usuarioID | int(11) | NO | MUL |  |  |
| fecha_atencion | datetime | YES |  |  |  |
| solucion | text | YES |  |  |  |

## Tabla: `ingreso_pagos` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| ingresopagoID | int(11) | NO | PRI |  | auto_increment |
| ingresoID | int(11) | NO | MUL |  |  |
| formapagoID | int(11) | NO | MUL |  |  |
| monto | decimal(10,2) | NO |  |  |  |

## Tabla: `ingresos` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| ingresoID | int(11) | NO | PRI |  | auto_increment |
| empresaID | int(11) | NO | MUL |  |  |
| cajaID | int(11) | NO | MUL |  |  |
| cuentaID | int(11) | NO | MUL |  |  |
| usuarioID | int(11) | NO |  |  |  |
| monto_total | decimal(10,2) | NO |  |  |  |
| concepto | varchar(200) | YES |  |  |  |
| entregado | tinyint(4) | NO |  | 0 |  |
| fecha_entrega | timestamp | YES |  |  |  |
| recaudacionID | int(11) | YES | MUL |  |  |
| fecha | timestamp | NO |  | current_timestamp() |  |

## Tabla: `migracion_log` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| migracionID | int(11) | NO | PRI |  | auto_increment |
| tabla_origen | varchar(50) | NO |  |  |  |
| tabla_destino | varchar(50) | NO |  |  |  |
| registros_origen | int(11) | NO |  |  |  |
| registros_migrados | int(11) | NO |  |  |  |
| fecha_migracion | datetime | YES |  | current_timestamp() |  |
| estado | enum('PENDIENTE','EN_PROCESO','COMPLETADO','ERROR') | YES |  | PENDIENTE |  |
| error_detalle | text | YES |  |  |  |
| usuario_migracion | int(11) | NO |  |  |  |

## Tabla: `notificaciones` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  |  |  |
| notificacionID | int(11) | NO | PRI |  | auto_increment |
| empresaID | int(11) | NO | MUL |  |  |
| fecha_notificacion | datetime | YES |  | current_timestamp() |  |

## Tabla: `opciones` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| opcionID | int(11) | NO | PRI |  | auto_increment |
| grupoID | int(11) | NO | MUL |  |  |
| opcion | varchar(200) | NO |  |  |  |
| contenido | varchar(200) | NO |  |  |  |
| orden | int(11) | NO |  |  |  |
| funcionalidadID | int(11) | YES |  |  |  |

## Tabla: `paises` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| paisID | int(11) | NO | PRI |  | auto_increment |
| nombre | varchar(100) | NO |  |  |  |
| codigo_iso | char(2) | YES |  |  |  |
| _estado | char(1) | YES |  | A |  |

## Tabla: `planes` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| planID | int(11) | NO | PRI |  | auto_increment |
| tarea | varchar(255) | NO |  |  |  |
| tipo | enum('CORTO','LARGO') | NO |  |  |  |
| estado | enum('PENDIENTE','PROCESO','TERMINADO') | YES |  | PENDIENTE |  |

## Tabla: `recaudaciones` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| recaudacionID | int(11) | NO | PRI |  | auto_increment |
| empresaID | int(11) | NO | MUL |  |  |
| cajaID | int(11) | YES | MUL |  |  |
| usuariorecepcionistaID | int(11) | NO | MUL |  |  |
| usuariopropietarioID | int(11) | NO | MUL |  |  |
| monto | decimal(12,2) | NO |  |  |  |
| comprobante_nro | varchar(255) | YES |  |  |  |
| fecha | timestamp | NO |  | current_timestamp() |  |
| observaciones | text | YES |  |  |  |

## Tabla: `reserva_habitacion` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| reservahabitacionID | int(11) | NO | PRI |  | auto_increment |
| reservaID | int(11) | NO | MUL |  |  |
| habitacionID | int(11) | NO | MUL |  |  |

## Tabla: `reservas` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| reservaID | int(11) | NO | PRI |  | auto_increment |
| empresaID | int(11) | NO | MUL |  |  |
| checkin | datetime | YES |  |  |  |
| checkout | datetime | YES |  |  |  |
| precio | decimal(15,2) | YES |  |  |  |
| estado | text | YES |  |  |  |
| observaciones | text | YES |  |  |  |

## Tabla: `roles` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| rolID | int(11) | NO | PRI |  | auto_increment |
| rol | varchar(20) | NO | UNI |  |  |
| descripcion | varchar(200) | YES |  |  |  |

## Tabla: `servicios_extra` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| servicioextraID | int(11) | NO | PRI |  | auto_increment |
| empresaID | int(11) | NO | MUL |  |  |
| ingresoID | int(11) | NO | MUL |  |  |
| tipo | enum('VISITA','MOMENTANEO','BANO') | YES |  |  |  |
| descripcion | varchar(200) | YES |  |  |  |
| monto | decimal(15,2) | YES |  |  |  |
| fecha | timestamp | NO |  | current_timestamp() |  |

## Tabla: `tipo_habitaciones` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| tipohabitacionID | int(11) | NO | PRI |  | auto_increment |
| nombre | varchar(50) | NO |  |  |  |
| precio | decimal(15,2) | NO |  |  |  |
| empresaID | int(11) | NO | MUL |  |  |
| descripcion | text | YES |  |  |  |

## Tabla: `usuarios` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| usuarioID | int(11) | NO | PRI |  | auto_increment |
| empleadoID | int(11) | YES | MUL |  |  |
| usuario | varchar(15) | NO | UNI |  |  |
| clave | varchar(200) | NO |  |  |  |

## Tabla: `usuarios_roles` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| _fec_insercion | timestamp | NO |  | current_timestamp() |  |
| _fec_modificacion | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| _usuario | int(11) | NO |  |  |  |
| _estado | char(1) | NO |  | A |  |
| usuariorolID | int(11) | NO | PRI |  | auto_increment |
| rolID | int(11) | NO | MUL |  |  |
| usuarioID | int(11) | NO | MUL |  |  |

## Tabla: `v_movimientos_caja` 
| Columna | Tipo | Nulo | Clave | Default | Extra |
| --- | --- | --- | --- | --- | --- |
| movimientoID | int(11) | NO |  | 0 |  |
| cajaID | int(11) | NO |  | 0 |  |
| empresaID | int(11) | NO |  | 0 |  |
| usuarioID | int(11) | NO |  | 0 |  |
| tipo | varchar(7) | NO |  |  |  |
| concepto | varchar(200) | YES |  |  |  |
| cuenta_nombre | varchar(100) | NO |  |  |  |
| forma_pago | varchar(20) | NO |  |  |  |
| monto | decimal(10,2) | NO |  | 0.00 |  |
| fecha | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| _estado | char(1) | NO |  |  |  |

