-- ESTRUCTURA DE LA BASE DE DATOS - 2026-04-24 09:49:18

-- ESTRUCTURA PARA LA TABLA: accesos
CREATE TABLE accesos (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  accesoID int(11) NOT NULL auto_increment,
  rolID int(11) NOT NULL,
  opcionID int(11) NOT NULL
);

-- ESTRUCTURA PARA LA TABLA: cajas
CREATE TABLE cajas (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  cajaID int(11) NOT NULL auto_increment,
  empresaID int(11) NOT NULL,
  usuarioID int(11) NOT NULL,
  estado enum('ABIERTA','CERRADA') DEFAULT 'CERRADA',
  fecha_apertura datetime DEFAULT 'current_timestamp()',
  fecha_cierre datetime,
  observaciones text
);

-- ESTRUCTURA PARA LA TABLA: cierre_cajas
CREATE TABLE cierre_cajas (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  cierrecajaID int(11) NOT NULL auto_increment,
  cajaID int(11) NOT NULL,
  formapagoID int(11) NOT NULL,
  monto decimal(12,2) NOT NULL
);

-- ESTRUCTURA PARA LA TABLA: clientes
CREATE TABLE clientes (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  clienteID int(11) NOT NULL auto_increment,
  ci varchar(15) NOT NULL,
  nombres varchar(50) NOT NULL,
  apellido1 varchar(30) NOT NULL,
  apellido2 varchar(30),
  fecha_nacimiento date NOT NULL,
  lugar_nacimiento varchar(100) NOT NULL,
  estado_civil varchar(25),
  profesion varchar(100),
  pais varchar(100),
  paisID int(11) NOT NULL
);

-- ESTRUCTURA PARA LA TABLA: empleado_empresas
CREATE TABLE empleado_empresas (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  empleadoempresaID int(11) NOT NULL auto_increment,
  empleadoID int(11) NOT NULL,
  rolID int(11) NOT NULL,
  empresaID int(11) NOT NULL,
  sueldo decimal(15,2),
  fecha_inicio date NOT NULL,
  fecha_fin date,
  es_titular tinyint(1) DEFAULT '0',
  estado_laboral enum('ACTIVO','INACTIVO','SUSPENDIDO') NOT NULL DEFAULT 'ACTIVO'
);

-- ESTRUCTURA PARA LA TABLA: empleados
CREATE TABLE empleados (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  empleadoID int(11) NOT NULL auto_increment,
  ci varchar(15) NOT NULL,
  nombres varchar(40) NOT NULL,
  apellidos varchar(40) NOT NULL,
  telefono varchar(15) NOT NULL,
  genero char(1) NOT NULL,
  fecha_nacimiento date
);

-- ESTRUCTURA PARA LA TABLA: empresa
CREATE TABLE empresa (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  empresaID int(11) NOT NULL auto_increment,
  nombre varchar(50) NOT NULL,
  direccion varchar(200) NOT NULL,
  telefono varchar(15) NOT NULL,
  logo_agencia varchar(100) NOT NULL,
  color_primario varchar(10) DEFAULT '#059669',
  color_secundario varchar(10) DEFAULT '#ffffff',
  ruc varchar(20),
  representante_legal varchar(100)
);

-- ESTRUCTURA PARA LA TABLA: empresa_funcionalidades
CREATE TABLE empresa_funcionalidades (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  empresafuncionID int(11) NOT NULL auto_increment,
  empresaID int(11) NOT NULL,
  funcionalidadID int(11) NOT NULL,
  fecha_activacion date DEFAULT 'current_timestamp()',
  fecha_vencimiento date,
  estado enum('ACTIVO','VENCIDO','CANCELADO') DEFAULT 'ACTIVO'
);

-- ESTRUCTURA PARA LA TABLA: formas_pago
CREATE TABLE formas_pago (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  formapagoID int(11) NOT NULL auto_increment,
  empresaID int(11) NOT NULL,
  tipo varchar(20) NOT NULL,
  descripcion varchar(100) NOT NULL
);

-- ESTRUCTURA PARA LA TABLA: funcionalidades
CREATE TABLE funcionalidades (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  funcionalidadID int(11) NOT NULL auto_increment,
  nombre varchar(50) NOT NULL,
  descripcion text,
  categoria varchar(30) NOT NULL,
  estado enum('ACTIVO','INACTIVO') DEFAULT 'ACTIVO'
);

-- ESTRUCTURA PARA LA TABLA: gastos
CREATE TABLE gastos (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  gastoID int(11) NOT NULL auto_increment,
  empresaID int(11) NOT NULL,
  tipo enum('MANTENIMIENTO','INSUMOS','OTRO'),
  descripcion varchar(200),
  monto decimal(15,2),
  fecha timestamp NOT NULL DEFAULT 'current_timestamp()'
);

-- ESTRUCTURA PARA LA TABLA: grupos
CREATE TABLE grupos (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  grupoID int(11) NOT NULL auto_increment,
  grupo varchar(30) NOT NULL,
  descripcion varchar(200)
);

-- ESTRUCTURA PARA LA TABLA: habitaciones
CREATE TABLE habitaciones (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  habitacionID int(11) NOT NULL auto_increment,
  tipohabitacionID int(11) NOT NULL,
  empresaID int(11) NOT NULL,
  numero varchar(10) NOT NULL,
  estado enum('DISPONIBLE','OCUPADA','RESERVADA','MANTENIMIENTO','DEUDA','LIMPIEZA') DEFAULT 'DISPONIBLE',
  descripcion varchar(255),
  tv tinyint(1) NOT NULL DEFAULT '0',
  bano tinyint(1) NOT NULL DEFAULT '0',
  ventilador tinyint(1) NOT NULL DEFAULT '0'
);

-- ESTRUCTURA PARA LA TABLA: hospedajes
CREATE TABLE hospedajes (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  hospedajeID int(11) NOT NULL auto_increment,
  empresaID int(11) NOT NULL,
  checkin datetime DEFAULT 'current_timestamp()',
  checkout datetime NOT NULL,
  monto decimal(15,2) NOT NULL,
  estado enum('ACTIVO','INACTIVO') DEFAULT 'ACTIVO',
  habitacionID int(11) NOT NULL,
  observaciones text,
  cajaID int(11)
);

-- ESTRUCTURA PARA LA TABLA: hospedajes_auditoria_montos
CREATE TABLE hospedajes_auditoria_montos (
  id int(11) NOT NULL auto_increment,
  hospedajeID int(11) NOT NULL,
  tipo_auditoria enum('MODIFICACION','ELIMINACION') DEFAULT 'MODIFICACION',
  monto_anterior decimal(15,2) NOT NULL,
  monto_nuevo decimal(15,2) NOT NULL,
  detalle_original text,
  detalle_nuevo text,
  estado_revision tinyint(4) DEFAULT '0',
  empresaID int(11) NOT NULL,
  motivo text,
  usuarioID int(11) NOT NULL,
  fecha datetime NOT NULL
);

-- ESTRUCTURA PARA LA TABLA: hospedajes_clientes
CREATE TABLE hospedajes_clientes (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  hospedajeclienteID int(11) NOT NULL auto_increment,
  hospedajeID int(11) NOT NULL,
  clienteID int(11) NOT NULL,
  empresaID int(11)
);

-- ESTRUCTURA PARA LA TABLA: incidentes
CREATE TABLE incidentes (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  incidenteID int(11) NOT NULL auto_increment,
  clienteID int(11) NOT NULL,
  empresaID int(11) NOT NULL,
  descripcion text NOT NULL,
  fecha date NOT NULL,
  estado enum('PENDIENTE','ACEPTADO','RECHAZADO') DEFAULT 'PENDIENTE',
  usuarioID int(11) NOT NULL,
  fecha_atencion datetime,
  solucion text
);

-- ESTRUCTURA PARA LA TABLA: migracion_log
CREATE TABLE migracion_log (
  migracionID int(11) NOT NULL auto_increment,
  tabla_origen varchar(50) NOT NULL,
  tabla_destino varchar(50) NOT NULL,
  registros_origen int(11) NOT NULL,
  registros_migrados int(11) NOT NULL,
  fecha_migracion datetime DEFAULT 'current_timestamp()',
  estado enum('PENDIENTE','EN_PROCESO','COMPLETADO','ERROR') DEFAULT 'PENDIENTE',
  error_detalle text,
  usuario_migracion int(11) NOT NULL
);

-- ESTRUCTURA PARA LA TABLA: movimientos
CREATE TABLE movimientos (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  movimientoID int(11) NOT NULL auto_increment,
  referenciaID int(11) NOT NULL,
  empresaID int(11) NOT NULL,
  cajaID int(11) NOT NULL,
  formapagoID int(11) NOT NULL,
  monto decimal(15,2) NOT NULL,
  categoria enum('HOSPEDAJE','SERVICIO_EXTRA','GASTO') NOT NULL,
  concepto varchar(200),
  detalle text,
  entregado tinyint(1) NOT NULL DEFAULT '0',
  fecha_entrega datetime,
  recaudacionID int(11),
  usuarioID int(11),
  tipo varchar(30)
);

-- ESTRUCTURA PARA LA TABLA: notificaciones
CREATE TABLE notificaciones (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL,
  notificacionID int(11) NOT NULL auto_increment,
  empresaID int(11) NOT NULL,
  fecha_notificacion datetime DEFAULT 'current_timestamp()'
);

-- ESTRUCTURA PARA LA TABLA: opciones
CREATE TABLE opciones (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  opcionID int(11) NOT NULL auto_increment,
  grupoID int(11) NOT NULL,
  opcion varchar(200) NOT NULL,
  contenido varchar(200) NOT NULL,
  orden int(11) NOT NULL
);

-- ESTRUCTURA PARA LA TABLA: paises
CREATE TABLE paises (
  paisID int(11) NOT NULL auto_increment,
  nombre varchar(100) NOT NULL,
  codigo_iso char(2),
  _estado char(1) DEFAULT 'A'
);

-- ESTRUCTURA PARA LA TABLA: recaudaciones
CREATE TABLE recaudaciones (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  recaudacionID int(11) NOT NULL auto_increment,
  empresaID int(11) NOT NULL,
  usuariorecepcionistaID int(11) NOT NULL,
  usuariopropietarioID int(11) NOT NULL,
  monto decimal(12,2) NOT NULL,
  comprobante_nro varchar(255),
  fecha timestamp NOT NULL DEFAULT 'current_timestamp()',
  observaciones text
);

-- ESTRUCTURA PARA LA TABLA: reserva_habitacion
CREATE TABLE reserva_habitacion (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  reservahabitacionID int(11) NOT NULL auto_increment,
  reservaID int(11) NOT NULL,
  habitacionID int(11) NOT NULL
);

-- ESTRUCTURA PARA LA TABLA: reservas
CREATE TABLE reservas (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  reservaID int(11) NOT NULL auto_increment,
  empresaID int(11) NOT NULL,
  checkin datetime,
  checkout datetime,
  precio decimal(15,2),
  estado text,
  observaciones text
);

-- ESTRUCTURA PARA LA TABLA: roles
CREATE TABLE roles (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  rolID int(11) NOT NULL auto_increment,
  rol varchar(20) NOT NULL,
  descripcion varchar(200)
);

-- ESTRUCTURA PARA LA TABLA: servicios_extra
CREATE TABLE servicios_extra (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  servicioextraID int(11) NOT NULL auto_increment,
  empresaID int(11) NOT NULL,
  tipo enum('VISITA','MOMENTANEO','BANO'),
  descripcion varchar(200),
  monto decimal(15,2),
  fecha timestamp NOT NULL DEFAULT 'current_timestamp()'
);

-- ESTRUCTURA PARA LA TABLA: tipo_habitaciones
CREATE TABLE tipo_habitaciones (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  tipohabitacionID int(11) NOT NULL auto_increment,
  nombre varchar(50) NOT NULL,
  precio decimal(15,2) NOT NULL,
  empresaID int(11) NOT NULL,
  descripcion text
);

-- ESTRUCTURA PARA LA TABLA: usuarios
CREATE TABLE usuarios (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  usuarioID int(11) NOT NULL auto_increment,
  empleadoID int(11),
  usuario varchar(15) NOT NULL,
  clave varchar(200) NOT NULL
);

-- ESTRUCTURA PARA LA TABLA: usuarios_roles
CREATE TABLE usuarios_roles (
  _fec_insercion timestamp NOT NULL DEFAULT 'current_timestamp()',
  _fec_modificacion timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  _usuario int(11) NOT NULL,
  _estado char(1) NOT NULL DEFAULT 'A',
  usuariorolID int(11) NOT NULL auto_increment,
  rolID int(11) NOT NULL,
  usuarioID int(11) NOT NULL
);

