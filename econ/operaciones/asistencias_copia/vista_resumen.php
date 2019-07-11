<?php
namespace econ\operaciones\asistencias;

use siu\extension_kernel\vista_g3w2;
use kernel\kernel;

class vista_resumen extends vista_g3w2
{
    function ini()
    {
		$this->set_template('template_vista_resumen');
		
		$clase = 'operaciones\asistencias\pagelet_resumen';
		$pl = kernel::localizador()->instanciar($clase, 'resumen');
		$this->add_pagelet($pl);

                kernel::pagina()->set_etiqueta('titulo', kernel::traductor()->trans("tit_asistencia_imprimir_planilla"));
		
		/* url botón Volver */
		$operacion = kernel::ruteador()->get_id_operacion();
		$this->agregar_a_contexto('url_back', kernel::vinculador()->crear($operacion));
    }
}

?>