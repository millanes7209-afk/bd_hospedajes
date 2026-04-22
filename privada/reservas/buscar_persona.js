$(document).ready(function() {
    // Función para resaltar el texto
    function highlightText(text, query) {
        const regex = new RegExp('(' + query + ')', 'gi');
        return text.replace(regex, '<span class="highlight">$1</span>');
    }

    // Función para realizar la búsqueda y mostrar resultados
    function buscarPersonas() {
        let ap = $('#ap').val().toLowerCase();
        let am = $('#am').val().toLowerCase();
        let nombres = $('#nombres').val().toLowerCase();
        let ci = $('#ci').val().toLowerCase();

        // Realizar la búsqueda
        $.ajax({
            url: 'buscar_persona.php',
            method: 'POST',
            data: {
                ap: ap,
                am: am,
                nombres: nombres,
                ci: ci
            },
            success: function(response) {
                let resultsHtml = response;

                // Resaltar texto en los resultados
                if (ap) resultsHtml = resultsHtml.replace(/(<td>.*?<\/td>)/g, function(match) {
                    return match.replace(new RegExp('(' + ap + ')', 'gi'), '<span class="highlight">$1</span>');
                });
                if (am) resultsHtml = resultsHtml.replace(/(<td>.*?<\/td>)/g, function(match) {
                    return match.replace(new RegExp('(' + am + ')', 'gi'), '<span class="highlight">$1</span>');
                });
                if (nombres) resultsHtml = resultsHtml.replace(/(<td>.*?<\/td>)/g, function(match) {
                    return match.replace(new RegExp('(' + nombres + ')', 'gi'), '<span class="highlight">$1</span>');
                });
                if (ci) resultsHtml = resultsHtml.replace(/(<td>.*?<\/td>)/g, function(match) {
                    return match.replace(new RegExp('(' + ci + ')', 'gi'), '<span class="highlight">$1</span>');
                });

                $('#resultadosBusqueda').html(resultsHtml);
            }
        });
    }

    // Evento de búsqueda en los campos de texto
    $('#ap, #am, #nombres, #ci').on('input', function() {
        buscarPersonas();
    });
});
