--Tabla para registrar los términos y condiciones para rendir integrador por parte de los alumnos
--DROP TABLE ufce_acpt_term_cond;
CREATE TABLE 'dba'.ufce_acpt_term_cond (
	legajo VARCHAR(15) not null,
	anio_academico INT not null,
	periodo_lectivo VARCHAR(20) not null,
	fecha DATETIME YEAR TO SECOND,
	PRIMARY KEY (legajo, anio_academico, periodo_lectivo)
);