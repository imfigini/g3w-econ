<?php
namespace econ\operaciones\inicio_coordinador;

use siu\extension_kernel\vista_g3w2;
use kernel\kernel;

class vista extends vista_g3w2
{
    function ini()
    {
        $clase = 'operaciones\inicio_coordinador\pagelet_intro';
        $pl = kernel::localizador()->instanciar($clase, 'intro');
        $this->add_pagelet($pl);

        kernel::pagina()->set_etiqueta('titulo', kernel::traductor()->trans("inicio_coordinador.titulo"));
    }
}

?>