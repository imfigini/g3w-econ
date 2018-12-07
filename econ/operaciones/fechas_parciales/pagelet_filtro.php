<?php
namespace econ\operaciones\fechas_parciales;

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
        
//        function get_mensaje()
//	{
//            return $this->controlador->get_mensaje();
//	}

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
            
          //  $this->data['mensaje'] = $this->get_mensaje();
            
            $this->add_var_js('periodo_hash', $periodo_hash);
            $this->add_var_js('anio_academico_hash', $anio_academico_hash);        
            $this->data['periodo_hash'] = $periodo_hash;
            $this->data['anio_academico_hash'] = $anio_academico_hash;

            if (!empty($anio_academico_hash) && !empty($periodo_hash))
            {
                $datos = $this->controlador->get_materias_y_comisiones_cincuentenario($anio_academico_hash, $periodo_hash);
//                print_r($datos); 
//                die;
                
                $this->data['datos'] = $datos;
                $this->data['datos_json'] = json_encode($datos, JSON_FORCE_OBJECT | JSON_PARTIAL_OUTPUT_ON_ERROR );
                
                $this->data['periodos_evaluacion'] = $this->controlador->get_periodos_evaluacion($anio_academico_hash, $periodo_hash);
                $dias_no_laborales = $this->controlador->get_dias_no_laborales($anio_academico_hash, $periodo_hash);
                $this->data['dias_no_laborales'] = json_encode($dias_no_laborales); 
                
                
                    /*
                $link_form_comision = kernel::vinculador()->crear('fechas_parciales', 'grabar_comision');
                $this->data['form_url_comision'] = $link_form_comision;     

                $link_form_materia = kernel::vinculador()->crear('fechas_parciales', 'grabar_materia');
                $this->data['form_url_materia'] = $link_form_materia;     
                */
            }
        }
        
        static function armar_arreglo_json($datos)
        {
            $resultado = array();
            //print_r($datos);
            foreach($datos AS $materia)
            {
                $m = array();
                $m['MATERIA'] = $materia['MATERIA'];
                $m['ES_FUNDAMENTO'] = $materia['ciclo']['es_ciclo_fundamento'];
                $m['CALIDAD'] = $materia['calidad'];
                //Si pertenecen todas las comisiones a un mismo ciclo, puede aplicar la misma fecha a todas
                if ($materia['ciclo']['ciclo_nombre'] == 'Fundamento' or $materia['ciclo']['ciclo_nombre'] == 'Profesional') 
                {
                    foreach ($materia['comisiones_mismo_dia']['promo']['DIAS'] as $fecha)
                    {
                        if (isset($fecha['DIA'])) 
                        {
                            $m['DIAS_PROMO'][] = $fecha['DIA'];
                        }
                    }
                    foreach ($materia['comisiones_mismo_dia']['regu']['DIAS'] as $fecha)    
                    {
                        if (isset($fecha['DIA'])) 
                        {
                            $m['DIAS_REGU'][] = $fecha['DIA'];
                        }
                    }
                }
                $com = array();
                foreach($materia['comisiones'] AS $comision)
                {
                    $c = array();
                    $c['COMISION'] = $comision['COMISION'];
                    switch ($comision['TIPO_COMISON'])
                    {
                        case 'Regular' : $c['TIPO_COMISION'] = 'R'; break;
                        case 'Promoción' : $c['TIPO_COMISION'] = 'P'; break;
                        case 'Promoción y Regular' : $c['TIPO_COMISION'] = 'PyR'; break;
                    }
                    $c['POSIBLES_FECHAS_EVAL'] = $comision['EVAL'];
                    $com[] = $c;
                }
                $m['COMISIONES'] = $com;
                $resultado[] = $m;
            }
            print_r($resultado);
            return json_encode($resultado);
        }
}
