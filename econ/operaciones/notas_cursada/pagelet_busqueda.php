<?php
namespace econ\operaciones\notas_cursada;
use kernel\kernel;

class pagelet_busqueda extends \siu\operaciones\notas_cursada\pagelet_busqueda
{
	function prepare()
    {
		$this->data = array();
		$this->add_var_js('url_autocomplete', kernel::vinculador()->crear('notas_cursada', 'auto_alumno', $this->get_comision()));
		//Iris: Se agregó para autocalcular nota
		$this->add_var_js('url_autocalcular', kernel::vinculador()->crear('notas_cursada', 'autocalcular', $this->get_comision()));
    }
}
?>
