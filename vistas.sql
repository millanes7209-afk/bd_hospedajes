/*CREATE VIEW vista_usuarios_personas AS 
SELECT CONCAT_WS(' ', per.ap, per.am, per.nombres) AS persona, usu.* 
FROM personas per
JOIN usuarios usu ON per.id_persona = usu.id_persona
WHERE per._estado = 'A';
*/
/*SELECT *FROM vista_personas*/

CREATE VIEW     vista_empresa AS 
SELECT          logo_agencia,nombre
FROM            empresa
WHERE           _estado = 'A';
/*

CREATE VIEW     vista_empleados_entregas AS
SELECT          CONCAT_WS(' ', emp.apellidos,emp.nombres)as mensajero,ent.*
FROM            empleados emp
JOIN            entregas ent ON emp.empleadoID=ent.empleadoID
WHERE           emp._estado = 'A'
AND             ent._estado = 'A';

CREATE VIEW     vista_cargos_empleados AS
SELECT          CONCAT_WS(' ', emp.apellidos,emp.nombres)as empleado,car.*
FROM            cargos car
JOIN            empleados emp ON car.cargoID=emp.cargoID
WHERE           emp._estado = 'A'
AND             car._estado = 'A';

CREATE VIEW     vista_personas_fechas AS 
SELECT          CONCAT_WS(' ', per.ap, per.am, per.nombres) AS persona, usu.* 
FROM            personas per
JOIN            usuarios usu ON per.id_persona = usu.id_persona
WHERE           per._estado = 'A';
*/