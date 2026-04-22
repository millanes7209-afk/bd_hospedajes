"use strict"

function validar(){
    
    var  usuario=document.formu.usuario.value;
    var clave=document.formu.clave.value;
    var id_persona = document.formu.id_persona.value;
    if(id_persona==""){
        alert("SELECCIONÉ UNA PERSONA");
        document.formu.id_persona.focus();
        return;
    }
    if(usuario==""){
        alert("USUARIO NECESARIO");
        document.formu.usuario.focus();
        return;
    }
    if(clave==""){
        alert("CLAVE NECESARIA");
        document.formu.clave.focus();
        return;
    }
    

    document.formu.submit()
}