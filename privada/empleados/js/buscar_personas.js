"use strict"
function buscar_EMPLEADOS(){
    var d1,d2,d3,d4,d11,d5,ajax,url,param,contenedor;
    contenedor = document.getElementById('EMPLEADOS1');
    d1=document.formu.paterno.value;
    if(d1.length==0){
        d1='%';
    }
    d4=document.formu.ci.value;
    d3=document.formu.nombres.value;
    d2=document.formu.materno.value;
    d5=document.formu.fecha.value;
    //alert(d5);
    ajax=nuevoAjax();
    url="ajax_buscar_Empleado.php";
    param="paterno="+d1+"&materno="+d2+"&nombres="+d3+"&ci="+d4+"&fecha="+d5;
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
