<?php
namespace econ\operaciones\asignacion_coord_materias;

use kernel\kernel;
use siu\extension_kernel\controlador_g3w2;
use siu\modelo\datos\catalogo;
use kernel\util\validador;


class controlador extends controlador_g3w2
{
    protected $datos_filtro = array('anio_academico'=>"", 'periodo'=>"");
    
    function modelo()
    {
    }

    function get_clase_vista()
    {
        switch ($this->accion) {
            case 'materia': 
                    return 'vista_materia';
            default: 
                    return 'vista';
        }
    }

    function accion__index()
    {
        if (!empty($_GET['formulario_filtro'])) {
            $this->datos_filtro = $_GET['formulario_filtro'];
        }
    }
     
    
    function get_materias_en_comision($anio_academico_hash, $periodo_hash=null)
    {
        $periodo = null;
        $anio_academico = null;
        $operacion = kernel::ruteador()->get_id_operacion();

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
                                'nro_inscripcion' =>  kernel::persona()->get_nro_inscripcion(),
                                'anio_academico' => $anio_academico,
                                'periodo' => $periodo
                    );
                $materias = catalogo::consultar('coord_materia', 'get_materias_en_comisiones', $parametros);

                foreach($materias as $key => $materia)
                {
                    $materias[$key]['LINK'] = kernel::vinculador()->crear($operacion, 'materia', array($materia['__ID__'], $anio_academico_hash, $periodo_hash));
                }
                return $materias;
            }
        }
        return false;
    }

    function get_periodo()
    {
        return $this->datos_filtro['periodo'];
    }

    function get_anio_academico()
    {
        return $this->datos_filtro['anio_academico'];
    }

    function set_periodo($periodo)
    {
        $this->datos_filtro['periodo'] = $periodo;
    }

    function set_anio_academico($anio_academico)
    {
        $this->datos_filtro['anio_academico'] = $anio_academico;
    }
    
    function accion__materia()
    {
        $materia_hash = $this->validate_param(0, 'get', validador::TIPO_ALPHANUM);
        $anio_academico_hash = $this->validate_param(1, 'get', validador::TIPO_ALPHANUM);
        $periodo_hash = $this->validate_param(2, 'get', validador::TIPO_ALPHANUM, array('allowempty' => true));

        $encabezado = $this->get_materia($anio_academico_hash, $periodo_hash, $materia_hash);
        $encabezado['anio_academico_hash'] = $anio_academico_hash;
        $encabezado['periodo_hash'] = $periodo_hash;
        $docentes = array();
        $coordinador = '';
        
        if (!empty($encabezado['MATERIA']))
        {
            $anio_academico = $this->decodificar_anio_academico($anio_academico_hash);
            $docentes = catalogo::consultar('coord_materia', 'get_docentes_de_materia', array('materia' => $encabezado['MATERIA'], 'anio_academico' => $anio_academico, 'periodo' => $encabezado['PERIODO']));
            $coordinador = catalogo::consultar('coord_materia', 'get_coordinador', array('materia' => $encabezado['MATERIA'], 'anio_academico' => $anio_academico, 'periodo' => $encabezado['PERIODO']));
        }
        
        $this->vista()->pagelet("contenido")->data['encabezado'] = $encabezado;
        $this->vista()->pagelet("contenido")->data['docentes'] = $docentes;
        $this->vista()->pagelet("contenido")->data['coordinador'] = $coordinador;
    }

    function accion__grabar()
    {
        if (kernel::request()->isPost()) {
            
            $parametros = $this->get_parametros_grabar();
            catalogo::consultar('coord_materia', 'set_coordinador', $parametros);
            $this->set_anio_academico($parametros['anio_academico_hash']);
            $this->set_periodo($parametros['periodo_hash']);
        }        
    }
    
    function get_parametros_grabar()
    {
        $parametros = array();
        
        $parametros['materia']          = $this->validate_param('materia', 'post', validador::TIPO_TEXTO);      
        $parametros['anio_academico']   = $this->validate_param('anio_academico', 'post', validador::TIPO_TEXTO);
        $parametros['periodo']          = $this->validate_param('periodo', 'post', validador::TIPO_TEXTO);  
        $parametros['coordinador']      = $this->validate_param('coord', 'post', validador::TIPO_TEXTO);
        $parametros['anio_academico_hash']   = $this->validate_param('anio_academico_hash', 'post', validador::TIPO_TEXTO);
        $parametros['periodo_hash']          = $this->validate_param('periodo_hash', 'post', validador::TIPO_TEXTO);  
        
        return $parametros;        
    }

        
    function get_materia($anio_academico_hash = null, $periodo_hash = null, $materia_hash = null)
    {
        $materia_actual = null;
        
        $materias = $this->get_materias_en_comision($anio_academico_hash, $periodo_hash);
        foreach($materias as $materia){
            if ($materia['__ID__'] == $materia_hash)
            {
                $materia_actual = $materia;
            }
        }
        return $materia_actual;
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
                        $this->form_builder = kernel::localizador()->instanciar('operaciones\asignacion_coord_materias\filtro\builder_form_filtro');
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
}
?>