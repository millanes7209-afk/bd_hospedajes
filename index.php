<?php
session_start();
require_once("conexion.php");

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Sistema Web Dulces Sueños</title>
  <link rel="stylesheet" href="css/login.css"> <!-- Aquí enlazamos el archivo CSS -->
  <!-- Enlace a Bootstrap CSS -->

  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

  <div class="login-container">
    <!-- Avatar que cambia dinámicamente -->
    <img id="user-avatar" class="avatar" src="img/imagen_login.jpeg" alt="Avatar Usuario">

    <div class="banner">INGRESAR</div>

    <!-- Aquí se mostrarán los mensajes de error o éxito -->
    <?php if (isset($_SESSION['mensaje'])): ?>
      <div class="alert alert-<?php echo $_SESSION['mensaje']['tipo']; ?>" role="alert">
        <?php echo $_SESSION['mensaje']['texto']; ?>
      </div>
      <?php unset($_SESSION['mensaje']); // Limpiar mensaje después de mostrar ?>
    <?php endif; ?>

    <!-- Formulario de Login -->
    <form action="validar.php" method="post" autocomplete="off">
      <div class="form-group">
        <h2>Usuario:</h2>
        <div class="input-container">
          <span class="input-icon">
            <img src="img/usuario.png" alt="Icono Usuario" class="icon">
          </span>
          <input type="text" name="nick" id="usuario" class="limpiar" placeholder="Usuario" oninput="mostrarAvatar()"
            required>
        </div>
      </div>
      <div class="form-group">
        <h2>Clave:</h2>
        <div class="input-container">
          <span class="input-icon">
            <img src="img/candado.jpg" alt="Icono Contraseña" class="icon">
          </span>
          <input type="password" name="password" id="password" placeholder="Contraseña" required
            oninput="ocultarAvatar()">
          <span class="toggle-password" onclick="togglePassword()">👁️</span>
          <!-- Ojo para mostrar/ocultar contraseña -->
        </div>
      </div>
      <button type="submit" name="accion" value="Ingresar" id="btn1">INGRESAR</button>
    </form>

    <a href="../Index.html">
      <button class="boton-atras">ATRÁS</button>
    </a>
  </div>

  <!-- Enlace al archivo JavaScript externo -->
  <script src="js/login.js"></script>
  <!-- Enlace a Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>

</html>