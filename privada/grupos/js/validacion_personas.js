    "use strict"

function validar(){
    var ci=document.formu.ci.value;
    var ap=document.formu.ap.value;
    var am=document.formu.am.value;
    var nombres=document.formu.nombres.value;
    var genero=document.formu.genero.value;
    
    if(ci==""){
        alert("Documento necesario");
        document.formu.ci.focus();
        return;
    }
    if(ap=="" && am==""){
        alert("Ingrese al menos un apellido");
        document.formu.ap.focus();
        return;
        
    }
    if(ap!=""){
        if(!v1.test(ap)){
            alert("Apellido incorrecto");
            document.formu.ap.focus();
            return;
            }
            
        }
    if(am!=""){
        if(!v1.test(am)){
            alert("Apellido incorrecto");
            document.formu.am.focus();
            return;
            }
        
    }
  
    if(nombres==""){
        alert("Nombre necesario");
        document.formu.nombres.focus();
        return;
        
    }else{
        if(!v1.test(nombres)){
            alert("Nombre incorrecto");
            document.formu.nombres.focus();
            return;
            }
    }
    if(genero==""){
        alert("Genero necesario");
        
        return;
    }
    
    
    document.formu.submit();
    alert("DATOS CORRECTOS");
}