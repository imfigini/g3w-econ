<?php
namespace econ\operaciones\periodos_evaluacion;

use siu\extension_kernel\vista_g3w2;
use kernel\kernel;

class vista extends vista_g3w2
{
	function ini()
	{
		$clase = 'operaciones\periodos_evaluacion\pagelet_filtro';
		$pl = kernel::localizador()->instanciar($clase, 'contenido');
		$this->add_pagelet($pl);

                kernel::pagina()->set_etiqueta('titulo', kernel::traductor()->trans("tit_periodos_evaluacion"));
	}
}
?>