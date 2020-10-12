--DROP PROCEDURE 'dba'.sp_rep_nuevos_inscriptos();

CREATE TABLE rep_nuevos_inscriptos (
	sede VARCHAR(5), 
	legajo VARCHAR(10),
	apellido VARCHAR (30),
	nombres VARCHAR(30),
	dni VARCHAR(15),
	carrera_nro VARCHAR(5),
	carrera VARCHAR(100),
	anio_ingreso int,
	fecha_nacim date,
	e_mail VARCHAR(50),
	ciudad_proced VARCHAR(100),
	prov_proced VARCHAR(60),
	colegio_secundario VARCHAR(100),
	ciudad_colegio VARCHAR(100),
	prov_colegio VARCHAR(60)
);
--DROP INDEX idx5;
CREATE INDEX idx5 ON rep_nuevos_inscriptos (legajo);


CREATE PROCEDURE "dba".sp_rep_nuevos_inscriptos()
BEGIN
--Inscriptos a la Facultad por primera vez
--php/listado_de_nuevos_inscriptos/ci_listado_de_nuevos_inscriptos.php
{
--DROP TABLE rep_nuevos_inscriptos;
CREATE TABLE rep_nuevos_inscriptos (
	sede VARCHAR(5), 
	legajo VARCHAR(10),
	apellido VARCHAR (30),
	nombres VARCHAR(30),
	dni VARCHAR(15),
	carrera_nro VARCHAR(5),
	carrera VARCHAR(100),
	anio_ingreso int,
	fecha_nacim date,
	e_mail VARCHAR(50),
	ciudad_proced VARCHAR(100),
	prov_proced VARCHAR(60),
	colegio_secundario VARCHAR(100),
	ciudad_colegio VARCHAR(100),
	prov_colegio VARCHAR(60)
);
--DROP INDEX idx5;
CREATE INDEX idx5 ON rep_nuevos_inscriptos (legajo);
}

DELETE FROM rep_nuevos_inscriptos;

SELECT DISTINCT A.*
FROM "dba".sga_alumnos A
	WHERE A.fecha_ingreso = (SELECT MIN(A2.fecha_ingreso) FROM "dba".sga_alumnos A2
					WHERE A.unidad_academica = A2.unidad_academica
					AND A.nro_inscripcion = A2.nro_inscripcion)
	INTO TEMP auxi WITH NO LOG;

INSERT INTO rep_nuevos_inscriptos
SELECT A.sede, A.legajo, P.apellido, P.nombres, P.nro_documento, A.carrera, C.nombre_reducido, YEAR(A.fecha_ingreso), 
	P.fecha_nacimiento, D.e_mail, INITCAP(LOC.nombre), INITCAP(PROV.nombre), INITCAP(COL.nombre), 
	INITCAP(LOC_SEC.nombre), INITCAP(PROV_SEC.nombre)
		FROM auxi A
		JOIN sga_personas P ON (A.unidad_academica = P.unidad_academica AND A.nro_inscripcion = P.nro_inscripcion)
		LEFT JOIN vw_datos_censales_actuales D ON (A.nro_inscripcion = D.nro_inscripcion)
		LEFT JOIN mug_localidades LOC ON (D.loc_proc = LOC.localidad)
		LEFT JOIN mug_dptos_partidos x ON (LOC.dpto_partido = x.dpto_partido)
		LEFT JOIN mug_provincias PROV ON (PROV.provincia = x.provincia)
		LEFT JOIN sga_coleg_sec COL ON (COL.colegio = P.colegio_secundario)
		LEFT JOIN mug_localidades LOC_SEC ON (COL.localidad = LOC_SEC.localidad)
		LEFT JOIN mug_dptos_partidos x_sec ON (LOC_SEC.dpto_partido = x_sec.dpto_partido)
		LEFT JOIN mug_provincias PROV_SEC ON (PROV_SEC.provincia = x_sec.provincia)
		JOIN sga_carreras C ON (A.unidad_academica = P.unidad_academica AND A.carrera = C.carrera);

ALTER INDEX idx5 TO cluster;

DROP TABLE auxi;

INSERT INTO rep_fecha_actualiz_tablas VALUES ('rep_nuevos_inscriptos', CURRENT YEAR TO SECOND);


END;
END PROCEDURE;
EXECUTE PROCEDURE 'dba'.sp_rep_nuevos_inscriptos();
