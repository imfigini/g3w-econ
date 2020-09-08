-- ********************************************************************************
-- CONTROL PERSONALIZADO
-- PROCEDUERE ctr_econ_10finales
-- 
-- ********************************************************************************
DROP PROCEDURE "dba".ctr_econ_10finales;
CREATE PROCEDURE "dba".ctr_econ_10finales (
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
	DEFINE i_aprobados integer;	
	DEFINE vc_faltantes VARCHAR(255);

	DEFINE i_materias_plan integer;	
	DEFINE vcplan LIKE sga_planes.plan;
	DEFINE vcversion LIKE sga_versiones_plan.version;
	DEFINE vc_materia LIKE sga_materias.materia;
	DEFINE vc_tiene_orient LIKE sga_titulos_plan.tiene_orient;
	
BEGIN

	LET iStatus = 1 ;
	LET vcMsg = '800574' ;
	LET i_actual = 0;
	LET vc_materia = NULL;
	LET i_faltantes = NULL;
	LET i_aprobados = NULL;
	LET i_materias_plan = NULL;
	LET vc_faltantes = '';
	LET vc_tiene_orient = 'N';
	
--SET DEBUG FILE TO '/INFORMIXTMP/ctr_econ_10finales.sql';
--TRACE ON;

	-- recupero el (plan, version) actual de la carrera
	EXECUTE PROCEDURE sp_plan_de_alumno (pUnidadAcademica, pCarrera, pLegajo, TODAY ) INTO vcPlan, vcVersion;
	IF (vcPlan IS NULL OR vcVersion IS NULL) THEN
		LET iStatus 	= -1; 
		LET vcMsg 	= '800361' || ',' || pLegajo ;   
		RETURN iStatus, vcMsg;
	END IF ;

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
		-- Verifico si el alumno ya eligió la orientacion, si no la tiene, cuento los finales que tiene aprobados y me fijo
		IF NOT EXISTS (
			SELECT *
			FROM sga_orient_alumno
			WHERE
				unidad_academica =  pUnidadAcademica AND
				carrera = pCarrera AND
				plan = vcPlan AND
				legajo = pLegajo
		) THEN
			--Cuento los finales que tiene aprobados
			SELECT COUNT(*) 
				INTO i_aprobados
			FROM vw_hist_academica
				WHERE legajo = pLegajo
				AND carrera = pCarrera
				AND resultado = 'A';			
		
			SELECT cnt_materias		
				INTO i_materias_plan
			FROM sga_planes
				WHERE carrera = pCarrera
				AND plan = vcPlan
				AND version_actual = vcVersion;

			LET i_faltantes = i_materias_plan - i_aprobados;

			-- Si debe más de 10 materias
			IF (i_faltantes > 10) THEN 
				RETURN -1, vcMsg || ',' || ' No tiene orientación y debe más de 10 materias.' ;
			END IF;

			RETURN iStatus,vcMsg;
		END IF;

		-- Si tiene elegida la orientación, debo ver qué materias le faltan dentro de la misma
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
	ELSE -- Esta rama es la que NO tiene orientacion:
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

	FOREACH SELECT DISTINCT materia 
			INTO vc_materia
			FROM tmp_faltantes

			LET vc_faltantes = vc_faltantes::varchar(100) || ' ' || vc_materia;
	END FOREACH;

	--Si adeuda más de 10 finales, no puede inscribirse
	IF i_faltantes > 10 THEN
		DROP TABLE tmp_faltantes;
		RETURN -1, vcMsg || ',' || ' Adeuda mas de 10 materias: (' || vc_faltantes || ')' ;
	END IF;

	--Si la materia a la que se está inscribiendo no es una de las faltantes, salvo que sea el requisto de idioma, no lo dejo
	IF NOT EXISTS (SELECT * FROM tmp_faltantes WHERE materia = pMateria) THEN
		DROP TABLE tmp_faltantes;
		--Si la materia que se quiere inscribir es "Taller de Idioma I" o "Taller de Idioma II"
		IF pMateria = 'LT004' OR pMateria = 'LT005' THEN
			--Y dicha materia es requisito en el plan del alumno, lo dejo inscirbirse
			IF EXISTS (SELECT * FROM sga_atrib_mat_plan WHERE materia IN ('LT004', 'LT005')
								AND carrera = pCarrera
								AND plan = vcPlan
								AND version = vcVersion 
								AND obligatoria = 'N') THEN 
				RETURN iStatus,vcMsg;	
			END IF;
		END IF;
		RETURN -1, vcMsg || ',' || ' Debe elegir una materia de las faltantes: (' || vc_faltantes || ')' ;
	END IF;

	DROP TABLE tmp_faltantes;
	RETURN iStatus,vcMsg;

--TRACE OFF;
END;
END PROCEDURE;

{
/**
* Pruebas:
EXECUTE PROCEDURE "dba".ctr_econ_10finales ('FCE', 'CA001', 'FCE-160015', 'M0112');

EXECUTE PROCEDURE "dba".ctr_econ_10finales ('FCE', 'CA002', 'FCE-630641', 'M0062');
EXECUTE PROCEDURE "dba".ctr_econ_10finales ('FCE', 'CA002', 'FCE-630641', 'LT004');

EXECUTE PROCEDURE "dba".ctr_econ_10finales ('FCE', 'CA002', 'FCE-492910', 'M0024');
EXECUTE PROCEDURE "dba".ctr_econ_10finales ('FCE', 'CA002', 'FCE-492910', 'M0062');

EXECUTE PROCEDURE "dba".ctr_econ_10finales ('FCE', 'CA002', 'FCE-610901', 'M0057');
EXECUTE PROCEDURE "dba".ctr_econ_10finales ('FCE', 'CA002', 'FCE-610901', 'M0062');

EXECUTE PROCEDURE "dba".ctr_econ_10finales ('FCE', 'CA002', 'FCE-667529', 'M0010');
EXECUTE PROCEDURE "dba".ctr_econ_10finales ('FCE', 'CA002', 'FCE-667529', 'M0015');
EXECUTE PROCEDURE "dba".ctr_econ_10finales ('FCE', 'CA002', 'FCE-667529', 'M0017');
EXECUTE PROCEDURE "dba".ctr_econ_10finales ('FCE', 'CA002', 'FCE-667529', 'M0024');
EXECUTE PROCEDURE "dba".ctr_econ_10finales ('FCE', 'CA002', 'FCE-667529', 'M0056');
}
