<?php
namespace econ\modelo\transacciones;

use kernel\kernel;
use siu\modelo\datos\catalogo;

class fechas_parciales_propuesta
{
	function get_materias_cincuentenario()
	{
		$parametros = array();
		$parametros['legajo'] = null;
        $parametros['carrera'] = null;
        $parametros['mix'] = null;

        $perfil = kernel::persona()->perfil()->get_id();
        if ($perfil == 'COORD')
        {
            $parametros['legajo'] = kernel::persona()->get_legajo_docente();
		}
		
		return catalogo::consultar('cursos', 'get_materias_cincuentenario', $parametros);
	}

	function get_comisiones_de_materia_con_dias_de_clase($parametros)
	{
		return catalogo::consultar('fechas_parciales', 'get_comisiones_de_materia_con_dias_de_clase', $parametros);
	}

	function get_dias_clase($parametros)
	{
		return catalogo::consultar('fechas_parciales', 'get_dias_clase', $parametros);
	}

	function get_fechas_no_validas($parametros)
	{
		return catalogo::consultar('fechas_parciales', 'get_fechas_no_validas',  $parametros);
		// 	print_r('<br>Dias : ');
		// 	print_r($dias_no_validos);
		// 	foreach ($dias_no_validos as $d)
		// 	{
		// 		$resultado[] = $d['FECHA'];
		// 	}
		// }
		// print_r('<br>Dias : ');
		// print_r($resultado);

		// return $resultado;
	}

	/* Parametros: comision */
	function get_fechas_asignadas_o_solicitadas($parametros)
	{
		return catalogo::consultar('fechas_parciales', 'get_fechas_asignadas_o_solicitadas', $parametros);
	}

	/* Retorna las fechas opupadas por las restantes materias del mismo mix */
	function get_fechas_eval_ocupadas($parametros)
	{
		$materias_mismo_mix = catalogo::consultar('cursos', 'get_materias_mismo_mix', $parametros); 

        $fechas = array();
        foreach($materias_mismo_mix AS $mat)
        {
			$parametros['materia'] = $mat['MATERIA'];
            $fechas_mat = catalogo::consultar('fechas_parciales', 'get_fechas_eval_ocupadas', $parametros); 
            $fechas = array_merge($fechas, $fechas_mat);
        }
        return $fechas;
	}

	function get_evaluaciones_observaciones($parametros)
	{
		$observaciones = catalogo::consultar('fechas_parciales', 'get_evaluaciones_observaciones', $parametros);
		if (count($observaciones) > 0){
			return $observaciones['OBSERVACIONES'];
		}
		return null;
	}

	/* Retrona: anio_academico, periodo_lectivo, materia */
	function get_datos_comision($comision)
    {
		return catalogo::consultar('fechas_parciales', 'get_datos_comision', Array('comision'=>$comision)); 
	}
}
