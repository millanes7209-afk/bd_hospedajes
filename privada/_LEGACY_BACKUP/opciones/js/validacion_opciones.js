"use strict"

function validar(){

    var opcion=document.formu.opcion.value;
    var id_grupo=document.formu.id_grupo.value;
    var contenido=document.formu.contenido.value;
    var orden=document.formu.orden.value;
    
    if(id_grupo==""){
        alert("CAMPO OBLIGATORIO");
        document.formu.id_grupo.focus();
        return;
    }

    if(opcion==""){
        alert("CAMPO OBLIGATORIO");
        document.formu.opcion.focus();
        return;
    }
    if(contenido==""){
        alert("SELECCIONE UN ARCHIVO");
        document.formu.contenido.focus();
        return;
    }
    if(orden==""){
        alert("SELECCIONE UN ARCHIVO");
        document.formu.orden.focus();
        return;
    }

    document.formu.submit();
    alert("DATOS CORRECTOS");
}