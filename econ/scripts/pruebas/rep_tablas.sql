CREATE TABLE 'dba'.rep_fecha_actualiz_tablas (
    tabla VARCHAR(100),
    fecha_ultima_actualizacion DATETIME YEAR TO SECOND
);


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

