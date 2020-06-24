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


--SP para controlar si cumple con los requisitos especiales (RD 050/2020) para iscribirse a una mesa de final. 
--DROP FUNCTION 'dba'.ctr_econ_corr(char,char,char,char);
CREATE PROCEDURE "dba".ctr_econ_corr (
		pUnidadAcademica 	LIKE sga_alumnos.unidad_academica,
		pCarrera	 		LIKE sga_alumnos.carrera,
		pLegajo  			LIKE sga_alumnos.legajo,
		pMateria 			LIKE sga_materias.materia
)
RETURNING smallint, varchar(255);
	DEFINE sql_err, isam_err, iStatus integer;
	DEFINE error_info, vcMsg varchar(76);
	DEFINE i_actual integer;
	DEFINE i_faltantes integer;	
	DEFINE vc_faltantes VARCHAR(255);
	
	DEFINE vcplan LIKE sga_planes.plan;
	DEFINE vcversion LIKE sga_versiones_plan.version;
	DEFINE vc_materia LIKE sga_materias.materia;
	
-- ON EXCEPTION SET sql_err, isam_err, error_info
-- 	LET iStatus = -1 ;
-- 	LET vcMsg = '800572' || ',' || 'ctr_econ_corr' || ',' || sql_err || ' - ' ||
-- 	error_info ;
-- 	DROP TABLE tmp_faltantes;
-- 	RETURN iStatus, vcMsg ;
-- END EXCEPTION;
BEGIN

	LET iStatus = 1 ;
	LET vcMsg = '800572' ;
	LET i_actual = 0;
	LET vc_materia = NULL;
	LET i_faltantes = NULL;
	LET vc_faltantes = '';
	
	-- Si la carrera es Auxiliar debe salir
	-- recupero el (plan, version) actual de la carrera
	EXECUTE PROCEDURE sp_plan_de_alumno (pUnidadAcademica, pCarrera, pLegajo, TODAY ) INTO vcPlan, vcVersion;
	IF (vcPlan IS NULL OR vcVersion IS NULL) THEN
		LET iStatus 	= -1; 
		LET vcMsg 	= '800361' || ',' || pLegajo ;   
		RETURN iStatus, vcMsg;
	END IF ;

	-- La materia a inscribirse, tiene que ser anterior
	-- de una que este inscripto actualmente.
	SELECT COUNT(*)
	INTO i_actual
	FROM sga_correlativas, sga_insc_cursadas, sga_comisiones
	WHERE 
		sga_insc_cursadas.unidad_academica = pUnidadAcademica AND
		sga_insc_cursadas.legajo = pLegajo AND
		sga_insc_cursadas.carrera = pCarrera AND
		sga_insc_cursadas.plan = vcPlan AND
		sga_insc_cursadas.version = vcVersion AND
		sga_insc_cursadas.comision = sga_comisiones.comision AND
		sga_comisiones.anio_academico = YEAR(TODAY) AND
		sga_comisiones.periodo_lectivo = 1 AND
		sga_comisiones.materia = sga_correlativas.materia AND
		sga_correlativas.materia_anterior = pMateria AND
		sga_correlativas.unidad_academica = sga_insc_cursadas.unidad_academica AND
		sga_correlativas.carrera = sga_insc_cursadas.carrera AND
		sga_correlativas.plan = sga_insc_cursadas.plan AND
		sga_correlativas.version = sga_insc_cursadas.version;

	-- Si cumple esta condicion, sale
	IF i_actual > 0 THEN
		RETURN iStatus, vcMsg;
	END IF;

	-- Ahora debo ver si es que solo debe una materia:
	SELECT 
		sga_atrib_mat_plan.materia
	FROM 
		sga_materias_ciclo, 
		sga_ciclos_orient,
		sga_ciclos_plan,
		sga_atrib_mat_plan,
		sga_orient_alumno	
	WHERE
		sga_ciclos_orient.carrera = sga_ciclos_plan.carrera AND
		sga_ciclos_orient.plan = sga_ciclos_plan.plan AND
		sga_ciclos_orient.version = sga_ciclos_plan.version AND
		sga_ciclos_orient.ciclo = sga_ciclos_plan.ciclo AND

		sga_materias_ciclo.ciclo = sga_ciclos_plan.ciclo AND

		sga_ciclos_plan.carrera = sga_atrib_mat_plan.carrera AND
		sga_ciclos_plan.plan = sga_atrib_mat_plan.plan AND
		sga_ciclos_plan.version = sga_atrib_mat_plan.version AND

		sga_atrib_mat_plan.materia = sga_materias_ciclo.materia AND 
		sga_atrib_mat_plan.unidad_academica = pUnidadAcademica AND
		sga_atrib_mat_plan.carrera = pCarrera AND
		sga_atrib_mat_plan.plan = vcPlan AND
		sga_atrib_mat_plan.version = vcVersion AND 
		sga_atrib_mat_plan.obligatoria = 'S' AND
		sga_atrib_mat_plan.materia NOT IN (
		SELECT vw_hist_academica.materia
		FROM vw_hist_academica
			WHERE 
				vw_hist_academica.unidad_academica = pUnidadAcademica AND
				vw_hist_academica.legajo = pLegajo AND
				vw_hist_academica.carrera = pCarrera AND
				vw_hist_academica.resultado = 'A'
		) AND
		sga_atrib_mat_plan.nombre_materia NOT LIKE 'Trabajo Final' AND
		sga_orient_alumno.legajo = pLegajo AND
		
		(sga_ciclos_orient.orientacion = '0' 
		OR sga_ciclos_orient.orientacion = sga_orient_alumno.orientacion)-- Plan basico o especializacion
	INTO TEMP tmp_faltantes
	WITH NO LOG;

	SELECT COUNT(*) 
	INTO i_faltantes
	FROM tmp_faltantes;


	FOREACH SELECT materia 
			INTO vc_materia
			FROM tmp_faltantes

			LET vc_faltantes = vc_faltantes || ' ' || vc_materia;
	END FOREACH;


	IF i_faltantes > 1 THEN
		DROP TABLE tmp_faltantes;
		RETURN -1, vcMsg || ',' || ' Adeuda otras materias: (' || vc_faltantes || ')' ;
	END IF;

	SELECT materia
	INTO  vc_materia
	FROM tmp_faltantes;

	DROP TABLE tmp_faltantes;
	IF vc_materia <> pMateria THEN
		RETURN iStatus, vcMsg;
	END IF;	
	

	RETURN iStatus,vcMsg;
END;
END PROCEDURE;