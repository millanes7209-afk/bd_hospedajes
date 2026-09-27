# Plan de Implementación: Modemosa Marketplace (Fase 1)

Modemosa es una plataforma marketplace C2C para prendas de vestir femeninas. En la **Fase 1**, los usuarios pueden explorar prendas, registrarse, publicar prendas con fotos, ser redirigidos a WhatsApp para negociar la compra, y marcar las prendas como vendidas especificando a la compradora.

---

## Cambios Propuestos

### Componente 1: Base de Datos y Seeders

#### `c:\xampp\htdocs\dulces\modemosa\.env`
- Configurar base de datos MySQL local (`modemosa` en XAMPP, port `3306`, user `root`).

#### `c:\xampp\htdocs\dulces\modemosa\database\seeders\ModemosaSeeder.php`
- Poblar la tabla `categorias` con categorías iniciales (`Blusas & Tops`, `Pantalones & Jeans`, `Vestidos & Enterizos`, `Calzado`, `Accesorios & Bolsos`, `Abrigos & Chaquetas`, `Faldas`).
- Crear usuario Administrador por defecto (`admin@modemosa.com`) y usuarios de prueba.

---

### Componente 2: Estilos y Sistema de Diseño (Branding)

#### `c:\xampp\htdocs\dulces\modemosa\public\css\modemosa.css`
- Implementar paleta institucional:
  - Primary: Verde Esmeralda (`#0E7A5F`)
  - Accent: Rosa Pastel (`#F4B8C8`) / Rosa Chicle (`#E882A2`)
  - Background/Cards: Blanco pulcro (`#FFFFFF`) y Gris Suave (`#F8F9FA`)
- Tarjetas de producto estilo boutique, badges de estado (`Nuevo`, `Usado`, `Reservada`, `Vendida`), botones con efecto hover micro-animado, tipografía moderna.

#### `c:\xampp\htdocs\dulces\modemosa\resources\views\layouts\app.blade.php`
- Incluir Bootstrap 5 CDN + `modemosa.css` + Bootstrap Icons.
- Implementar Navbar responsivo con selector de categorías, barra de búsqueda, botón "Vender Prenda", avatar y dropdown de perfil/panel admin.
- Integrar alertas flash para notificaciones de éxito/error.

---

### Componente 3: Vistas y Controladores

#### `c:\xampp\htdocs\dulces\modemosa\resources\views\catalogo\index.blade.php`
- Vista principal del catálogo con hero banner representativo, filtros de categoría/talla/precio, buscador en tiempo real, y grid responsivo de prendas.

#### `c:\xampp\htdocs\dulces\modemosa\resources\views\catalogo\show.blade.php`
- Vista de detalle con galería/carrusel de fotos de la prenda, datos del vendedor, botón destacado **"Contactar por WhatsApp"**, y modal para **"Reportar publicación"**.

#### `c:\xampp\htdocs\dulces\modemosa\resources\views\mis-prendas\index.blade.php`
- Dashboard de la vendedora: listado de mis publicaciones con badges de estado, accesos a editar, eliminar o **"Marcar como Vendida"**.

#### `c:\xampp\htdocs\dulces\modemosa\resources\views\mis-prendas\create.blade.php`
- Formulario de publicación con vista previa de imágenes, selección de categoría, talla, marca, estado de la prenda y precio.

#### `c:\xampp\htdocs\dulces\modemosa\resources\views\mis-prendas\edit.blade.php`
- Formulario para actualizar datos e imágenes de una prenda existente.

#### `c:\xampp\htdocs\dulces\modemosa\resources\views\mis-prendas\vendida.blade.php`
- Modal/Vista para seleccionar al comprador dentro del listado de usuarias interesadas que contactaron por WhatsApp.

#### `c:\xampp\htdocs\dulces\modemosa\resources\views\admin\reportes.blade.php`
- Panel de administración para moderación de contenido, visualización de reportes pendientes, retiro de prendas o suspensión de cuentas.

#### `c:\xampp\htdocs\dulces\modemosa\routes\web.php`
- Mapear todas las rutas para Catálogo, Mis Prendas, Interés (WhatsApp), Autenticación y Administración.

---

## Plan de Verificación

### Pruebas Automatizadas & Consola
- Ejecutar migraciones y seeders: `php artisan migrate:fresh --seed`
- Verificar sintaxis y estado de rutas: `php artisan route:list`

### Verificación Manual en Navegador
1. **Registro/Login**: Registrar una nueva usuaria con teléfono WhatsApp.
2. **Publicar Prenda**: Subir prenda con título, categoría, talla, precio y fotos.
3. **Catálogo & Filtros**: Explorar catálogo como visitante, filtrar por categoría y talla.
4. **Contacto por WhatsApp**: Hacer clic en "Contactar por WhatsApp" y verificar la generación del enlace `wa.me/591...` y registro en la tabla `intereses`.
5. **Marcar como Vendida**: Entrar como vendedora, presionar "Marcar como Vendida", seleccionar a la compradora interesada y verificar que el estado cambia a `vendida`.
6. **Reportar & Admin**: Reportar una prenda como visitante/usuario y gestionarla desde el panel `/admin/reportes`.
