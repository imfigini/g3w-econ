<?php
namespace econ\operaciones\fechas_parciales_prop_2019;

use kernel\interfaz\pagelet;
use siu\modelo\datos\catalogo;
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
    
	function get_anio_academico_hash()
	{
		return $this->controlador->get_anio_academico();
	}
	 
	function get_anio_seleccionado()
	{
		return $this->controlador->get_anio_seleccionado();
	}

	function get_mensaje()
	{
		return $this->controlador->get_mensaje();
	}

	function get_mensaje_error()
	{
		return $this->controlador->get_mensaje_error();
	}
        
	public function prepare()
	{   
		$operacion = kernel::ruteador()->get_id_operacion();
		$this->add_var_js('url_buscar_periodos', kernel::vinculador()->crear($operacion, 'buscar_periodos'));
		
		$periodo_hash = $this->get_periodo();
		$anio_academico_hash = $this->get_anio_academico_hash();
		$form = $this->get_form_builder();
		$form->set_anio_academico($anio_academico_hash);
		$form->set_periodo($periodo_hash);
		
		$this->data['mensaje'] = $this->get_mensaje();
		$this->data['mensaje_error'] = $this->get_mensaje_error();
		
		$this->add_var_js('periodo_hash', $periodo_hash);
		$this->add_var_js('anio_academico_hash', $anio_academico_hash);        
		$this->data['periodo_hash'] = $periodo_hash;
		$this->data['anio_academico_hash'] = $anio_academico_hash;
		
		if (!empty($anio_academico_hash) && !empty($periodo_hash))
		{
			$anio_academico = $this->get_anio_seleccionado();
			//print_r($anio_academico);
			if ($anio_academico == 2019)
			{
				$datos = $this->controlador->get_materias_y_comisiones_cincuentenario($anio_academico_hash, $periodo_hash);
				//kernel::log()->add_debug('$datos '.__LINE__, $datos);
				//print_r($datos);

				$dias_no_laborales = $this->controlador->get_dias_no_laborales($anio_academico_hash, $periodo_hash);
				
				//print_R($datos);
				$this->data['datos'] = $datos;
				$this->data['datos_json'] = json_encode($datos, JSON_FORCE_OBJECT | JSON_PARTIAL_OUTPUT_ON_ERROR );
				
				$this->data['periodos_evaluacion'] = $this->controlador->get_periodos_evaluacion($anio_academico_hash, $periodo_hash);
				$this->data['dias_no_laborales'] = json_encode($dias_no_laborales); 
				$priodo_solicitud_fechas = $this->controlador->get_periodo_solicitud_fechas($anio_academico_hash, $periodo_hash);
				$this->data['priodo_solicitud_fechas'] = $priodo_solicitud_fechas[0];

				$link_form_materia = kernel::vinculador()->crear('fechas_parciales_prop_2019', 'grabar_materia');
				$this->data['form_url_materia'] = $link_form_materia;     
			}
		}
	}
}
