"use strict"

function validar(){
    var  telefono=document.formu.telefono.value;
    var direccion=document.formu.direccion.value;
    var nombre=document.formu.nombre.value;

    
    if(telefono==""){
        alert("Telefono necesario");
        document.formu.telefono.focus();
        return;
    }
    if(direccion==""){
        alert("Dirección necesaria");
        document.formu.direccion.focus();
        return;
    }
  
    if(nombre==""){
        alert("Nombre necesario");
        document.formu.nombre.focus();
        return;
        
    }else{
        if(!v1.test(nombre)){
            alert("Nombre incorrecto");
            document.formu.nombre.focus();
            return;
            }
    }
   
    
    
    document.formu.submit();
    alert("CAMBIOS REGISTRADOS");
}