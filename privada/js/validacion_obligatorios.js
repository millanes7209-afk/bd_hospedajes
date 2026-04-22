

document.addEventListener("DOMContentLoaded", function() {

    const forms = document.querySelectorAll('.needs-validation');
    

    forms.forEach(function(form) {
        form.addEventListener('submit', function(event) {

            if (!form.checkValidity()) {
                event.preventDefault(); // Evita el envío del formulario
                event.stopPropagation(); // Detiene la propagación del evento
            }else{}
            form.classList.add('was-validated'); // Añade la clase para mostrar mensajes de error
        }, false);
    });
});


