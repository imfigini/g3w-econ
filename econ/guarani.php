<?php
namespace econ;

use kernel\kernel;

class guarani extends \siu\guarani
{
	static protected $ponderacion_notas;
	static protected $fechas_parciales;

	//----------------------------------------------------------------------
	// Transacciones
	//----------------------------------------------------------------------

	static function ponderacion_notas()
	{
		if (!self::$ponderacion_notas) {
			self::$ponderacion_notas = kernel::localizador()->instanciar("modelo\\transacciones\\ponderacion_notas");
		}
		return self::$ponderacion_notas;
	}

	static function fechas_parciales()
	{
		if (!self::$fechas_parciales) {
			self::$fechas_parciales = kernel::localizador()->instanciar("modelo\\transacciones\\fechas_parciales");
		}
		return self::$fechas_parciales;
	}

}
?>
