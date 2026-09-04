<?php
if(!function_exists('str_ireplace')) {
    function str_ireplace($search,$replace,$subject) {
        echo$search = preg_quote($search, "/");
        return preg_replace("/".$search."/i", $replace, $subject); 
    } 
}

function resaltar($palabra, $texto) {
    $aux = $reemp = str_ireplace($palabra,'%s',$texto);
    //echo"aux".$aux."<br>";
    $veces =substr_count($reemp,'%s');
    if($veces == 0)return $texto;
    $palabras_originales=array();
    for($i=0;$i<$veces;$i++){
          $palabras_originales[]='<strong style="color: black;background-color: yellow;font-weight: bold;">'.substr($texto,strpos($aux,'%s'),strlen($palabra)).'</strong>';
          $aux=substr($aux,0,strpos($aux,'%s')).$palabra.substr($aux,strlen(substr($aux,0,strpos($aux,'%s')))+2);
    }
    return vsprintf($reemp,$palabras_originales);
}
?> 