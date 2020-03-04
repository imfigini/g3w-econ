<?php
namespace econ\operaciones\ponder_notas_detalle;

use siu\extension_kernel\vista_g3w2;
use kernel\kernel;

class vista extends vista_g3w2
{
	function ini()
	{
		$clase = 'operaciones\ponder_notas_detalle\pagelet_filtro';
		$pl = kernel::localizador()->instanciar($clase, 'contenido');
		$this->add_pagelet($pl);

                kernel::pagina()->set_etiqueta('titulo', kernel::traductor()->trans("tit_ponder_notas_detalle"));
	}
}
?>
