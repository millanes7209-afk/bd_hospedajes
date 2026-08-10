// validacion.js

// Función para validar texto con espacios
function validarTextoConEspacios(input) {
    const esValido = /^[A-Za-z\s]+$/.test(input.value);
    input.classList.toggle('is-invalid', !esValido);

    let feedback = input.nextElementSibling;
    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
        feedback = document.createElement('div');
        feedback.classList.add('invalid-feedback');
        input.parentNode.appendChild(feedback);
    }
    feedback.textContent = esValido ? '' : 'Ingrese un texto válido.';
    return esValido;
}

// Función para validar números (comentada como ejemplo)
 /*
function validarNumero(input) {
    const esValido = /^\d+$/.test(input.value);
    input.classList.toggle('is-invalid', !esValido);

    let feedback = input.nextElementSibling;
    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
        feedback = document.createElement('div');
        feedback.classList.add('invalid-feedback');
        input.parentNode.appendChild(feedback);
    }
    feedback.textContent = esValido ? '' : 'Ingrese un número válido.';
    return esValido;
}
*/

// Validar formulario al enviar
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.needs-validation');
    if (form) {
        form.addEventListener('submit', function (event) {
            let esValido = true;

            form.querySelectorAll('input').forEach(input => {
                if (input.type === 'text') {
                    if (!validarTextoConEspacios(input)) esValido = false;
                } 
                // Aquí puedes añadir más validaciones según el tipo de input
                // Ejemplo para números:
                /*
                else if (input.type === 'number') {
                    if (!validarNumero(input)) esValido = false;
                }
                */
            });

            if (!esValido) {
                event.preventDefault();
                event.stopPropagation();
                form.classList.add('was-validated');
            }
        });
    }
});

/*
    Para añadir nuevos tipos de validación:
    
    1. **Definir Nuevas Funciones de Validación:**
       - Crea una nueva función similar a `validarTextoConEspacios` y la función comentada de `validarNumero`.
       - Asegúrate de que la función ajuste el mensaje de `invalid-feedback` y el estado de `is-invalid` correctamente.

    2. **Actualizar Validación del Formulario:**
       - Dentro del evento `submit`, añade condiciones para manejar nuevos tipos de inputs.
       - Llama a la nueva función de validación según el tipo de campo (e.g., `input.type === 'email'` para correos electrónicos).

    Ejemplo de nueva validación:
    ```javascript
    // Validar correos electrónicos
    function validarEmail(input) {
        const esValido = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value);
        input.classList.toggle('is-invalid', !esValido);

        let feedback = input.nextElementSibling;
        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            feedback = document.createElement('div');
            feedback.classList.add('invalid-feedback');
            input.parentNode.appendChild(feedback);
        }
        feedback.textContent = esValido ? '' : 'Ingrese un correo electrónico válido.';
        return esValido;
    }
    ```

    - Añade una nueva condición en el `forEach` del formulario para validar correos electrónicos:
    ```javascript
    else if (input.type === 'email') {
        if (!validarEmail(input)) esValido = false;
    }
    ```
*/
