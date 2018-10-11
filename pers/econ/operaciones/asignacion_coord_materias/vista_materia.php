<?php
namespace econ\operaciones\asignacion_coord_materias;

use siu\extension_kernel\vista_g3w2;
use kernel\kernel;

class vista_materia extends vista_g3w2
{
	function ini()
	{
        //$this->set_template('template_comision');
        
            $clase = 'operaciones\asignacion_coord_materias\pagelet_materia';
            $pl = kernel::localizador()->instanciar($clase, 'contenido');
            $this->add_pagelet($pl);

            kernel::pagina()->set_etiqueta('titulo', kernel::traductor()->trans("tit_asignacion_coord_materias"));
	}
}
?>