IF NOT EXISTS (SELECT *
FROM ufce_fotos_dni
	WHERE 
		unidad_academica = pUnidadAcademica AND
		archivo IS NOT NULL AND
		YEAR(fecha_actualizacion) = YEAR(TODAY) AND
		nro_inscripcion = vcNroInscripcion) THEN
	RETURN -1, vcMsg || ',' || ' Debes registrar la foto de tu DNI' ;
END IF

IF NOT EXISTS (SELECT *
FROM sga_datos_cen_aux
	WHERE 
		unidad_academica = pUnidadAcademica AND
		YEAR(fecha_relevamiento) = YEAR(TODAY) AND
		nro_inscripcion = vcNroInscripcion AND
		celular_numero IS NOT NULL) THEN
	RETURN -1, vcMsg || ',' || ' Debes actualizar tu celular en tus datos censales' ;
END IF

IF NOT EXISTS (SELECT *
FROM sga_datos_censales
	WHERE 
		unidad_academica = pUnidadAcademica AND
		YEAR(fecha_relevamiento) = YEAR(TODAY) AND
		nro_inscripcion = vcNroInscripcion AND
		e_mail IS NOT NULL) THEN
	RETURN -1, vcMsg || ',' || ' Debes actualizar tu e-mail en tus datos censales' ;
END IF


SELECT *
FROM sga_alumnos
WHERE legajo = 'FCE-34215'


			pUnidadAcademica 	LIKE sga_alumnos.unidad_academica,
		pCarrera	 		LIKE sga_alumnos.carrera,
		pLegajo  			LIKE sga_alumnos.legajo,
		pMateria 			LIKE sga_materias.materia