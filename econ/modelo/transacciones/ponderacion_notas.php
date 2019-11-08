<?php
namespace econ\modelo\transacciones;

use siu\modelo\datos\catalogo;

class ponderacion_notas 
{
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
		
		return $datos;
    }
 
}
