<?php
namespace econ\operaciones\fechas_parciales_calendario;

use kernel\kernel;
use siu\extension_kernel\controlador_g3w2;
use siu\modelo\datos\catalogo;
use kernel\util\validador;
//use siu\modelo\guarani_notificacion;


class controlador extends controlador_g3w2
{
    protected $datos_filtro = array('anio_academico'=>"", 'periodo'=>"", 'carrera'=>"", 'mix'=>"");
    
    function modelo()
    {
    }

    function accion__index()
    {
        if (!empty($_GET['formulario_filtro'])) {
            $this->datos_filtro = $_GET['formulario_filtro'];
        }
    }

    function get_periodo()
    {
        return $this->datos_filtro['periodo'];
    }

    function get_anio_academico()
    {
        return $this->datos_filtro['anio_academico'];
    }

    function get_carrera()
    {
        return $this->datos_filtro['carrera'];
    }
    
    function get_mix()
    {
        return $this->datos_filtro['mix'];
    }
    
    function set_periodo($periodo)
    {
        $this->datos_filtro['periodo'] = $periodo;
    }

    function set_anio_academico($anio_academico)
    {
        $this->datos_filtro['anio_academico'] = $anio_academico;
    }

    function set_carrera($carrera)
    {
        $this->datos_filtro['carrera'] = $carrera;
    }

    function set_mix($mix)
    {
        $this->datos_filtro['mix'] = $mix;
    }
    

    function accion__buscar_periodos() 
    {
        $anio_academico_hash = $this->validate_param('anio_academico', 'get', validador::TIPO_TEXTO);
        $anio_academico = $this->decodificar_anio_academico($anio_academico_hash);
        $datos = array();
        if (!is_null($anio_academico)){
                $datos = catalogo::consultar('unidad_academica', 'buscar_periodos_lectivos', array('anio' => $anio_academico));
        }
        $this->render_raw_json($datos);
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
        if (! isset($this->form_builder)) {
                        $this->form_builder = kernel::localizador()->instanciar('operaciones\fechas_parciales_calendario\filtro\builder_form_filtro');
        }

        return $this->form_builder;
    }

    function decodificar_anio_academico($anio_hash) 
    {
        $datos = catalogo::consultar('unidad_academica_econ', 'anios_academicos');
        if (!empty($datos)) {
                foreach($datos as $value){
                        if ($value['_ID_'] == $anio_hash){
                                return $value['ANIO_ACADEMICO'];
                        }
                }
        }
        return false;
    }

    function decodificar_periodo($periodo_hash = null, $anio_academico = null) 
    {
        if (!is_null($anio_academico)){
                $periodos = catalogo::consultar('unidad_academica', 'buscar_periodos_lectivos', array('anio' => $anio_academico));
                if (!is_null($periodos)) {
                        foreach($periodos as $value){
                                if ($value['ID'] == $periodo_hash){
                                        return $value['PERIODO_LECTIVO'];
                                }
                        }
                }
        }
        return false;
    }

    
    function get_evaluaciones($anio_academico_hash, $periodo_hash, $carrera, $mix)
    {
        if (!empty($anio_academico_hash))
        {
            $anio_academico = $this->decodificar_anio_academico($anio_academico_hash);
            if (!empty($anio_academico) and !empty($periodo_hash))
            {
                $periodo = $this->decodificar_periodo($periodo_hash, $anio_academico);

                $parametros = array(
                            'anio_academico' => $anio_academico,
                            'periodo' => $periodo,
                            'carrera' => $carrera,
                            'anio_cursada' => substr($mix, 0, 1),
                            'mix' => substr($mix, 1, 1)
                        );
                
                $resultado = catalogo::consultar('evaluaciones_parciales', 'get_evaluaciones_aceptadas', $parametros);
                $evaluaciones_aceptadas = $this->eliminar_acentos($resultado);
                $resultado = catalogo::consultar('evaluaciones_parciales', 'get_evaluaciones_pendientes', $parametros);
                $evaluaciones_pendientes = $this->eliminar_acentos($resultado);
                $evaluaciones = self::asignar_colores($evaluaciones_aceptadas, $evaluaciones_pendientes);
                return $evaluaciones;
            }
        }
        return null;
    }

    function asignar_colores($evaluaciones_aceptadas, $evaluaciones_pendientes)
    {
        $colores = array(0=>'DodgerBlue', 1=>'LimeGreen', 2=>'Gold', 3=>'LightCoral', 4=>'DarkTurquoise');
        $colores_claros = array(0=>'LightSkyBlue', 1=>'Lime', 2=>'LightGoldenRodYellow', 3=>'LightPink', 4=>'PaleTurquoise'); 
        $evaluaciones = array_merge($evaluaciones_aceptadas, $evaluaciones_pendientes);
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
                    switch ($evaluaciones[$i]['ESTADO'])
                    {
                        case 'A': $evaluaciones[$i]['COLOR'] = $colores[$k]; break;
                        case 'P': $evaluaciones[$i]['COLOR'] = $colores_claros[$k]; break;
                    }
                }
            }
        }
        //print_r($evaluaciones);
        return $evaluaciones;
    }
    
    function get_dias_no_laborales($anio_academico_hash, $periodo_hash)
    {
        if (!empty($anio_academico_hash))
        {
            $anio_academico = $this->decodificar_anio_academico($anio_academico_hash);
            if (!empty($periodo_hash))
            {
                $periodo = $this->decodificar_periodo($periodo_hash, $anio_academico);
                $parametros = array(
                        'anio_academico' => $anio_academico,
                        'periodo' => $periodo
                    );
                return catalogo::consultar('evaluaciones_parciales', 'get_dias_no_laborales', $parametros);
            }
        }
        return null;
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
?>