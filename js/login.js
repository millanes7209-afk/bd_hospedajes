// Cambiar avatar cuando el usuario está escribiendo
function mostrarAvatar() {
    let usuario = document.getElementById('usuario').value;
    let avatar = document.getElementById('user-avatar');
    if (usuario) {
      avatar.src = '../img/curioseando.avif'; // Cambia la ruta por tu imagen
    }
  }
  
  // Cambiar avatar cuando el usuario empieza a escribir la contraseña
  function ocultarAvatar() {
    let password = document.getElementById('password').value;
    let avatar = document.getElementById('user-avatar');
    if (password) {
      avatar.src = '../img/vendado.webp'; // Cambia la ruta por tu imagen de los ojos vendados
    }
  }
  
  // Función para ver/ocultar la contraseña
  function togglePassword() {
    let passwordInput = document.getElementById('password');
    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
    } else {
      passwordInput.type = 'password';
    }
  }
  