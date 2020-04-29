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
	function get_escala_nota($parametros)
	{
		$sql = "SELECT escala_notas 
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

}
?>
