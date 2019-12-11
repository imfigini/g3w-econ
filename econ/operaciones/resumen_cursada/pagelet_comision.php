<?php
namespace econ\operaciones\resumen_cursada;

use kernel\interfaz\pagelet;
use kernel\kernel;
use kernel\util\u;
use siu\modelo\datos\catalogo;

class pagelet_comision extends \siu\operaciones\resumen_cursada\pagelet_comision
{
	public function get_nombre(){
		return 'comision';
	}

	public function prepare()
	{
		$this->add_var_js('comision_hash', $this->controlador->get_comision_hash());
		$this->add_var_js('anio_academico_hash', $this->controlador->get_anio_academico_hash());
		$this->add_var_js('periodo_hash', $this->controlador->get_periodo_hash());

		$operacion = kernel::ruteador()->get_id_operacion();
		$this->data['url']['generar_pdf'] = kernel::vinculador()->crear($operacion, 'generar_pdf');
		$this->data['url']['generar_excel'] = kernel::vinculador()->crear($operacion, 'generar_excel');
	}
}
?>