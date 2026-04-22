// JavaScript Document
function nuevoAjax(){
	var xmlhttp=false;
	try {
		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP"); 
		// Creación del objeto ajax para navegadores diferentes a Explorer
	} catch (e) {
		try {
		        // o bien
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			// Creación del objet ajax para Explorer
		} catch (E) {
			xmlhttp = false;
		}
	}

	if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
		xmlhttp = new XMLHttpRequest();
	}
	return xmlhttp;
}