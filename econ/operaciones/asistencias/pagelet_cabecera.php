<?php
namespace econ\operaciones\asistencias;

use kernel\interfaz\pagelet;
use kernel\kernel;
use siu\guarani;

class pagelet_cabecera extends \siu\operaciones\asistencias\pagelet_cabecera
{	
    function get_nombre()
    {
        return 'cabecera';
    }

    function prepare()
    {
		$this->data = array();
		$this->data = $this->controlador->get_clase_encabezado_econ();
    }
}
?>
