<?php
namespace econ\operaciones\fechas_parciales_propuesta;

use siu\extension_kernel\vista_g3w2;
use kernel\kernel;

class vista extends vista_g3w2
{
	function ini()
	{
		$clase = 'operaciones\fechas_parciales_propuesta\pagelet_filtro';
		$pl = kernel::localizador()->instanciar($clase, 'filtro');
		$this->add_pagelet($pl, 1);

        $clase = 'operaciones\fechas_parciales_propuesta\pagelet_propuesta2019';
        $pl = kernel::localizador()->instanciar($clase, 'propuesta2019');
        $this->add_pagelet($pl);
		
        $clase = 'operaciones\fechas_parciales_propuesta\pagelet_propuesta';
        $pl = kernel::localizador()->instanciar($clase, 'propuesta');
        $this->add_pagelet($pl);

		kernel::pagina()->set_etiqueta('titulo', kernel::traductor()->trans("tit_fechas_parciales_propuesta"));
			
	}
}
?>


