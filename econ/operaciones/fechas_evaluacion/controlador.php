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
    
    function get_materias_cincuentenario()
    {
        $parametros = array('legajo' => null);
        $perfil = kernel::persona()->perfil()->get_id();
        if ($perfil == 'DOC')
        {
            $parametros = array('legajo'=> kernel::persona()->get_legajo_docente());
        }
        //die;
        $materias = catalogo::consultar('cursos', 'get_materias_cincuentenario', $parametros);
        $operacion = kernel::ruteador()->get_id_operacion();
        foreach($materias as $key => $materia)
        {
            $materias[$key]['LINK'] = kernel::vinculador()->crear($operacion, 'info_materia', array('parametro'=>1));
        }
        return $materias;
    }
    
    function get_comisiones($anio_academico_hash, $periodo_hash=null, $materia)
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
                $parametros = array(
                                'anio_academico' => $anio_academico,
                                'periodo' => $periodo,
                                'materia' => $materia
                    );
                return catalogo::consultar('cursos', 'get_comisiones_de_materia', $parametros);
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

    /** Graba sólo la comisión */
    function accion__grabar_comision()
    {
        $parametros = $this->get_parametros_grabar_comision();
      
        $mensaje = '';
        if (kernel::request()->isPost()) 
        {
            try 
            {
                catalogo::consultar('cursos', 'set_porcentajes_instancias', $parametros);
                $comision_nombre = catalogo::consultar('cursos', 'get_nombre_comision', $parametros);
                $materia_nombre = catalogo::consultar('cursos', 'get_nombre_materia_de_comision', $parametros);
                $mensaje = 'Se actualizó correctamente los % de notas para el curso '.$comision_nombre.' de la materia '.$materia_nombre.'.';
                $this->set_anio_academico($parametros['anio_academico_hash']);
                $this->set_periodo($parametros['periodo_hash']);
                $this->set_mensaje($mensaje);
            }
            catch (\Exception $e) 
            {
                $mensaje = 'Error en la actualización.';
                $this->set_anio_academico($parametros['anio_academico_hash']);
                $this->set_periodo($parametros['periodo_hash']);
                $this->set_mensaje($e->getMessage().' - '.$mensaje);
            }
        }
        
    }
    
    function get_parametros_grabar_comision()
    {
        $parametros = array();
        $parametros['comision'] = $this->validate_param('comision', 'post', validador::TIPO_TEXTO);
        
        $parciales = 'porc_parciales_'.$parametros['comision'];
        $integrador = 'porc_integrador_'.$parametros['comision'];
        $trabajos = 'porc_trabajos_'.$parametros['comision'];
        
        $parametros['porc_parciales']       = $this->validate_param($parciales, 'post', validador::TIPO_TEXTO);
        $parametros['porc_integrador']      = $this->validate_param($integrador, 'post', validador::TIPO_TEXTO);
        $parametros['porc_trabajos']        = $this->validate_param($trabajos, 'post', validador::TIPO_TEXTO);
        $parametros['anio_academico_hash']  = $this->validate_param('anio_academico_hash', 'post', validador::TIPO_TEXTO);
        $parametros['periodo_hash']         = $this->validate_param('periodo_hash', 'post', validador::TIPO_TEXTO); 

        return $parametros;        
    }

    /** Graba todas las comisiones promocionables de la materia */
    function accion__grabar_materia()
    {
        $parametros = $this->get_parametros_grabar_materia();
        
        if (kernel::request()->isPost()) 
        {
            $comisiones = $this->get_comisiones_promo($parametros['anio_academico_hash'], $parametros['periodo_hash'], $parametros['materia']);
            $result = 1;
            foreach($comisiones AS $comision)
            {
                $parametros['comision'] = $comision['COMISION'];
                $r = catalogo::consultar('cursos', 'set_porcentajes_instancias', $parametros);
                $result = $result * $r;
            }
            if ($result)
            {
                $materia_nombre = catalogo::consultar('cursos', 'get_nombre_materia', $parametros);
                $mensaje = 'Se actualizó correctamente los % de notas para todos los cursos de la materia '.$materia_nombre.'.';
            }
            else
            {
                $mensaje = 'Error en la actualización.';
            }
            
            $this->set_anio_academico($parametros['anio_academico_hash']);
            $this->set_periodo($parametros['periodo_hash']);
            $this->set_mensaje($mensaje);
        }        
    }
    
    function get_parametros_grabar_materia()
    {
        $parametros = array();
        $parametros['materia'] = $this->validate_param('materia', 'post', validador::TIPO_TEXTO);
        
        $parciales = 'porc_parciales_'.$parametros['materia'];
        $integrador = 'porc_integrador_'.$parametros['materia'];
        $trabajos = 'porc_trabajos_'.$parametros['materia'];
        
        $parametros['porc_parciales']       = $this->validate_param($parciales, 'post', validador::TIPO_TEXTO);
        $parametros['porc_integrador']      = $this->validate_param($integrador, 'post', validador::TIPO_TEXTO);
        $parametros['porc_trabajos']        = $this->validate_param($trabajos, 'post', validador::TIPO_TEXTO);
        $parametros['anio_academico_hash']  = $this->validate_param('anio_academico_hash', 'post', validador::TIPO_TEXTO);
        $parametros['periodo_hash']         = $this->validate_param('periodo_hash', 'post', validador::TIPO_TEXTO); 

        return $parametros;        
    }

        
//    function get_materia($anio_academico_hash = null, $periodo_hash = null, $materia_hash = null)
//    {
//        $materia_actual = null;
//        
//        $materias = $this->get_materias_en_comision($anio_academico_hash, $periodo_hash);
//        foreach($materias as $materia){
//            if ($materia['__ID__'] == $materia_hash)
//            {
//                $materia_actual = $materia;
//            }
//        }
//        return $materia_actual;
//    }        
        
        
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