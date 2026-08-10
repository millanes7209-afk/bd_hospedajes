<?php
session_start();
require_once("conexion.php");
require_once("libreria_menu.php");

//$db->debug=true;

echo "<html>
       <head>
         
         <style>
           
       </head>
       <body>
       <!-- Modal de Bootstrap -->
<div class='modal fade' id='modalNotificacion' tabindex='-1' aria-labelledby='modalNotificacionLabel' aria-hidden='true'>
  <div class='modal-dialog'>
    <div class='modal-content'>
      <div class='modal-header'>
        <h5 class='modal-title' id='modalNotificacionLabel'>Notificación Pendiente</h5>
        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
      </div>
      <div class='modal-body'>
        <p id='mensajeNotificacion'></p>
      </div>
      <div class='modal-footer'>
        <button type='button' class='btn btn-danger' onclick='posponerNotificacion(modalNotificacionID)'>Posponer</button>
        <button type='button' class='btn btn-success' onclick='marcarNotificacionAtendida(modalNotificacionID)'>Factura Emitida</button>
      </div>
    </div>
  </div>
</div>
         <p></p>  
       </body>
      </html>";
?>
