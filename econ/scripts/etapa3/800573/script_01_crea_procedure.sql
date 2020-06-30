-- ********************************************************************************
-- CONTROL PERSONALIZADO
-- PROCEDUERE ctr_econ_dni_foto
-- Verifica que tenga los datos censales (algunos) actualizadso
-- ********************************************************************************
-- DROP PROCEDURE "dba".ctr_econ_dni_foto;
CREATE PROCEDURE "dba".ctr_econ_dni_foto (
		pUnidadAcademica 	LIKE sga_alumnos.unidad_academica,
		pCarrera	 		LIKE sga_alumnos.carrera,
		pLegajo  			LIKE sga_alumnos.legajo,
		pMateria 			LIKE sga_materias.materia
)
RETURNING smallint, varchar(255);
	DEFINE sql_err, isam_err, iStatus integer;
	DEFINE error_info, vcMsg varchar(76);
	DEFINE vcNroInscripcion varchar(10);
-- ON EXCEPTION SET sql_err, isam_err, error_info
-- 	LET iStatus = -1 ;
-- 	LET vcMsg = '800572' || ',' || 'ctr_econ_corr' || ',' || sql_err || ' - ' ||
-- 	error_info ;
-- 	DROP TABLE tmp_faltantes;
-- 	RETURN iStatus, vcMsg ;
-- END EXCEPTION;
BEGIN

	LET iStatus = 1 ;
	LET vcMsg = '800573' ;

	SELECT nro_inscripcion
	INTO vcNroInscripcion
	FROM sga_alumnos
	WHERE 
		unidad_academica = pUnidadAcademica AND
		carrera = pCarrera AND
		legajo = pLegajo;
	
	IF NOT EXISTS (SELECT *
	FROM ufce_fotos_dni
		WHERE 
			unidad_academica = pUnidadAcademica AND
			archivo IS NOT NULL AND
			YEAR(fecha_actualizacion) = YEAR(TODAY) AND
			nro_inscripcion = vcNroInscripcion) THEN
		RETURN -1, vcMsg || ',' || ' Debes registrar la foto de tu DNI' ;
	END IF;

	RETURN iStatus,vcMsg;
END;
END PROCEDURE;

{
/**
* Pruebas:
EXECUTE PROCEDURE "dba".ctr_econ_dni_foto ('FCE', 'CA002',	'FCE-737945', 'M0112');
EXECUTE PROCEDURE "dba".ctr_econ_dni_foto ('FCE', 'CA001', 'FCE-34215', 'M0112');
}
