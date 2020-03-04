<?php
namespace econ\modelo\datos\db;

use kernel\kernel;
use kernel\util\db\db;

class parametros
{
	/**
    * parametros: operacion
    * cache: no
    * filas: 1
    */
	function get_parametro($parametros)
	{
		$sql = "SELECT parametro
				FROM ufce_parametros
				WHERE operacion = {$parametros['operacion']}";
		return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
	}
}
