<?php
namespace econ\operaciones\asignacion_coord_materias;

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
            $this->data['materias'] = $this->controlador->get_materias_en_comision($anio_academico_hash, $periodo_hash);
            $this->data['mensaje'] = $this->get_mensaje();
            $this->data['mensaje_error'] = $this->get_mensaje_error();
            
            $link_replicar_coordinadores = kernel::vinculador()->crear('asignacion_coord_materias', 'replicar_coordinadores');
            $this->data['form_url_replicar_coord'] = $link_replicar_coordinadores;     

	}
}
