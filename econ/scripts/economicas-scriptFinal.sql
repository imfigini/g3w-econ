-- Dar permisos en aca_tipos_usuar_ag a los usuarios que se quiera tengan el perfil OFD (OFICINA DE DOCENTES)
{
INSERT INTO aca_tipos_usuar_ag VALUES ('FCE', 'FCE-427', 'OFD', 'A');	--28200412 --Lucia
}

------------------------------------------------------------------------------------------------------------------
----- Crear nueva tabla para administrar los coordinadores de materias -------------------------------------------
CREATE TABLE ufce_coordinadores_materias (
	unidad_academica VARCHAR(5) NOT NULL,
	materia VARCHAR(5) NOT NULL, 
	anio_academico INT NOT NULL, 
	periodo_lectivo VARCHAR(20) NOT NULL, 
	coordinador VARCHAR(10),
	PRIMARY KEY (materia, anio_academico, periodo_lectivo),
	FOREIGN KEY (unidad_academica, materia) REFERENCES sga_materias(unidad_academica, materia),
	FOREIGN KEY (anio_academico, periodo_lectivo) REFERENCES sga_periodos_lect(anio_academico, periodo_lectivo),
	FOREIGN KEY (coordinador) REFERENCES sga_docentes(legajo)
);



------------------------------------------------------------------------------------------------------------------
----------- PARA ADMINISTRAR PORCENTAJE EN EL PESO DE LAS NOTAS --------------------------------------------------
----------- SOLO PARA MATERIAS POR PROMOCION ---------------------------------------------------------------------
--SELECT * FROM sga_comisiones;
CREATE TABLE ufce_comisiones_porc_notas
(
	comision 		INTEGER NOT NULL, 
	porc_parciales 		DECIMAL(4,2),
	porc_integrador 	DECIMAL(4,2),
	porc_trabajos		DECIMAL(4,2),
	PRIMARY KEY (comision),
	FOREIGN KEY (comision) REFERENCES sga_comisiones(comision)
);

------------------------------------------------------------------------------------------------------------------
----------- PARA ADMINISTRAR PERIODOS DE EVALUACIONES ------------------------------------------------------------
--DROP TABLE ufce_orden_periodo;
CREATE TABLE ufce_orden_periodo (
	orden INT NOT NULL,
	descripcion VARCHAR(128),
	PRIMARY KEY (orden)
);
INSERT INTO ufce_orden_periodo VALUES (1, 'Primer Período de Evaluaciones');
INSERT INTO ufce_orden_periodo VALUES (2, 'Segundo Período de Evaluaciones');
INSERT INTO ufce_orden_periodo VALUES (3, '1º Llamado Julio/Agosto o Diciembre');


--DROP TABLE ufce_eval_parc_periodos;
CREATE TABLE ufce_eval_parc_periodos (
	anio_academico INT not null,
	periodo_lectivo VARCHAR(20) not null,
	orden INT not null,
	fecha_inicio DATE, 
	fecha_fin DATE,
	PRIMARY KEY (anio_academico, periodo_lectivo, orden),
	FOREIGN KEY (anio_academico, periodo_lectivo) REFERENCES sga_periodos_lect(anio_academico, periodo_lectivo),
	FOREIGN KEY (orden) REFERENCES ufce_orden_periodo(orden)
);

--DROP TABLE ufce_priodo_solic_fecha_parc;
CREATE TABLE ufce_priodo_solic_fecha_parc (
	anio_academico INT not null,
	periodo_lectivo VARCHAR(20) not null,
	fecha_inicio DATE,
	fecha_fin DATE,
	PRIMARY KEY (anio_academico, periodo_lectivo),
	FOREIGN KEY (anio_academico, periodo_lectivo) REFERENCES sga_periodos_lect(anio_academico, periodo_lectivo)
);

--DROP TABLE ufce_cron_eval_parc;
CREATE TABLE ufce_cron_eval_parc
(
	comision INT not null,
	evaluacion INT not null,
	fecha_hora DATETIME YEAR TO SECOND not null,
	estado VARCHAR(1) CHECK (estado IN ('P', 'A', 'R', 'N')),
	PRIMARY KEY (comision, evaluacion, fecha_hora)
--	,FOREIGN KEY (comision, evaluacion) REFERENCES sga_atr_eval_parc(comision, evaluacion)
);
--DROP TABLE ufce_cron_eval_parc_obs;
CREATE TABLE ufce_cron_eval_parc_obs
(
	materia VARCHAR(5) not null,
	anio_academico INT not null,
	periodo_lectivo VARCHAR(20) not null, 
	observaciones VARCHAR (255),
	PRIMARY KEY (materia, anio_academico, periodo_lectivo)
);

