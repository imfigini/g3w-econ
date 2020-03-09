<?php
namespace econ\modelo\transacciones;

use siu\modelo\datos\catalogo;
use kernel\kernel;

class ponderacion_notas 
{
	/* Parametros: anio_academico, periodo, materia */
	function info__get_ponderaciones_notas($parametros)
    {
		$datos = Array();
		$parametros['calidad'] = 'P';
		$con_integrador = catalogo::consultar('ponderacion_notas', 'get_ponderaciones_notas', $parametros);

		if (count($con_integrador)> 0) {
			$datos['PORC_P_PARCIALES'] = $con_integrador['PORC_PARCIALES'];
			$datos['PORC_P_INTEGRADOR'] = $con_integrador['PORC_INTEGRADOR'];
			$datos['PORC_P_TRABAJOS'] = $con_integrador['PORC_TRABAJOS'];
		}

		$parametros['calidad'] = 'R';
		$sin_integrador = catalogo::consultar('ponderacion_notas', 'get_ponderaciones_notas', $parametros);
		if (count($sin_integrador)> 0) {
			$datos['PORC_R_PARCIALES'] = $sin_integrador['PORC_PARCIALES'];
			$datos['PORC_R_TRABAJOS'] = $sin_integrador['PORC_TRABAJOS'];
		}
	
		$parametros['calidad'] = 'D';
		$prom_directa = catalogo::consultar('ponderacion_notas', 'get_ponderaciones_notas', $parametros);
		if (count($prom_directa)> 0) {
			$datos['PORC_D_PARCIALES'] = $prom_directa['PORC_PARCIALES'];
			$datos['PORC_D_TRABAJOS'] = $prom_directa['PORC_TRABAJOS'];
		}

		return $datos;
    }
	
	/* Parametros: anio_academico, periodo, materia */
	function info__is_promo_directa($parametros)
    {
		return catalogo::consultar('prom_directa', 'is_promo_directa', $parametros);
	}
	
	/* Parametros: anio_academico, periodo, materia */
	function info__is_promo($parametros)
    {
		return catalogo::consultar('prom_directa', 'is_promo', $parametros);
	}

}
