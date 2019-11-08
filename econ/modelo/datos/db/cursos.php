<?php
namespace econ\modelo\datos\db;

use kernel\kernel;
use kernel\util\db\db;
use siu\modelo\datos\util;
use siu\errores\error_guarani;

class cursos 
{
    /**
     * parametros: legajo, carrera, mix
     * param_null: legajo, carrera, mix
     * cache: no
     * filas: n
     */
    function get_materias_cincuentenario($parametros)
    {
        $sql = "SELECT  DISTINCT M.materia,
                        M.nombre AS materia_nombre
                    FROM sga_materias M, ufce_mixes X 
                    WHERE X.materia = M.materia ";

        if ($parametros['legajo'] != "''")
        {
            $legajo = $parametros['legajo'];
            $sql .= " AND M.materia IN (SELECT materia FROM ufce_coordinadores_materias WHERE coordinador = $legajo) ";
        }
        if (isset($parametros['carrera']) and $parametros['carrera'] != "''")
        {
            $carrera = $parametros['carrera'];
            $sql .= " AND X.carrera = $carrera ";
        }
        if (isset($parametros['mix']) and $parametros['mix'] != "''")
        {
            $anio = substr($parametros['mix'], 1, 1);
            $mix = substr($parametros['mix'], 2, 1);
            $sql .= " AND X.anio_de_cursada = $anio
                      AND X.mix = '$mix'  ";
        }
        $sql .= " ORDER BY 2";
        $result = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        return $result;
    }
    
    /**
     * parametros: materia
     * cache: no
     * filas: n
     */
    function get_ciclo_de_materias($parametros)
    {
        $materia = $parametros['materia'];
        $sql = "SELECT DISTINCT 
                            CASE 
                                    WHEN lower(CIC.nombre) LIKE '%fundam%' THEN 'F'
                                    WHEN lower(CIC.nombre) LIKE '%prof%' THEN 'P'
                            END AS ciclo
                    FROM sga_materias M
                    JOIN sga_materias_ciclo MC ON (MC.materia = M.materia)
                    JOIN sga_ciclos CIC ON (CIC.ciclo = MC.ciclo)
                    JOIN sga_ciclos_plan CP ON (CIC.ciclo = CP.ciclo)
                    WHERE CP.plan IN (SELECT plan FROM sga_planes WHERE carrera = CP.carrera AND plan = CP.plan AND version_actual = CP.version AND estado = 'V')
                    AND M.materia = $materia ";
        $result = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        switch (count($result))
        {
            case 1: return ($result[0]['CICLO']); 
            case 2: return ('FyP'); 
        }
    }
                
    /**
     * parametros: anio_academico, periodo, materia
     * cache: no
     * filas: 1
     */
    function get_observaciones_materia($parametros)
    {
        $materia = $parametros['materia'];
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $sql = "SELECT observaciones
                    FROM ufce_cron_eval_parc_obs 
                    WHERE materia = $materia 
                        AND anio_academico = $anio_academico 
						AND periodo_lectivo = $periodo ";
        $result = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
        if (count($result) > 0)
        {
            return $result['OBSERVACIONES'];
        }
        return null;
    }

    /**
     * parametros: anio_academico, periodo, materia
     * param_null: periodo
     * cache: no
     * filas: n
     */
    function get_comisiones_promo_de_materia($parametros)
    {
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $materia = $parametros['materia'];
        
        $promociones = '4,6,9,10';
        //$regulares = '3,4,5,7,8,10';
        //
        //Sólo retorna las comisiones que son promocionables
        $sql = "SELECT  C.comision, 
                        C.nombre AS comision_nombre, 
                        C.anio_academico, 
                        C.periodo_lectivo,
                        C.escala_notas, 
                        C.carrera, 
                        DECODE(C.turno,'T', 'Tarde', 'N', 'Noche', 'M', 'Mañana', 'No informa') as turno,
                        EN.nombre AS escala_notas_nombre,
                        UC.porc_parciales,
                        UC.porc_integrador,
                        UC.porc_trabajos
                FROM sga_comisiones C
                JOIN sga_escala_notas EN ON (EN.escala_notas = C.escala_notas)
                LEFT JOIN ufce_comisiones_porc_notas UC ON (UC.comision = C.comision)
                WHERE C.estado = 'A'
                        AND EN.escala_notas IN ($promociones)
                        AND C.anio_academico = $anio_academico
                        AND C.materia = $materia ";

        if ($periodo != "''")
        {
            $sql .= " AND C.periodo_lectivo = $periodo";
        }
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
    /**
    * parametros: comision
    * cache: memoria
    * filas: n
    */
    function get_nombre_comision($parametros)
    {
        $comision = $parametros['comision'];

        $sql = "SELECT nombre
                    FROM sga_comisiones
                WHERE   comision = $comision";
        $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        return $datos[0]['NOMBRE'];  
    }

    /**
    * parametros: materia
    * cache: memoria
    * filas: n
    */
    function get_nombre_materia($parametros)
    {
        if (isset($parametros['materia']))
        {
            $materia = $parametros['materia'];
        
            $sql = "SELECT nombre
                        FROM sga_materias
                    WHERE   materia = $materia";
            $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
            return $datos[0]['NOMBRE'];  
        }
        if (isset($parametros['comision']))
        {
            $comision = $parametros['comision'];
        
            $sql = "SELECT nombre
                        FROM sga_materias
                    WHERE   materia IN (
                                SELECT materia FROM sga_comisiones WHERE comision = $comision)";
            $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
            return $datos[0]['NOMBRE'];  
        }
        return '';
    }
    
    /**
    * parametros: comision
    * cache: memoria
    * filas: n
    */
    function get_nombre_materia_de_comision($parametros)
    {
        $comision = $parametros['comision'];

        $sql = "SELECT nombre
                    FROM sga_materias
                WHERE   materia IN (
                            SELECT materia FROM sga_comisiones WHERE comision = $comision)";
        $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        return $datos[0]['NOMBRE'];  
    }

    /**
     * parametros: anio_academico, periodo, materia
     * param_null: periodo
     * cache: no
     * filas: n
     */
    function get_comisiones_de_materia_con_dias_de_clase($parametros)
    {
        /*  3	Reales Regular
            4	Reales Promoció
            6	Promocion Oblig
		*/
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $materia = $parametros['materia'];
        //Retorna todas las comisiones de la materia detallando si es regular, promoción o ambas
        //Sólo retorna las comisiones que tienen días de cursada asignados
        //DECODE(C.turno,'T', 'Tarde', 'N', 'Noche', 'M', 'Mañana', 'No informa') as turno, 
         $sql = "SELECT  C.comision, 
                        C.nombre AS comision_nombre, 
                        C.anio_academico, 
                        C.periodo_lectivo,
						C.escala_notas,
                        CASE 
                            WHEN C.escala_notas = 3 THEN 'R'
                            WHEN C.escala_notas = 4 THEN 'PyR'
                            WHEN C.escala_notas = 5 THEN 'R'
                            WHEN C.escala_notas = 6 THEN 'P'
                            WHEN C.escala_notas = 8 THEN 'R'
                            WHEN C.escala_notas = 9 THEN 'P'
                            WHEN C.escala_notas = 10 THEN 'PyR'
                        END AS escala,
                        C.turno,
                        C.carrera
                FROM sga_comisiones C
                JOIN sga_escala_notas EN ON (EN.escala_notas = C.escala_notas)
                WHERE C.estado = 'A'
                        AND C.anio_academico = $anio_academico
                        AND C.materia = $materia 
                        AND C.comision IN (SELECT comision FROM sga_calendcursada)";  
      
        if ($periodo != "''")
        {
            $sql .= " AND C.periodo_lectivo = $periodo";
        }
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
 
    /**
     * parametros: comision
     * cache: no
     * filas: n
     */
    function get_dias_clase($parametros)
    {
        $comision = $parametros['comision'];
        $sql = "SELECT DISTINCT sga_asignaciones.dia_semana - 1 AS dia_semana, 
                                sga_asignaciones.hs_comienzo_clase, 
                                sga_asignaciones.hs_finaliz_clase,
                                CASE 
                                    when sga_asignaciones.dia_semana = 1 then 'Domingo'
                                    when sga_asignaciones.dia_semana = 2 then 'Lunes'
                                    when sga_asignaciones.dia_semana = 3 then 'Martes'
                                    when sga_asignaciones.dia_semana = 4 then 'Miércoles'
                                    when sga_asignaciones.dia_semana = 5 then 'Jueves'
                                    when sga_asignaciones.dia_semana = 6 then 'Viernes'
                                    when sga_asignaciones.dia_semana = 7 then 'Sábado'
                                END AS dia_nombre
                    FROM    sga_comisiones, 
                            sga_calendcursada,
                            sga_asignaciones
		WHERE   sga_comisiones.comision = sga_calendcursada.comision
                        AND sga_calendcursada.comision = $comision
                        AND sga_calendcursada.asignacion = sga_asignaciones.asignacion
                ORDER BY 1";
        
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
    /**
     * parametros: anio_academico, periodo, materia
     * cache: no
     * filas: n
     */
    function get_tipo_escala_de_materia($parametros)
    {
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $materia = $parametros['materia'];
        
        $promociones = '4,6,9,10';
        $regulares = '3,4,5,8,10';
        
        $sql = "SELECT  DISTINCT escala_notas
                FROM sga_comisiones
                WHERE estado = 'A'
                        AND anio_academico = $anio_academico
                        AND periodo_lectivo = $periodo
                        AND materia = $materia 
			AND escala_notas IN ($promociones)";
        $prom = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        
        $sql = "SELECT  DISTINCT escala_notas
                FROM sga_comisiones
                WHERE estado = 'A'
                        AND anio_academico = $anio_academico
                        AND periodo_lectivo = $periodo
                        AND materia = $materia 
			AND escala_notas IN ($regulares)";
        $reg = kernel::db()->consultar($sql, db::FETCH_ASSOC);

        if (count($prom) > 0)
        {
            if (count($reg) > 0)
            {
                return 'PyR';
            }
            else
            {
                return 'P';
            }
        }
        if (count($reg) > 0)
        {
            return 'R';
        }
        return '';        
    }
    
   
    /**
     * parametros: anio_academico, periodo
     * cache: no
     * filas: n
     */
    function get_periodos_evaluacion($parametros)
    {
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $sql = "SELECT  orden,
                        fecha_inicio, 
                        fecha_fin
                    FROM ufce_eval_parc_periodos
                WHERE anio_academico = $anio_academico
                AND periodo_lectivo = $periodo";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
    /**
     * parametros: materia
     * cache: no
     * filas: n
     */
    function get_materias_mismo_mix($parametros)
    {
        $materia = $parametros['materia'];
        $sql = "SELECT DISTINCT materia 
                    FROM ufce_mixes A 
                    WHERE anio_de_cursada IN (
                                    SELECT anio_de_cursada 
                                        FROM ufce_mixes 
                                        WHERE   ufce_mixes.mix = A.mix 
                                            AND ufce_mixes.carrera = A.carrera 
                                            AND materia = $materia
                            )
                    AND materia <> $materia";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
    /**
     * parametros: materia, anio_academico, periodo
     * cache: no
     * filas: n
     */
    function get_fechas_eval_asignadas($parametros)
    {
        $materia = $parametros['materia'];
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $sql = "SELECT DISTINCT DATE(fecha_hora) AS fecha, evaluacion
                    FROM sga_cron_eval_parc 
                    WHERE comision IN (
                                    SELECT comision FROM sga_comisiones 
                                    WHERE materia = $materia
                                        AND anio_academico = $anio_academico 
                                        AND periodo_lectivo = $periodo
                            ) 
                UNION
                SELECT DISTINCT DATE(fecha_hora) AS fecha, evaluacion
                    FROM ufce_cron_eval_parc 
                    WHERE comision IN (
                                    SELECT comision FROM sga_comisiones 
                                    WHERE materia = $materia
                                        AND anio_academico = $anio_academico 
                                        AND periodo_lectivo = $periodo
                            ) 
                        AND estado IN ('A', 'P')";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
    /**
     * parametros: comision
     * cache: no
     * filas: n
     */
    function get_fechas_no_validas($parametros)
    {
        $sql = "SELECT fecha FROM sga_calendcursada
                    WHERE comision = {$parametros['comision']}
					AND valido = 'N'";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
    /**
     * parametros: comision, dia_semana
     * cache: no
     * filas: n
     */
    function get_hora_inicio($parametros)
    {
        $comision = $parametros['comision'];
        $dia_semana = $parametros['dia_semana'];
        $sql = "SELECT hs_comienzo_clase
                    FROM sga_asignaciones 
                WHERE asignacion IN (SELECT asignacion 
                                        FROM sga_calendcursada 
                                            WHERE comision = $comision)
                                            AND dia_semana = $dia_semana";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
    /**
    * parametros: comision, evaluacion, fecha_hora
    * cache: no
    * filas: n
    */
    function alta_propuesta_evaluacion_parcial($parametros)
    {
        $comision = $parametros['comision'];
        $evaluacion = $parametros['evaluacion'];
        $fecha_hora = $parametros['fecha_hora'];
        		
		$sql = "SELECT estado, fecha_hora FROM ufce_cron_eval_parc 
                    WHERE comision = $comision 
                        AND evaluacion = $evaluacion";

		$eval = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
		
		if (!empty($eval))
        {
			if ($eval['ESTADO'] != 'P') {//Si el estado es distinto de 'P' es que la instancia de evaluación ya ha sido creada
				switch ($evaluacion)
				{
					case "'1'": $inst = '1º Parcial'; break;
					case "'2'": $inst = '2º Parcial'; break;
					case "'7'": $inst = 'Recuperatorio Global'; break;
					case "'14'": $inst = 'Integrador'; break;
				}
				throw new error_guarani("No se puede modificar la fecha propuesta dado que ya ha sido creada la instancia de evaluación $inst para la comisión $comision. ");
			}
			else
			{
	            if ($eval[0]['FECHA_HORA'] != $fecha_hora)
    	        {
        	        $sql = "UPDATE ufce_cron_eval_parc
            	        	    SET fecha_hora = $fecha_hora
                		    WHERE comision = $comision 
							AND evaluacion = $evaluacion";
    	            return kernel::db()->ejecutar($sql);
	            }
			}
		}
        else
        {
            $sql = "INSERT INTO ufce_cron_eval_parc (comision, evaluacion, fecha_hora, estado)
                        VALUES ($comision, $evaluacion, $fecha_hora, 'P')";
			return kernel::db()->ejecutar($sql);
        }
     }
    
    /**
    * parametros: comision, evaluacion
    * cache: no
    * filas: n
    */
    function get_evaluaciones_existentes($parametros)
    {
        $comision = $parametros['comision'];
        $evaluacion = $parametros['evaluacion'];
        $sql = "SELECT fecha_hora, 'A' as estado
                    FROM sga_cron_eval_parc 
                        WHERE comision = $comision
                        AND evaluacion = $evaluacion";
        $resultado = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        if (empty($resultado))
        {
            $sql = "SELECT fecha_hora, estado
                    FROM ufce_cron_eval_parc 
                        WHERE comision = $comision
                        AND evaluacion = $evaluacion";
            $resultado = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        }
        return $resultado;
    }

    /**
    * parametros: comision, evaluacion
    * cache: no
    * filas: 1
    */
    function get_evaluacion_asignada($parametros)
    {
        $comision = $parametros['comision'];
        $evaluacion = $parametros['evaluacion'];
        $sql = "SELECT E.fecha_hora, NVL(U.estado, 'U') AS estado
					FROM sga_cron_eval_parc E
					LEFT JOIN ufce_cron_eval_parc U ON (U.comision = E.comision AND U.evaluacion = E.evaluacion)
                        WHERE E.comision = $comision
						AND E.evaluacion = $evaluacion";
		$resultado = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
		if (count($resultado) == 0) 
		{
			$resultado['FECHA_HORA'] = null;
			$resultado['ESTADO'] = null;
		}
		if ($resultado['ESTADO'] == 'U')
		{
			$parametros['fecha'] = json_encode($resultado['FECHA_HORA']);
			$resultado['ESTADO'] = $this->get_estado_comision_fecha($parametros);
		}
		return $resultado;
	}


    /**
    * parametros: comision, evaluacion, fecha
    * cache: no
    * filas: 1
    */
    function get_estado_comision_fecha($parametros)
    {
		//Si la fecha corresponde con la solicitada por el coordinador, devuelve 'A'
		//Sino, si la fecha corresponde a un día de cursada de la comisión devuelve 'C'
		//En todo otro caso devuelve 'R'
		//Si el horario NO corresponde al de la cursada: agrega una H al final ('AH' / 'CH' / 'RH')

		$comision = $parametros['comision'];
		$evaluacion = $parametros['evaluacion'];
		$fecha = substr($parametros['fecha'], 1, 10);
		$hora = substr($parametros['fecha'], 12, 8);
		kernel::log()->add_debug('get_estado_comision_fecha hora: ', $hora);

		$sql = "SELECT DATE(fecha_hora) as fecha
					FROM ufce_cron_eval_parc 
					WHERE comision = $comision 
					AND evaluacion = $evaluacion";

		$existe = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
		if ($existe['FECHA'] && $existe['FECHA'] == $fecha) 
		{
			$dia_semana = date('N', strtotime($fecha)) + 1;
			$sql = "SELECT hs_comienzo_clase
						FROM   	sga_asignaciones
						WHERE  	asignacion IN (SELECT asignacion
												FROM   sga_calendcursada
												WHERE  comision = $comision)
								AND dia_semana = $dia_semana";
			$result = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
			if (isset($result['HS_COMIENZO_CLASE']) and trim($result['HS_COMIENZO_CLASE']) == trim($hora)) {
				$estado = 'A';
			}
			else {
				$estado = 'AH';
			}
		} else {
			$sql = "SELECT dia_semana
					FROM   sga_asignaciones
					WHERE  asignacion IN (SELECT asignacion
											FROM   sga_calendcursada
											WHERE  comision = $comision)";
			$r = kernel::db()->consultar($sql, db::FETCH_ASSOC);

			$dia_semana = date('N', strtotime($fecha)) + 1;
			$estado = 'R';	
			foreach ($r as $dia) {
				if ($dia['DIA_SEMANA'] == $dia_semana) 
				{
					$sql = "SELECT hs_comienzo_clase
						FROM   	sga_asignaciones
						WHERE  	asignacion IN (SELECT asignacion
												FROM   sga_calendcursada
												WHERE  comision = $comision)
								AND dia_semana = $dia_semana";
					$result = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
					if (isset($result['HS_COMIENZO_CLASE']) and trim($result['HS_COMIENZO_CLASE']) == trim($hora)) {
						$estado = 'C';
					}
					else {
						$estado = 'CH';
					}
				}
			}
			if ($estado == 'R')
			{
				$sql = "SELECT FIRST 1 hs_comienzo_clase
						FROM   	sga_asignaciones
						WHERE  	asignacion IN (SELECT asignacion
												FROM   sga_calendcursada
												WHERE  comision = $comision)";
				$result = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
				if (isset($result['HS_COMIENZO_CLASE']) and trim($result['HS_COMIENZO_CLASE']) == trim($hora)) {
					$estado = 'R';
				}
				else {
					$estado = 'RH';
				}
			}
		}
		return $estado;
	}

	/**
    * parametros: comision, evaluacion
    * cache: no
    * filas: 1
    */
	function get_fecha_solicitada($parametros)
    {
        $comision = $parametros['comision'];
        $evaluacion = $parametros['evaluacion'];
		$sql = "SELECT fecha_hora, estado
				FROM ufce_cron_eval_parc 
					WHERE comision = $comision
					AND evaluacion = $evaluacion";
		return kernel::db()->consultar($sql, db::FETCH_ASSOC);
	}
	    
	/**
    * parametros: materia, anio_academico, periodo
    * cache: no
    * filas: 1
    */
    function get_evaluaciones_observaciones($parametros)
    {
        $materia = $parametros['materia'];
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $sql = "SELECT observaciones
                FROM ufce_cron_eval_parc_obs 
                    WHERE materia = $materia
                    AND anio_academico = $anio_academico
					AND periodo_lectivo = $periodo";
        $obs = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
        if (count($obs) > 0) 
        {
            return ($obs['OBSERVACIONES']);
        }
        return null;
    }
 
    /**
    * parametros: materia, anio_academico, periodo_lectivo, observaciones
    * cache: no
    * filas: n
    */
    function set_evaluaciones_observaciones($parametros)
    {
        $materia = $parametros['materia'];
        $anio_academico = $parametros['anio_academico'];
        $periodo_lectivo = $parametros['periodo_lectivo'];
        $observaciones = trim($parametros['observaciones']);
        $sql = "SELECT *
                FROM ufce_cron_eval_parc_obs 
                    WHERE materia = $materia
                    AND anio_academico = $anio_academico
                    AND periodo_lectivo = $periodo_lectivo";
		$obs = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
		$oper = '';
        if (count($obs) > 0)
        {
            $sql = "UPDATE ufce_cron_eval_parc_obs 
                        SET observaciones = $observaciones
                    WHERE materia = $materia
                    AND anio_academico = $anio_academico
					AND periodo_lectivo = $periodo_lectivo
					AND observaciones NOT LIKE $observaciones ";
			$oper = 'U';
        }
        else
        {
            $sql = "INSERT INTO ufce_cron_eval_parc_obs (materia, anio_academico, periodo_lectivo, observaciones)
						VALUES ($materia, $anio_academico, $periodo_lectivo, $observaciones)";
			$oper = 'I';
        }
		$exito = kernel::db()->ejecutar($sql);
		if ($exito) 
		{
			$sql_log = "INSERT INTO ufce_cron_eval_parc_obs_log 
							(materia, anio_academico, periodo_lectivo, observaciones, fecha, oper)
						VALUES ($materia, $anio_academico, $periodo_lectivo, $observaciones, CURRENT, '$oper')";
			kernel::db()->ejecutar($sql_log);
		}
		return $exito;
    }
    
    /**
    * parametros: comision, evaluacion, fecha_hora
    * cache: no
    * filas: n
    */
    function alta_evaluacion_parcial($parametros)
    {
		kernel::log()->add_debug('alta_evaluacion_parcial', $parametros); 
		$comision = $parametros['comision'];
        $evaluacion = $parametros['evaluacion'];
        $escala = 3;	//Las instancias de evaluación parcial siempre deben tener escala: 3 (Reales Regular)
        $fecha_hora = $parametros['fecha_hora'];

		$estado_notif = 'U';
		if ($this->existe_evaluacion_parcial($parametros)) {
			$estado_notif = 'M';
		}

		$sql = "EXECUTE PROCEDURE sp_i_atrcroevalpar($comision, $evaluacion, $escala, $fecha_hora)";
		kernel::log()->add_debug('sp_i_atrcroevalpar', $sql); 
	//	die;
        $result['mensaje'] = util::ejecutar_procedure($sql);
        
        $sql = "SELECT descripcion FROM sga_eval_parc WHERE evaluacion = $evaluacion";
        $eval_descrip = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
        $eval_descrip = $eval_descrip['DESCRIPCION'];
        
        if ($result['mensaje'] == 'OK')
        {
			$result['success'] = 1;
			$estado = $this->get_estado_comision_fecha(array('comision'=>$comision, 'evaluacion'=>$evaluacion, 'fecha'=>$fecha_hora));	
			$result['estado'] = $estado;
			$result['fecha_hora'] = $fecha_hora;

			$estado = json_encode($estado);

            $sql = "UPDATE ufce_cron_eval_parc
                        SET estado = $estado,
							estado_notific = '$estado_notif'
                    WHERE comision = $comision 
                    AND evaluacion = $evaluacion";
            kernel::db()->ejecutar($sql);
			$result['mensaje'] = "Se dio de alta correctamente la evaluación $eval_descrip para la comisión $comision. ";
        }
        else
        {
			$result['success'] = -1;
			$result['mensaje'] .= " Evaluación $eval_descrip en la comisión $comision. ";
		}
		return $result;
    }
	
    /**
    * parametros: comision, evaluacion
    * cache: no
    */
	private function existe_evaluacion_parcial($parametros)
	{
		$sql = "SELECT COUNT(*) AS cant
				FROM sga_atr_eval_parc
				WHERE comision = {$parametros['comision']}
					AND evaluacion = {$parametros['evaluacion']}";
		$existe = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
		return ($existe['CANT'] > 0);
	}

    /**
    * parametros: comision
    * cache: no
    * filas: n
    */
    function get_materia($parametros)
    {
        $comision = $parametros['comision'];
        $sql = "SELECT materia FROM sga_comisiones WHERE comision = $comision";
        $materia = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        return $materia[0]['MATERIA'];
    }

    
    /**
     * parametros: comision, fecha
     * cache: no
     * filas: n
     */    
    function get_hora_comienzo_clase($parametros)
    {
        $comision = $parametros['comision'];
        $fecha = $parametros['fecha'];
        $sql = "SELECT hs_comienzo_clase
                    FROM sga_asignaciones
                    WHERE asignacion IN (
                            SELECT asignacion 
                                FROM sga_calendcursada
                                WHERE comision = $comision
                                AND fecha = $fecha)";
        
        $hs_comienzo_clase = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        if (count($hs_comienzo_clase) == 0)
        {
            $sql = "SELECT FIRST 1 hs_comienzo_clase 
                        FROM sga_asignaciones 
                        WHERE asignacion IN ( 
                                SELECT asignacion 
                                    FROM sga_calendcursada 
                                    WHERE comision = $comision)";
            $hs_comienzo_clase = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        }
        return $hs_comienzo_clase[0]['HS_COMIENZO_CLASE'];
    }
    
    /**
    * parametros: materia, anio_academico, periodo
    * cache: no
    * filas: n
    */
    function get_evaluaciones_de_materia($parametros)
    {
		$materia = $parametros['materia'];
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $sql = "SELECT fecha_hora::DATE as fecha, evaluacion, 'A' as estado
                    FROM sga_cron_eval_parc 
                    WHERE comision IN (SELECT comision FROM sga_comisiones 
				WHERE materia = $materia
				AND anio_academico = $anio_academico
				AND periodo_lectivo = $periodo)
                UNION
                SELECT fecha_hora::DATE as fecha, evaluacion, estado
                    FROM ufce_cron_eval_parc 
                    WHERE comision IN (SELECT comision FROM sga_comisiones 
				WHERE materia = $materia
				AND anio_academico = $anio_academico
				AND periodo_lectivo = $periodo)
        		AND estado = 'P'";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }

}

