"use strict"

function validar(){
    
    var  ci=document.formu.ci.value;
    var apellidos=document.formu.apellidos.value;
    var nombres=document.formu.nombres.value;
    var telefono=document.formu.telefono.value;
    var cargoID = document.formu.cargoID.value;

    if(cargoID==""){
        alert("SELECCIONE UN CARGO");
        document.formu.cargoID.focus();
        return;
    }
    if(ci==""){
        alert("CEDULA DE IDENTIDAD NECESARIA");
        document.formu.ci.focus();
        return;
    }
    if(apellidos==""){
        alert("CAMPO APELLIDOS NECESARIO");
        document.formu.apellidos.focus();
        return;
        
    }else{
        if(!v1.test(apellidos)){
            alert("CAMPO APELLIDOS INCORRECTO");
            document.formu.apellidos.focus();
            return;
            }
    }
    if(nombres==""){
        alert("CAMPO NOMBRES NECESARIO");
        document.formu.nombres.focus();
        return;
        
    }else{
        if(!v1.test(nombres)){
            alert("CAMPO NOMBRES INCORRECTO");
            document.formu.nombres.focus();
            return;
            }
    }
    if(telefono==""){
        alert("TELEFONO NECESARIO");
        document.formu.telefono.focus();
        return;
    }
    

    document.formu.submit();
    alert("DATOS CORRECTOS");
}