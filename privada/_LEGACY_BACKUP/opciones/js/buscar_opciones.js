"use strict"
function buscar_opciones(){
    var d1,d2,d3,d4,ajax,url,param,contenedor;
    contenedor = document.getElementById('opciones1');
    d1=document.formu.grupo.value;
    d2=document.formu.opcion.value;
    ajax=nuevoAjax();
    url="ajax_buscar_opciones.php";
    
    param="grupo="+d1+"&opcion="+d2;
    //alert(param);
    ajax.open("POST",url,true);
    ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    ajax.onreadystatechange=function(){
        if(ajax.readyState==4){
            contenedor.innerHTML=ajax.responseText;
        }
    }
    ajax.send(param);
}