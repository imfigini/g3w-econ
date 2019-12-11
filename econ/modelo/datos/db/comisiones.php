<?php
namespace econ\modelo\datos\db;
use kernel\kernel;
use kernel\util\db\db;
use siu\modelo\datos\catalogo;

class comisiones extends \siu\modelo\datos\db\comisiones
{
	/**
	 * parametros: comision, visible_al_alumno
	 * param_null: visible_al_alumno
	 * cache: memoria
	 * filas: n
	 */
	function get_parciales($parametros)
	{
		// Recupera los datos de las evaluaciones parciales de la comision. No los resultados de las notas de los alumnos.
		
	    // visible_al_alumno = S-Las evaluaciones visibles al alumno / N - Las evaluaciones que no son visibles al alumno
	    
	    $filtro_visible_al_alumno = "";
	    if($parametros['visible_al_alumno'] != "''"){
	       $filtro_visible_al_alumno = " AND sga_tipo_eval_parc.visible_al_alumno = 'S' ";
		}

		//Iris: Se modificó el ORDER BY porque no mostraba ordenado por fecha
		// Recupera evaluaciones parciales y las notas de los alumnos...
		$sql = "SELECT	sga_atr_eval_parc.comision as comision,
						sga_atr_eval_parc.evaluacion as evaluacion,
						sga_eval_parc.descripcion as evaluacion_desc,
						sga_tipo_eval_parc.descripcion as tipo_evaluacion,
						sga_cron_eval_parc.fecha_hora as fecha_hora_parcial,
						to_char(sga_cron_eval_parc.fecha_hora, '%d/%m/%Y') as fecha_parcial,
						to_char(sga_cron_eval_parc.fecha_hora, '%Y%m%d') as fecha_orden
				FROM 	sga_atr_eval_parc,
						sga_eval_parc,
						sga_cron_eval_parc,
						sga_tipo_eval_parc
				WHERE	sga_atr_eval_parc.comision    = {$parametros['comision']}
				AND     sga_eval_parc.evaluacion      = sga_atr_eval_parc.evaluacion						
				AND		sga_cron_eval_parc.comision   = sga_atr_eval_parc.comision
				AND		sga_cron_eval_parc.evaluacion = sga_atr_eval_parc.evaluacion
				AND		sga_tipo_eval_parc.tipo_evaluac_parc = sga_eval_parc.tipo_evaluac_parc
				$filtro_visible_al_alumno
				ORDER BY 	sga_cron_eval_parc.fecha_hora,
							sga_atr_eval_parc.evaluacion ";
			RETURN kernel::db()->consultar($sql);
	}

	/**
	 * parametros: comision
	 * cache: no
	 * filas: n
	 */
	function get_parciales_alumnos_econ($parametros)
	{
	    // Recupera los inscriptos a la comisión, las evaluaciones parciales y las notas que obtuvieron, si es que tienen...
		$sql = "SELECT 	sga_insc_cursadas.comision, 
						sga_cron_eval_parc.evaluacion, 
						sga_insc_cursadas.legajo, 
						sga_personas.apellido || ', ' || sga_personas.nombres as alumno,
						sga_eval_parc_alum.nota, 
						sga_eval_parc_alum.resultado,
						DECODE(sga_eval_parc_alum.resultado, 'A', 'Aprobado' , 'R', 'Reprobado' , 'U', 'Ausente' , 'P', 'Promocionado' ) as resultado_desc
				FROM sga_insc_cursadas
				JOIN sga_alumnos on (sga_alumnos.legajo = sga_insc_cursadas.legajo and sga_alumnos.carrera = sga_insc_cursadas.carrera)
				JOIN sga_personas on (sga_personas.nro_inscripcion = sga_alumnos.nro_inscripcion)
				LEFT JOIN sga_cron_eval_parc on (sga_cron_eval_parc.comision = sga_insc_cursadas.comision)
				LEFT JOIN sga_eval_parc_alum on (sga_insc_cursadas.comision = sga_eval_parc_alum.comision and sga_cron_eval_parc.evaluacion = sga_eval_parc_alum.evaluacion and sga_insc_cursadas.legajo = sga_eval_parc_alum.legajo)
					WHERE sga_insc_cursadas.comision = {$parametros['comision']}
				ORDER BY 	sga_cron_eval_parc.fecha_hora, 
							sga_cron_eval_parc.evaluacion, 
							sga_personas.apellido, 
							sga_personas.nombres ";
		return kernel::db()->consultar($sql); 
	}

}
?>
