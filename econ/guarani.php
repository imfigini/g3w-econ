<?php
namespace econ;

use kernel\kernel;

class guarani extends \siu\guarani
{
	static protected $ponderacion_notas;

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
	
}
?>
