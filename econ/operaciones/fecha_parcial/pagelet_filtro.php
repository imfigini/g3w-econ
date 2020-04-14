<?php
namespace econ\operaciones\fecha_parcial;

use kernel\interfaz\pagelet;
use kernel\kernel;

class pagelet_filtro extends pagelet {
	public function get_nombre()
	{
		return 'filtro';
	}

	function get_js_files()
	{
		$js = parent::get_js_files();
		$archivos = array_merge($js, $this->get_vista_form()->get_js_files());
        $archivos[] = kernel::vinculador()->vinculo_recurso("js/date-es-AR.js");
		return $archivos;
	}

	/**
	 * @return guarani_form
	 */
	function get_form()
	{
		return $this->get_form_builder()->get_formulario();
	}

	function get_vista_form(){
		return $this->get_form_builder()->get_vista();
	}
	
	/**
	* @return builder_form_filtro
	*/
	function get_form_builder()
	{
		return $this->controlador->get_form_builder();
	}

	public function prepare()
	{
		$operacion = kernel::ruteador()->get_id_operacion();

		$this->add_var_js('url_buscar', kernel::vinculador()->crear($operacion));
		$this->add_var_js('url_buscar_planes', kernel::vinculador()->crear($operacion, 'buscar_planes'));
		$this->add_var_js('url_buscar_materias', kernel::vinculador()->crear($operacion, 'buscar_materias'));

        $this->add_mensaje_js('filtro_todos', ucfirst(kernel::traductor()->trans('fecha_parcial.filtro_todos')));

        $this->add_var_js('ver', ucfirst(kernel::traductor()->trans('plan.ver')));
        $this->add_var_js('ocultar', ucfirst(kernel::traductor()->trans('plan.ocultar')));
	}
}
?>