--Lucia: 
INSERT INTO aca_tipos_usuar_ag VALUES ('FCE', 'FCE-427', 'OFA', 'A');
INSERT INTO aca_tipos_usuar_ag VALUES ('FCE', 'FCE-427', 'OFD', 'A');
INSERT INTO aca_tipos_usuar_ag VALUES ('FCE', 'FCE-427', 'COORD', 'A');

update ufce_coordinadores_materias 
	set coordinador = '10002'
where materia = 'L0025'
and anio_academico = 2020;

--Mail:
update ufce_parametros
	set parametro = 'imfigini@slab.exa.unicen.edu.ar'
where operacion = 'mail_dd';
