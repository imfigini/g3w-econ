<?php
namespace econ\operaciones\fechas_parciales_acept;

use kernel\kernel;
use siu\extension_kernel\controlador_g3w2;
use siu\modelo\datos\catalogo;
use kernel\util\validador;
use econ\guarani;
//use siu\modelo\guarani_notificacion;


class controlador extends controlador_g3w2
{
    protected $datos_filtro = array('anio_academico'=>"", 'periodo'=>"", 'carrera'=>"", 'mix'=>"", 'mensaje'=>'', 'mensaje_error'=>'', 'comision'=>"");
    
    function modelo()
    {
		return guarani::fechas_parciales();
    }

    function accion__index()
    {
        if (!empty($_GET['formulario_filtro'])) {
            $this->datos_filtro = $_GET['formulario_filtro'];
        }
    }
    
    function get_materias_y_comisiones_cincuentenario($anio_academico_hash, $periodo_hash, $carrera, $mix)
    {
		if (empty($anio_academico_hash) || empty($periodo_hash)) {
            return null;
        }
		
		$anio_academico =  $this->decodificar_anio_academico($anio_academico_hash);
        $periodo = $this->decodificar_periodo($periodo_hash, $anio_academico);
        
		$parametros = array('anio_academico' => $anio_academico,
							'periodo' => $periodo);

        $materias = $this->modelo()->get_materias_cincuentenario($carrera, $mix, $anio_academico, $periodo);
        //MATERIA, MATERIA_NOMBRE

		$datos = array();
        $cant = count($materias);
        for ($i=0; $i<$cant; $i++)
        {
			$parametros['materia'] = $materias[$i]['MATERIA'];

            $comisiones = $this->modelo()->get_comisiones_de_materia_con_dias_de_clase($parametros);
            //COMISION, COMISION_NOMBRE, TURNO, CARRERA

            if (count($comisiones) > 0)
            {
                $comisiones = $this->get_datos_comisiones($comisiones);

                $materias[$i]['DIAS'] = $this->get_mismos_dias($comisiones);
				//DIA_SEMANA
				
                $materias[$i]['OBSERVACIONES'] = $this->modelo()->get_evaluaciones_observaciones($parametros);
                //OBSERVACIONES
                
                $materias[$i]['COMISIONES'] = $comisiones;
                $datos[] = $materias[$i];
            }
		}
        return $datos;
	}
	
	private function get_datos_comisiones($comisiones)
	{
		$resultado = Array();
		foreach($comisiones as $comision)
		{
			$dias_clase = $this->modelo()->get_dias_clase($comision);
			$dias_no_validos = $this->modelo()->get_fechas_no_validas_comision($comision['COMISION']);

			$comision['DIAS_CLASE'] = $dias_clase;
			$comision['DIAS_CLASE_JSON'] = json_encode($dias_clase, JSON_FORCE_OBJECT | JSON_PARTIAL_OUTPUT_ON_ERROR);
			$comision['DIAS_NO_VALIDOS_JSON'] = json_encode($dias_no_validos, JSON_FORCE_OBJECT | JSON_PARTIAL_OUTPUT_ON_ERROR);
			$comision['FECHAS_SOLICITADAS'] = $this->modelo()->get_fechas_eval_solicitadas($comision['COMISION']);
			$comision['EVAL_ASIGNADAS'] = $this->modelo()->get_fechas_eval_asignadas($comision['COMISION']);
			$resultado[] = $comision;
		}
		return $resultado;
	}

    function get_periodos_evaluacion($anio_academico_hash, $periodo_hash)
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
                }
                $parametros = array(
                                'anio_academico' => $anio_academico,
                                'periodo' => $periodo
                    );
                return catalogo::consultar('cursos', 'get_periodos_evaluacion', $parametros);
            }
        }
    }

    private function get_mismos_dias($comisiones)
    {
        $cant = count($comisiones);
        if ($cant == 0) {
            return null;
        }
        $mismos_dias = true;
        for ($i=1; $i<$cant; $i++)
        {
            $dias_clase_1 = $comisiones[$i-1]['DIAS_CLASE'];
            $dias_clase_2 = $comisiones[$i]['DIAS_CLASE'];
            if (count($dias_clase_1) != count($dias_clase_2)) {
                $mismos_dias = false;
            }
            else
            {
                $cant_dias = count($dias_clase_1);
                for ($j=0; $j<$cant_dias; $j++)
                {
                    if ($dias_clase_1[$j]['DIA_SEMANA'] != $dias_clase_2[$j]['DIA_SEMANA'])  {
                        $mismos_dias = false;
                    }
                }
            }
        }
        if ($mismos_dias)
        {
            $resultado = array();
            foreach ($comisiones[0]['DIAS_CLASE'] AS $dia)
            {
                $r['DIA_SEMANA'] = $dia['DIA_SEMANA'];
                $r['DIA_NOMBRE'] = $dia['DIA_NOMBRE'];
                $resultado[] = $r;
            }
            return $resultado;  
        }
        else
        {
            return null;
        }              
    }

    function get_dias_no_laborales($anio_academico_hash, $periodo_hash)
    {
        if (!empty($anio_academico_hash))  {
            $anio_academico = $this->decodificar_anio_academico($anio_academico_hash);
            if (!empty($periodo_hash))  {
                $periodo = $this->decodificar_periodo($periodo_hash, $anio_academico);
            }
            $parametros = array(
                        'anio_academico' => $anio_academico,
                        'periodo' => $periodo
                    );
            return catalogo::consultar('evaluaciones_parciales', 'get_dias_no_laborales', $parametros);
        }
        return null;
    }

	function accion__grabar_comision()
    {
		$datos['comision'] = $this->validate_param('comision', 'post', validador::TIPO_ALPHANUM);
		$datos['evaluacion'] = $this->validate_param('evaluacion', 'post', validador::TIPO_ALPHANUM);
		$datos['fecha_hora'] = $this->validate_param('fecha_hora', 'post', validador::TIPO_TEXTO);
		$estado = $this->validate_param('estado', 'post', validador::TIPO_TEXTO);

		if (trim($estado) == 'H') // || strpos($estado, 'H') !== FALSE) {
		{
			$fecha_hora = catalogo::consultar('cursos', 'get_evaluacion_asignada', $datos);
			$fecha = substr($fecha_hora['FECHA_HORA'], 0, 10);
			$hora = trim($datos['fecha_hora']); //Si es un cambio de horario, sólo viene la hora
			$datos['fecha_hora'] = $fecha.' '.$hora;
		}

		kernel::log()->add_debug('accion__grabar_comision', $datos); 
		$mensaje[] = catalogo::consultar('cursos', 'alta_evaluacion_parcial', $datos);
		kernel::log()->add_debug('accion__grabar_comision mensaje', $mensaje); 
		
		$this->render_ajax($mensaje);
	}

	/* Verifica si el día de hoy está dentro del cuatrimestre que se consulta, para de acuerdo a eso habilitar la edición o no */
	function hoy_dentro_de_periodo($anio_academico_hash, $periodo_hash)
	{
		$parametros['anio'] = $this->decodificar_anio_academico($anio_academico_hash);
		$parametros['periodo'] = $this->decodificar_periodo($periodo_hash, $parametros['anio']); 
		return catalogo::consultar('fechas_parciales', 'hoy_dentro_de_periodo', $parametros);
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
    
    function get_comision()
    {
        return $this->datos_filtro['comision'];
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

    function set_carrera($carrera)
    {
        $this->datos_filtro['carrera'] = $carrera;
    }

    function set_mix($mix)
    {
        $this->datos_filtro['mix'] = $mix;
    }
    
    function set_comision($comision)
    {
        $this->datos_filtro['comision'] = $comision;
    }

    function set_mensaje($mensaje)
    {
        $this->datos_filtro['mensaje'] = $mensaje;
    }
    
    function set_mensaje_error($mensaje)
    {
        $this->datos_filtro['mensaje_error'] = $mensaje;
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
                        $this->form_builder = kernel::localizador()->instanciar('operaciones\fechas_parciales_acept\filtro\builder_form_filtro');
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
