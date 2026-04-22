# Planes a Largo Plazo - Visión Futura

Este documento centraliza las ideas y grandes módulos que se implementarán a futuro.

## 1. Módulo de Reservas
- **Objetivo:** Permitir reservar habitaciones desde la misma pantalla de recepción.
- **Visualización:** Integrar un estado de "RESERVADA" en el mapa de habitaciones.
- **Lógica:** Bloqueo de fechas futuras y gestión de depósitos de garantía.

## 2. Respaldo Visual de Pagos (QR)
- **Objetivo:** Capturar y almacenar una foto o captura de pantalla del comprobante de transferencia bancaria.
- **Funcionalidad:** 
  - Al seleccionar "QR" como forma de pago, se habilitará un campo de carga de imagen.
  - El sistema almacenará el archivo vinculado al registro de caja (`movimientoID`).
- **Auditoría:** El administrador podrá visualizar el comprobante directamente desde el historial de auditoría para verificar la veracidad del depósito.
