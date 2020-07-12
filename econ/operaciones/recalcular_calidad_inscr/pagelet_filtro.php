<?php
namespace econ\operaciones\recalcular_calidad_inscr;

use kernel\interfaz\pagelet;
use kernel\kernel;

class pagelet_filtro extends pagelet {
	
	public function get_nombre()
	{
		return 'filtro';
	}

	/**
	 * @return guarani_form
	 */
	function get_form()
	{
		return $this->get_form_builder()->get_formulario();
	}

	function get_vista_form()
	{
		return $this->get_form_builder()->get_vista();
	}
	
	/**
	* @return builder_form_filtro
	*/
	function get_form_builder()
	{
		return $this->controlador->get_form_builder();
	}
    
	function get_periodo()
	{
		return $this->controlador->get_periodo();
	}
    
	function get_anio_academico()
	{
		return $this->controlador->get_anio_academico();
	}

	function get_materia()
	{
		return $this->controlador->get_materia();
	}

	function get_calidad()
	{
		return $this->controlador->get_calidad();
	}

	function get_mensaje_error()
	{
		return $this->controlador->get_mensaje_error();
	}

	function get_ultima_fecha_fin_turno_examen_regular($anio_academico_hash, $periodo_hash)
    {
		return $this->controlador->get_ultima_fecha_fin_turno_examen_regular($anio_academico_hash, $periodo_hash);
	}

	public function prepare()
	{   
		$operacion = kernel::ruteador()->get_id_operacion();
		$this->add_var_js('url_buscar_periodos', kernel::vinculador()->crear($operacion, 'buscar_periodos'));
		$this->add_var_js('url_buscar_materias', kernel::vinculador()->crear($operacion, 'buscar_materias'));

		$periodo_hash = $this->get_periodo();
		$anio_academico_hash = $this->get_anio_academico();
		$materia = $this->get_materia();
		$fecha_limite = $this->get_ultima_fecha_fin_turno_examen_regular($anio_academico_hash, $periodo_hash);
		$calidad = $this->get_calidad();
		$form = $this->get_form_builder();
		$form->set_anio_academico($anio_academico_hash);
		$form->set_periodo($periodo_hash);
		$form->set_materia($materia);
				
		$this->add_var_js('periodo_hash', $periodo_hash);
		$this->add_var_js('anio_academico_hash', $anio_academico_hash);        
		$this->add_var_js('materia', $materia);
		$this->add_var_js('calidad', $calidad);
		$this->add_var_js('fecha_limite', $fecha_limite);        

		$this->data['periodo_hash'] = $periodo_hash;
		$this->data['anio_academico_hash'] = $anio_academico_hash;
		$this->data['fecha_limite'] = $fecha_limite;
		$this->data['materia'] = $materia;
		$this->data['alumnos'] = $this->controlador->get_alumnos_calidad($anio_academico_hash, $periodo_hash, $calidad);
		//kernel::log()->add_debug('get_materias_promo_directa', $this->data['materias']);

		$this->data['form_url_grabar'] = kernel::vinculador()->crear('recalcular_calidad_inscr', 'grabar');
	}
}

