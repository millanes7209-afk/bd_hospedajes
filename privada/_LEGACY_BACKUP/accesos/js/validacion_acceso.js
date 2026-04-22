"use strict"

function validar(){
    
    var id_opcion=document.formu.id_opcion.value;
    var id_rol = document.formu.id_rol.value;
    if(id_opcion==""){
        alert("SELECCIONE UNA OPCION");
        document.formu.id_opcion.focus();
        return;
    }
    if(id_rol==""){
        alert("SELECCIONE UN ROL");
        document.formu.id_rol.focus();
        return;
    }
    
    
    

    document.formu.submit();
    alert("DATOS CORRECTOS");
}