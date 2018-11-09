<?php
namespace econ\operaciones\periodos_evaluacion;

use kernel\kernel;
use siu\extension_kernel\controlador_g3w2;
use siu\modelo\datos\catalogo;
use kernel\util\validador;
//use siu\modelo\guarani_notificacion;

class controlador extends controlador_g3w2
{
    protected $datos_filtro = array('anio_academico'=>"", 'periodo'=>"", 'mensaje'=>'');
    
    function modelo()
    {
    }

    function accion__index()
    {
        if (!empty($_GET['formulario_filtro'])) {
            $this->datos_filtro = $_GET['formulario_filtro'];
        }
    }
    
    function get_periodos_evaluacion($anio_academico_hash, $periodo_hash)
    {
        if (!empty($anio_academico_hash))
        {
            $anio_academico = $this->decodificar_anio_academico($anio_academico_hash);
            if (!empty($anio_academico))
            {
                if (!empty($periodo_hash))
                {
                    $periodo = $this->decodificar_periodo($periodo_hash, $anio_academico);
                }
                else
                {
                    $periodo = '';
                }    
                $parametros = array(
                                'anio_academico' => $anio_academico,
                                'periodo' => $periodo
                    );
                return catalogo::consultar('evaluaciones_parciales', 'get_periodos_evaluacion', $parametros);
            }
        }
        return null;
    }
    
    function get_periodo()
    {
        return $this->datos_filtro['periodo'];
    }

    function get_anio_academico()
    {
        return $this->datos_filtro['anio_academico'];
    }

    function get_mensaje()
    {
        return $this->datos_filtro['mensaje'];
    }
    
    function get_fecha_inicio()
    {
        $anio_academico_hash = $this->get_anio_academico();
        $periodo_hash = $this->get_periodo();
        if (!empty($anio_academico_hash))
        {
            $anio_academico = $this->decodificar_anio_academico($anio_academico_hash);
            if (!empty($anio_academico))
            {
                if (!empty($periodo_hash))
                {
                    $periodo = $this->decodificar_periodo($periodo_hash, $anio_academico);
                    if ($periodo[0] == '1')
                    {
                        return '01/06/'.$anio_academico;
                    }
                    else
                    {
                        return '01/11/'.$anio_academico;
                    }
                }
            }
        }
        return '';
    }
    
    function set_periodo($periodo)
    {
        $this->datos_filtro['periodo'] = $periodo;
    }

    function set_anio_academico($anio_academico)
    {
        $this->datos_filtro['anio_academico'] = $anio_academico;
    }

    function set_mensaje($mensaje)
    {
        $this->datos_filtro['mensaje'] = $mensaje;
    }

    /** Graba los períodos de evaluación definidos */
    function accion__grabar_periodo_evaluacion()
    {
        $anio_academico_hash = $this->validate_param('anio_academico_hash', 'post', validador::TIPO_TEXTO);
        $periodo_hash = $this->validate_param('periodo_hash', 'post', validador::TIPO_TEXTO);

        if (kernel::request()->isPost()) 
        {
            try 
            {
                $parametros = array();
                $parametros['anio_academico'] = $this->decodificar_anio_academico($anio_academico_hash);
                $parametros['periodo'] = $this->decodificar_periodo($periodo_hash, $parametros['anio_academico']);
        
                $periodos = $this->get_periodos_evaluacion($anio_academico_hash, $periodo_hash);
                foreach ($periodos as $periodo)
                {
                    $parametros['evaluacion'] = $periodo['EVALUACION'];
                    $fecha_inicio = 'fecha_inicio_'.$periodo['EVALUACION'];
                    $parametros['fecha_inicio'] = $this->validate_param("$fecha_inicio", 'post', validador::TIPO_TEXTO);
                    $fecha_fin = 'fecha_fin_'.$periodo['EVALUACION'];
                    $parametros['fecha_fin'] = $this->validate_param("$fecha_fin", 'post', validador::TIPO_TEXTO);
                    catalogo::consultar('evaluaciones_parciales', 'set_periodos_evaluacion', $parametros);
                }
                
                $mensaje = 'Se actualizaron correctamente los períodos de evaluación correspondientes al '.$parametros['periodo'].' del año '.$parametros['anio_academico'].'.';
                $this->set_anio_academico($anio_academico_hash);
                $this->set_periodo($periodo_hash);
                $this->set_mensaje($mensaje);
            }
            catch (\Exception $e) 
            {
                $mensaje = 'Error en la actualización.';
                $this->set_anio_academico($anio_academico_hash);
                $this->set_periodo($periodo_hash);
                $this->set_mensaje($e->getMessage().' - '.$mensaje);
            }
        }
        
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
                        $this->form_builder = kernel::localizador()->instanciar('operaciones\periodos_evaluacion\filtro\builder_form_filtro');
        }

        return $this->form_builder;
    }

    function decodificar_anio_academico($anio_hash) 
    {
        $datos = catalogo::consultar('unidad_academica', 'anios_academicos');
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
}
?>