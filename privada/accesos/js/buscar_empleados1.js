"use strict"
function buscar_empleados1(){
    var d1,d2,d3,d4,ajax,url,param,contenedor;
    contenedor = document.getElementById('empleados1');
    d1=document.formu2.cargo1.options[document.formu2.cargo1.selectedIndex].value;
    d2=document.formu2.apellido1.value;
    d3=document.formu2.nombre1.value;
    ajax=nuevoAjax();
    url="ajax_buscar_empleados.php";
    
    param="cargo="+d1+"&apellidos="+d2+"&nombres="+d3;
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