<?php
namespace econ\operaciones\recalcular_calidad_inscr;

use kernel\kernel;
use siu\extension_kernel\controlador_g3w2;
use siu\modelo\datos\catalogo;
use kernel\util\validador;


class controlador extends controlador_g3w2
{
    protected $datos_filtro = array('anio_academico'=>"", 'periodo'=>"", 'calidad'=> "", 'mensaje'=>"", 'mensaje_error'=>"");
    
    function modelo()
    {
    }

	function accion__index()
    {
        if (!empty($_GET['formulario_filtro'])) {
            $this->datos_filtro = $_GET['formulario_filtro'];
        }
	}
	
	function get_ultima_fecha_fin_turno_examen_regular($anio_academico_hash, $periodo_hash=null)
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
					$fecha_limite = catalogo::consultar('carga_evaluaciones_parciales', 'get_ultima_fecha_fin_turno_examen_regular', $parametros);
					return $fecha_limite['FECHA'];
 				}
            }
        }
        return false;
	}
    
    function get_alumnos_calidad($anio_academico_hash, $periodo_hash, $calidad)
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
								'periodo' => $periodo,
								'calidad' => $calidad
    	                );
                    $datos = catalogo::consultar('insc_cursadas', 'get_alumnos_calidad_inscripcion', $parametros);
                    $fecha = catalogo::consultar('carga_evaluaciones_parciales', 'get_ultima_fecha_fin_turno_examen_regular', $parametros);
                  
                    if (isset($fecha['FECHA']))
                    {
                        $hoy = date("Y-m-d");
                        $fin_turno = date("Y-m-d", strtotime($fecha['FECHA']));
                        
                        if ($hoy > $fin_turno)
                        {
                            $cant = count($datos);
                            for ($i=0; $i<$cant; $i++)
                            {
                                if ($datos[$i]['CALIDAD_INSC'] == 'R') {
                                    $parametros['legajo'] = $datos[$i]['LEGAJO'];
                                    $parametros['carrera'] = $datos[$i]['CARRERA'];
                                    $parametros['materia'] = $datos[$i]['MATERIA'];
                                    $correlativ_cumpl = catalogo::consultar('carga_evaluaciones_parciales', 'tiene_correlativas_cumplidas', $parametros);
                                    kernel::log()->add_debug('$correlativ_cumpl', $correlativ_cumpl);
                                    ($correlativ_cumpl) ? $datos[$i]['CALIDAD_ASIGNAR'] = 'P' : $datos[$i]['CALIDAD_ASIGNAR'] = 'R';
                                }
                            }
                        }
                    }
					return $datos;
 				}
            }
        }
        return false;
    }

    function get_periodo()
    {
        return $this->datos_filtro['periodo'];
    }

	function get_calidad()
    {
        return $this->datos_filtro['calidad'];
	}
	
    function get_anio_academico()
    {
        return $this->datos_filtro['anio_academico'];
    }

    function get_mensaje()
    {
        return $this->datos_filtro['mensaje'];
	}
	
	function get_mensaje_error()
    {
        return $this->datos_filtro['mensaje_error'];
    }
    
    function set_periodo($periodo)
    {
        $this->datos_filtro['periodo'] = $periodo;
    }

    function set_anio_academico($anio_academico)
    {
        $this->datos_filtro['anio_academico'] = $anio_academico;
	}
	
	function set_calidad($calidad)
    {
        $this->datos_filtro['calidad'] = $calidad;
    }

    function set_mensaje($mensaje)
    {
        $this->datos_filtro['mensaje'] = $mensaje;
	}
	
	function set_mensaje_error($mensaje)
    {
        $this->datos_filtro['mensaje_error'] = $mensaje;
    }
    
	function accion__grabar()
	{
		if (kernel::request()->isPost()) 
		{
            $datos = $this->validate_param('datos', 'post', validador::TIPO_TEXTO); 
			foreach ($datos AS $carrera=>$dato)
			{
				$parametros['carrera'] = $carrera;
				foreach($dato AS $legajo=>$com)
				{
					$parametros['legajo'] = $legajo;
					foreach($com AS $comision=>$calidad)
					{
                        foreach($calidad AS $calidad_insc=>$calidad_asignar)
                        {
                            if ($calidad_insc != $calidad_asignar)
                            {
                                $parametros['comision'] = $comision;
                                $parametros['calidad'] = $calidad_asignar;
                            	catalogo::consultar('insc_cursadas', 'update_calidad_insc_cursada', $parametros);
                            }
                        }
					}
				}
			}
			$anio_academico_hash = $this->validate_param('anio_academico_hash', 'post', validador::TIPO_TEXTO); 
			$periodo_hash = $this->validate_param('periodo_hash', 'post', validador::TIPO_TEXTO); 
			$calidad = $this->validate_param('calidad', 'post', validador::TIPO_TEXTO); 
			$this->set_anio_academico($anio_academico_hash);
			$this->set_periodo($periodo_hash);
			$this->set_calidad($calidad);
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
                        $this->form_builder = kernel::localizador()->instanciar('operaciones\recalcular_calidad_inscr\filtro\builder_form_filtro');
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
