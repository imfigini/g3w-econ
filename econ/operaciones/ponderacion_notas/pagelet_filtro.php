<?php
namespace econ\operaciones\ponderacion_notas;

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
    
	function get_anio_academico()
	{
		return $this->controlador->get_anio_academico();
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
		$anio_academico_hash = $this->get_anio_academico();
		
		$form = $this->get_form_builder();
		$form->set_anio_academico($anio_academico_hash);
		$form->set_periodo($periodo_hash);

		$this->add_var_js('periodo_hash', $periodo_hash);
		$this->add_var_js('anio_academico_hash', $anio_academico_hash);
		$this->data['periodo_hash'] = $periodo_hash;
		$this->data['anio_academico_hash'] = $anio_academico_hash;

		
		if (!empty($anio_academico_hash) && !empty($periodo_hash))
		{
			$this->data['mensaje'] = $this->get_mensaje();
			$this->data['mensaje_error'] = $this->get_mensaje_error();		
			$this->data['es_editable'] = $this->controlador->hoy_dentro_de_periodo($anio_academico_hash, $periodo_hash);	
		
			$materias = $this->controlador->get_materias_cincuentenario($anio_academico_hash, $periodo_hash);
		
			$cant = count($materias);
			for ($i=0; $i<$cant; $i++)
			{
				$materias[$i]['PROMO'] = $this->controlador->is_promo($anio_academico_hash, $periodo_hash, $materias[$i]['MATERIA']);
				if (!$materias[$i]['PROMO'])
				{
					$this->controlador->ctrl_no_tiene_ponderacion_prom($anio_academico_hash, $periodo_hash, $materias[$i]['MATERIA'], 'P');
				}

				$materias[$i]['PROMO_DIRECTA'] = $this->controlador->is_promo_directa($anio_academico_hash, $periodo_hash, $materias[$i]['MATERIA']);
				if (!$materias[$i]['PROMO_DIRECTA'])
				{
					$this->controlador->ctrl_no_tiene_ponderacion_prom($anio_academico_hash, $periodo_hash, $materias[$i]['MATERIA'], 'D');
				}
				$materias[$i]['PONDERACIONES'] = $this->controlador->get_ponderaciones_notas($anio_academico_hash, $periodo_hash, $materias[$i]['MATERIA']);
			}

			$this->data['datos'] = $materias;
			$link_form_materia = kernel::vinculador()->crear('ponderacion_notas', 'grabar_materia');
			$this->data['form_url_materia'] = $link_form_materia;     
		}
	}
}
