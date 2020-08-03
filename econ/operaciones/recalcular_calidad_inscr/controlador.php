<?php
namespace econ\operaciones\recalcular_calidad_inscr;

use kernel\kernel;
use siu\extension_kernel\controlador_g3w2;
use siu\modelo\datos\catalogo;
use kernel\util\validador;

class controlador extends controlador_g3w2
{
    protected $datos_filtro = array('anio_academico'=>"", 'periodo'=>"", 'calidad'=> "", 'materia'=> "", 'mensaje'=>"", 'mensaje_error'=>"");
    
    function modelo()
    {
    }

	function accion__index()
    {
        if (!empty($_GET['formulario_filtro'])) {
            $this->datos_filtro = $_GET['formulario_filtro'];
        }
	}
	
	function get_fecha_ctr_correlativas($anio_academico_hash, $periodo_hash=null)
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
					$fecha_limite = catalogo::consultar('generales', 'get_fecha_ctr_correlativas', $parametros);
					return $fecha_limite['FECHA'];
 				}
            }
        }
        return false;
	}
    
    function get_alumnos_calidad($anio_academico_hash, $periodo_hash, $calidad, $materia)
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
                                'calidad' => $calidad,
                                'materia' => null
                        );
                    if (!empty($materia)) {
                        $parametros['materia'] = $materia;
                    }
                    $datos = catalogo::consultar('insc_cursadas', 'get_alumnos_calidad_inscripcion', $parametros);
                    $fecha = $this->get_fecha_ctr_correlativas($anio_academico_hash, $periodo_hash);
                    // kernel::log()->add_debug('fecha_ctr_correlativas: ', $fecha);
                    
                    if (isset($fecha))
                    {
                        $parametros['fecha'] = date("d-m-Y", strtotime($fecha));
                        $hoy = date("Y-m-d");
                        $fecha_ctr_correlativas = date("Y-m-d", strtotime($fecha));
                        
                        if ($hoy >= $fecha_ctr_correlativas)
                        {
                            $resultado = array();
                            $cant = count($datos);
                            for ($i=0; $i<$cant; $i++)
                            {
                                if ($datos[$i]['CALIDAD_INSC'] == 'R') {
                                    
                                    $dato = $this->verificar_posible_cambio_R_a_P($datos[$i], $parametros);
                                    $resultado[] = $dato;
                                }
                                if ($datos[$i]['CALIDAD_INSC'] == 'P') {
                                    
                                    $dato = $this->verificar_posible_cambio_P_a_R($datos[$i], $parametros);
                                    $resultado[] = $dato;
                                }
                            }
                            return $resultado;
                        }
                    }
					return $datos;
 				}
            }
        }
        return null;
    }

    function verificar_posible_cambio_R_a_P($alumno, $parametros)
    {
        $alumno['OBSERV'] = '';
        $alumno['MODIFICABLE'] = 0;

        $parametros['legajo'] = $alumno['LEGAJO'];
        $parametros['carrera'] = $alumno['CARRERA'];
        $parametros['materia'] = $alumno['MATERIA'];
        $parametros['comision'] = $alumno['COMISION'];

        $correlativ_cumpl = catalogo::consultar('carga_evaluaciones_parciales', 'tiene_correlativas_cumplidas', $parametros);
          
        if ($correlativ_cumpl[0] != 1) {
            $alumno['CALIDAD_ASIGNAR'] = 'R';
            $alumno['OBSERV'] .= substr($correlativ_cumpl[1], strpos($correlativ_cumpl[1], ',')+1) . '. ';
            $alumno['MODIFICABLE'] = 0;
            return $alumno;
        }
        else
        {
            $alumno['CALIDAD_ASIGNAR'] = 'P';
            $alumno['OBSERV'] .= 'Tiene Correlat. ';
            $alumno['MODIFICABLE'] = 1;

            //Verifica si el acta PROMO de la comisión está cerrada
            $acta_promo = catalogo::consultar('insc_cursadas', 'estado_acta_promo', $parametros);
            if (isset($acta_promo['ESTADO']) && $acta_promo['ESTADO'] == 'C')
            {
                $alumno['OBSERV'] .= utf8_decode('El acta PROMO de la comisión está cerrada. ');
                $alumno['MODIFICABLE'] = 0;
                return $alumno;
            }
            else
            {
                //Si no está cerrada el acta PROMO, verifica si tiene nota cargada en acta abierta
                $curs_pte = catalogo::consultar('insc_cursadas', 'existe_en_cursada_pdte', $parametros);
                //kernel::log()->add_debug('get_alumnos_calidad $curs_pte: ', $curs_pte);
                if (isset($curs_pte['ACTA_PROMOCION'])) {
                    $alumno['OBSERV'] .= 'Tiene cargada nota en acta PROMO abierta ('.$curs_pte['ACTA_PROMOCION'].'). ';
                    $alumno['MODIFICABLE'] = 2;
                }
                if (isset($curs_pte['ACTA_REGULAR'])) {
                    $alumno['OBSERV'] .= 'Tiene cargada nota en acta REGULAR abierta ('.$curs_pte['ACTA_REGULAR'].'). ';
                    $alumno['MODIFICABLE'] = 2;
                }
            }
        }
        return $alumno;
    }

    function verificar_posible_cambio_P_a_R($alumno, $parametros)
    {
        $alumno['OBSERV'] = '';
        $alumno['MODIFICABLE'] = 0;

        $parametros['legajo'] = $alumno['LEGAJO'];
        $parametros['carrera'] = $alumno['CARRERA'];
        $parametros['materia'] = $alumno['MATERIA'];
        $parametros['comision'] = $alumno['COMISION'];

        $correlativ_cumpl = catalogo::consultar('carga_evaluaciones_parciales', 'tiene_correlativas_cumplidas', $parametros);
          
        if ($correlativ_cumpl[0] == 1) {
            $alumno['CALIDAD_ASIGNAR'] = 'P';
            $alumno['OBSERV'] .= 'Tiene Correlat. ';
            $alumno['MODIFICABLE'] = 0;
            return $alumno;
        }
        else
        {
            $alumno['CALIDAD_ASIGNAR'] = 'R';
            $alumno['OBSERV'] .= substr($correlativ_cumpl[1], strpos($correlativ_cumpl[1], ',')+1) . '. ';
            $alumno['MODIFICABLE'] = 1;

            //Verifica si el acta PROMO de la comisión está cerrada
            $acta_promo = catalogo::consultar('insc_cursadas', 'estado_acta_promo', $parametros);
            if (isset($acta_promo['ESTADO']) && $acta_promo['ESTADO'] == 'C')
            {
                $alumno['OBSERV'] .= utf8_decode('El acta PROMO de la comisión está cerrada. ');
                $alumno['MODIFICABLE'] = 0;
                return $alumno;
            }
            else
            {
                //Si no está cerrada el acta PROMO, verifica si tiene nota cargada en acta abierta
                $curs_pte = catalogo::consultar('insc_cursadas', 'existe_en_cursada_pdte', $parametros);
                //kernel::log()->add_debug('get_alumnos_calidad $curs_pte: ', $curs_pte);
                if (isset($curs_pte['ACTA_PROMOCION'])) {
                    $alumno['OBSERV'] .= 'Tiene cargada nota en acta PROMO abierta ('.$curs_pte['ACTA_PROMOCION'].'). ';
                    $alumno['MODIFICABLE'] = 2;
                }
                if (isset($curs_pte['ACTA_REGULAR'])) {
                    $alumno['OBSERV'] .= 'Tiene cargada nota en acta REGULAR abierta ('.$curs_pte['ACTA_REGULAR'].'). ';
                    $alumno['MODIFICABLE'] = 2;
                }
            }
        }
        return $alumno;
    }

    
	function accion__grabar()
	{
        $parametros['legajo'] = $this->validate_param('legajo', 'get', validador::TIPO_TEXTO); 
        $parametros['carrera'] = $this->validate_param('carrera', 'get', validador::TIPO_TEXTO); 
        $parametros['materia'] = $this->validate_param('materia', 'get', validador::TIPO_TEXTO); 
        $parametros['comision'] = $this->validate_param('comision', 'get', validador::TIPO_TEXTO); 
        $parametros['calidad_asignar'] = trim($this->validate_param('calidad_asignar', 'get', validador::TIPO_TEXTO)); 
        
        $anio_academico_hash = $this->validate_param('anio_academico', 'get', validador::TIPO_TEXTO); 
        $periodo_hash = $this->validate_param('periodo', 'get', validador::TIPO_TEXTO); 
        if (!empty($anio_academico_hash))
        {
            $anio_academico = $this->decodificar_anio_academico($anio_academico_hash);
            if (!empty($anio_academico))
            {
                if (!empty($periodo_hash))
                {
                    $periodo = $this->decodificar_periodo($periodo_hash, $anio_academico);
                    $parametros['anio_academico'] = $anio_academico;
                    $parametros['periodo'] = $periodo;
                
                    $fecha = $this->get_fecha_ctr_correlativas($anio_academico_hash, $periodo_hash);
                    if (isset($fecha))
                    {
                        $hoy = date("Y-m-d");
                        $fin_turno = date("Y-m-d", strtotime($fecha));
                        
                        if ($hoy > $fin_turno)
                        {
                            $alumno = array('LEGAJO' => $parametros['legajo'], 
                                            'CARRERA' => $parametros['carrera'],
                                            'MATERIA' => $parametros['materia'],
                                            'COMISION' => $parametros['comision']);

                            if ($parametros['calidad_asignar'] == 'P') // Cambio de R a P
                            {
                                $resultado = $this->verificar_posible_cambio_R_a_P($alumno, $parametros);
                                //kernel::log()->add_debug('accion__grabar $resultado RaP', $resultado);

                                switch($resultado['MODIFICABLE']) {
                                    case 1: 
                                        catalogo::consultar('insc_cursadas', 'update_calidad_insc_cursada', $parametros);                      
                                        $msj = utf8_decode('Ok. Se cambió la calidad a P para: '.$alumno['LEGAJO']);
                                        break;
                                    case 2: 
                                        catalogo::consultar('insc_cursadas', 'update_calidad_insc_cursada', $parametros);                      
                                        $msj = utf8_decode('Se cambió la calidad a P para: '.$alumno['LEGAJO'].'. Notificar al docente. ');
                                        $msj .= $resultado['OBSERV'];
                                        break;
                                    case 0: 
                                        $msj = utf8_decode('NO se pudo cambiar la calidad de inscripción para: '.$alumno['LEGAJO']);
                                        $msj .= $resultado['OBSERV'];
                                        break;
                                    default: 
                                        $msj = utf8_decode('Error al grabar para: '.$alumno['LEGAJO']);
                                }
                                $mensaje = array('resultado'=>$resultado['MODIFICABLE'], 'mensaje'=>$msj);
                                $this->render_ajax($mensaje);
                            }
                            else //Cambio de P a R
                            {
                                $resultado = $this->verificar_posible_cambio_P_a_R($alumno, $parametros);
                                //kernel::log()->add_debug('accion__grabar $resultado PaR', $resultado);

                                switch($resultado['MODIFICABLE']) {
                                    case 1: 
                                        catalogo::consultar('insc_cursadas', 'update_calidad_insc_cursada', $parametros);                      
                                        $msj = utf8_decode('Ok. Se cambió la calidad a R para: '.$alumno['LEGAJO']);
                                        break;
                                    case 2: 
                                        catalogo::consultar('insc_cursadas', 'update_calidad_insc_cursada', $parametros);                      
                                        $msj = utf8_decode('Se cambió la calidad a R para: '.$alumno['LEGAJO'].'. Notificar al docente. ');
                                        $msj .= $resultado['OBSERV'];
                                        break;
                                    case 0: 
                                        $msj = utf8_decode('No se pudo cambiar la calidad de inscripción para: '.$alumno['LEGAJO']);
                                        $msj .= $resultado['OBSERV'];
                                        break;
                                    default: 
                                        $msj = utf8_decode('Error al grabar para: '.$alumno['LEGAJO']);
                                }
                                $mensaje = array('resultado'=>$resultado['MODIFICABLE'], 'mensaje'=>$msj);
                                $this->render_ajax($mensaje);
                            }
                        }
                    }
                }
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
    

    function accion__buscar_materias() 
    {
        $anio_academico_hash = $this->validate_param('anio_academico', 'get', validador::TIPO_TEXTO);
        $anio_academico = $this->decodificar_anio_academico($anio_academico_hash);
        $periodo_hash = $this->validate_param('periodo', 'get', validador::TIPO_TEXTO);
        $periodo = $this->decodificar_periodo($periodo_hash, $anio_academico);

        $datos = array();
        if (!is_null($anio_academico) && !is_null($periodo))
        {
            $parametros['anio_academico'] = $anio_academico;
            $parametros['periodo_lectivo'] = $periodo;
            $datos = catalogo::consultar('insc_cursadas', 'get_materias_promo_con_comision', $parametros);
        }
        //kernel::log()->add_debug('accion__buscar_materias', $datos);
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

    function get_materia()
    {
        return $this->datos_filtro['materia'];
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
    
    function set_materia($materia)
    {
        $this->datos_filtro['materia'] = $materia;
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
}
?>
