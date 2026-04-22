"use strict"

function validar(){
    var  cargo=document.formu.cargo.value;
    var descripcion=document.formu.descripcion.value;
 
    
    if(cargo==""){
        alert("Cargo necesario");
        document.formu.cargo.focus();
        return;
    }
    if(descripcion==""){
        alert("Descripción necesaria");
        document.formu.descripcion.focus();
        return;
    }
    
    document.formu.submit()
    alert("DATOS CORRECTOS")
}