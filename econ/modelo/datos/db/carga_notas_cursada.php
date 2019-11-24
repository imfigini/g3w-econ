<?php
namespace econ\modelo\datos\db;
use kernel\kernel;
use kernel\util\db\db;
use siu\modelo\datos\catalogo;

class carga_notas_cursada extends \siu\modelo\datos\db\carga_notas_cursada
{
	
	/**
	 * parametros: comision
	 * cache: no
	 * filas: 1
	 */
	function get_anio_comision($parametros)
	{
		$sql = "SELECT anio_academico 
					FROM sga_comisiones
				WHERE comision = {$parametros['comision']}";
		return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
	}

	/**
	 * parametros: comision
	 * cache: no
	 * filas: 1
	 */
	function get_datos_de_comision($parametros)
	{
		$sql = "SELECT materia, anio_academico, periodo_lectivo
					FROM sga_comisiones
				WHERE comision = {$parametros['comision']}";
		return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
	}

	/**
	 * parametros: comision, legajo
	 * cache: no
	 * filas: 1
	 */
	function get_carrera_incripto($parametros)
	{
		$sql = "SELECT carrera
					FROM sga_insc_cursadas
				WHERE legajo = {$parametros['legajo']}
				AND comision = {$parametros['comision']}";
		return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
	}

	/**
	 * parametros: _ua, comision, legajo
	 * cache: no
	 * cache_expiracion: 300
	 * filas: n
	 */
    function get_posible_nota_alumno($parametros)
    {
		$sql = "execute procedure sp_actasCurDocente({$parametros['_ua']}, ".
													"{$parametros['nro_inscripcion']})";
		$datos = kernel::db()->consultar($sql, db::FETCH_NUM);
		$nuevo = array();
		foreach($datos as $id => $dato) {
			$id = catalogo::generar_id($dato[1]);
			$nuevo[$id][catalogo::id] = $id;			
			$nuevo[$id]['ACTA_REG'] = $dato[1];
			$nuevo[$id]['ACTA_PRO'] = $dato[2];
			$nuevo[$id]['COMISION'] = $dato[3];
			$nuevo[$id]['COMISION_NOMBRE'] = $dato[4];
			$nuevo[$id]['MATERIA'] = $dato[5];
			$nuevo[$id]['MATERIA_NOMBRE'] = $dato[6];
			$nuevo[$id]['ANIO_ACADEMICO'] = $dato[7];
			$nuevo[$id]['PERIODO_LECTIVO'] = $dato[8];
			$nuevo[$id]['ESCALA_NOTAS'] = $dato[9];
	//		$nuevo[$id]['FOLIO'] = $dato[10];
	//		$nuevo[$id]['PORCENTAJE'] = $dato[11];
        }
		return $nuevo;	        
    }
}
?>
