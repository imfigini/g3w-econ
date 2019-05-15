<?php
namespace econ\operaciones\mixes;

use siu\extension_kernel\vista_g3w2;
use kernel\kernel;


class vista_modificar extends vista_g3w2
{
	function ini()
	{
		$clase = 'operaciones\mixes\pagelet_modificar';
		$pl = kernel::localizador()->instanciar($clase, 'contenido');
		$this->add_pagelet($pl);
	}
        
}