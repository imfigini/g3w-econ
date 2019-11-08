<?php
namespace econ\operaciones\fechas_parciales_calend_2019;

use siu\extension_kernel\vista_g3w2;
use kernel\kernel;

class vista extends vista_g3w2
{
	function ini()
	{
		$clase = 'operaciones\fechas_parciales_calend_2019\pagelet_filtro';
		$pl = kernel::localizador()->instanciar($clase, 'contenido');
		$this->add_pagelet($pl);

                kernel::pagina()->set_etiqueta('titulo', kernel::traductor()->trans("tit_fechas_parciales_calend_2019"));
	}
}
?>
