create table dba.ufce_parametros
(
	operacion varchar(15),
	parametro varchar(25),
	descripcion varchar(255)
);

insert into ufce_parametros values 
	('mail_dd', 'docentes@econ.unicen.edu.ar', 'Mail de la Dirección de Docentes. Utilizado para el envío de mail a los docentes por la confirmación de fechas de evaluaciones parciales.');

insert into ufce_parametros values 
	('mail_no_reply', 'no-reply-guarani@econ.unicen.edu.ar', 'Mail utilizado para el envío de mail automáticos a la DD cuando los coordinadores graban una nueva observación en la propuesta de fechas de evaluaciones parciales.');

--select * from ufce_parametros;

-------------------------------

drop table ufce_cron_eval_parc_obs_log;
CREATE TABLE dba.ufce_cron_eval_parc_obs_log
(
	id SERIAL,
	materia VARCHAR(5),
	anio_academico INT,
	periodo_lectivo VARCHAR(20),
	observaciones VARCHAR(255),
	fecha DATETIME YEAR TO SECOND,
	oper VARCHAR(1) CHECK (oper IN ('I', 'U', 'D')),
	PRIMARY KEY (id)
);

--select * from ufce_cron_eval_parc_obs_log;
