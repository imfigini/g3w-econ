-- ********************************************************************************
-- CONTROL PERSONALIZADO
-- PROCEDUERE ctr_cursada_NM4
-- 
-- ********************************************************************************
-- DROP PROCEDURE "dba".ctr_econ_corr;
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
	DEFINE vc_tiene_orient LIKE sga_titulos_plan.tiene_orient;
	
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
	LET vc_tiene_orient = 'N';
	
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
	FROM sga_correlativas, sga_insc_cursadas, sga_comisiones, sga_periodos_lect
	WHERE 
		sga_insc_cursadas.unidad_academica = pUnidadAcademica AND
		sga_insc_cursadas.legajo = pLegajo AND
		sga_insc_cursadas.carrera = pCarrera AND
		sga_insc_cursadas.plan = vcPlan AND
		sga_insc_cursadas.version = vcVersion AND
		sga_insc_cursadas.comision = sga_comisiones.comision AND
		sga_periodos_lect.anio_academico = sga_comisiones.anio_academico AND
		sga_periodos_lect.periodo_lectivo = sga_comisiones.periodo_lectivo AND

		sga_periodos_lect.anio_academico = YEAR(TODAY) AND
		(TODAY BETWEEN sga_periodos_lect.fecha_inicio AND sga_periodos_lect.fecha_fin) AND
		
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

	-- Verifico si el plan tiene orientacion:
	SELECT tiene_orient
	INTO vc_tiene_orient 
	FROM sga_titulos_plan
	WHERE 
		unidad_academica =  pUnidadAcademica AND
		carrera = pCarrera AND
		plan = vcPlan;

	-- El plan tiene orientacion. Debo verificar si ya la eligio y que materias le faltan
	IF vc_tiene_orient = 'S' THEN
	BEGIN
		-- Verifico si tienen orientacion, si no la tiene, es porque le falta mucho aun
		IF NOT EXISTS (
			SELECT *
			FROM sga_orient_alumno
			WHERE
				unidad_academica =  pUnidadAcademica AND
				carrera = pCarrera AND
				plan = vcPlan AND
				legajo = pLegajo
		) THEN
			RETURN -1, vcMsg || ',' || ' Debe faltarse solo una materia.' ;

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
	END
	ELSE -- Esta rama es la que tiene orientacion:
	BEGIN
		SELECT 
			sga_atrib_mat_plan.materia
		FROM 
			sga_atrib_mat_plan	
		WHERE
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
			sga_atrib_mat_plan.nombre_materia NOT LIKE 'Trabajo Final'
		INTO TEMP tmp_faltantes
		WITH NO LOG;

	END
	END IF;

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
		RETURN -1, vcMsg || ',' || ' Solo podes rendir la materia: (' || vc_materia || ')';
	END IF;	
	

	RETURN iStatus,vcMsg;
END;
END PROCEDURE;

/**
* Pruebas:
EXECUTE PROCEDURE "dba".ctr_econ_corr ('FCE', 'CA001', 'FCE-160015', 'M0112');
EXECUTE PROCEDURE "dba".ctr_econ_corr ('FCE', 'CA001', 'FCE-34215', 'M0112');

