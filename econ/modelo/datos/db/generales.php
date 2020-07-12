<?php
namespace econ\modelo\datos\db;

use kernel\kernel;
use kernel\util\db\db;
use siu\modelo\datos\util;

class generales
{

	/**
	 * parametros: anio_academico, periodo
	 * cache: no
	 * filas: 1
	 */
	function get_fecha_ctr_correlativas($parametros)
	{
		$sql = "SELECT fecha
					FROM ufce_fechas_ctr_correlat
					WHERE 	anio_academico = {$parametros['anio_academico']}
						AND periodo_lectivo = {$parametros['periodo']} ";
		
		return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
	}

	/**
	 * parametros: anio_academico, periodo, fecha_ctr_correlat
	 * cache: no
	 * filas: 1
	 */
	function set_fecha_ctr_correlativas($parametros)
	{
		$fecha = $this->get_fecha_ctr_correlativas($parametros);
		$nueva_fecha = self::strToMDY($parametros['fecha_ctr_correlat']);
        if (isset($fecha['FECHA'])) {
            $sql = "UPDATE ufce_fechas_ctr_correlat
                        SET fecha = $nueva_fecha
                    WHERE anio_academico = {$parametros['anio_academico']}
                    AND periodo_lectivo = {$parametros['periodo']} ";    
        }
        else {
            $sql = "INSERT INTO ufce_fechas_ctr_correlat (anio_academico, periodo_lectivo, fecha)
                        VALUES ({$parametros['anio_academico']}, {$parametros['periodo']}, $nueva_fecha) ";  
		}
		kernel::db()->ejecutar($sql);
	}

	/**
     * parametros: strFecha
     * cache: no
     * filas: n
     * El formato de strFecha debe ser: d/m/Y o Y-m-d
     */
    static function strToMDY($strFecha)
    {
        $fecha = explode("'", $strFecha);
		
		if (strpos($fecha[1], '/') != false) {
            $fecha = explode('/', $fecha[1]);
			return 'MDY('.$fecha[1].','.$fecha[0].','.$fecha[2].')';
		}
		if (strpos($fecha[1], '-') != false) {
            $fecha = explode('-', $fecha[1]);
    		return 'MDY('.$fecha[1].','.$fecha[2].','.$fecha[0].')';
		}
		throw new Exception('Formato de fecha no manejado');
        
    }

}
