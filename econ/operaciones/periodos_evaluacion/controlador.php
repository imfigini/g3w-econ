<?php
namespace econ\operaciones\periodos_evaluacion;

use kernel\kernel;
use siu\extension_kernel\controlador_g3w2;
use siu\modelo\datos\catalogo;
use kernel\util\validador;
//use siu\modelo\guarani_notificacion;

class controlador extends controlador_g3w2
{
	protected $datos_filtro = Array('anio_academico'=>"", 'periodo'=>"");
	protected $mensaje = "";
    
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
    
    function get_periodo_solicitud_fechas($anio_academico_hash, $periodo_hash)
    {
        if (!empty($anio_academico_hash))
        {
            $anio_academico = $this->decodificar_anio_academico($anio_academico_hash);
            if (!empty($anio_academico))
            {
                if (!empty($periodo_hash))
                {
                    $periodo = $this->decodificar_periodo($periodo_hash, $anio_academico);
                    $parametros = array(
                                    'anio_academico' => $anio_academico,
                                    'periodo' => $periodo
                        );
                    return catalogo::consultar('evaluaciones_parciales', 'get_periodo_solicitud_fecha', $parametros);
                }
            }
        }
        return null;
    }
    
    function get_periodo_lectivo($anio_academico_hash, $periodo_hash)
    {
        if (!empty($anio_academico_hash))
        {
            $anio_academico = $this->decodificar_anio_academico($anio_academico_hash);
            if (!empty($periodo_hash))
            {
                $periodo = $this->decodificar_periodo($periodo_hash, $anio_academico);
            }
            $parametros = array(
                        'anio_academico' => $anio_academico,
                        'periodo' => $periodo
                    );
            return catalogo::consultar('evaluaciones_parciales', 'get_periodo_lectivo', $parametros);
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
            return null;
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
        return $this->mensaje;
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
        $this->mensaje = $mensaje;
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
                    $parametros['orden'] = $periodo['ORDEN'];
                    $daterange = 'daterange_'.$periodo['ORDEN'];
                    $intervalo = $this->validate_param("$daterange", 'post', validador::TIPO_TEXTO);
                    $intervalo = explode('-', $intervalo);
                    $parametros['fecha_inicio'] = $intervalo[0];
					$parametros['fecha_fin'] = $intervalo[1];
                    if ($parametros['fecha_inicio'] != '' && $parametros['fecha_fin'] != '')
                    {
						//Período de examen con suspensión de clases (orden = 4)
						if ($parametros['orden'] == 4)
						{
							//hay que validar los días de clase del período guardado con anteriordad
							$periodo_old = catalogo::consultar('evaluaciones_parciales', 'get_periodo', $parametros);
							if (isset($periodo_old['FECHA_INICIO']) && isset($periodo_old['FECHA_FIN']))
							{
								$param['fecha_inicio'] = $periodo_old['FECHA_INICIO'];
								$param['fecha_fin'] = $periodo_old['FECHA_FIN'];
								$param['valido'] = 'S';
								catalogo::consultar('evaluaciones_parciales', 'set_validez_clases', $param);
							}
							//hay que ivalidar los días de clase para que no compute la asitencia
							$parametros['valido'] = 'N';
							catalogo::consultar('evaluaciones_parciales', 'set_validez_clases', $parametros);
						}

						catalogo::consultar('evaluaciones_parciales', 'set_periodos_evaluacion', $parametros);
                    }
                }
                
                $this->grabar_periodo_solicitud_fechas($parametros['anio_academico'], $parametros['periodo']);
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
    
    function grabar_periodo_solicitud_fechas($anio_academico, $periodo)
    {
        $intervalo = $this->validate_param("daterange_solicitud_fechas", 'post', validador::TIPO_TEXTO);
        $intervalo = explode('-', $intervalo);
        $parametros['fecha_inicio'] = $intervalo[0];
        $parametros['fecha_fin'] = $intervalo[1];
        $parametros['anio_academico'] = $anio_academico;
        $parametros['periodo'] = $periodo;
        if ($parametros['fecha_inicio'] != '' && $parametros['fecha_fin'] != '')
        {
            catalogo::consultar('evaluaciones_parciales', 'set_periodo_solicitud_fecha', $parametros);
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
