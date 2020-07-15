--Tabla para almacenar fecha límite a controlar cumplimiento de materias correlativas aprobadas
--DROP TABLE ufce_fechas_ctr_correlat;
CREATE TABLE 'dba'.ufce_fechas_ctr_correlat (
	anio_academico INT not null,
	periodo_lectivo VARCHAR(20) not null,
	fecha DATE,
	PRIMARY KEY (anio_academico, periodo_lectivo),
	FOREIGN KEY (anio_academico, periodo_lectivo) REFERENCES sga_periodos_lect(anio_academico, periodo_lectivo)
);
