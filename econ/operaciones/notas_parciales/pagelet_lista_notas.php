<?php
namespace econ\operaciones\notas_parciales;

use kernel\interfaz\pagelet;
use kernel\kernel;
use siu\guarani;

class pagelet_lista_notas extends \siu\operaciones\notas_parciales\pagelet_lista_notas
{
	protected $encabezado;

	
    function get_nombre()
    {
        return 'lista_notas';
    }
	
	/**
	 * @return carga_notas_cursada
	 */
	protected function modelo()
	{
		return $this->controlador->modelo();
	}
	
	
	protected function get_encabezado()
	{
		return $this->controlador->get_encabezado();
	}
	
	protected function get_renglones()
	{
		// se pide al controlador para hacer validación con los datos enviados al guardar
		$renglones = $this->controlador->get_renglones();
		return $renglones;
	}
	
	protected function get_evaluacion_id()
	{
		$eval_id = $this->controlador->get_evaluacion_id();
		return $eval_id;
	}

    function prepare()
    {
		$this->data = array();
		$this->data['renglones'] = $this->get_renglones();
		$this->data['encabezado'] = $this->get_encabezado();
		$this->add_mensaje_js('nota_invalida', kernel::traductor()->trans('nota_invalida'));
		$this->add_mensaje_js('asistencia_invalida', kernel::traductor()->trans('asistencia_invalida'));
		$notas = $this->controlador->get_escala_js();
		$this->add_var_js('escala', $notas);
		$this->add_var_js('evaluacion_id', $this->get_evaluacion_id());

		$operacion = kernel::ruteador()->get_id_operacion();
		$this->data['url']['generar_pdf'] = kernel::vinculador()->crear($operacion, 'generar_pdf');
		$this->data['url']['generar_excel'] = kernel::vinculador()->crear($operacion, 'generar_excel');
	}

}
?>
