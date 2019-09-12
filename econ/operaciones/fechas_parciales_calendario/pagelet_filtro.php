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
        $this->add_var_js('url_correr_evaluacion', kernel::vinculador()->crear($operacion, 'correr_evaluacion'));

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
        $this->data['limites_periodo'] = $this->controlador->get_limites_periodo($anio_academico_hash, $periodo_hash);
        
        if (!empty($anio_academico_hash) && !empty($periodo_hash))
        {
            $evaluaciones = $this->controlador->get_evaluaciones($anio_academico_hash, $periodo_hash, $carrera, $mix);
            $evaluaciones = $this->eliminar_acentos($evaluaciones);
            $evaluaciones = $this->asignar_colores($evaluaciones);
            $this->data['eventos'] = $this->get_eventos($evaluaciones);

            $dias_no_laborales = $this->controlador->get_dias_no_laborales($anio_academico_hash, $periodo_hash);
            $this->data['dias_no_laborales_json'] = json_encode($dias_no_laborales, JSON_FORCE_OBJECT | JSON_PARTIAL_OUTPUT_ON_ERROR );
        }
    }
        
    public function get_eventos($datos)
    {
        //print_r($datos); die;
        $resultado = array();
        
        foreach ($datos as $evento)
        {
            $evaluacion         = trim($evento['EVALUACION']);
            $materia_nombre     = trim($evento['MATERIA_NOMBRE']);
            $materia            = $evento['MATERIA'];
            $eval               = $evento['EVAL_ID'];
            $estado             = $evento['ESTADO'];
            $color_acep         = $evento['COLOR_ACEP'];
            $color_pend         = $evento['COLOR_PEND'];

            $id                 = $materia.'-'.$eval;
            $title              = $evaluacion.' - '.$materia_nombre;
            $start              = $evento['FECHA'];
            $tip                = $evento['MATERIA_NOMBRE'];

            $calendarEvent = self::buildEvent($id, $title, $start, $tip, $materia, $eval, $estado, $color_acep, $color_pend);
            $resultado[] = $calendarEvent;
        }

        $resultado = implode(',', $resultado);
        $resultado = '['.$resultado.']';
        
        return $resultado;
    }

    static function buildEvent($id, $title, $start, $tip, $materia, $eval, $estado, $color_acep, $color_pend)
    {
        switch ($estado)
        {
            case 'A': $backgroundColor = $color_acep; break;
            case 'P': $backgroundColor = $color_pend; break;
        }

        $resultado = array();
        $evento = array (
                        '_id'               => $id,
                        'title'			    => $title,
                        'tip'               => $tip,
                        'start'			    => $start,
                        'textColor'         => 'black',
                        'backgroundColor'   => $backgroundColor,
                        'materia'           => $materia,
                        'evaluacion'        => $eval,
                        'estado'            => $estado,
                        'color_acep'        => $color_acep,
                        'color_pend'        => $color_pend,
                        'delta'             => 0
                        );

        foreach($evento as $colName => $dataValue)  {
            $resultado[] = '"'.$colName . '":"'. $dataValue . '"'; 
        }
        $resultado = implode(',', $resultado);
        $resultado = '{'.$resultado.'}';	
        return $resultado;
    }

    function asignar_colores($evaluaciones)
    {
        $colores = array(0=>'DodgerBlue', 1=>'LimeGreen', 2=>'Gold', 3=>'LightCoral', 4=>'DarkTurquoise');
        $colores_claros = array(0=>'LightSkyBlue', 1=>'Lime', 2=>'LightGoldenRodYellow', 3=>'LightPink', 4=>'PaleTurquoise'); 

        $materias = array();
        foreach($evaluaciones as $e)
        {
            $mat = $e['MATERIA'];
            if (!in_array($mat, $materias))
            {
                $materias[] = $mat;
            }
        }
        $cant = count($evaluaciones);
        foreach($materias as $k=>$mat)
        {
            for ($i=0; $i<$cant; $i++)
            {
                if ($evaluaciones[$i]['MATERIA'] == $mat)
                {
                    $evaluaciones[$i]['COLOR_ACEP'] = $colores[$k];
                    $evaluaciones[$i]['COLOR_PEND'] = $colores_claros[$k];
                }
            }
        }
        //print_r($evaluaciones);
        return $evaluaciones;
    }

    private function eliminar_acentos($arreglo)
    {
        $resultado = array();
        foreach ($arreglo as $a)
        {
            $a['MATERIA_NOMBRE'] = $this->eliminar_tildes($a['MATERIA_NOMBRE']);
            $resultado[] = $a;
        }
        return $resultado;
    }
    
    private function eliminar_tildes($cadena)
    {
        $cadena = str_replace(
            array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
            $cadena
        );

        $cadena = str_replace(
            array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
            $cadena );

        $cadena = str_replace(
            array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
            $cadena );

        $cadena = str_replace(
            array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
            array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
            $cadena );

        $cadena = str_replace(
            array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
            array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
            $cadena );

        $cadena = str_replace(
            array('ñ', 'Ñ', 'ç', 'Ç'),
            array('n', 'N', 'c', 'C'),
            $cadena
        );

        return $cadena;
    }
}
