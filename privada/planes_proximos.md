# Planes Próximos - Tareas Inmediatas y Reglas

Este documento detalla los requerimientos y reglas de negocio para la fase actual de desarrollo.

## 1. Auditoría y Seguridad Financiera
- **Regla de Oro:** Un hospedaje solo se puede modificar si pertenece a la **caja del mismo turno**, al **mismo usuario** y a la **mismas empresa**. Esto es crítico para mantener la integridad de los cierres de caja.
- **Detección de Cambios de Montos:** Implementar alertas visuales en el panel de auditoría cuando se detecten cambios manuales en el JSON de pagos.

## 2. Optimización de Interfaz
- **Mapa de Recepción:** Implementación de diseño compacto con hover informativo en habitaciones ocupadas para mejorar la visibilidad global.
- **Registro de Momentáneos:** Desarrollo de módulo para estancias rápidas o clientes sin carnet.
- **Agrupamiento:** Organizar el mapa de habitaciones agrupando por **piso** y por **tipo** de habitación para una búsqueda más lógica.
- **Consistencia de Navegación:** Mantener el sistema de pestañas y sidebar automático en todos los nuevos módulos.

## 3. Reglas de Reportes
- **Filtros de Cajas Cerradas:** Los filtros y visualización de historial de cajas cerradas en `vista_cajas.php` están restringidos por Rol: Los recepcionistas solo ven sus propios cierres, mientras que los administradores/propietarios pueden auditar a todo el equipo.

## 4. Automatización del Sistema (Back-end)
- **Trigger de Accesos:** Implementar un disparador en la tabla `opciones` para que cada nueva funcionalidad creada sea visible automáticamente para el Desarrollador (Administrador).
- **Módulo de Sistema:** Creación de grupo "SISTEMA" para la gestión dinámica de Menús (Grupos), Pestañas (Opciones) y Permisos (Accesos).

## 5. Lógica Financiera
- **Ingresos y Egresos:** Revisar y optimizar los botones de la lógica de ingresos y egresos, asegurando que se ajusten estrictamente a la estructura de la base de datos para evitar desajustes en los saldos de caja.

## 6. Refinamientos de Recepción
- **Equipamiento en Mapa:** Mostrar TV/Baño/Ventilador en los botones de habitaciones DISPONIBLES en el mapa interactivo.
