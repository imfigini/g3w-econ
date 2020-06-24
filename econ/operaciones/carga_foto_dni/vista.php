<?php
namespace econ\operaciones\carga_foto_dni;

use siu\extension_kernel\vista_g3w2;
use kernel\kernel;

class vista extends vista_g3w2
{
	function ini()
	{
        $clase = 'operaciones\carga_foto_dni\pagelet_formulario';
		$pl = kernel::localizador()->instanciar($clase, 'formulario');
        $this->add_pagelet($pl);
    }
   
     
}
?>