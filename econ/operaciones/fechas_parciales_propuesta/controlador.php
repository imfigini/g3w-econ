<?php
namespace econ\operaciones\fechas_parciales_propuesta;

use kernel\kernel;
use siu\extension_kernel\controlador_g3w2;
use siu\modelo\datos\catalogo;
use kernel\util\validador;
use siu\errores\error_guarani;
use kernel\util\mail;
use econ\guarani;

class controlador extends controlador_g3w2
{
    protected $datos_filtro = array('anio_academico'=>"", 'periodo'=>"", 'mensaje'=>'', 'mensaje_error'=>'');
    
    function modelo()
    {
		return guarani::fechas_parciales_propuesta();
    }

    function accion__index()
    {
        if (!empty($_GET['formulario_filtro'])) {
            $this->datos_filtro = $_GET['formulario_filtro'];
        }
    }
    
    function get_materias_y_comisiones_cincuentenario($anio_academico_hash, $periodo_hash)
    {
        if (empty($anio_academico_hash) || empty($periodo_hash))
        {
            return null;
        }
        
        $materias = $this->modelo()->get_materias_cincuentenario();
        //MATERIA, MATERIA_NOMBRE
        $anio_academico =  $this->decodificar_anio_academico($anio_academico_hash);
        $periodo = $this->decodificar_periodo($periodo_hash, $anio_academico);
        
		$parametros = array('anio_academico' => $anio_academico,
							'periodo' => $periodo);

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
				
                $materias[$i]['FECHAS_OCUPADAS'] = $this->modelo()->get_fechas_eval_ocupadas($parametros);
                //EVALUACION, EVAL_NOMBRE, FECHA

				$materias[$i]['FECHAS_NO_VALIDAS'] = $this->modelo()->get_fechas_no_validas($parametros);
				//FECHA
                
                $materias[$i]['OBSERVACIONES'] = $this->modelo()->get_evaluaciones_observaciones($parametros);
                //OBSERVACIONES
                
                $materias[$i]['COMISIONES'] = $comisiones;
                $datos[] = $materias[$i];
            }
		}
        return $datos;
    }
	
	function get_datos_comisiones($comisiones)
	{
		$resultado = Array();
		foreach($comisiones as $comision)
		{
			$comision['DIAS_CLASE'] = $this->modelo()->get_dias_clase($comision);
			$comision['FECHAS_EVAL'] = $this->modelo()->get_fechas_asignadas_o_solicitadas($comision);
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

                    $parametros = array(
                                'anio_academico' => $anio_academico,
                                'periodo' => $periodo
                        );
                    return catalogo::consultar('cursos', 'get_periodos_evaluacion', $parametros);
                }
            }
        }
    }
    
    function get_periodo_solicitud_fechas($anio_academico_hash, $periodo_hash)
    {
        $periodo = null;
        $anio_academico = null;

        if (!empty($anio_academico_hash))
        {
            $anio_academico = $this->decodificar_anio_academico($anio_academico_hash);
            if (!empty($anio_academico) && !empty($periodo_hash))
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
   
    function get_mismos_dias($comisiones)
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
                    if ($dias_clase_1[$j]['DIA_SEMANA'] != $dias_clase_2[$j]['DIA_SEMANA']) {
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
                $resultado[] = $r;
            }
            return $resultado;  
		} 
		else {
            return null;
        }              
    }

    function get_dias_no_laborales($anio_academico_hash, $periodo_hash)
    {
        if (!empty($anio_academico_hash))
        {
            $anio_academico = $this->decodificar_anio_academico($anio_academico_hash);
            if (!empty($periodo_hash)) {
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

    function get_periodo()
    {
        return $this->datos_filtro['periodo'];
    }

    function get_anio_academico()
    {
        return $this->datos_filtro['anio_academico'];
    }

	function get_anio_seleccionado()
    {
        if (!empty($this->get_anio_academico())) {
			return $this->decodificar_anio_academico($this->get_anio_academico());
		}
		return null;
	}
	
    function get_mensaje()
    {
        if (!isset($this->datos_filtro['mensaje'])) {
            return '';
        }
        return $this->datos_filtro['mensaje'];
    }
    
    function get_mensaje_error()
    {
        if (!isset($this->datos_filtro['mensaje_error'])) {
            return '';
        }
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

    function add_mensaje($mensaje)
    {
        $this->datos_filtro['mensaje'] .= $mensaje;
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
                        $this->form_builder = kernel::localizador()->instanciar('operaciones\fechas_parciales_propuesta\filtro\builder_form_filtro');
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
    
    /** Graba todas las comisiones de la materia */
    function accion__grabar_materia()
    {
        $datos = $this->get_parametros_grabar_materia();
        $resultado = '';
        if (kernel::request()->isPost()) 
        {
            $parametros = array('anio_academico' => $datos['anio_academico'],
                                'periodo' => $datos['periodo'],
                                'materia' => $datos['materia']);
								
			$comisiones = $this->modelo()->get_comisiones_de_materia_con_dias_de_clase($parametros);
			
            foreach ($comisiones as $comision)
            {
                $comision = $comision['COMISION'];
				$dias_clase = $this->modelo()->get_dias_clase(Array('comision'=>$comision));
			
                $resultado = '';
				if (!empty($datos['fecha_promo1'])) {
					$resultado .= $this->grabar_instancia($comision, $datos, $dias_clase, 'promo1');
				}
				if (!empty($datos['fecha_promo2'])) {
					$resultado .= $this->grabar_instancia($comision, $datos, $dias_clase, 'promo2');
				}
				if (!empty($datos['fecha_integ'])) {
					$resultado .= $this->grabar_instancia($comision, $datos, $dias_clase, 'integ');
				}
            }

            $resultado_obs = '';
            //Observaciones
            if (!empty($datos['observaciones']))
            {
                $param['materia'] = $datos['materia'];
                $param['anio_academico'] = $datos['anio_academico'];
                $param['periodo_lectivo'] = $datos['periodo'];
                $param['observaciones'] = $datos['observaciones'];
				$resultado_obs = catalogo::consultar('cursos', 'set_evaluaciones_observaciones', $param);
            }

            $this->set_anio_academico($datos['anio_academico_hash']);
            $this->set_periodo($datos['periodo_hash']);
            
            if ($resultado_obs == '1')
            {
				$this->enviar_mensaje_x_mail_a_DD($param);
				$resultado .= utf8_decode('Se han guardado las observaciones y se han enviado a la Dirección de Docentes. Materia: ');
			}
			if (!empty($resultado)) {
				$resultado .= $datos['materia_nombre'];
			}
            $this->set_mensaje($resultado);
        }
    }
    
    private function get_parametros_grabar_materia()
    {
        $parametros = array();
        $parametros['materia'] = $this->validate_param('materia', 'post', validador::TIPO_TEXTO);

		$promo1 = 'datepicker_materia_promo1_'.$parametros['materia'];
        $promo2 = 'datepicker_materia_promo2_'.$parametros['materia'];
        $integ = 'datepicker_materia_integ_'.$parametros['materia'];
        
        $parametros['fecha_promo1'] = $this->validate_param($promo1, 'post', validador::TIPO_TEXTO);
        $parametros['fecha_promo2'] = $this->validate_param($promo2, 'post', validador::TIPO_TEXTO);
        $parametros['fecha_integ'] = $this->validate_param($integ, 'post', validador::TIPO_TEXTO);
        
        $parametros['anio_academico_hash']  = $this->validate_param('anio_academico_hash', 'post', validador::TIPO_TEXTO);
        $parametros['periodo_hash']         = $this->validate_param('periodo_hash', 'post', validador::TIPO_TEXTO); 
        $parametros['anio_academico'] =  $this->decodificar_anio_academico($parametros['anio_academico_hash']);
        $parametros['periodo'] = $this->decodificar_periodo($parametros['periodo_hash'], $parametros['anio_academico']);

        $parametros['materia_nombre'] = catalogo::consultar('cursos', 'get_nombre_materia', array('materia'=>$parametros['materia']));
        $obs = 'observaciones_'.$parametros['materia'];
        $parametros['observaciones'] = $this->validate_param($obs, 'post', validador::TIPO_TEXTO);
        
        return $parametros;        
    }

    private function grabar_instancia($comision, $datos, $dias_clase, $instancia)
    {        
        switch ($instancia) {
            case 'promo1': 	$inst = 1; $inst_nombre = '1º Parcial'; break;
            case 'promo2': 	$inst = 2; $inst_nombre = '2º Parcial'; break;
            case 'recup': 	$inst = 7; $inst_nombre = 'Recuperatorio Global'; break;
            case 'integ': 	$inst = 14; $inst_nombre = 'Integrador'; break;
        }
        try 
        {
			kernel::db()->abrir_transaccion(); 
			
			$parametros['comision'] = $comision;
			$parametros['evaluacion'] = $inst;
            $fecha_instancia = 'fecha_'.$instancia;
            $parametros['fecha_hora'] = $this->get_fecha_hora($datos["$fecha_instancia"], $dias_clase);
            $this->verificar_si_fecha_posible($comision, $parametros['fecha_hora']);
			$this->verificar_fechas_posibles_con_demas_instancias($comision, $instancia, $parametros['fecha_hora']);            

			$resultado = catalogo::consultar('cursos', 'alta_propuesta_evaluacion_parcial', $parametros);

			if ($instancia == 'integ') {  //También hay que guardar el Recuperatoprio Global con igual fecha al Integrador
				$parametros['evaluacion'] = 7; 
				$resultado_recup = catalogo::consultar('cursos', 'alta_propuesta_evaluacion_parcial', $parametros);
			}
			
			if ($resultado == 1) {   
				if ($resultado_recup) {
					$inst_nombre .= ' y el Recuperatorio Global';
				}
				return 'Se han guardado las fechas para el '.$inst_nombre.'. ';
			}
			kernel::db()->cerrar_transaccion();
		}
		
        catch (error_guarani $e)
        {
            $msj = $e->getMessage();
			kernel::db()->abortar_transaccion($msj);
            $this->set_anio_academico($datos['anio_academico_hash']);
            $this->set_periodo($datos['periodo_hash']);
            $this->set_mensaje_error($msj. ' Materia: '.$datos['materia_nombre']);
		}
	}
	
    /** 
     * Verifica que ese dia, el anterior y el posterior no esta ya asignado a otra comision del mismo mix
     */
    function verificar_si_fecha_posible($comision, $fecha_hora)
    {
        $datos = $this->modelo()->get_datos_comision($comision);
	
        $parametros = array('anio_academico' => $datos['ANIO_ACADEMICO'],
                    'periodo' => $datos['PERIODO_LECTIVO'],
                    'materia' => $datos['MATERIA']);

		$fechas_ocupadas = $this->modelo()->get_fechas_eval_ocupadas($parametros);

		$fecha = rtrim(substr($fecha_hora, 0, 10));
        $fecha_ant = date('Y-m-d', strtotime('-1 day', strtotime($fecha)));
        $fecha_sig = date('Y-m-d', strtotime('+1 day', strtotime($fecha)));

        $ocupada = false;
        foreach ($fechas_ocupadas as $f_ocup)
        {
			if ($f_ocup['FECHA'] == $fecha
				|| $f_ocup['FECHA'] == $fecha_ant
				|| $f_ocup['FECHA'] == $fecha_sig )
			{
				$ocupada = true;
			}
        }

        if ($ocupada) {
            $msj = "La fecha solicitada $fecha ya se encuentra reservada. Intente con otra.";
            throw new error_guarani($msj);
		}
        return true;
    }


	private function enviar_mensaje_x_mail_a_DD($param)
    {
		$observaciones = $param['observaciones'];

		if (!isset($observaciones) || empty($observaciones) || trim($observaciones) == '') {
			return;
		}
		
		$mail_coordinador = kernel::persona()->get_mail();
		$coordinador_nombre = kernel::persona()->get_nombre();
		$materia = $param['materia'];
		$materia_nombre = catalogo::consultar('cursos', 'get_nombre_materia', array('materia'=>$materia));
		$fecha = date('d/m/Y H:m:s');

		$asunto = utf8_decode("Propuesta de fechas para evaluaciones: Nueva observacion (").$materia_nombre.")";
	
		$tpl = kernel::load_template('mail/mail_titulo.twig');
        $cuerpo = $tpl->render(
				array( 'coordinador_nombre' => $coordinador_nombre,
					'mail_coordinador' => $mail_coordinador,
					'materia_nombre' => $materia_nombre,
					'materia' => $materia,
					'fecha' => $fecha,
					'observaciones' => $observaciones)
                );
		
		$dir_from = catalogo::consultar('parametros', 'get_parametro', array('operacion'=>'mail_sistema'));
		$dir_from = $dir_from['PARAMETRO'];
		
		$dir_reply = $mail_coordinador;

		$dir_to = catalogo::consultar('parametros', 'get_parametro', array('operacion'=>'mail_dd'));
		$dir_to = $dir_to['PARAMETRO'];

		//Iris: Comentar esta seccion en produccion-------------------------------------
		$dir_to = 'imfigini@slab.exa.unicen.edu.ar';
		//$dir_to = 'bosch.marcela@gmail.com';
		//Iris: Fin comentar esta seccion en produccion---------------------------------

		$mail = new mail($dir_to, $asunto, $cuerpo, $dir_from);
		$mail->set_reply($dir_reply);
        $mail->set_html(true);
        $mail->enviar();
    }

    private function get_fecha_hora($fecha, $dias_clase)
    {
        //$dt = \DateTime::createFromFormat('!d/m/Y', $fecha);
		$dt = \DateTime::createFromFormat('!Y-m-d', $fecha);
		$dia_semana = $dt->format('w');
        $fecha_formateada = $dt->format('Y-m-d');
                
        foreach($dias_clase AS $d)
        {
            if ($d['DIA_SEMANA'] == $dia_semana) {
                $hora = $d['HS_COMIENZO_CLASE'];
                return $fecha_formateada.' '.$hora;
            }
        }
        return $fecha;
    }
    

    function verificar_fechas_posibles_con_demas_instancias($comision, $instancia, $fecha_hora)
    {
		$promo1 = 1;
		$promo2 = 2;
		$recup = 7;
		$integ = 14;

		$fecha = rtrim(substr($fecha_hora, 0, 10));
		$fechas_evaluacion = $this->modelo()->get_fechas_asignadas_o_solicitadas(Array('comision'=>$comision));

        $posible = true;
        foreach ($fechas_evaluacion as $eval)
        {
            switch ($instancia)
            {
                case 'promo1': 
					if ($eval['EVALUACION'] == $promo2 || $eval['EVALUACION'] == $recup || $eval['EVALUACION'] == $integ) {
					 	if ($eval['FECHA'] <= $fecha) {
    	                    $posible = false;
        	            }
					}
					break;
				case 'promo2': 
                    if ($eval['EVALUACION'] == $promo1 && $eval['FECHA'] >= $fecha) {
                        $posible = false;
                    }
                    if ( ($eval['EVALUACION'] == $recup || $eval['EVALUACION'] == $integ) && $eval['FECHA'] <= $fecha ) {
                        $posible = false;
                    }
                    break;
                case 'recup': 
                    if ( ($eval['EVALUACION'] == $promo1 || $eval['EVALUACION'] == $promo2) && $eval['FECHA'] >= $fecha) {
                        $posible = false;
					}
					break;
                case 'integ': 
					if ( ($eval['EVALUACION'] == $promo1 || $eval['EVALUACION'] == $promo2) && $eval['FECHA'] >= $fecha) {
							$posible = false;
					}
					break;
			}
        }
		if (!$posible)
		{
            $msj = "Verifique la cronologaa de las fechas. Instancia posterior de evaluacian debe tener fecha posterior, y viceversa.";
            throw new error_guarani($msj);
        }
        return true;
    }
}
?>
