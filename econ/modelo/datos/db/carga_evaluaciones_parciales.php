<?php
namespace econ\modelo\datos\db;
use kernel\kernel;
use kernel\util\db\db;
use siu\modelo\datos\catalogo;
//use siu\modelo\entidades\alumno_foto;

class carga_evaluaciones_parciales extends \siu\modelo\datos\db\carga_evaluaciones_parciales
{
    /**
     * parametros: _ua, legajo
     * cache: no
     * cache_expiracion: 3600
     * filas: n
     */
    function listado_evaluaciones_parciales_econ($parametros)
    {
        //Iris: En la consulta se agreg� al coordinador
        //Iris: En la consulta se agreg� si pertenece o no la materia a alg�n plan del 50�
		$sql = "SELECT
			sga_comisiones.materia,
			sga_materias.nombre as materia_nombre,
			(SELECT ufce_coordinadores_materias.coordinador FROM ufce_coordinadores_materias WHERE 
				ufce_coordinadores_materias.materia = sga_comisiones.materia
				AND ufce_coordinadores_materias.anio_academico = sga_comisiones.anio_academico
				AND ufce_coordinadores_materias.periodo_lectivo = sga_comisiones.periodo_lectivo) AS coordinador,
                        -- (SELECT COUNT(*)=0 FROM sga_atrib_mat_plan MP WHERE materia = sga_comisiones.materia AND plan IN 
                        --         (SELECT plan FROM sga_planes WHERE carrera = MP.carrera AND plan = MP.plan AND version_actual = MP.version AND estado = 'V') ) 
                        --         AS in_plan_viejo,
			(SELECT COUNT(*)>0 FROM ufce_mixes WHERE materia = sga_comisiones.materia) AS in_mix,
			sga_comisiones.comision,
			sga_comisiones.nombre as comision_nombre,
			sga_comisiones.anio_academico,
			sga_comisiones.periodo_lectivo,
			sga_comisiones.catedra,
			DECODE(sga_comisiones.turno,'T', 'Tarde', 'N', 'Noche', 'M', 'Ma�ana', 'No informa') as turno,
			sga_sedes.sede,
			sga_sedes.nombre as sede_nombre,
			(SELECT COUNT(*)  FROM sga_insc_cursadas WHERE comision = sga_comisiones.comision AND estado in ('A','P','E')) as cant_inscriptos,
			(SELECT COUNT(*)  FROM sga_eval_parc_alum as a 
		      WHERE a.comision = sga_atr_eval_parc.comision 
		        AND a.evaluacion = sga_atr_eval_parc.evaluacion 
		        AND a.nota IS NOT NULL) as cant_con_notas,
			(SELECT COUNT(*)  FROM sga_eval_parc_alum as a 
		      WHERE a.comision = sga_atr_eval_parc.comision 
		        AND a.evaluacion = sga_atr_eval_parc.evaluacion) as total_alumnos_eval,
			sga_atr_eval_parc.evaluacion,
			sga_eval_parc.evaluacion as evaluacion_cod_tipo,
			INITCAP(sga_eval_parc.descripcion) as evaluacion_nombre,
			sga_tipo_eval_parc.descripcion as evaluacion_tipo,
			sga_cron_eval_parc.fecha_hora as evaluacion_fecha,
			sga_periodos_lect.anio_academico,
			sga_periodos_lect.fecha_inicio,
                        CASE
				WHEN lower(sga_escala_notas.nombre) LIKE '%promo%' THEN 'PROMO'
				ELSE 'REGULAR'
			END AS escala_notas
		FROM 
			sga_docentes_com,
			sga_comisiones,
			sga_periodos_lect,
			sga_materias,
			sga_sedes,
                        sga_escala_notas,
			OUTER (sga_atr_eval_parc,
							sga_eval_parc,
							sga_tipo_eval_parc,
							sga_cron_eval_parc)
			WHERE sga_docentes_com.legajo =  {$parametros['legajo']}
			--AND sga_materias.unidad_academica = {$parametros['_ua']}
			AND sga_comisiones.comision = sga_docentes_com.comision
			AND sga_materias.unidad_academica = sga_comisiones.unidad_academica
			AND sga_materias.materia = sga_comisiones.materia
			AND sga_periodos_lect.anio_academico = sga_comisiones.anio_academico
			AND sga_periodos_lect.periodo_lectivo = sga_comisiones.periodo_lectivo
			AND sga_periodos_lect.fecha_inactivacion >= TODAY
			AND sga_comisiones.sede = sga_sedes.sede
			AND sga_atr_eval_parc.comision = sga_comisiones.comision
			AND sga_eval_parc.evaluacion   = sga_atr_eval_parc.evaluacion
			AND sga_tipo_eval_parc.tipo_evaluac_parc = sga_eval_parc.tipo_evaluac_parc
			AND sga_cron_eval_parc.evaluacion = sga_eval_parc.evaluacion
			AND sga_cron_eval_parc.comision   = sga_atr_eval_parc.comision
                        AND sga_comisiones.escala_notas = sga_escala_notas.escala_notas

			-- No mostrar comisiones con actas de cursadas cerradas
			-- AND NOT EXISTS  (SELECT 1 FROM sga_actas_cursado	WHERE comision = sga_comisiones.comision AND estado = 'C')

			ORDER BY sga_periodos_lect.anio_academico,
					sga_periodos_lect.fecha_inicio,
					sga_materias.nombre,
					sga_comisiones.nombre,
			        sga_cron_eval_parc.fecha_hora,
					sga_atr_eval_parc.evaluacion ";
					
		$datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
		foreach(array_keys($datos) as $id) {
			//$datos[$id]['__ID__'] = catalogo::generar_id($datos[$id]['COMISION']);
			$datos[$id]['COMISION_URL'] = catalogo::generar_id($datos[$id]['COMISION']);
			if (isset($datos[$id]['EVALUACION'])){
				$id_url = catalogo::generar_id($datos[$id]['COMISION'].$datos[$id]['EVALUACION']);
				$datos[$id][catalogo::id] = $id_url;
			} else {
				$datos[$id][catalogo::id] = '';
			}
			
			if ($datos[$id]['CANT_INSCRIPTOS'] != 0){
				$porc = $datos[$id]['CANT_CON_NOTAS'] / $datos[$id]['CANT_INSCRIPTOS'] * 100;
                                                                        if ($porc > 100){
                                                                            $datos[$id]['PORCENTAJE_CARGA'] = 100;
                                                                        } else {
                                                                            $datos[$id]['PORCENTAJE_CARGA'] = number_format ( $porc, 0);
                                                                        }
                                                                        unset($porc);
			} else {
				$datos[$id]['PORCENTAJE_CARGA'] = 0;
			}
			
		}
                //var_dump($datos);
		return $datos;
    }

    /**
    * parametros: materia
    * cache: memoria
    * filas: n
    */
    function get_ciclo_materia($materia)
    {
        $sql = "SELECT COUNT(*) as cant
                    FROM sga_ciclos
                    JOIN sga_materias_ciclo ON (sga_materias_ciclo.ciclo = sga_ciclos.ciclo)
                WHERE sga_materias_ciclo.materia = $materia
                    AND lower(sga_ciclos.nombre) LIKE '%fundam%'" ;
        $ciclo = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        if (isset($ciclo) && $ciclo[0]['CANT'] > 0)
        {
            return 'FUNDAMENTO';
        }
        $sql = "SELECT COUNT(*) as cant
                    FROM sga_ciclos
                    JOIN sga_materias_ciclo ON (sga_materias_ciclo.ciclo = sga_ciclos.ciclo)
                WHERE sga_materias_ciclo.materia = $materia
                    AND lower(sga_ciclos.nombre) LIKE '%profes%'" ;
        $ciclo = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        if (isset($ciclo) && $ciclo[0]['CANT'] > 0)
        {
            return 'PROFESIONAL';
        }
        return '';
    }
    
    /**
    * parametros: 
    * cache: memoria
    * filas: n
    */
    function listado_tipo_evaluacion_econ($parametros=null)
    {
        $sql = "SELECT evaluacion, descripcion_abrev 
                    FROM sga_eval_parc 
                ORDER BY 1";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
	}
	
	/**
	 * parametros: comision, evaluacion
	 * cache: no
	 * filas: n
	 */
    function evaluacion_detalle($parametros)
    {
        $sql = "execute procedure sp_eval_parc_alum(	{$parametros['comision']},
														{$parametros['evaluacion']})";
		return kernel::db()->consultar($sql, db::FETCH_NUM);
	}

	/**
	 * parametros: legajo, comision, evaluacion
	 * cache: no
	 * filas: 1
	 */
	function get_nota_parcial($parametros) 
	{
		$sql = "SELECT nota, resultado
					FROM sga_eval_parc_alum
				WHERE legajo = {$parametros['legajo']}
				AND comision = {$parametros['comision']}
				AND evaluacion = {$parametros['evaluacion']} ";
		return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
	}

	/**
	 * parametros: _ua, legajo, carrera, materia, fecha
	 * cache: no
	 * filas: 1
	 */
	function tiene_correlativas_cumplidas($parametros)
	{
		// $parametros['FECHA'] TIENE QUE SER DE LA FOMRA 'd-m-Y'
		// $fecha = $this->get_ultima_fecha_fin_turno_examen_regular($parametros);
		// $fecha_formateada = date("d-m-Y", strtotime($fecha['FECHA']));
		kernel::log()->add_debug('tiene_correlativas_cumplidas fecha', $parametros['fecha']);
		$sql = " EXECUTE PROCEDURE ctr_corre_iex_fech(	{$parametros['_ua']}, 
														{$parametros['carrera']}, 
														{$parametros['legajo']}, 
														{$parametros['materia']},
														{$parametros['fecha']}) ";
														// '$fecha_formateada') ";														
		kernel::log()->add_debug('tiene_correlativas_cumplidas', $sql);
		return kernel::db()->consultar_fila($sql, db::FETCH_NUM);
	}

	// /**
	//  * parametros: anio_academico, periodo
	//  * cache: no
	//  * filas: 1
	//  */
	// function get_ultima_fecha_fin_turno_examen_regular($parametros)
	// {
	// 	$sql = "SELECT MAX(T.fecha_fin) AS fecha
	// 				FROM sga_turnos_examen T, sga_periodos_lect P
	// 				WHERE 	P.anio_academico = {$parametros['anio_academico']}
	// 						AND P.periodo_lectivo = {$parametros['periodo']}
	// 						AND P.anio_academico = T.anio_academico
	// 						AND tipo_de_turno = 'N'
	// 						AND T.fecha_fin BETWEEN P.fecha_inicio AND P.fecha_fin ";
	// 	return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
	// }

	
	/**
	 * parametros: legajo, comision
	 * cache: no
	 * filas: 1
	 */
	function get_porc_asistencia($parametros)
	{
		$sql = "SELECT 	SUM(motivo_justific) AS justif, 
						SUM(cant_inasistencias) AS inasis 
					FROM sga_inasistencias 
					WHERE comision = {$parametros['comision']}
						AND legajo = {$parametros['legajo']} ";
		$inasis_alu = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);

		$clases = $this->get_cant_clases_al_dia_de_hoy($parametros);
		$cant_clases = $clases['CANT_CLASES'];

		if ($cant_clases == 0) {
			return 0;
		}
		/*
		//Si hay instancias de evaluacion posteriores a la fecha de finalizaci�n de clases, se deben contabilizar
		$comision = str_replace("'","",$parametros['comision']);
		$eval_posterior = catalogo::consultar('carga_asistencias', 'get_evaluaciones_posterior_fin_clases', Array('comision'=>$comision));
		if (isset($eval_posterior)) {
			$cant_clases += count($eval_posterior);
		}
		*/

		$cant_asistio = $cant_clases - $inasis_alu['INASIS'] + $inasis_alu['JUSTIF'];
		$porc_asistencia = ($cant_asistio / $cant_clases) * 100;
		return round($porc_asistencia, 2, PHP_ROUND_HALF_DOWN);
	}

	/**
	 * parametros: legajo, comision, porc_asist
	 * cache: no
	 * filas: 1
	 */
    function tiene_asistencia($parametros)
    {
		/* Dado un alumno, una comisi�n y un porcentaje m�nimo de asitencia requerido
			retorna si el alumno cumple o no con dicho porcentaje 
			0 <= porc_asist <= 100
			*/
		$porc_asistencia_alu = self::get_porc_asistencia($parametros);
		if ($porc_asistencia_alu >= $parametros['porc_asist']) {
			return true;
		}
		return false;
	}

	/**
	 * parametros: comision
	 * cache: si
	 * filas: 1
	 */
    function get_cant_clases_al_dia_de_hoy($parametros)
    {
		/* Retorna la cantidad de clases validas que ya han pasado hasta el d�a de la fecha incluido */
		$sql = "SELECT COUNT(*) AS cant_clases 
					FROM sga_calendcursada 
					WHERE comision = {$parametros['comision']}
					AND valido = 'S' 
					AND fecha <= TODAY ";
		return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
	}

	/**
	 * parametros: legajo, comision, evaluacion
	 * cache: no
	 * filas: 1
	 */
	function asistio_evaluacion($parametros)
	{
		$sql = "SELECT COUNT(*) AS cant
				FROM sga_eval_parc_alum
				WHERE legajo = {$parametros['legajo']}
					AND comision = {$parametros['comision']}
					AND evaluacion = {$parametros['evaluacion']} 
					AND resultado <> 'U' ";
		$asistio = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
		if ($asistio['CANT'] > 0) {
			return true;
		}
		return false;
	}

	/**
	 * parametros: comision, fecha
	 * cache: no
	 * filas: 1
	 */
	function get_clase($parametros)
	{
		$sql = "SELECT clase 
					FROM sga_calendcursada
					WHERE comision = {$parametros['comision']}
					and fecha = {$parametros['fecha']}";
		return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
	}

	/**
	 * parametros: legajo, comision, evaluacion
	 * cache: no
	 * filas: 1
	 */
	function get_nota_evaluacion($parametros)
	{
		$sql = "SELECT nota, resultado
				FROM sga_eval_parc_alum
				WHERE legajo = {$parametros['legajo']}
					AND comision = {$parametros['comision']}
					AND evaluacion = {$parametros['evaluacion']} 
					AND resultado <> 'U' ";
		return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
	}

	/**
	 * parametros: comision, legajo
	 * cache: no
	 * filas: n
	 */
	function get_notas_eval_alumno($parametros)
	{
		$sql = "SELECT evaluacion, nota, resultado
				FROM sga_eval_parc_alum
				WHERE legajo = {$parametros['legajo']}
					AND comision = {$parametros['comision']}
					AND resultado <> 'U' ";
		return kernel::db()->consultar($sql, db::FETCH_ASSOC);
	}
	

	/**
	 * parametros: comision, evaluacion
	 * cache: no
	 * filas: 1
	 */
	function ya_paso_evaluacion($parametros)
	{
		$sql = "SELECT fecha_hora::DATE < TODAY AS ya_paso, fecha_hora, TODAY
				FROM sga_cron_eval_parc
				WHERE comision = {$parametros['comision']}
				AND evaluacion = {$parametros['evaluacion']}";
		kernel::log()->add_debug('paso_resultado_sql', $sql);
		$resultado = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
		kernel::log()->add_debug('paso_resultado', $resultado);
		return $resultado['YA_PASO'];
	}


}
