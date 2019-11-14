<?php
namespace econ\operaciones\ponderacion_notas;

use kernel\kernel;
use siu\extension_kernel\controlador_g3w2;
use siu\modelo\datos\catalogo;
use kernel\util\validador;
use econ\guarani;
use siu\errores\error_guarani;


class controlador extends controlador_g3w2
{
    protected $datos_filtro = array('anio_academico'=>"", 'periodo'=>"", 'mensaje'=>"", 'mensaje_error'=>'');
    
    function modelo()
    {
		return guarani::ponderacion_notas();
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

    function set_mensaje($mensaje)
    {
        $this->datos_filtro['mensaje'] = $mensaje;
    }

    function set_mensaje_error($mensaje)
    {
        $this->datos_filtro['mensaje_error'] = $mensaje;
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
                        $this->form_builder = kernel::localizador()->instanciar('operaciones\ponderacion_notas\filtro\builder_form_filtro');
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

	function accion__buscar_periodos() 
    {
            $anio_academico_hash = $this->validate_param('anio_academico', 'get', validador::TIPO_TEXTO);
            $anio_academico = $this->decodificar_anio_academico($anio_academico_hash);
            $datos = array();
            if (!is_null($anio_academico)) {
				$datos = catalogo::consultar('unidad_academica', 'buscar_periodos_lectivos', array('anio' => $anio_academico));
            }
            $this->render_raw_json($datos);
	}
	
	/** Graba las ponderaciones asignadas a la materia */
    function get_materias_cincuentenario()
    {
        $parametros['legajo'] = null;
        $parametros['carrera'] = null;
        $parametros['mix'] = null;
        
        $perfil = kernel::persona()->perfil()->get_id();
        if ($perfil == 'COORD')
        {
            $parametros['legajo'] = kernel::persona()->get_legajo_docente();
        }
        $materias = catalogo::consultar('cursos', 'get_materias_cincuentenario', $parametros);
        $operacion = kernel::ruteador()->get_id_operacion(); 
        foreach($materias as $key => $materia) 
        {
            $materias[$key]['LINK'] = kernel::vinculador()->crear($operacion, 'info_materia', array('parametro'=>1));
        }
        return $materias;
    }

	function get_ponderaciones_notas($anio_academico_hash, $periodo_hash, $materia)
	{
		$periodo = null;
        $anio_academico = null;

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
                                'materia' => $materia
							);
					return $this->modelo()->info__get_ponderaciones_notas($parametros);
				}
            }
        }
        return null;
	}

	function accion__grabar_materia()
    {
        if (kernel::request()->isPost()) 
        {
			try 
			{
				$mensaje = '';
				$mensaje_error = '';
				$tiene_tp = false;
				$parametros = $this->get_parametros_grabar_ponderaciones();

				$parametros_con_integrador = $this->get_parametros_grabar_con_integrador($parametros);
				if (isset($parametros_con_integrador)) 
				{
					$result = catalogo::consultar('ponderacion_notas', 'set_ponderaciones_notas', $parametros_con_integrador);
					$tiene_tp = ($parametros_con_integrador['porc_trabajos'] > 0);

					if ($result) {
						$mensaje .= 'Se actualizó correctamente los % de notas (CON integrador). ';
					} else {
						$mensaje_error .= 'ERROR en la asignación de ponderaciones para el caso CON integrador. ';
					}
				}

				$parametros_sin_integrador = $this->get_parametros_grabar_sin_integrador($parametros);
				if (isset($parametros_sin_integrador)) 
				{
					$result = catalogo::consultar('ponderacion_notas', 'set_ponderaciones_notas', $parametros_sin_integrador);
					$tiene_tp = $tiene_tp || ($parametros_sin_integrador['porc_trabajos'] > 0);

					if ($result) {
						$mensaje .= 'Se actualizó correctamente los % de notas (SIN integrador). ';
					} else {
						$mensaje_error .= 'ERROR en la asignación de ponderaciones para el caso SIN integrador. ';
					}
				}
				
				$this->set_mensaje($mensaje);
				$this->set_mensaje_error($mensaje_error);

				if ($tiene_tp) {
					$this->crear_instancia_evaluacion_tp($parametros);
				} else {
					$this->borrar_instancia_evaluacion_tp($parametros);
				}
			
			}
			catch (error_guarani $e)
			{
				$msj = $e->getMessage();
				$this->set_mensaje_error($msj);
			}
		}        
		$this->set_anio_academico($parametros['anio_academico_hash']);
		$this->set_periodo($parametros['periodo_hash']);
    }
    
	private function get_parametros_grabar_ponderaciones()
	{
        $parametros = array();
        $parametros['materia'] 			= $this->validate_param('materia', 'post', validador::TIPO_TEXTO);

		$parametros['anio_academico_hash'] 	= $this->validate_param('anio_academico_hash', 'post', validador::TIPO_TEXTO);
		$parametros['periodo_hash'] 		= $this->validate_param('periodo_hash', 'post', validador::TIPO_TEXTO); 
		$parametros['anio_academico'] 		= $this->decodificar_anio_academico($parametros['anio_academico_hash']);
		$parametros['periodo']				= $this->decodificar_periodo($parametros['periodo_hash'], $parametros['anio_academico']);

        return $parametros;        
	}

	private function get_parametros_grabar_con_integrador($parametros)
    {
		$parciales = 'porc_parciales_P_'.$parametros['materia'];
        $integrador = 'porc_integrador_P_'.$parametros['materia'];
        $trabajos = 'porc_trabajos_P_'.$parametros['materia'];

        $parametros['porc_parciales']       = $this->validate_param($parciales, 'post', validador::TIPO_TEXTO);
        $parametros['porc_integrador']      = $this->validate_param($integrador, 'post', validador::TIPO_TEXTO);
        $parametros['porc_trabajos']        = $this->validate_param($trabajos, 'post', validador::TIPO_TEXTO);
		$parametros['calidad']				= 'P';

		if (empty($parametros['porc_parciales']) && empty($parametros['porc_integrador']) && empty($parametros['porc_trabajos'])) {
			return null;
		}

		if (empty($parametros['porc_parciales']) || empty($parametros['porc_integrador'])) {
			throw new error_guarani('Falta cargar algna ponderación para la materia '.$parametros['materia']);
		}

        return $parametros;        
    }

    private function get_parametros_grabar_sin_integrador($parametros)
    {
		$parciales = 'porc_parciales_R_'.$parametros['materia'];
        $trabajos = 'porc_trabajos_R_'.$parametros['materia'];
        
		$parametros['porc_parciales']       = $this->validate_param($parciales, 'post', validador::TIPO_TEXTO);
		$parametros['porc_integrador']		= null;
        $parametros['porc_trabajos']        = $this->validate_param($trabajos, 'post', validador::TIPO_TEXTO);
		$parametros['calidad']				= 'R';

		if (empty($parametros['porc_parciales'])) {
			return null;
		}
		return $parametros;        
    }
        
    
	private function crear_instancia_evaluacion_tp($parametros)
	{
		try 
        {
			kernel::db()->abrir_transaccion(); 
			$comisiones = catalogo::consultar('fechas_parciales', 'get_comisiones_de_materia_con_dias_de_clase', $parametros);
			foreach($comisiones as $comision)
			{
				$datos['comision'] = $comision['COMISION'];
				$datos['evaluacion'] = 15;	//TP
				$datos['fecha_hora'] = date("Y-m-d H:i:s"); 
				catalogo::consultar('cursos', 'alta_evaluacion_parcial', $datos);
			}
			kernel::db()->cerrar_transaccion();
		}
		catch (error_guarani $e)
        {
			kernel::db()->abortar_transaccion($e->getMessage());
            $this->set_mensaje_error($e->getMessage());
		}
	}

	private function borrar_instancia_evaluacion_tp($parametros)
	{
		try 
        {
			kernel::db()->abrir_transaccion(); 
			$comisiones = catalogo::consultar('fechas_parciales', 'get_comisiones_de_materia_con_dias_de_clase', $parametros);
			foreach($comisiones as $comision)
			{
				$datos['comision'] = $comision['COMISION'];
				$datos['evaluacion'] = 15;	//TP
				catalogo::consultar('cursos', 'baja_evaluacion_parcial', $datos);
			}
			kernel::db()->cerrar_transaccion();
		}
		catch (error_guarani $e)
        {
			kernel::db()->abortar_transaccion($e->getMessage());
            //$this->set_mensaje_error($e->getMessage());
		}
	}

	/* Verifica si el día de hoy está dentro del cuatrimestre que se consulta, para de acuerdo a eso habilitar la edición o no */
	function hoy_dentro_de_periodo($anio_academico_hash, $periodo_hash)
	{
		$parametros['anio'] = $this->decodificar_anio_academico($anio_academico_hash);
		$parametros['periodo'] = $this->decodificar_periodo($periodo_hash, $parametros['anio']); 
		return catalogo::consultar('fechas_parciales', 'hoy_dentro_de_periodo', $parametros);
	}
    
}
?>
