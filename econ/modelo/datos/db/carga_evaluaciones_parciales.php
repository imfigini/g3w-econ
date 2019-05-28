<?php
namespace econ\modelo\datos\db;
use kernel\kernel;
use kernel\util\db\db;
use siu\modelo\datos\catalogo;

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
                        (SELECT COUNT(*)=0 FROM sga_atrib_mat_plan MP WHERE materia = sga_comisiones.materia AND plan IN 
                                (SELECT plan FROM sga_planes WHERE carrera = MP.carrera AND plan = MP.plan AND version_actual = MP.version AND estado = 'V') ) 
                                AS in_plan_viejo,
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
			sga_eval_parc.descripcion as evaluacion_nombre,
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
        //var_dump($parametros); 
        $sql = "SELECT evaluacion, descripcion_abrev 
                    FROM sga_eval_parc 
                ORDER BY 1";
        $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        return $datos;  
    }
}
