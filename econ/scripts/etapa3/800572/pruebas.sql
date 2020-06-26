	SELECT 
		sga_ciclos_orient.orientacion,
		sga_materias_ciclo.ciclo, 
		*
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
		sga_atrib_mat_plan.unidad_academica = 'FCE' AND
		sga_atrib_mat_plan.carrera = 'CA001' AND
		sga_atrib_mat_plan.plan = '2001' AND
		sga_atrib_mat_plan.version = '9' AND 
		sga_atrib_mat_plan.obligatoria = 'S' AND
		sga_atrib_mat_plan.materia NOT IN (
		SELECT vw_hist_academica.materia
		FROM vw_hist_academica
			WHERE 
				vw_hist_academica.unidad_academica = 'FCE' AND
				vw_hist_academica.legajo = 'FCE-34215' AND
				vw_hist_academica.carrera = 'CA001' AND
				vw_hist_academica.resultado = 'A'
		) AND
		sga_atrib_mat_plan.nombre_materia NOT LIKE 'Trabajo Final' AND
		sga_orient_alumno.legajo = 'FCE-34215' AND
		
		(sga_ciclos_orient.orientacion = '0' 
		OR sga_ciclos_orient.orientacion = sga_orient_alumno.orientacion)-- Plan basico o especializacion
		;