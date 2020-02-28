<?php

namespace econ\operaciones\notas_examen;

use kernel\kernel;
use siu\guarani;
use siu\modelo\datos\catalogo;

class pagelet_renglones extends \siu\operaciones\notas_examen\pagelet_renglones
{
	function esenteranota($nota)
	{
		if (strpos($nota, ',') >0 ){
			return false;
		}
		return true;
	}

}
