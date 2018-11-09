<?php
namespace econ\operaciones\periodos_evaluacion;

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

	public function prepare()
	{   
            $operacion = kernel::ruteador()->get_id_operacion();
            $this->add_var_js('url_buscar_periodos', kernel::vinculador()->crear($operacion, 'buscar_periodos'));

//            $this->add_var_js('msg_guardado_exitoso', trans('datos_censales.guardado_exitoso'));
//            $this->add_var_js('msg_error_al_guardar', trans('datos_censales.error_al_guardar'));
           
            $periodo_hash = $this->get_periodo();
            $anio_academico_hash = $this->get_anio_academico();
            $form = $this->get_form_builder();
            $form->set_anio_academico($anio_academico_hash);
            $form->set_periodo($periodo_hash);
            
            $this->data['mensaje'] = $this->get_mensaje();
            
            $this->add_var_js('periodo_hash', $periodo_hash);
            $this->add_var_js('anio_academico_hash', $anio_academico_hash);        
            $this->data['periodo_hash'] = $periodo_hash;
            $this->data['anio_academico_hash'] = $anio_academico_hash;

            $materias = $this->controlador->get_materias_cincuentenario();
            
            $datos = array();
            $cant = count($materias);
            for ($i=0; $i<$cant; $i++)
            {
                $materias[$i]['ciclo'] = catalogo::consultar('cursos', 'get_ciclo_de_materias', array('materia'=>$materias[$i]['MATERIA'])); 
                $comisiones = $this->controlador->get_comisiones($anio_academico_hash, $periodo_hash, $materias[$i]['MATERIA']);
                if (count($comisiones) > 0)
                {
                    $materias[$i]['comisiones'] = $comisiones;
                    $datos[] = $materias[$i];
                }
            }
            
            $this->data['datos'] = $datos;
	        
            $link_form_comision = kernel::vinculador()->crear('periodos_evaluacion', 'grabar_comision');
            $this->data['form_url_comision'] = $link_form_comision;     
            
            $link_form_materia = kernel::vinculador()->crear('periodos_evaluacion', 'grabar_materia');
            $this->data['form_url_materia'] = $link_form_materia;     
            
        }
}
