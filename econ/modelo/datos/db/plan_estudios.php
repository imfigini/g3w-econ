<?php
namespace econ\modelo\datos\db;

use kernel\kernel;
use kernel\util\db\db;
use siu\modelo\datos\catalogo;

class plan_estudios extends \siu\modelo\datos\db\plan_estudios
{
	/**
	 * parametros:  _ua, carrera
	 * cache: memoria
	 * filas: n
	 */
	function planes($parametros)
	{
		//Iris: Se decartan planes 1992 y 2002 por pedido de Chelo
		$sql = "SELECT	sga_planes.plan, sga_planes.version_actual, sga_planes.fecha_ent_vigencia
				FROM	sga_planes
				WHERE	sga_planes.unidad_academica = {$parametros['_ua']}
				AND		sga_planes.carrera = {$parametros['carrera']}
				AND     sga_planes.estado IN ('A','V')
				AND 	sga_planes.plan NOT IN ('1992', '2002')
				ORDER BY sga_planes.fecha_ent_vigencia DESC
				";
		
		$datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);

        $nuevo = array();
        foreach ($datos as $clave => $dato){
            $nuevo[$clave] = $dato;
            $nuevo[$clave]['_ID_'] = catalogo::generar_id($dato['PLAN']);
        }
        return $nuevo;
	}
	
}

?>
