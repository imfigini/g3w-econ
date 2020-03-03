<?php
namespace econ\modelo\transacciones;

use kernel\kernel;
use siu\modelo\datos\catalogo;

class fechas_parciales
{
	function get_materias_cincuentenario($carrera, $mix, $anio_academico, $periodo_lectivo)
	{
		$parametros = array();
		$parametros['legajo'] = null;
        $parametros['carrera'] = null;
        $parametros['mix'] = null;

        $perfil = kernel::persona()->perfil()->get_id();
        if ($perfil == 'COORD') {
            $parametros['legajo'] = kernel::persona()->get_legajo_docente();
		}
		if ($carrera) {
			$parametros['carrera'] = $carrera;
		}
		if ($mix) {
			$parametros['mix'] = $mix;
		}
		if ($anio_academico) {
			$parametros['anio_academico'] = $anio_academico;
		}
		if ($periodo_lectivo) {
			$parametros['periodo_lectivo'] = $periodo_lectivo;
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
		return catalogo::consultar('fechas_parciales', 'get_fechas_no_validas_materia',  $parametros);
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

	/* Retorna las fechas indicadas como no validas para la comision */
    function get_fechas_no_validas_comision($comision)
    {
        $dias_no_validos = catalogo::consultar('cursos', 'get_fechas_no_validas_comision', Array('comision'=>$comision));
        $arreglo = Array();
        foreach ($dias_no_validos AS $d)
        {
			$arreglo[] = $d['FECHA'];
		}
        return $arreglo;
	}
	
	/* Retrona las fechas de evaluacion solicitadas o propuestas por el coordinador */
	function get_fechas_eval_solicitadas($comision)
    {
		$datos = catalogo::consultar('fechas_parciales', 'get_fechas_eval_solicitadas', Array('comision'=>$comision)); 
		$resultado = Array();
		foreach($datos AS $dato)
		{
			$eval = trim($dato['EVAL_NOMBRE']);
			$resultado[$eval]['FECHA_HORA'] = $dato['FECHA_HORA'];
			$resultado[$eval]['ESTADO'] = $dato['ESTADO'];
		}
		return $resultado;
	}

	/* Retrona las fechas de evaluacion creadas y asignadas */
	function get_fechas_eval_asignadas($comision)
    {
		$datos = catalogo::consultar('fechas_parciales', 'get_fechas_eval_asignadas', Array('comision'=>$comision)); 
		$resultado = Array();
		foreach($datos AS $dato)
		{
			$eval = trim($dato['EVAL_NOMBRE']);
			$resultado[$eval]['FECHA_HORA'] = $dato['FECHA_HORA'];
		}
		return $resultado;

	}
}
