<?php
namespace econ\operaciones\fechas_parciales_calendario;

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
        
        function get_carrera()
	{
            return $this->controlador->get_carrera();
	}
        
        function get_mix()
	{
            return $this->controlador->get_mix();
	}
        
	public function prepare()
	{   
            $operacion = kernel::ruteador()->get_id_operacion();
            $this->add_var_js('url_buscar_periodos', kernel::vinculador()->crear($operacion, 'buscar_periodos'));
//            $this->add_var_js('url_buscar_carreras', kernel::vinculador()->crear($operacion, 'buscar_carreras'));

            $periodo_hash = $this->get_periodo();
            $anio_academico_hash = $this->get_anio_academico();
            $carrera = $this->get_carrera();
            $mix = $this->get_mix();
            $form = $this->get_form_builder();
            $form->set_anio_academico($anio_academico_hash);
            $form->set_periodo($periodo_hash);
            $form->set_carrera($carrera);
            $form->set_mix($mix);

            $this->add_var_js('periodo_hash', $periodo_hash);
            $this->add_var_js('anio_academico_hash', $anio_academico_hash);        
            $this->add_var_js('carrera', $carrera);        
            $this->add_var_js('mix', $mix);        

            $this->data['periodo_hash'] = $periodo_hash;
            $this->data['anio_academico_hash'] = $anio_academico_hash;
            $this->data['carrera'] = $carrera;
            $this->data['mix'] = $mix;
            
            if (!empty($anio_academico_hash) && !empty($periodo_hash))
            {
                $datos = $this->controlador->get_evaluaciones($anio_academico_hash, $periodo_hash, $carrera, $mix);
                $this->data['eventos'] = $this->get_eventos($datos);
                //print_r($this->data['eventos']);

                $dias_no_laborales = $this->controlador->get_dias_no_laborales($anio_academico_hash, $periodo_hash);
                $this->data['dias_no_laborales_json'] = json_encode($dias_no_laborales, JSON_FORCE_OBJECT | JSON_PARTIAL_OUTPUT_ON_ERROR );

//                print_r($this->data);
//                $this->data['periodos_evaluacion'] = $this->controlador->get_periodos_evaluacion($anio_academico_hash, $periodo_hash);
//                $this->data['dias_no_laborales'] = json_encode($dias_no_laborales); 

            }
        }
        
    public function get_eventos($datos)
    {
        //print_r($datos); die;
        $resultado = array();
        // $i = 0;
        kernel::log()->add_debug('$get_eventos: ', $datos);
        foreach ($datos as $evento)
        {
            $id                 = $evento['MATERIA']. ' - '.trim($evento['EVALUACION']);
            $materia_nombre     = $evento['MATERIA_NOMBRE'];
            $title              = trim($evento['EVALUACION']).' - '.trim($materia_nombre);
            $start              = $evento['FECHA'];
            $end                = $evento['FECHA'];
            $tip                = $evento['MATERIA_NOMBRE'];
            $url                = '';
            $backgroundColor	= $evento['COLOR'];
            $evaluacion         = $evento['EVALUACION'];

            $calendarEvent = self::buildEvent($id, $title, $start, $end, $url, $tip, $backgroundColor, $evaluacion);
            $resultado[] = $calendarEvent;
            
            // if ($i<4)
            //     $i++;
            // else
            //     $i=0;
        }
        //Para probar lo  de los feriados
        // $calendarEvent = self::buildEvent('All Day Event', '2019-09-10', '', '', 'feriado', NCURSES_COLOR_YELLOW);
        // $resultado[] = $calendarEvent;

        // red areas where no events can be dropped
        // $calendarEvent = self::buildFeriado('2019-09-17');
        // $resultado[] = $calendarEvent;
        // $calendarEvent = self::buildFeriado('2019-09-05');
        // $resultado[] = $calendarEvent;

        $resultado = implode(',', $resultado);
        $resultado = '['.$resultado.']';
        
        //print_r($resultado); 
        return $resultado;
    }

    static function buildEvent($id, $title, $start, $end, $url, $tip, $backgroundColor, $evaluacion)
    {
        $resultado = array();
        $evento = array (
                        'id'            => $id,
                        'title'			=> $title,
                        'tip'           => $tip,
                        'start'			=> $start,
                        //'url'			=> $url,
                        'textColor'     => 'black',
                        'backgroundColor'       => $backgroundColor,
                        'evaluacion'    => $evaluacion
                        );

        foreach($evento as $colName => $dataValue)  {
            $resultado[] = '"'.$colName . '":"'. $dataValue . '"'; 
        }
        $resultado = implode(',', $resultado);
        $resultado = '{'.$resultado.'}';	
        return $resultado;
    }

    static function buildFeriado($fecha)
    {
        $resultado = array();
        $evento = array (
                        'id'            => 'feriado',
                        'start'			=> $fecha,
                        'end'			=> $fecha,
                        'overlap'       => 'false',
                        'rendering'     => 'background',
                        'color'         => 'LightCoral'
                        );

        foreach($evento as $colName => $dataValue)  {
            $resultado[] = '"'.$colName . '":"'. $dataValue . '"'; 
        }
        $resultado = implode(',', $resultado);
        $resultado = '{'.$resultado.'}';	
        return $resultado;
    }

}
