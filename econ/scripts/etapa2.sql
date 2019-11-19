------------------------------------------------------------------------------------------------------------
-----PARA PARAMETROS GENERALES CONFIGURABLES, APLICABLES A DISTINAS OPERACIONES Y FUNCIONALIDADES
--DROP table ufce_parametros;
create table dba.ufce_parametros
(
	operacion varchar(25) not null,
	parametro varchar(50),
	descripcion varchar(255),
	primary key (operacion)
);

insert into ufce_parametros values 
	('mail_dd', 'docentes@econ.unicen.edu.ar', 'Mail de la Dirección de Docentes. Utilizado para el envío de mail a los coordinadores por la confirmación de fechas de evaluaciones parciales.');
insert into ufce_parametros values 
	('mail_sistema', 'sistema-guarani@econ.unicen.edu.ar', 'Mail utilizado para el envío de mail automáticos a la DD cuando los coordinadores graban una nueva observación en la propuesta de fechas de evaluaciones parciales.');
--insert into ufce_parametros values 
--	('mail_no_reply', 'no-reply-guarani@econ.unicen.edu.ar', 'Mail utilizado para el envío de mail automáticos a la DD cuando los coordinadores graban una nueva observación en la propuesta de fechas de evaluaciones parciales.');

--select * from ufce_parametros;

------------------------------------------------------------------------------------------------------------
----PARA LLEVAR REGISTRO DE LAS OBSERVACIONES QUE VA DEJANDO EL COORDINADOR EN LA OPERACIÓN DE SOLICITUD DE FECHAS PARA EVALUACIONES PARCIALES
ALTER TABLE ufce_cron_eval_parc_obs MODIFY (observaciones VARCHAR(255));
--drop table ufce_cron_eval_parc_obs_log;
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
------------------------------------------------------------------------------------------------------------
-----TIPOS DE ESTADOS EN LOS QUE PUEDE ESTAR LA SOLICITUD DE FECHAS DE EVALUACIONES POR PARCIALES
------------------------------------------------------------------------------------------------------------
alter table ufce_cron_eval_parc add estado_notific varchar(1) default 'U' not null; 
ALTER TABLE ufce_cron_eval_parc ADD CONSTRAINT CHECK (estado_notific in ('U', 'A', 'M'));
ALTER TABLE ufce_cron_eval_parc MODIFY (estado VARCHAR(2) DEFAULT 'P' NOT NULL);
ALTER TABLE ufce_cron_eval_parc ADD CONSTRAINT FOREIGN KEY (estado) REFERENCES ufce_cron_eval_parc_estados(estado);
--select * from ufce_cron_eval_parc;

-- DROP TABLE dba.ufce_cron_eval_parc_estados;
CREATE TABLE dba.ufce_cron_eval_parc_estados (
	estado varchar(2) NOT NULL,
	descripcion varchar(255),
	PRIMARY KEY (estado)
);
insert into ufce_cron_eval_parc_estados (estado, descripcion)
	values ('P', 'Pendiente a ser revisada por la DD. No tiene instancia de evaluación creada');
insert into ufce_cron_eval_parc_estados (estado, descripcion)
	values ('A', 'Aceptada la fecha solicitada. Tiene instancia de evaluación creada');
insert into ufce_cron_eval_parc_estados (estado, descripcion)
	values ('C', 'Modificada a otro día de cursada. Tiene instancia de evaluación asociada a otra fecha');
insert into ufce_cron_eval_parc_estados (estado, descripcion)
	values ('R', 'Reasiganda a otro día de la semana que no corresponde a la cursada. Tiene instancia de evaluación asociada a otra fecha');
insert into ufce_cron_eval_parc_estados (estado, descripcion)
	values ('AH', 'Aceptada la fecha solicitada, pero modificado el horario. Tiene instancia de evaluación creada con un horario distinto a la cursada');
insert into ufce_cron_eval_parc_estados (estado, descripcion)
	values ('CH', 'Modificada a otro día de cursada y modificado el horario. Tiene instancia de evaluación asociada a otra fecha y otro horario');
insert into ufce_cron_eval_parc_estados (estado, descripcion)
	values ('RH', 'Reasiganda a otro día de la semana que no corresponde a la cursada y a otro horario. Tiene instancia de evaluación asociada a otra fecha y otro horario');

--select * from ufce_cron_eval_parc_estados;

------------------------------------------------------------------------------------------------------------
----------- PARA ADMINISTRAR PORCENTAJE EN EL PESO DE LAS NOTAS --------------------------------------------------
DROP TABLE dba.ufce_comisiones_porc_notas;
--DROP TABLE dba.ufce_ponderacion_notas;
CREATE TABLE dba.ufce_ponderacion_notas
(
	anio_academico 		INT NOT NULL,
	periodo_lectivo 	VARCHAR(20) NOT NULL,
	materia 			VARCHAR(5) NOT NULL, 
	calidad				VARCHAR(1) NOT NULL, 	--P:Promo, R:Regular, D: Promo Directa
	porc_parciales 		DECIMAL(5,2),
	porc_integrador 	DECIMAL(5,2),
	porc_trabajos		DECIMAL(5,2),
	CHECK (calidad IN ('P', 'R', 'D')),	
	PRIMARY KEY (anio_academico, periodo_lectivo, materia, calidad)
);


----------- PARA ADMINISTRAR MATERIAS POR PROMOCIÓN DIRECTA --------------------------------------------------
--DROP TABLE dba.ufce_materias_promo_directa;
CREATE TABLE dba.ufce_materias_promo_directa
(
	anio_academico 		INT NOT NULL,
	periodo_lectivo 	VARCHAR(20) NOT NULL,
	materia 			VARCHAR(5) NOT NULL,
	promo_directa		VARCHAR(1) NOT NULL,
	CHECK (promo_directa IN ('S', 'N')),
	PRIMARY KEY (anio_academico, periodo_lectivo, materia)
);
