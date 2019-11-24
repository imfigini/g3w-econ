<?php
namespace econ\operaciones\notas_cursada;

use kernel\interfaz\pagelet;
use kernel\kernel;
use siu\guarani;

class pagelet_autocalcular extends pagelet
{
    function get_nombre()
    {
        return 'autocalcular';
    }
		
	function get_comision()
	{
		$cabecera = $this->controlador->get_encabezado();
		return $cabecera['COMISION'];
	}
	
    function prepare()
    {
		$this->data = array();
		
		$this->add_var_js('max_textarea', pagelet_renglones::MAX_CARACTERES_CORREGIDO_POR);
		$this->add_var_js('texto_invalido', kernel::traductor()->trans('texto_invalido'));
		$this->add_var_js('nota_invalida', kernel::traductor()->trans('nota_invalida'));
		$this->add_var_js('asistencia_invalida', kernel::traductor()->trans('asistencia_invalida'));
		
		//Iris: Se agregó para autocalcular nota
		$operacion = kernel::ruteador()->get_id_operacion();
		$this->add_var_js('url_autocalcular', kernel::vinculador()->crear($operacion, 'autocalcular'));
		
        $encabezado = $this->controlador->get_encabezado();
        $this->add_var_js('fecha_inicio', $encabezado['FECHA_INICIO']);
		$this->add_var_js('fecha_fin', date('d/m/Y'));
		$this->add_var_js('fecha_invalida', kernel::traductor()->trans('fecha_invalida_acta', array(
			'%1%' => $encabezado['FECHA_INICIO'],
			'%2%' => date('d/m/Y')
		)));
    }
}
?>
