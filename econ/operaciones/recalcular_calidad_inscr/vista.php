<?php
namespace econ\operaciones\recalcular_calidad_inscr;

use siu\extension_kernel\vista_g3w2;
use kernel\kernel;

class vista extends vista_g3w2
{
	function ini()
	{
		$clase = 'operaciones\recalcular_calidad_inscr\pagelet_filtro';
		$pl = kernel::localizador()->instanciar($clase, 'contenido');
		$this->add_pagelet($pl);

		kernel::pagina()->set_etiqueta('titulo', kernel::traductor()->trans("tit_recalcular_calidad_inscr"));
	}
}
?>
