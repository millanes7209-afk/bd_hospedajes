    "use strict"

function validar(){
    var grupo=document.formu.grupo.value;
  
    if(grupo==""){
        alert("Nombre de grupo necesario");
        document.formu.grupo.focus();
        return;
        
    }
    
    
    document.formu.submit();
    alert("DATOS CORRECTOS");
}