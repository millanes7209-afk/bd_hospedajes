// validacion.js
(function () {
    'use strict'
    // Selecciona todos los formularios con la clase needs-validation
    var forms = document.querySelectorAll('.needs-validation')
    
    // Itera sobre cada formulario y añade el evento submit
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                // Si el formulario no es válido, evita el envío y aplica la clase was-validated
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
})()
