--Lucia (para que un mismo docente tenga acceso a todos los perfiles): 
INSERT INTO aca_tipos_usuar_ag VALUES ('FCE', 'FCE-427', 'OFA', 'A');
INSERT INTO aca_tipos_usuar_ag VALUES ('FCE', 'FCE-427', 'OFD', 'A');
INSERT INTO aca_tipos_usuar_ag VALUES ('FCE', 'FCE-427', 'COORD', 'A');

--Extras:
INSERT INTO aca_tipos_usuar_ag VALUES ('FCE', 'FCE-427', 'GER', 'A');
INSERT INTO aca_tipos_usuar_ag VALUES ('FCE', 'FCE-427', 'ADM', 'A');


--Para que sea coordinador de un materia
update ufce_coordinadores_materias 
	set coordinador = '10002'
where materia = 'L0025'
and anio_academico = 2020;

--Mail:
update ufce_parametros
	set parametro = 'imfigini@slab.exa.unicen.edu.ar'
where operacion = 'mail_dd';

--Borrar claves
--md5(1234) = 81dc9bdb52d04dc20036dbd8313ed055
UPDATE aca_usuarios_ag SET clave = '81dc9bdb52d04dc20036dbd8313ed055', 
			fec_ult_actualiz = TODAY,
			intentos_fallidos = 0,
			bloqueado = 'N'
WHERE unidad_academica IS NOT NULL;



{
--Para etapa3 poder hacer pruebas
UPDATE sga_llamados_mesa SET habilitado ='S' WHERE anio_academico = 2020;
UPDATE sga_exep_insc_llam SET fecha_inicio = CURRENT WHERE anio_academico = 2020;
UPDATE par_cont_x_oper SET actua_como = 'A' WHERE operacion = 'exa00006' AND control IN (800573);
UPDATE sga_turnos_examen SET fecha_inicio = TODAY-1 WHERE anio_academico = 2020;

--Sino da error al actualizar calidad inscripción a cursada de alumno (con el usuario informix, no con el apache)
--por SP: sp_param_sistema
INSERT INTO sga_datos_usuarios (usuario, unidad_academica, sede)
	VALUES ('informix', 'FCE', '00000');

}