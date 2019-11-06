<?php
namespace econ;

use kernel\kernel;

class guarani extends \siu\guarani
{
	static protected $ponderacion_notas;
	static protected $fechas_parciales_propuesta;

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

	static function fechas_parciales_propuesta()
	{
		if (!self::$fechas_parciales_propuesta) {
			self::$fechas_parciales_propuesta = kernel::localizador()->instanciar("modelo\\transacciones\\fechas_parciales_propuesta");
		}
		return self::$fechas_parciales_propuesta;
	}
	
}
?>
