CREATE PROCEDURE "dba".sp_titulo_aprobado( pUA like sga_alumnos.unidad_academica, pCarrera like sga_alumnos.carrera, pLegajo like sga_alumnos.legajo, pTitulo like sga_titulos_otorg.titulo )
	
  -- Este procedure retorna una lista con las orientaciones que tiene aprobadas el alumno.  Si la carrera no tiene orientaciones, entonces retorna '0', (la
  -- orientacion basica ).
  --
  -- Si no aprobo el titulo, no devuelve nada.
  RETURNING 	varchar(5),		-- Orientacion
  		varchar(5),		-- Plan
		varchar(5);		-- Version
  DEFINE 	vcPlan,vcVersion	varchar(5);
  DEFINE	vcOrientacion, vcOrientAlumno	varchar(5);
  DEFINE	vcMateria	varchar(5);
  DEFINE	vcCiclo		varchar(5);
  DEFINE	fPuntos,fpuntosparcial, fPuntosOrBas		float;
  DEFINE	iCantMaterias, iRtn, iTieneOrient	integer ;
  DEFINE	vcOrientBasica, vcOrientAdicional	varchar(5);
  DEFINE	vEgreso	LIKE sga_titulos_plan.calculo_egreso;
  DEFINE	fCreditosEgreso, fCreditosOriBas,fCreditosOriAdic 	LIKE sga_titulos_plan.total_cred_egreso;
  DEFINE        vcMsg	varchar(255);
  DEFINE        dFecha		DATE;
  DEFINE 	vcNroInsc		like sga_titulos_otorg.nro_inscripcion;
  DEFINE 	vcNroInscripcion		like sga_alumnos.nro_inscripcion;
  DEFINE		pAle		varchar(5);

BEGIN
   LET dFecha = TODAY; -- Este prodria reemplazarse por un parametro.
  LET iTieneOrient = 0;
  LET vcOrientBasica = '0';

SELECT nro_inscripcion  INTO vcNroInscripcion
    FROM sga_alumnos
    WHERE unidad_academica = pUA  AND 
	carrera = pCarrera  AND
	legajo = pLegajo  ;
  -- Obtengo el modo de egreso, plan y version del alumno
  EXECUTE PROCEDURE sp_plan_de_alumno( pUA, pCarrera, pLegajo, dFecha) INTO vcPlan, vcVersion;
  -----------------------------------------------------------------------------------------------------------------------------------------------------------------------
  -- Verifico que tenga cargado el requisito de ingles
  -----------------------------------------------------------------------------------------------------------------------------------------------------------------------
if (pCarrera <> 'CA003') then
 IF (vcPlan <> '1992' ) THEN 

  SELECT nro_inscripcion INTO vcNroInsc
	FROM sga_req_cumplidos
	WHERE 
		requisito = 2 AND
		nro_inscripcion = vcNroInscripcion AND
		carrera = pCarrera AND
		fecha_presentacion <= TODAY;

  IF ( vcNroInsc IS NULL ) THEN
	LET vcMateria = "Ingles";
	LET fPuntos = 0;
	LET vcCiclo = 'N/A';
	raise exception -746,0,'El Alumno (' || pLegajo || ') no cumple con el requisito de Ingles';
	RETURN;
  END IF;

  END IF;
end if;

  -----------------------------------------------------------------------------------------------------------------------------------------------------------------------
  -- Verifico que tenga los puntos de Actividades de Libre Eleccion suficioentes, respetando minimos obligatorios y maximos.
  -----------------------------------------------------------------------------------------------------------------------------------------------------------------------  
if (pCarrera <> 'CA003') then
IF (vcPlan <> '1992') THEN

  LET pAle = 'bien';
  EXECUTE PROCEDURE sp_controlar_extracur(pUA,pCarrera,pLegajo) INTO pAle;
  IF (pAle <> 'bien') THEN  --No cumple con los creditos de ALE
	LET vcMateria = pAle;
	LET fPuntos = 0;
	LET vcCiclo = 'N/A';
	raise exception -746,0,'El Alumno (' || pLegajo || ') no cumple creditos de ALE';
	RETURN;
  END IF;

END IF;
END IF;

  -----------------------------------------------------------------------------------------------------------------------------------------------------------------------
  -- Verifico si el Alumno cumplio con las Actividades curriculares segun el Título del Plan.
  -------------------------------------------------------------------------------------------------
  EXECUTE PROCEDURE sp_tit_actextracur( pUA, pCarrera, pLegajo, pTitulo, vcPlan, vcVersion) INTO iRtn, vcMsg;
  IF iRtn = -1 Then
	-- raise exception -746,0,vcMsg;
	Return ; 
  End If;	
 -------------------------------------------------------------------------------------------------
 -- Creo la tabla temporal de materias aprobadas. Este sera usado en sp_ciclo_aprobado
 -------------------------------------------------------------------------------------------------
 BEGIN
   ON EXCEPTION IN (-206)
   END EXCEPTION WITH RESUME;
   DROP TABLE tmp_ha_titulos;
 END;
 CREATE TEMP TABLE tmp_ha_titulos (materia varchar(5)) WITH NO LOG;
 INSERT INTO tmp_ha_titulos (materia) 
   SELECT materia
	FROM vw_hist_academica
	WHERE  unidad_academica = pUA
	AND    carrera = pCarrera  
	AND    legajo  = pLegajo
	AND    resultado = 'A'
	AND    fecha <= dFecha;
 -------------------------------------------------------------------------------------------------
  -- Recupero la forma de Egreso del Titulo.
  SELECT calculo_egreso  INTO vEgreso
  FROM sga_titulos_plan 
  WHERE unidad_academica = pUA
	AND carrera = pCarrera
	AND plan = vcPlan
	AND titulo = pTitulo;

  -- Egreso por Completitud de materias del Titulo.
  IF vEgreso  = 'AM' THEN
	 -----------------------------------------------------------------------------------------------------------------------------------------------------------------------
	 -- Verifico que tenga las orientaciones aprobadas, primero la orientacion básica y luego las otras.
	 -----------------------------------------------------------------------------------------------------------------------------------------------------------------------  
	 EXECUTE PROCEDURE sp_orient_aprobada ( pUA, pCarrera, pLegajo, pTitulo, vcPlan, vcVersion , vcOrientBasica, vEgreso,  'P') 
	   INTO vcOrientBasica, vcCiclo, vcMateria,  fPuntos ;
	 LET iCantMaterias = DBINFO("sqlca.sqlerrd2") ;
	 IF iCantMaterias > 0 THEN
	   DROP TABLE tmp_ha_titulos;
	   RETURN ;
	 END IF;
	 LET vcOrientBasica = '0';
	 -- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 -- Verifico si Tiene Orientaciones el Plan con ese Titulo y si 
	 -- el alumnno debia seleccionar una Orientacion
	 -- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 LET vcOrientAlumno = NULL;
	 IF (SELECT COUNT(*) FROM sga_titulos_plan
		 WHERE unidad_academica = pUA  AND
			carrera = pCarrera  AND
			plan = vcPlan   AND
			titulo = pTitulo  AND
			tiene_orient = 'S' AND
			elige_orient = 'S' ) > 0 THEN
		-- Verifico que el Alumno tenga una orientacion seleccionada:
		IF (SELECT COUNT(*) FROM sga_orient_alumno
			 WHERE unidad_academica = pUA  AND
				carrera = pCarrera  AND
				legajo = pLegajo  AND
				plan = vcPlan   AND
				titulo = pTitulo  ) <= 0 THEN
			DROP TABLE tmp_ha_titulos;
			-- Error. El alumno debe tener una Orientacion seleccionada...
			-- No puede egresar.
			raise exception -746,0,'El Alumno (' || pLegajo || ') debe seleccionar una Orientación';
		ELSE
			-- Selecciono Orientacion por la cual debo ver si egreso.
			SELECT orientacion
			INTO vcOrientAlumno
			FROM sga_orient_alumno
			WHERE unidad_academica = pUA  AND
				carrera = pCarrera  AND
				legajo = pLegajo  AND
				plan = vcPlan   AND
				titulo = pTitulo ;
		
		END IF;
	
	 END IF;
	 -- ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++		
	 -- tiene materias pendientes de la orientacion básica, nunca puede ser un egresado.
	 -----------------------------------------------------------------------------------------------------------------------------------------------------------------------
	 FOREACH SELECT orientacion
		   INTO vcOrientAdicional
		   FROM sga_orientaciones 
	  	WHERE unidad_academica = pUA  AND
			carrera = pCarrera  AND
			plan = vcPlan   AND
			titulo = pTitulo  AND
			orientacion <> vcOrientBasica 
		  -- Obtengo las materias pendientes para la orientacion, si no tiene ninguna entonces es un egresado.
		  LET iTieneOrient = 1;
		  IF vcOrientAlumno IS NULL or vcOrientAlumno = vcOrientAdicional THEN
			EXECUTE PROCEDURE sp_orient_aprobada ( pUA, pCarrera, pLegajo, pTitulo, vcPlan, vcVersion , vcOrientAdicional, vEgreso , 'P' ) 
			INTO vcOrientacion, vcCiclo, vcMateria, fPuntos;
			LET iCantMaterias = DBINFO("sqlca.sqlerrd2");
			IF iCantMaterias <= 0 THEN
				-- No debe nada de la orientacion, asi que retorno los valores
				RETURN vcOrientAdicional, vcPlan, vcVersion WITH RESUME;
			END IF;
		  END IF; -- Orientacion del Alumno o cualquier orientacion del Plan
	 END FOREACH; -- Orientaciones
	
	 DROP TABLE tmp_ha_titulos;
	 IF iTieneOrient = 0 THEN
	   -- No tiene orientaciones y tampoco materias pendientes para la basica
	   RETURN vcOrientBasica, vcPlan, vcVersion; -- Retorna la orientacion básica
	 END IF;
	 -- Sale sin orientacion aprobada.
	 RETURN ;
  END IF;
  IF vEgreso = 'CT' THEN
	-----------------------------------------------------------------------------------
	-- Obtengo los puntos necesario para Egresar
	-----------------------------------------------------------------------------------
	
	SELECT tp.total_cred_egreso
	INTO fCreditosEgreso
	FROM sga_titulos_plan tp
             WHERE tp.unidad_academica = pUA
	           AND tp.carrera = pCarrera
	           AND tp.plan = vcplan;
	 -------------------------------------------------------------------
	 -- Recupero los puntos dela Orientacion basica
	 -------------------------------------------------------------------
	 EXECUTE PROCEDURE sp_orient_aprobada ( pUA, pCarrera, pLegajo, pTitulo, vcPlan, vcVersion , vcOrientBasica, vEgreso,  'T') 
		INTO vcOrientBasica, vcCiclo, vcMateria,  fPuntosOrBas ;
	 LET vcOrientBasica = '0';
	 -- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 -- Verifico si Tiene Orientaciones el Plan con ese Titulo y si 
	 -- el alumnno debia seleccionar una Orientacion
	 -- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 LET vcOrientAlumno = NULL;
	 IF (SELECT COUNT(*) FROM sga_titulos_plan
		 WHERE unidad_academica = pUA  AND
			carrera = pCarrera  AND
			plan = vcPlan   AND
			titulo = pTitulo  AND
			tiene_orient = 'S' AND
			elige_orient = 'S' ) > 0 THEN
		-- Verifico que el Alumno tenga una orientacion seleccionada:
		IF (SELECT COUNT(*) FROM sga_orient_alumno
			 WHERE unidad_academica = pUA  AND
				carrera = pCarrera  AND
				legajo = pLegajo  AND
				plan = vcPlan   AND
				titulo = pTitulo  ) <= 0 THEN
			DROP TABLE tmp_ha_titulos;
			-- Error. El alumno debe tener una Orientacion seleccionada...
			-- No puede egresar.
			raise exception -746,0,'El Alumno (' || pLegajo || ') debe seleccionar una Orientación';
		ELSE
			-- Selecciono Orientacion por la cual debo ver si egreso.
			SELECT orientacion
			INTO vcOrientAlumno
			FROM sga_orient_alumno
			WHERE unidad_academica = pUA  AND
				carrera = pCarrera  AND
				legajo = pLegajo  AND
				plan = vcPlan   AND
				titulo = pTitulo ;
		
		END IF;
	 END IF;
	 LET fpuntos = fpuntosparcial;
	 FOREACH SELECT orientacion
		   INTO vcOrientAdicional
		   FROM sga_orientaciones 
	  	WHERE  unidad_academica = pUA  AND
			carrera = pCarrera  AND
			plan = vcPlan   AND
			titulo = pTitulo  AND
			orientacion <> vcOrientBasica 
		  LET iTieneOrient = 1;
		  LET fpuntosparcial = 0;
	
		  IF vcOrientAlumno IS NULL or vcOrientAlumno = vcOrientAdicional THEN
			-- Obtengo los puntos del a orientacion
			EXECUTE PROCEDURE sp_orient_aprobada ( pUA, pCarrera, pLegajo, pTitulo, vcPlan, vcVersion , vcOrientAdicional , 'T' ) 
			INTO vcOrientacion, vcCiclo, vcMateria, fpuntosparcial;
			LET fpuntosparcial = fpuntosparcial + fPuntosOrBas;
			
			-- Orientacion Basica + Orientacion x
			IF fPuntosparcial >= fCreditosEgreso  THEN
			   -- Egresa por creditos del titulo por esta orientacion.
			   RETURN vcOrientAdicional, vcPlan, vcVersion WITH RESUME; -- Retorna la orientacion
			END IF
		  END IF; -- Orientacion del Alumno o cualquier orientacion del Plan
	 END FOREACH; -- Orientaciones
	DROP TABLE tmp_ha_titulos;
	-- Si el titulo no tiene orientaciones, verifico que pasa con la basica.
	IF iTieneOrient = 0 THEN
		IF fPuntosOrBas >= fCreditosEgreso  THEN
		   -- Egresa por la Orientacion Basica.	
		   RETURN vcOrientBasica, vcPlan, vcVersion; -- Retorna la orientacion básica
		END IF
	END IF;
	Return; -- sale del sp
  END IF; -- Creditos del Titulo.

  IF vEgreso = 'CO' THEN
	-----------------------------------------------------------------------------
	-- Obtengo los creditos de la orientacion basica
	-----------------------------------------------------------------------------
	SELECT ori.total_cred_egreso
	INTO fCreditosOriBas 
	FROM sga_orientaciones ori
	WHERE ori.unidad_academica = pUA
	           AND ori.carrera = pCarrera
	           AND ori.plan = vcplan
	           AND ori.titulo = pTitulo 
	           AND ori.orientacion = '0';
	
	 -----------------------------------------------------------------------------------------------------------------------------------------------------------------------
	 -- Verifico que tenga las orientaciones aprobadas, primero la orientacion básica y luego las otras.
	 -----------------------------------------------------------------------------------------------------------------------------------------------------------------------  
	 EXECUTE PROCEDURE sp_orient_aprobada ( pUA, pCarrera, pLegajo, pTitulo, vcPlan, vcVersion , vcOrientBasica, vEgreso,  'T') 
	 INTO vcOrientBasica, vcCiclo, vcMateria,  fPuntosOrBas ;
	-- No cumplio con los creditos de la orientacion basica.
	IF fpuntosOrBas < fCreditosOriBas THEN
		DROP TABLE tmp_ha_titulos;
		RETURN ;
	END IF;
		 LET vcOrientBasica = '0';
		 -- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		 -- Verifico si Tiene Orientaciones el Plan con ese Titulo y si 
		 -- el alumnno debia seleccionar una Orientacion
		 -- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		 LET vcOrientAlumno = NULL;
		 IF (SELECT COUNT(*) FROM sga_titulos_plan
			 WHERE unidad_academica = pUA  AND
				carrera = pCarrera  AND
				plan = vcPlan   AND
				titulo = pTitulo  AND
				tiene_orient = 'S' AND
				elige_orient = 'S' ) > 0 THEN
			-- Verifico que el Alumno tenga una orientacion seleccionada:
			IF (SELECT COUNT(*) FROM sga_orient_alumno
				 WHERE unidad_academica = pUA  AND
					carrera = pCarrera  AND
					legajo = pLegajo  AND
					plan = vcPlan   AND
					titulo = pTitulo  ) <= 0 THEN
				DROP TABLE tmp_ha_titulos;
				-- Error. El alumno debe tener una Orientacion seleccionada...
				-- No puede egresar.
				raise exception -746,0,'El Alumno (' || pLegajo || ') debe seleccionar una Orientación';
			ELSE
				-- Selecciono Orientacion por la cual debo ver si egreso.
				SELECT orientacion
				INTO vcOrientAlumno
				FROM sga_orient_alumno
				WHERE unidad_academica = pUA  AND
					carrera = pCarrera  AND
					legajo = pLegajo  AND
					plan = vcPlan   AND
					titulo = pTitulo ;
		
			END IF;
		END IF;
		 LET fpuntos = fpuntosparcial;
		 FOREACH SELECT orientacion, total_cred_egreso
			   INTO vcOrientAdicional, fCreditosOriAdic
			   FROM sga_orientaciones 
		  	WHERE unidad_academica = pUA  AND
				carrera = pCarrera  AND
				plan = vcPlan   AND
				titulo = pTitulo  AND
				orientacion <> vcOrientBasica 
			  LET iTieneOrient = 1;
			  LET fpuntosparcial = 0;
	
			  IF vcOrientAlumno IS NULL or vcOrientAlumno = vcOrientAdicional THEN
				-- Obtengo los puntos del a orientacion
				EXECUTE PROCEDURE sp_orient_aprobada ( pUA, pCarrera, pLegajo, pTitulo, vcPlan, vcVersion , vcOrientAdicional , 'T' ) 
				INTO vcOrientacion, vcCiclo, vcMateria, fpuntosparcial;
				-- Egresa por la Orientacion y continua buscando en las otras orientaciones.
				IF fPuntosparcial >= fCreditosOriAdic THEN
				   RETURN vcOrientAdicional, vcPlan, vcVersion WITH RESUME; -- Retorna la orientacion
				END IF
			  END IF; -- Orientacion del Alumno o cualquier orientacion del Plan
		 END FOREACH; -- Orientaciones
	-- No tiene Orientaciones el Titulo.
	IF iTieneOrient = 0 THEN
	   DROP TABLE tmp_ha_titulos;
	   RETURN vcOrientBasica, vcPlan, vcVersion; -- Retorna la orientacion básica
	END IF;
  END IF; -- Creditos por Orientacion
  IF vEgreso = 'CC' THEN
	
	 ---------------------------------------------------------------------------------------
	 -- Verifico que tenga la orientaciones basica aprobada segun los creditos de sus ciclos
	 ---------------------------------------------------------------------------------------
	 EXECUTE PROCEDURE sp_orient_aprobada ( pUA, pCarrera, pLegajo, pTitulo, vcPlan, vcVersion , vcOrientBasica, vEgreso,  'T') 
	 INTO vcOrientBasica, vcCiclo, vcMateria,  fPuntosOrBas ;
	 -- Si retorna un ciclo, es porque no cumplio con los creditos de ese ciclo. No Egresa hay que estudiar mas....	
	 LET iCantMaterias = DBINFO("sqlca.sqlerrd2") ;
	 IF iCantMaterias > 0 THEN
		DROP TABLE tmp_ha_titulos;
		RETURN ;
	 END IF;
	 LET vcOrientBasica = '0';
	 -- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 -- Verifico si Tiene Orientaciones el Plan con ese Titulo y si 
	 -- el alumnno debia seleccionar una Orientacion
	 -- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 LET vcOrientAlumno = NULL;
	 IF (SELECT COUNT(*) FROM sga_titulos_plan
		 WHERE unidad_academica = pUA  AND
			carrera = pCarrera  AND
			plan = vcPlan   AND
			titulo = pTitulo  AND
			tiene_orient = 'S' AND
			elige_orient = 'S' ) > 0 THEN
		-- Verifico que el Alumno tenga una orientacion seleccionada:
		IF (SELECT COUNT(*) FROM sga_orient_alumno
			 WHERE unidad_academica = pUA  AND
				carrera = pCarrera  AND
				legajo = pLegajo  AND
				plan = vcPlan   AND
				titulo = pTitulo  ) <= 0 THEN
			DROP TABLE tmp_ha_titulos;
			-- Error. El alumno debe tener una Orientacion seleccionada...
			-- No puede egresar.
			raise exception -746,0,'El Alumno (' || pLegajo || ') debe seleccionar una Orientación';
		ELSE
			-- Selecciono Orientacion por la cual debo ver si egreso.
			SELECT orientacion
			INTO vcOrientAlumno
			FROM sga_orient_alumno
			WHERE unidad_academica = pUA  AND
				carrera = pCarrera  AND
				legajo = pLegajo  AND
				plan = vcPlan   AND
				titulo = pTitulo ;
	
		END IF;
	END IF;
	 -- Recorre las demas orientaciones.
	 FOREACH SELECT orientacion
		   INTO vcOrientAdicional
		   FROM sga_orientaciones 
   		 WHERE unidad_academica = pUA  AND
		      carrera = pCarrera  AND
			plan = vcPlan   AND
			titulo = pTitulo  AND
			orientacion <> vcOrientBasica 
		  LET iTieneOrient = 1;
		  IF vcOrientAlumno IS NULL OR vcOrientAlumno = vcOrientAdicional THEN
			-- Obtengo los puntos del a orientacion
			EXECUTE PROCEDURE sp_orient_aprobada ( pUA, pCarrera, pLegajo, pTitulo, vcPlan, vcVersion , vcOrientAdicional , 'T' ) 
			INTO vcOrientacion, vcCiclo, vcMateria, fpuntosparcial;
			LET iCantMaterias = DBINFO("sqlca.sqlerrd2") ;
			-- Como no devuelve nada, esta todo bien, aprueba por esta orientacion, sigue buscando en las demas.				
			IF iCantMaterias <= 0 THEN
				RETURN vcOrientAdicional, vcPlan, vcVersion WITH RESUME; 
		 	END IF;
		 END IF; -- Orientacion del Alumno o cualquier orientacion del Plan
	 END FOREACH; -- Orientaciones
	 DROP TABLE tmp_ha_titulos;
	 IF iTieneOrient = 0 THEN
	 	-- No tiene orientaciones y tampoco Ciclos pendientes para la orientacion basica
		RETURN vcOrientBasica, vcPlan, vcVersion; -- Retorna la orientacion básica
	 END IF;
  END IF;
  -- Borro la tabla temporal de HA
 BEGIN
   ON EXCEPTION IN (-206)
   END EXCEPTION WITH RESUME;
   DROP TABLE tmp_ha_titulos;
 END;
END;
END PROCEDURE
;