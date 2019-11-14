<?php
namespace econ\modelo\datos\db;

use kernel\kernel;
use kernel\util\db\db;
use siu\modelo\datos\util;

class ponderacion_notas 
{
    /**
     * parametros: anio_academico, periodo, materia, calidad
     * cache: no
     * filas: 1
     */ 
	function get_ponderaciones_notas($parametros)
	{
		$sql = "SELECT calidad, porc_parciales, porc_integrador, porc_trabajos
					FROM   ufce_ponderacion_notas
					WHERE  	anio_academico = {$parametros['anio_academico']}
			   				AND periodo_lectivo = {$parametros['periodo']}
							AND materia = {$parametros['materia']} 
							AND calidad = {$parametros['calidad']} ";
		return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
	}

    /**
	* parametros: anio_academico, periodo, materia, calidad, porc_parciales, porc_integrador, porc_trabajos
	* param_null: porc_integrador
    * cache: no
    * filas: 1
    */
    function update_ponderaciones_notas($parametros)
    {
		$calidad = $parametros['calidad'];
		($calidad == "'P'") ? $porc_integrador = $parametros['porc_integrador'] : $porc_integrador = 'null';

        $sql = "UPDATE ufce_ponderacion_notas 
                    SET porc_parciales = {$parametros['porc_parciales']},
                        porc_integrador = $porc_integrador,
                        porc_trabajos = {$parametros['porc_trabajos']}
					WHERE anio_academico = {$parametros['anio_academico']}
						AND periodo_lectivo = {$parametros['periodo']}
						AND materia = {$parametros['materia']}
						AND calidad = $calidad";
        return kernel::db()->ejecutar($sql);
    }
    
    /**
    * parametros: anio_academico, periodo, materia, calidad, porc_parciales, porc_integrador, porc_trabajos
	* param_null: porc_integrador
	* cache: no
    * filas: 1
    */
    function set_ponderaciones_notas($parametros)
    {
		//kernel::log()->add_debug('set_ponderaciones_notas', $parametros);
		$existe = $this->get_ponderaciones_notas($parametros);
        if ($existe) {
            return $this->update_ponderaciones_notas($parametros);
        }
        else
        {
			$anio = $parametros['anio_academico'];
			$periodo = $parametros['periodo'];
			$materia = $parametros['materia'];
			$calidad = $parametros['calidad'];
            $porc_parciales = $parametros['porc_parciales'];
			if ($calidad == "'P'") {
				$porc_integrador = $parametros['porc_integrador'];
			} 
			
            $porc_trabajos = $parametros['porc_trabajos'];

            if ($calidad == "'P'") {
				$sql = "INSERT INTO ufce_ponderacion_notas (anio_academico, periodo_lectivo, materia, calidad, 
															porc_parciales, porc_integrador, porc_trabajos) VALUES
							($anio, $periodo, $materia, $calidad, $porc_parciales, $porc_integrador, $porc_trabajos)";
			} else {
				$sql = "INSERT INTO ufce_ponderacion_notas (anio_academico, periodo_lectivo, materia, calidad, 
															porc_parciales, porc_trabajos) VALUES
							($anio, $periodo, $materia, $calidad, $porc_parciales, $porc_trabajos)";
			}
            return kernel::db()->ejecutar($sql);
        }
    }

}
