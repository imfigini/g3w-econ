<?php
namespace econ\modelo\transacciones;

use kernel\kernel;
use siu\modelo\datos\catalogo;
use siu\errores\error_guarani;
use siu\errores\error_guarani_procesar_renglones;
use \siu\modelo\datos\util;

class carga_asistencias extends \siu\modelo\transacciones\carga_asistencias
{
	
	//---------------------------------------------
	//	LISTA CLASES
	//---------------------------------------------
	
	function get_lista_clases() 
	{
		$parametros = array('legajo' => kernel::persona()->get_legajo_docente());
		return catalogo::consultar('carga_asistencias', 'listado_clases_docente', $parametros);
	}
	
	
}

?>
