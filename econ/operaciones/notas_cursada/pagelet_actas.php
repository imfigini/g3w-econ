<?php
namespace econ\operaciones\notas_cursada;

use kernel\interfaz\pagelet;
use kernel\kernel;
use siu\guarani;
use siu\modelo\datos\catalogo;

class pagelet_actas extends \siu\operaciones\notas_cursada\pagelet_actas
{
    function get_nombre()
    {
        return 'actas';
    }

	function get_lista_actas()
	{
		$actas = $this->controlador->modelo()->info__actas_abiertas();
		foreach ($actas as $key => $acta) {
			$actas[$key]['LINK'] = kernel::vinculador()->crear('notas_cursada', 'edicion', $acta[catalogo::id]);
		}
		
		return $actas;
	}
	
    function prepare()
    {
		$this->data = array();
		$this->data['actas'] = $this->get_lista_actas();
    }
}
?>
