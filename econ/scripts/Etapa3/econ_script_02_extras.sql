--Creación de tabla para registrar la carga de las fotos de los DNI por parte de los alumnos.
--DROP TABLE 'dba'.ufce_fotos_dni;
CREATE TABLE 'dba'.ufce_fotos_dni (
	unidad_academica VARCHAR(5) NOT NULL,
	nro_inscripcion VARCHAR(10) NOT NULL,
	archivo VARCHAR(125) NOT NULL,
	fecha_actualizacion DATE NOT NULL,
	PRIMARY KEY (unidad_academica, nro_inscripcion),
	FOREIGN KEY (unidad_academica, nro_inscripcion) REFERENCES sga_personas(unidad_academica, nro_inscripcion)
);

