function toggleMenu() {
    var menu = document.querySelector('.nav');
    menu.classList.toggle('active');
  }
  
  document.addEventListener('DOMContentLoaded', function() {
    // Obtener todos los elementos de menú principal
    var menuItems = document.querySelectorAll('.nav > li > a');
  
    menuItems.forEach(function(item) {
      item.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Obtener el submenú correspondiente
        var submenu = this.nextElementSibling;
        
        // Cerrar cualquier otro submenú abierto
        var openSubmenus = document.querySelectorAll('.nav li ul');
        openSubmenus.forEach(function(openSubmenu) {
          if (openSubmenu !== submenu) {
            openSubmenu.style.display = 'none';
          }
        });
  
        // Alternar la visualización del submenú actual
        if (submenu.style.display === 'block') {
          submenu.style.display = 'none';
        } else {
          submenu.style.display = 'block';
        }
      });
    });
  });
  