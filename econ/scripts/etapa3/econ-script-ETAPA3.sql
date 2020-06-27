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

--Para activar los controles en "Inscripció a examen"
UPDATE sga_conf_controles  
SET activo = 'S'
WHERE control IN ('800572', '800573')
AND operacion = 'exa00006';

UPDATE par_cont_x_oper 
SET es_valido = 'S'
WHERE operacion = 'exa00006'
AND control IN (800572, 800573);
