<?php
namespace econ\operaciones\fecha_parcial;

use siu\extension_kernel\vista_g3w2;
use kernel\kernel;

class vista extends vista_g3w2
{
    function ini()
    {
		$clase = 'operaciones\fecha_parcial\pagelet_filtro';
		$pl = kernel::localizador()->instanciar($clase, 'filtro');
        $this->add_pagelet($pl);
	
		kernel::pagina()->set_etiqueta('titulo', kernel::traductor()->trans("tit_fecha_parcial"));
    }
}
?>