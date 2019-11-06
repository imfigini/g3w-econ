<?php
namespace econ\operaciones\fechas_parciales_2019;

use kernel\kernel;
use siu\extension_kernel\controlador_g3w2;
use siu\modelo\datos\catalogo;
use kernel\util\validador;
use siu\errores\error_guarani;
use kernel\util\MailJob;
use kernel\util\mail;
//include 'func_util.php';
//use siu\modelo\guarani_notificacion;



class controlador extends controlador_g3w2
{
    protected $datos_filtro = array('anio_academico'=>"", 'periodo'=>"", 'mensaje'=>'', 'mensaje_error'=>'');
    
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
        $parametros['legajo'] = null;
        $parametros['carrera'] = null;
        $parametros['mix'] = null;

        $perfil = kernel::persona()->perfil()->get_id();
        if ($perfil == 'COORD')
        {
            $parametros['legajo'] = kernel::persona()->get_legajo_docente();
        }
        $materias = catalogo::consultar('cursos', 'get_materias_cincuentenario', $parametros);
        return $materias;
    }
    
    function get_materias_y_comisiones_cincuentenario($anio_academico_hash, $periodo_hash)
    {
        if (empty($anio_academico_hash) || empty($periodo_hash))
        {
            return null;
        }
        
        $materias = $this->get_materias_cincuentenario(); 
        //MATERIA, MATERIA_NOMBRE
        $anio_academico =  $this->decodificar_anio_academico($anio_academico_hash);
        $periodo = $this->decodificar_periodo($periodo_hash, $anio_academico);
        
        $datos = array();
        $cant = count($materias);
        for ($i=0; $i<$cant; $i++)
        {
            $materias[$i]['CICLO'] = catalogo::consultar('cursos', 'get_ciclo_de_materias', array('materia'=>$materias[$i]['MATERIA'])); 
            //F - P - FyP

            $parametros = array('anio_academico' => $anio_academico,
                                'periodo' => $periodo,
                                'materia' => $materias[$i]['MATERIA']);

            $comisiones = catalogo::consultar('cursos', 'get_comisiones_de_materia_con_dias_de_clase', $parametros);
            //COMISION, COMISION_NOMBRE, ANIO_ACADEMICO, PERIODO_LECTIVO, ESCALA, TURNO, CARRERA, OBSERVACIONES

            if (count($comisiones) > 0)
            {
                $materias[$i]['CALIDAD'] = catalogo::consultar('cursos', 'get_tipo_escala_de_materia', $parametros);
                // 'P', 'R' o 'PyR'

                $comisiones = $this->get_dias_de_clase_comision($comisiones);
                //DIAS_CLASE [DIA_SEMANA, HS_COMIENZO_CLASE, HS_FINALIZ_CLASE, DIA_NOMBRE]
                
                $comisiones = $this->get_fechas_no_validas($comisiones);
                //DIAS_NO_VALIDOS (son los dias que por razones particulares del docente no se dictan clases en la comision)
                
                $comisiones = $this->get_evaluaciones_existentes($comisiones);
                //[EVAL_XXXX] FECHA_HORA, ESTADO, READONLY (son las fechas que ya tiene reservada la comision, en cada instancia)

                $promocionables = array();
                $regulares = array();

                foreach ($comisiones AS $comision)
                {
                    switch(trim($comision['ESCALA']))
                    {
                        case 'P':   $promocionables[] = $comision; 
                                    break;
                        case 'R':   $regulares[] = $comision; 
                                    break;
                        case 'PyR': $promocionables[] = $comision;
                                    $regulares[] = $comision;
                                    break;
                    }
                }
                $materias[$i]['DIAS_PROMO'] = $this->get_mismos_dias($promocionables);
                $materias[$i]['DIAS_REGU'] = $this->get_mismos_dias($regulares);
                //[DIA, DIA_NOMBRE]] 
                //[DIA, DIA_NOMBRE]] 
                
                $materias[$i]['FECHAS_OCUPADAS'] = $this->get_fechas_ya_asignadas($parametros);
                //FECHA, EVALUACION
                
                $materias[$i]['OBSERVACIONES'] = catalogo::consultar('cursos', 'get_evaluaciones_observaciones', $parametros);
                //OBSERVACIONES
                
                $materias[$i]['COMISIONES'] = $comisiones;
                $datos[] = $materias[$i];
            }
        }
        return $datos;
    }
    
    /*
     * $parametros: 'anio_academico', 'periodo', 'materia'
     */
    function get_fechas_ya_asignadas($parametros)
    {
        //print_r($parametros);
        $materias_mismo_mix = catalogo::consultar('cursos', 'get_materias_mismo_mix', $parametros); 
        //print_r($materias_mismo_mix);

        $fechas = array();
        foreach($materias_mismo_mix AS $mat)
        {
            $parametros['materia'] = $mat['MATERIA'];
            $fechas_mat = catalogo::consultar('cursos', 'get_fechas_eval_asignadas', $parametros); 
            $fechas = array_merge($fechas, $fechas_mat);
        }
        //print_r($fechas);
        return $fechas;
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

    /*
     * Retorna los dias de la semana asignados a cada comision, y la banda horaria
     * y las fechas de clase especificas asignadas a cada comision (validas)
     */
    function get_dias_de_clase_comision($comisiones)
    {
        $resultado = array();
        foreach ($comisiones AS $comision)
        {
            $parametros['comision'] = $comision['COMISION'];
            $comision['DIAS_CLASE'] = catalogo::consultar('cursos', 'get_dias_clase', $parametros);
            $comision['DIAS_CLASE_JSON'] = json_encode($comision['DIAS_CLASE'], JSON_FORCE_OBJECT | JSON_PARTIAL_OUTPUT_ON_ERROR );
            $resultado[] = $comision;
        }
        return $resultado;
    }
    
    /*
     * Retorna las fechas indicadas como no validas para cada comisian
     */
    function get_fechas_no_validas($comisiones)
    {
        $resultado = array();
        foreach ($comisiones AS $comision)
        {
            $parametros['comision'] = $comision['COMISION'];
            $dias_no_validos = catalogo::consultar('cursos', 'get_fechas_no_validas', $parametros);
            $arreglo = Array();
            foreach ($dias_no_validos AS $d)
            {
                $arreglo[] = $d['FECHA'];
            }
            $comision['DIAS_NO_VALIDOS'] = $arreglo;
            $resultado[] = $comision;
        }
        return $resultado;
    }
    
    function get_mismos_dias($comisiones)
    {
        $cant = count($comisiones);
        if ($cant == 0)
        {
            return null;
        }
        $mismos_dias = true;
        for ($i=1; $i<$cant; $i++)
        {
            $dias_clase_1 = $comisiones[$i-1]['DIAS_CLASE'];
            $dias_clase_2 = $comisiones[$i]['DIAS_CLASE'];
            if (count($dias_clase_1) != count($dias_clase_2))
            {
                $mismos_dias = false;
            }
            else
            {
                $cant_dias = count($dias_clase_1);
                for ($j=0; $j<$cant_dias; $j++)
                {
                    if ($dias_clase_1[$j]['DIA_SEMANA'] != $dias_clase_2[$j]['DIA_SEMANA'])
                    {
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
            return catalogo::consultar('evaluaciones_parciales', 'get_dias_no_laborales', $parametros);
        }
        return null;
    }

    function get_evaluaciones_existentes($comisiones)
    {
        $result = array();
        foreach($comisiones as $comision)
        {
            $parametros['comision'] = $comision['COMISION'];
            $escala = $comision['ESCALA'];

            //PROMO
            if (strpos($escala, 'P') !== false)
            {
                $parametros['evaluacion'] = 1;
                $eval = catalogo::consultar('cursos', 'get_evaluaciones_existentes', $parametros);
                (isset($eval))? : $eval[0] = null;
                $comision['EVAL_PROMO1'] = $eval[0];
                $comision['EVAL_PROMO1']['READONLY'] = $this->is_eval_readonly($eval[0]);
                
                $parametros['evaluacion'] = 2;
                $eval = catalogo::consultar('cursos', 'get_evaluaciones_existentes', $parametros);
                (isset($eval))? : $eval[0] = null;
                $comision['EVAL_PROMO2'] = $eval[0];
                $comision['EVAL_PROMO2']['READONLY'] = $this->is_eval_readonly($eval[0]);
                
                $parametros['evaluacion'] = 7;
                $eval = catalogo::consultar('cursos', 'get_evaluaciones_existentes', $parametros);
                (isset($eval))? : $eval[0] = null;
                $comision['EVAL_RECUP'] = $eval[0];
                $comision['EVAL_RECUP']['READONLY'] = $this->is_eval_readonly($eval[0]);
                
                $parametros['evaluacion'] = 14;
                $eval = catalogo::consultar('cursos', 'get_evaluaciones_existentes', $parametros);
                (isset($eval))? : $eval[0] = null;
                $comision['EVAL_INTEG'] = $eval[0];
                $comision['EVAL_INTEG']['READONLY'] = $this->is_eval_readonly($eval[0]);
            }
            
            //REGULAR
            if (strpos($escala, 'R') !== false)
            {
                $parametros['evaluacion'] = 21;
                $eval = catalogo::consultar('cursos', 'get_evaluaciones_existentes', $parametros);
                (isset($eval))? : $eval[0] = null;
                $comision['EVAL_REGU1'] = $eval[0];
                $comision['EVAL_REGU1']['READONLY'] = $this->is_eval_readonly($eval[0]);

                $parametros['evaluacion'] = 4;
                $eval = catalogo::consultar('cursos', 'get_evaluaciones_existentes', $parametros);
                (isset($eval))? : $eval[0] = null;
                $comision['EVAL_RECUP1'] = $eval[0];
                $comision['EVAL_RECUP1']['READONLY'] = $this->is_eval_readonly($eval[0]);
                
                $parametros['evaluacion'] = 5;
                $eval = catalogo::consultar('cursos', 'get_evaluaciones_existentes', $parametros);
                (isset($eval))? : $eval[0] = null;
                $comision['EVAL_RECUP2'] = $eval[0];
                $comision['EVAL_RECUP2']['READONLY'] = $this->is_eval_readonly($eval[0]);            

            }
            $result[] = $comision;
        }
        return $result;
    }
    
    /*
     * Si hay fecha cargada y el estado es NULL o 'A' no puede modificar el docente
     */
    private function is_eval_readonly($evaluacion)
    {
        if (!empty($evaluacion['FECHA_HORA']))
        {
            if (empty($evaluacion['ESTADO']) || $evaluacion['ESTADO'] == 'A')
            {
                return true;
            }
        }
        return false;
    }
    
    function get_ciclo_de_materias($materia)
    {
        $parametros = array('materia' => $materia);
        return catalogo::consultar('cursos', 'get_ciclo_de_materias', $parametros);
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
        if (!empty($this->get_anio_academico()))
        {
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
                        $this->form_builder = kernel::localizador()->instanciar('operaciones\fechas_parciales_2019\filtro\builder_form_filtro');
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
    

//    
//    private function verify_format($fecha_hora)
//    {
//        $re = '/^(\d{4})-(0[1-9]|1[0-2])-([0-2][0-9]|3[0-1])\s([0-1][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/';
//        if (preg_match($re, $fecha_hora, $matches))
//        {
//            return true;
//        }
//        return false;
//    }

    /** 
     * Verifica que ese daa, el anterior y el posterior no esta ya asignado a otra comisian del mismo mix
     * En caso de ser "Recuperatorio Unico" no debe verificar anterior y posterior
     */
    function verificar_si_fecha_posible($anio_academico, $periodo, $fecha_hora, $comision, $instancia_evaluacion)
    {
        $promo1 = 1;
        $promo2 = 2;
        $recup = 7;
        $integ = 14;
        $regu1 = 21;
        $recup1 = 4;
        $recup2 = 5;
        
        $materia = catalogo::consultar('cursos', 'get_materia', array('comision'=>$comision));
        
        $parametros = array('anio_academico' => $anio_academico,
                    'periodo' => $periodo,
                    'materia' => $materia);

        $fechas_ocupadas = $this->get_fechas_ya_asignadas($parametros); 
        $fecha = rtrim(substr($fecha_hora, 0, 10));
        $fecha_ant = date('Y-m-d', strtotime('-1 day', strtotime($fecha)));
        $fecha_sig = date('Y-m-d', strtotime('+1 day', strtotime($fecha)));
        
        $ocupada = false;
        foreach ($fechas_ocupadas as $f_ocup)
        {
            if ($instancia_evaluacion == 'promo1')
            {
                if ($f_ocup['EVALUACION'] == $promo1 
                        || $f_ocup['EVALUACION'] == $regu1 )
                {
                    if ($f_ocup['FECHA'] == $fecha
                            || $f_ocup['FECHA'] == $fecha_ant
                            || $f_ocup['FECHA'] == $fecha_sig )
                    {
                        $ocupada = true;
                    }
                }
            }
            if (instancia_evaluacion == 'promo2')
            {
                if ($f_ocup['EVALUACION'] == $promo2 
                        || $f_ocup['EVALUACION'] == $integ )
                {
                    if ($f_ocup['FECHA'] == $fecha
                            || $f_ocup['FECHA'] == $fecha_ant
                            || $f_ocup['FECHA'] == $fecha_sig)
                    {
                        $ocupada = true;
                    }
                }
                else if ($f_ocup['EVALUACION'] == $recup
                        || $f_ocup['EVALUACION'] == $recup1
                        || $f_ocup['EVALUACION'] == $recup2 )
                {
                    if ($f_ocup['FECHA'] == $fecha)
                    {
                        $ocupada = true;
                    }
                }
            } 
            if (instancia_evaluacion == 'recup')
            {
                if ($f_ocup['EVALUACION'] == $promo2 
                        || $f_ocup['EVALUACION'] == $recup
                        || $f_ocup['EVALUACION'] == $integ
                        || $f_ocup['EVALUACION'] == $recup1
                        || $f_ocup['EVALUACION'] == $recup2 )
                {
                    if ($f_ocup['FECHA'] == $fecha)
                    {
                        $ocupada = true;
                    }
                }
            }
            if (instancia_evaluacion == 'integ')
            {
                if ($f_ocup['EVALUACION'] == $promo2 )
                {
                    if ($f_ocup['FECHA'] == $fecha
                            || $f_ocup['FECHA'] == $fecha_ant
                            || $f_ocup['FECHA'] == $fecha_sig)
                    {
                        $ocupada = true;
                    }
                }
                else if ($f_ocup['EVALUACION'] == $recup
                        || $f_ocup['EVALUACION'] == $integ
                        || $f_ocup['EVALUACION'] == $recup1
                        || $f_ocup['EVALUACION'] == $recup2 )
                {
                    if ($f_ocup['FECHA'] == $fecha)
                    {
                        $ocupada = true;
                    }
                }
            }
            if (instancia_evaluacion == 'regu1')
            {
                
                if ( $f_ocup['EVALUACION'] == $promo1 
                        || $f_ocup['EVALUACION'] == $regu1 )
                {
                    if ($f_ocup['FECHA'] == $fecha
                            || $f_ocup['FECHA'] == $fecha_ant
                            || $f_ocup['FECHA'] == $fecha_sig)
                    {
                        $ocupada = true;
                    }
                }
            }
            if (instancia_evaluacion == 'recup1')
            {
                if ($f_ocup['EVALUACION'] == $promo2 
                        || $f_ocup['EVALUACION'] == $recup
                        || $f_ocup['EVALUACION'] == $integ
                        || $f_ocup['EVALUACION'] == $recup1
                        || $f_ocup['EVALUACION'] == $recup2 )
                {
                    if ($f_ocup['FECHA'] == $fecha)
                    {
                        $ocupada = true;
                    }
                }
            }
            if (instancia_evaluacion == 'recup2')
            {
                if ($f_ocup['EVALUACION'] == $promo2 
                        || $f_ocup['EVALUACION'] == $recup
                        || $f_ocup['EVALUACION'] == $integ
                        || $f_ocup['EVALUACION'] == $recup1
                        || $f_ocup['EVALUACION'] == $recup2 )
                {
                    if ($f_ocup['FECHA'] == $fecha)
                    {
                        $ocupada = true;
                    }
                }
            }
        }

        if ($ocupada)
        {
            $msj = "La fecha solicitada $fecha ya se encuentra reservada. Intente con otra.";
            throw new error_guarani($msj);
        }
        return true;
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
            $comisiones = catalogo::consultar('cursos', 'get_comisiones_de_materia_con_dias_de_clase', $parametros);


            foreach ($comisiones as $comision)
            {
                $parametros['comision'] = $comision['COMISION'];

                switch (trim($comision['ESCALA']))
                {
                    case 'R': $parametros['escala_notas'] = 3; break;    
                    case 'P': $parametros['escala_notas'] = 6; break;   
                    case 'PyR': $parametros['escala_notas'] = 4; break;   
                }

                $dias_clase = catalogo::consultar('cursos', 'get_dias_clase', $parametros);
                $resultado = '';
                //PROMO
                if (strpos($comision['ESCALA'], 'P') !== false)
                {
                    if (!empty($datos['fecha_promo1']))
                    {
                        $resultado .= $this->grabar_instancia($parametros, $datos, $dias_clase, 'promo1');
                    }
                    if (!empty($datos['fecha_promo2']))
                    {
                        $resultado .= $this->grabar_instancia($parametros, $datos, $dias_clase, 'promo2');
                    }
                    if (!empty($datos['fecha_recup']))
                    {
                        $resultado .= $this->grabar_instancia($parametros, $datos, $dias_clase, 'recup');
                    }
                    if (!empty($datos['fecha_integ']))
                    {
                        $resultado .= $this->grabar_instancia($parametros, $datos, $dias_clase, 'integ');
                    }
                }

                //REGULAR
                if (strpos($comision['ESCALA'], 'R') !== false)
                {
                    if (!empty($datos['fecha_regu1']))
                    {
                        $this->grabar_instancia($parametros, $datos, $dias_clase, 'regu1');
                    }
                    if (!empty($datos['fecha_recup1']))
                    {
                        $this->grabar_instancia($parametros, $datos, $dias_clase, 'recup1');
                    }
                    if (!empty($datos['fecha_recup2']))
                    {
                        $this->grabar_instancia($parametros, $datos, $dias_clase, 'recup2');
                    }
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
    
    private function enviar_mensaje_x_mail_a_DD($param)
    {
		$observaciones = $param['observaciones'];

		if (!isset($observaciones) || empty($observaciones) || trim($observaciones) == '')
		{
			return;
		}
		
		$mail_coordinador = kernel::persona()->get_mail();
		$coordinador_nombre = kernel::persona()->get_nombre();
		$materia = $param['materia'];
		$materia_nombre = catalogo::consultar('cursos', 'get_nombre_materia', array('materia'=>$materia));
		$fecha = date('d/m/Y H:m:s');

		$asunto = utf8_decode("Propuesta de fechas para evaluaciones: Nueva observación (").$materia_nombre.")";
	
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

		//Iris: Comentar esta secci?n en producci?n-------------------------------------
		$dir_to = 'imfigini@slab.exa.unicen.edu.ar';
		//$dir_to = 'bosch.marcela@gmail.com';
		//Iris: Fin comentar esta secci?n en producci?n---------------------------------

		$mail = new mail($dir_to, $asunto, $cuerpo, $dir_from);
		$mail->set_reply($dir_reply);
        $mail->set_html(true);
        $mail->enviar();
    }

    private function grabar_instancia($parametros, $datos, $dias_clase, $instancia)
    {        
        switch ($instancia)
        {
            case 'promo1': $inst = 1; $inst_nombre = '1a Parcial Promo'; break;
            case 'promo2': $inst = 2; $inst_nombre = '2a Parcial Promo'; break;
            case 'recup': $inst = 7; $inst_nombre = 'Recuperatorio Promo'; break;
            case 'integ': $inst = 14; $inst_nombre = 'Integrador'; break;
            case 'regu1': $inst = 21; $inst_nombre = 'Parcial Regular'; break;
            case 'recup1': $inst = 4; $inst_nombre = '1a Recuperatorio Regular'; break;
            case 'recup2': $inst = 5; $inst_nombre = '2a Recuperatorio Regular'; break;
        }
        try 
        {
            kernel::db()->abrir_transaccion(); 
            $parametros['evaluacion'] = $inst;
            $fecha_instancia = 'fecha_'.$instancia;
            $parametros['fecha_hora'] = $this->get_fecha_hora($datos["$fecha_instancia"], $dias_clase);
            $this->verificar_si_fecha_posible($datos['anio_academico'], $datos['periodo'], $parametros['fecha_hora'], $parametros['comision'], $instancia);
            $this->verificar_fecha_posibles_con_demas_instancias($instancia, $parametros['fecha_hora'], $datos['evaluaciones_de_materia']);
            kernel::db()->cerrar_transaccion();
            $resultado = catalogo::consultar('cursos', 'alta_propuesta_evaluacion_parcial', $parametros);
            if (strpos(0, $resultado) == false)
            {   
                return 'Se han guardado las fechas para el '.$inst_nombre.'. ';
            }

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

    private function get_fecha_hora($fecha, $dias_clase)
    {
        //$dt = \DateTime::createFromFormat('!d/m/Y', $fecha);
        $dt = \DateTime::createFromFormat('!Y-m-d', $fecha);
        $dia_semana = $dt->format('w');
        $fecha_formateada = $dt->format('Y-m-d');
                
        foreach($dias_clase AS $d)
        {
            if ($d['DIA_SEMANA'] == $dia_semana)
            {
                $hora = $d['HS_COMIENZO_CLASE'];
                return $fecha_formateada.' '.$hora;
            }
        }
        return $fecha;
    }
    
    function get_parametros_grabar_materia()
    {
        $parametros = array();
        $parametros['materia'] = $this->validate_param('materia', 'post', validador::TIPO_TEXTO);
        $parametros['calidad'] = $this->validate_param('materia_calidad', 'post', validador::TIPO_TEXTO);

        //PROMO
        if (strpos($parametros['calidad'], 'P') !== false)
        {
            $parametros = $this->get_parametros_materia_promo($parametros);
        }

        //REGULAR
        if (strpos($parametros['calidad'], 'R') !== false)
        {
            $parametros = $this->get_parametros_materia_regu($parametros);
        }
        
        $parametros['anio_academico_hash']  = $this->validate_param('anio_academico_hash', 'post', validador::TIPO_TEXTO);
        $parametros['periodo_hash']         = $this->validate_param('periodo_hash', 'post', validador::TIPO_TEXTO); 
        $parametros['anio_academico'] =  $this->decodificar_anio_academico($parametros['anio_academico_hash']);
        $parametros['periodo'] = $this->decodificar_periodo($parametros['periodo_hash'], $parametros['anio_academico']);

        $parametros['evaluaciones_de_materia'] = catalogo::consultar('cursos', 'get_evaluaciones_de_materia', $parametros);
                
        $parametros['materia_nombre'] = catalogo::consultar('cursos', 'get_nombre_materia', array('materia'=>$parametros['materia']));
        $obs = 'observaciones_'.$parametros['materia'];
        $parametros['observaciones'] = $this->validate_param($obs, 'post', validador::TIPO_TEXTO);
        
        return $parametros;        
    }

    private function get_parametros_materia_promo($parametros)
    {
        $promo1 = 'datepicker_materia_promo1_'.$parametros['materia'];
        $promo2 = 'datepicker_materia_promo2_'.$parametros['materia'];
        $recup = 'datepicker_materia_recup_'.$parametros['materia'];
        $integ = 'datepicker_materia_integ_'.$parametros['materia'];
        
        $parametros['fecha_promo1'] = $this->validate_param($promo1, 'post', validador::TIPO_TEXTO);
        $parametros['fecha_promo2'] = $this->validate_param($promo2, 'post', validador::TIPO_TEXTO);
        $parametros['fecha_recup'] = $this->validate_param($recup, 'post', validador::TIPO_TEXTO);
        $parametros['fecha_integ'] = $this->validate_param($integ, 'post', validador::TIPO_TEXTO);
        
        return $parametros;
    }
    
    private function get_parametros_materia_regu($parametros)
    {
        $regu1 = 'datepicker_materia_regu1_'.$parametros['materia'];
        $recup1 = 'datepicker_materia_recup1_'.$parametros['materia'];
        $recup2 = 'datepicker_materia_recup2_'.$parametros['materia'];

        $parametros['fecha_regu1'] = $this->validate_param($regu1, 'post', validador::TIPO_TEXTO);
        $parametros['fecha_recup1'] = $this->validate_param($recup1, 'post', validador::TIPO_TEXTO);        
        $parametros['fecha_recup2'] = $this->validate_param($recup2, 'post', validador::TIPO_TEXTO);

        return $parametros;
    }        
    
    function verificar_fecha_posibles_con_demas_instancias($instancia, $fecha_hora, $evaluaciones_de_materia)
    {
        $fecha = rtrim(substr($fecha_hora, 0, 10));
        
        $ocupada = false;
        foreach ($evaluaciones_de_materia as $eval)
        {
            switch ($instancia)
            {
                case 'promo2': 
                    if ($eval['EVALUACION'] == 7 && $eval['FECHA'] <= $fecha) 
                    {
                        $ocupada = true;
                    }
                    if ($eval['EVALUACION'] == 14 && $eval['FECHA'] <= $fecha) 
                    {
                        $ocupada = true;
                    }
                    break;
                case 'recup': 
                    if ($eval['EVALUACION'] == 2 && $eval['FECHA'] >= $fecha) 
                    {
                        $ocupada = true;
                    }
                    if ($eval['EVALUACION'] == 14 && $eval['FECHA'] <= $fecha) 
                    {
                        $ocupada = true;
                    }
                    break;
                case 'integ': 
                    if ($eval['EVALUACION'] == 2 && $eval['FECHA'] >= $fecha) 
                    {
                        $ocupada = true;
                    }
                    if ($eval['EVALUACION'] == 7 && $eval['FECHA'] >= $fecha) 
                    {
                        $ocupada = true;
                    }
                    break;
                case 'recup1': 
                    if ($eval['EVALUACION'] == 5 && $eval['FECHA'] <= $fecha) 
                    {
                        $ocupada = true;
                    }
                    break;
                case 'recup2': 
                    if ($eval['EVALUACION'] == 4 && $eval['FECHA'] >= $fecha) 
                    {
                        $ocupada = true;
                    }
                    break;
            }
        }
        if ($ocupada)
        {
            $msj = "Verifique la cronologaa de las fechas. Instancia posterior de evaluacian debe tener fecha posterior, y viceversa.";
            throw new error_guarani($msj);
        }
        return true;
    }
}
?>
