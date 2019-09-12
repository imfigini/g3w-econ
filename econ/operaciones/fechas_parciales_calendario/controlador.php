<?php
namespace econ\operaciones\fechas_parciales_calendario;

use kernel\kernel;
use siu\extension_kernel\controlador_g3w2;
use siu\modelo\datos\catalogo;
use kernel\util\validador;
use siu\errores\error_guarani;
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

    function accion__correr_evaluacion() 
    {
        $parametros['materia'] = $this->validate_param('materia', 'post', validador::TIPO_TEXTO);      
        $parametros['evaluacion'] = $this->validate_param('evaluacion', 'post', validador::TIPO_TEXTO);
        $parametros['fecha_orig'] = $this->validate_param('fecha_orig', 'post', validador::TIPO_TEXTO);
        $parametros['fecha_dest'] = $this->validate_param('fecha_dest', 'post', validador::TIPO_TEXTO);
        $color_acep = $this->validate_param('color_acep', 'post', validador::TIPO_TEXTO);
        $anio_academico_hash = $this->validate_param('anio_academico_hash', 'post', validador::TIPO_TEXTO);
        $periodo_hash = $this->validate_param('periodo_hash', 'post', validador::TIPO_TEXTO);  
        $parametros['anio_academico'] = $this->decodificar_anio_academico($anio_academico_hash);
        $parametros['periodo'] = $this->decodificar_periodo($periodo_hash, $parametros['anio_academico']);
        
        //kernel::log()->add_debug('accion__confirmar_evaluacion parametros: ', $parametros);

        try
        {
            kernel::db()->abrir_transaccion();

            $datos_propuestos = catalogo::consultar('evaluaciones_parciales_calendario', 'get_fechas_propuestas', $parametros);
            //kernel::log()->add_debug('accion__confirmar_evaluacion datos: ', $datos_propuestos);

            //throw new error_guarani('probando supeusto error!!!!!!!!!!!!!');

            $exito = true;

            foreach($datos_propuestos as $dato)
            {

                //print_r($dato);
                $param['comision'] = $dato['COMISION'];
                $param['evaluacion'] = $parametros['evaluacion'];

                $tiene_notas_cargadas = catalogo::consultar('evaluaciones_parciales_calendario', 'tiene_notas_cargadas', $param);
                if ($tiene_notas_cargadas) {
                    throw new error_guarani('La comisión '.$dato['COMISION'].' tiene notas cragadas. ');
                }

                $fecha = date("Y-m-d", strtotime($dato['FECHA']));
                $estado = 'R';
                if ($fecha == $parametros['fecha_dest']) {
                    $estado = 'A';
                }
                $hora_inicio = catalogo::consultar('evaluaciones_parciales_calendario', 'get_hora_comienzo_clase', $param);
                
                $param['escala_notas'] = $dato['ESCALA_NOTAS'];
                $param['fecha_hora'] = $parametros['fecha_dest'].' '.$hora_inicio[0];
                $param['estado'] = $estado;
                
                $alta = catalogo::consultar('cursos', 'alta_evaluacion_parcial', $param);
                if (!$alta['success']) {
                    $exito = false;
                }
            }
            //$resultado = array();
            if ($exito)
            {
                $resultado['fecha'] = $parametros['fecha_dest'];
                $resultado['backgroundColor'] = $color_acep;
            }
            kernel::db()->cerrar_transaccion();
            //kernel::log()->add_debug('accion__confirmar_evaluacion: ', $resultado);
            $this->render_ajax('mensaje', $resultado);
        }
        catch (error_guarani $e)
        {
            kernel::db()->abortar_transaccion();
            $this->finalizar_request_con_notificaciones('No se puede modificar la fecha de la evaluación. ', $e);
        }
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

    function get_limites_periodo($anio_academico_hash = null, $periodo_hash = null)
    {
        if (!empty($anio_academico_hash) && !empty($periodo_hash))
        {
            $parametros['anio_academico'] = $this->decodificar_anio_academico($anio_academico_hash);
            $parametros['periodo'] = $this->decodificar_periodo($periodo_hash, $parametros['anio_academico']);
            if (!is_null($anio_academico_hash) && !is_null($periodo_hash)) {
                return catalogo::consultar('unidad_academica_econ', 'get_limites_periodo', $parametros);
            }
        }
        return null;
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
                
                $evaluaciones_aceptadas = catalogo::consultar('evaluaciones_parciales', 'get_evaluaciones_aceptadas', $parametros);
                $evaluaciones_pendientes = catalogo::consultar('evaluaciones_parciales', 'get_evaluaciones_pendientes', $parametros);
                $evaluaciones = array_merge($evaluaciones_aceptadas, $evaluaciones_pendientes);
                return $evaluaciones;
            }
        }
        return null;
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
    
}
?>