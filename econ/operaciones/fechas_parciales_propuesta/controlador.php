<?php
namespace econ\operaciones\fechas_parciales_propuesta;

use kernel\kernel;
use siu\extension_kernel\controlador_g3w2;
use siu\modelo\datos\catalogo;
use kernel\util\validador;
use siu\errores\error_guarani;
//include 'func_util.php';
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
        $materias = $this->get_materias_cincuentenario(); 
        //MATERIA, MATERIA_NOMBRE

        $datos = array();
        $cant = count($materias);
        for ($i=0; $i<$cant; $i++)
        {
            $materias[$i]['CICLO'] = catalogo::consultar('cursos', 'get_ciclo_de_materias', array('materia'=>$materias[$i]['MATERIA'])); 
            //F - P - FyP

            $comisiones = $this->get_comisiones_de_materia_con_dias_de_clase($anio_academico_hash, $periodo_hash, $materias[$i]['MATERIA']);
            //COMISION, COMISION_NOMBRE, ANIO_ACADEMICO, PERIODO_LECTIVO, ESCALA, TURNO, CARRERA, OBSERVACIONES

            if (count($comisiones) > 0)
            {
                $materias[$i]['CALIDAD'] = $this->get_tipo_escala_de_materia($anio_academico_hash, $periodo_hash, $materias[$i]['MATERIA']);
                // 'P', 'R' o 'PyR'

                $comisiones = $this->get_dias_de_clase_comision($comisiones);
                //DIAS_CLASE [DIA_SEMANA, HS_COMIENZO_CLASE, HS_FINALIZ_CLASE, DIA_NOMBRE]
                
                $comisiones = $this->get_fechas_no_validas($comisiones);

                $comisiones = $this->get_evaluaciones_existentes($comisiones);

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
                
                $materias[$i]['FECHAS_OCUPADAS'] = $this->get_fechas_ya_asignadas($materias[$i]['MATERIA']);
               
                $materias[$i]['OBSERVACIONES'] = $this->get_observaciones_materia($materias[$i]['MATERIA'], $anio_academico_hash, $periodo_hash);
                
                $materias[$i]['COMISIONES'] = $comisiones;
                $datos[] = $materias[$i];
            }
        }
        return $datos;
    }
    
    function get_fechas_ya_asignadas($materia)
    {
        $materias_mismo_mix = catalogo::consultar('cursos', 'get_materias_mismo_mix', array('materia'=>$materia)); 
        $fechas_ya_asignadas = array();
        foreach($materias_mismo_mix AS $mat)
        {
            $fechas = catalogo::consultar('cursos', 'get_fechas_eval_asignadas', array('materia'=>$mat['MATERIA'])); 
            foreach ($fechas as $f)
            {
                $fechas_ya_asignadas[] = $f['FECHA'];
            }
        }
        return $fechas_ya_asignadas;
    }
    
    function get_comisiones_de_materia_con_dias_de_clase($anio_academico_hash, $periodo_hash, $materia)
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
                                'periodo' => $periodo,
                                'materia' => $materia
                    );
                return catalogo::consultar('cursos', 'get_comisiones_de_materia_con_dias_de_clase', $parametros);
            }
        }
        return null;
    }
    
    function get_observaciones_materia($materia, $anio_academico_hash, $periodo_hash)
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
                                'periodo_lectivo' => $periodo,
                                'materia' => $materia
                    );
                return catalogo::consultar('cursos', 'get_evaluaciones_observaciones', $parametros);
            }
        }
        return null;
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
    
    /*
     * Retorna los días de la semana asignados a cada comisión, y la banda horaria
     //* y las fechas de clase específicas asignadas a cada comisión (válidas)
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
     * Retorna las fechas indicadas como no válidas para cada comisión
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
    
    function get_tipo_escala_de_materia($anio_academico_hash, $periodo_hash=null, $materia)
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
                                'periodo' => $periodo,
                                'materia' => $materia
                    );
                return catalogo::consultar('cursos', 'get_tipo_escala_de_materia', $parametros);
            }
        }
        return null;
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
            if ($escala == 'P  ' || $escala == 'PyR')
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
            if ($escala == 'R  ' || $escala == 'PyR')
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
    
    
    /** Graba sólo la comisión */
    function accion__grabar_comision()
    {
        $datos = $this->get_parametros_grabar_comision();
        try 
        {
            kernel::db()->abrir_transaccion();
            $mensaje = '';
            if (kernel::request()->isPost()) 
            {
                $parametros['comision'] = $datos['comision'];
                $parametros['escala_notas'] = $datos['escala'];
                
                $resultado = '';
                //PROMO
                if (!empty($datos['fecha_promo1']) && !$datos['promo1_readonly'])
                {
                    $parametros['evaluacion'] = 1;
                    $this->verificar_si_fecha_posible($datos['fecha_promo1'], $parametros['comision'], true);
                    $parametros['fecha_hora'] = $datos['fecha_promo1'];
                    $resultado .= catalogo::consultar('cursos', 'alta_propuesta_evaluacion_parcial', $parametros);
                }
                if (!empty($datos['fecha_promo2']) && !$datos['promo2_readonly'])
                {
                    $parametros['evaluacion'] = 2;
                    $this->verificar_si_fecha_posible($datos['fecha_promo2'], $parametros['comision'], true);
                    $parametros['fecha_hora'] = $datos['fecha_promo2'];
                    $resultado .= catalogo::consultar('cursos', 'alta_propuesta_evaluacion_parcial', $parametros);
                }
                if (!empty($datos['fecha_recup']) && !$datos['recup_readonly'])
                {
                    $parametros['evaluacion'] = 7;
                    $this->verificar_si_fecha_posible($datos['fecha_recup'], $parametros['comision'], false);
                    $parametros['fecha_hora'] = $datos['fecha_recup'];
                    $resultado .= catalogo::consultar('cursos', 'alta_propuesta_evaluacion_parcial', $parametros);
                }
                if (!empty($datos['fecha_integ']) && !$datos['integ_readonly'])
                {
                    $parametros['evaluacion'] = 14;
                    $this->verificar_si_fecha_posible($datos['fecha_integ'], $parametros['comision'], true);
                    $parametros['fecha_hora'] = $datos['fecha_integ'];
                    $resultado .= catalogo::consultar('cursos', 'alta_propuesta_evaluacion_parcial', $parametros);
                }

                //REGULAR
                if (!empty($datos['fecha_regu1']) && !$datos['regu1_readonly'])
                {
                    $parametros['evaluacion'] = 21;
                    $this->verificar_si_fecha_posible($datos['fecha_regu1'], $parametros['comision'], true);
                    $parametros['fecha_hora'] = $datos['fecha_regu1'];
                    $resultado .= catalogo::consultar('cursos', 'alta_propuesta_evaluacion_parcial', $parametros);
                }

                if (!empty($datos['fecha_recup1']) && !$datos['recup1_readonly'])
                {
                    $parametros['evaluacion'] = 4;
                    $this->verificar_si_fecha_posible($datos['fecha_recup1'], $parametros['comision'], true);
                    $parametros['fecha_hora'] = $datos['fecha_recup1'];
                    $resultado .= catalogo::consultar('cursos', 'alta_propuesta_evaluacion_parcial', $parametros);
                }
                if (!empty($datos['fecha_recup2']) && !$datos['recup2_readonly'])
                {
                    $parametros['evaluacion'] = 5;
                    $this->verificar_si_fecha_posible($datos['fecha_recup2'], $parametros['comision'], true);
                    $parametros['fecha_hora'] = $datos['fecha_recup2'];
                    $resultado .= catalogo::consultar('cursos', 'alta_propuesta_evaluacion_parcial', $parametros);
                }

                $mensaje = '';
                if (substr($resultado, 0, 1) == 1)
                {        
                    $mensaje = 'Se han guardado las fechas solicitadas para la comisión: '.$datos['comision'];
                }
                $this->set_anio_academico($datos['anio_academico_hash']);
                $this->set_periodo($datos['periodo_hash']);
                $this->set_mensaje($mensaje);
                kernel::db()->cerrar_transaccion();
            }
        } 
        catch (error_guarani $e)
        {
            $msj = $e->getMessage();
            kernel::db()->abortar_transaccion($msj);
            $this->set_anio_academico($datos['anio_academico_hash']);
            $this->set_periodo($datos['periodo_hash']);
            $this->set_mensaje_error($msj. ' Comision: '.$datos['comision']);
        }
        catch (\Exception $ex)
        {
            $msj = $ex->getMessage();
            kernel::db()->abortar_transaccion($msj);
            $this->set_anio_academico($datos['anio_academico_hash']);
            $this->set_periodo($datos['periodo_hash']);
            $this->set_mensaje_error($msj. ' Comision: '.$datos['comision']);
        }
    }
    
    function get_parametros_grabar_comision()
    {
        try
        {
            $parametros = array();
       
            $parametros['comision'] = $this->validate_param('comision', 'post', validador::TIPO_TEXTO);
            $tipo_escala = 'escala_'.$parametros['comision'];
            $parametros['tipo_escala'] = $this->validate_param($tipo_escala, 'post', validador::TIPO_TEXTO);
            $dias_clse_com = 'dias_clase_'.$parametros['comision'];
            $dias_clase = $this->validate_param($dias_clse_com, 'post', validador::TIPO_TEXTO);
            $parametros['dias_clase'] = (array) json_decode($dias_clase);
            $observaciones = 'observaciones_'.$parametros['comision'];
            $parametros['observaciones'] = $this->validate_param($observaciones, 'post', validador::TIPO_TEXTO);

            //PROMO
            if ($parametros['tipo_escala'] == 'P  ' || $parametros['tipo_escala'] == 'PyR' )
            {
                $parametros = $this->get_parametros_comision_promo($parametros);
            }

            //REGULAR
            if ($parametros['tipo_escala'] == 'R  ' || $parametros['tipo_escala'] == 'PyR' )
            {
                $parametros = $this->get_parametros_comision_regu($parametros);
            }

            switch ($parametros['tipo_escala'])
            {
                case 'R  ': $parametros['escala'] = 3; break;    
                case 'P  ': $parametros['escala'] = 6; break;   
                case 'PyR': $parametros['escala'] = 4; break;   
            }

            $parametros['anio_academico_hash']  = $this->validate_param('anio_academico_hash', 'post', validador::TIPO_TEXTO);
            $parametros['periodo_hash']         = $this->validate_param('periodo_hash', 'post', validador::TIPO_TEXTO); 

            return $parametros;        
        }
        catch (\Exception $ex)
        {
            throw $ex;
        }
    }

    private function get_parametros_comision_promo($parametros)
    {
        try 
        {
            $comision = $parametros['comision'];
        
            $fecha_promo1 = $this->validate_param('datepicker_comision_promo1_'.$comision, 'post', validador::TIPO_TEXTO);
            $parametros['fecha_promo1'] = self::format_fecha_hora($fecha_promo1, $parametros['dias_clase']);
            $parametros['promo1_readonly'] = $this->validate_param('promo1_readonly_'.$comision, 'post', validador::TIPO_TEXTO);

            $fecha_promo2 = $this->validate_param('datepicker_comision_promo2_'.$comision, 'post', validador::TIPO_TEXTO);
            $parametros['fecha_promo2'] = self::format_fecha_hora($fecha_promo2, $parametros['dias_clase']);
            $parametros['promo2_readonly'] = $this->validate_param('promo2_readonly_'.$comision, 'post', validador::TIPO_TEXTO);  

            $fecha_recup = $this->validate_param('datepicker_comision_recup_'.$comision, 'post', validador::TIPO_TEXTO);
            $parametros['fecha_recup'] = self::format_fecha_hora($fecha_recup, $parametros['dias_clase']);
            $parametros['recup_readonly'] = $this->validate_param('recup_readonly_'.$comision, 'post', validador::TIPO_TEXTO);  

            $fecha_integ = $this->validate_param('datepicker_comision_integ_'.$comision, 'post', validador::TIPO_TEXTO);
            $parametros['fecha_integ'] = self::format_fecha_hora($fecha_integ, $parametros['dias_clase']);
            $parametros['integ_readonly'] = $this->validate_param('integ_readonly_'.$comision, 'post', validador::TIPO_TEXTO);  

            return $parametros;
        } 
        catch (\Exception $ex) 
        {
            throw $ex;
        }
        
    }
    
    private function get_parametros_comision_regu($parametros)
    {
        try 
        {
            $comision = $parametros['comision'];

            $fecha_regu1 = $this->validate_param('datepicker_comision_regu1_'.$comision, 'post', validador::TIPO_TEXTO);
            $parametros['fecha_regu1'] = self::format_fecha_hora($fecha_regu1, $parametros['dias_clase']);
            $parametros['regu1_readonly'] = $this->validate_param('regu1_readonly_'.$comision, 'post', validador::TIPO_TEXTO);

            $fecha_recup1 = $this->validate_param('datepicker_comision_recup1_'.$comision, 'post', validador::TIPO_TEXTO);
            $parametros['fecha_recup1'] = self::format_fecha_hora($fecha_recup1, $parametros['dias_clase']);
            $parametros['recup1_readonly'] = $this->validate_param('recup1_readonly_'.$comision, 'post', validador::TIPO_TEXTO);      

            $fecha_recup2 = $this->validate_param('datepicker_comision_recup2_'.$comision, 'post', validador::TIPO_TEXTO);
            $parametros['fecha_recup2'] = self::format_fecha_hora($fecha_recup2, $parametros['dias_clase']);
            $parametros['recup2_readonly'] = $this->validate_param('recup2_readonly_'.$comision, 'post', validador::TIPO_TEXTO);

            return $parametros;
        } 
        catch (\Exception $ex) 
        {
            throw $ex;
        }
    }
    
    private function format_fecha_hora($fecha, $dias_clase)
    {
        if (!empty($fecha) and !self::verify_format($fecha))
        {
            $dt = \DateTime::createFromFormat('!Y-m-d', $fecha);
            $dia_semana = $dt->format('w');
            $fecha_formateada = $dt->format('Y-m-d');
                
            foreach($dias_clase AS $d)
            {
                if ($d->{'DIA_SEMANA'} == $dia_semana)
                {
                    $hora = $d->{'HS_COMIENZO_CLASE'};
                    return $fecha_formateada.' '.$hora;
                }
            }
            return $fecha_formateada;
        }
        return null;
    }
    
//    
//    function get_datetime_from_string($datetime_str, $format = 'Y-m-d H:i:s')
//    {
//        //return DateTime::createFromFormat(trim($format), $datetime_str);
//        $result = \DateTime::createFromFormat(trim($format), $datetime_str);
//        $errs = \DateTime::getLastErrors();
//        print_r('<br>get_datetime_from_string: ');
//        var_dump($result, $errs);
//        return ($result && $errs['warning_count'] == 0 && $errs['error_count'] == 0)
//            ? $result
//            : false;
//    }
    
    private function verify_format($fecha_hora)
    {
        $re = '/^(\d{4})-(0[1-9]|1[0-2])-([0-2][0-9]|3[0-1])\s([0-1][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/';
        if (preg_match($re, $fecha_hora, $matches))
        {
            return true;
        }
        return false;
    }

    /** 
     * Verifica que ese día, el anterior y el posterior no esté ya asignado a otra comisión del mismo mix
     * En caso de ser "Recuperatorio Unico" no debe verificar anterior y posterior
     */
    function verificar_si_fecha_posible($fecha_hora, $comision, $verifica_ant_y_post)
    {
        $materia = catalogo::consultar('cursos', 'get_materia', array('comision'=>$comision));
        $fechas_ocupadas = $this->get_fechas_ya_asignadas($materia); 
//        print_r('<br>$fechas_ocupadas: ');
//        print_r($fechas_ocupadas);
        
        $fecha = rtrim(substr($fecha_hora, 0, 10));
//        print_r('<br>$fecha: ');
//        print_r($fecha);
        
        $ocupada = in_array($fecha, $fechas_ocupadas);

        if (!$verifica_ant_y_post)
        {
            if ($ocupada)
            {
                $msj = "La fecha solicitada $fecha ya se encuentra reservada. Intente con otra.";
                throw new error_guarani($msj);
            }
            return true;
        }
        //$dt = \DateTime::createFromFormat('!Y-m-d', $fecha);
        $fecha_ant = date('Y-m-d', strtotime('-1 day', strtotime($fecha)));
        $ocupada_ant = in_array($fecha_ant, $fechas_ocupadas);

        $fecha_sig = date('Y-m-d', strtotime('+1 day', strtotime($fecha)));
        $ocupada_sig = in_array($fecha_sig, $fechas_ocupadas);

        if ($ocupada||$ocupada_ant||$ocupada_sig)
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
//        print_r("<br> datos: ");
//        print_r($datos);
        
        $resultado = '';
        if (kernel::request()->isPost()) 
        {
            $comisiones = $this->get_comisiones_de_materia_con_dias_de_clase($datos['anio_academico_hash'], $datos['periodo_hash'], $datos['materia']);

            try 
            {
                kernel::db()->abrir_transaccion();
                foreach ($comisiones as $comision)
                {
                    $parametros['comision'] = $comision['COMISION'];

                    switch ($comision['ESCALA'])
                    {
                        case 'R  ': $parametros['escala_notas'] = 3; break;    
                        case 'P  ': $parametros['escala_notas'] = 6; break;   
                        case 'PyR': $parametros['escala_notas'] = 4; break;   
                    }

                    $dias_clase = catalogo::consultar('cursos', 'get_dias_clase', $parametros);

                    //PROMO
                    if ($comision['ESCALA'] == 'P  ' || $comision['ESCALA'] == 'PyR')
                    {
                        if (!empty($datos['fecha_promo1']))
                        {
                            $parametros['evaluacion'] = 1;
                            $parametros['fecha_hora'] = $this->get_fecha_hora($datos['fecha_promo1'], $dias_clase);
                            $this->verificar_si_fecha_posible($parametros['fecha_hora'], $parametros['comision'], true);
                            $resultado .= catalogo::consultar('cursos', 'alta_propuesta_evaluacion_parcial', $parametros);
                        }
                        if (!empty($datos['fecha_promo2']))
                        {
                            $parametros['evaluacion'] = 2;
                            $parametros['fecha_hora'] = $this->get_fecha_hora($datos['fecha_promo2'], $dias_clase);
                            $this->verificar_si_fecha_posible($parametros['fecha_hora'], $parametros['comision'], true);
                            $resultado .= catalogo::consultar('cursos', 'alta_propuesta_evaluacion_parcial', $parametros);
                        }
                        if (!empty($datos['fecha_recup']))
                        {
                            $parametros['evaluacion'] = 7;
                            $parametros['fecha_hora'] = $this->get_fecha_hora($datos['fecha_recup'], $dias_clase);
                            $this->verificar_si_fecha_posible($parametros['fecha_hora'], $parametros['comision'], false);
                            $resultado .= catalogo::consultar('cursos', 'alta_propuesta_evaluacion_parcial', $parametros);
                        }
                        if (!empty($datos['fecha_integ']))
                        {
                            $parametros['evaluacion'] = 14;
                            $parametros['fecha_hora'] = $this->get_fecha_hora($datos['fecha_integ'], $dias_clase);
                            $this->verificar_si_fecha_posible($parametros['fecha_hora'], $parametros['comision'], true);
                            $resultado .= catalogo::consultar('cursos', 'alta_propuesta_evaluacion_parcial', $parametros);
                        }
                    }

                    //REGULAR
                    if ($comision['ESCALA'] == 'R  ' || $comision['ESCALA'] == 'PyR')
                    {
                        if (!empty($datos['fecha_regu1']))
                        {
                            $parametros['evaluacion'] = 21;
                            $parametros['fecha_hora'] = $this->get_fecha_hora($datos['fecha_regu1'], $dias_clase);
                            $this->verificar_si_fecha_posible($datos['fecha_regu1'], $parametros['comision'], true);
                            $resultado .= catalogo::consultar('cursos', 'alta_propuesta_evaluacion_parcial', $parametros);
                        }
                        if (!empty($datos['fecha_recup1']))
                        {
                            $parametros['evaluacion'] = 4;
                            $parametros['fecha_hora'] = $this->get_fecha_hora($datos['fecha_recup1'], $dias_clase);
                            $this->verificar_si_fecha_posible($datos['fecha_recup1'], $parametros['comision'], true);
                            $resultado .= catalogo::consultar('cursos', 'alta_propuesta_evaluacion_parcial', $parametros);
                        }
                        if (!empty($datos['fecha_recup2']))
                        {
                            $parametros['evaluacion'] = 5;
                            $parametros['fecha_hora'] = $this->get_fecha_hora($datos['fecha_recup2'], $dias_clase);
                            $this->verificar_si_fecha_posible($datos['fecha_recup2'], $parametros['comision'], true);
                            $resultado .= catalogo::consultar('cursos', 'alta_propuesta_evaluacion_parcial', $parametros);
                        }
                    }
                }
                
                $resultado_obs = '';
                //Observaciones
                if (!empty($datos['observaciones']))
                {
                    $param['materia'] = $datos['materia'];
                    $param['anio_academico'] = $this->decodificar_anio_academico($datos['anio_academico_hash']);
                    $param['periodo_lectivo'] = $this->decodificar_periodo($datos['periodo_hash'], $param['anio_academico']);
                    $param['observaciones'] = $datos['observaciones'];
                    $resultado_obs = catalogo::consultar('cursos', 'set_evaluaciones_observaciones', $param);
                }

                $this->set_anio_academico($datos['anio_academico_hash']);
                $this->set_periodo($datos['periodo_hash']);
                $mensaje = '';
                if (substr($resultado, 0, 1) == 1)
                {        
                    $mensaje = 'Se han guardado las fechas solicitadas para todas las comisiones. ';
                }
                if ($resultado_obs == '1')
                {
                    $mensaje .= 'Se han guardado las observaciones. ';
                }
                if (strlen($mensaje)>0)
                {
                    $mensaje .= ' Materia: '.$datos['materia_nombre'].'. ';
                }
                $this->set_mensaje($mensaje);
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
        if ($parametros['calidad'] == 'P  ' || $parametros['calidad'] == 'PyR' )
        {
            $parametros = $this->get_parametros_materia_promo($parametros);
        }

        //REGULAR
        if ($parametros['calidad'] == 'R  ' || $parametros['calidad'] == 'PyR' )
        {
            $parametros = $this->get_parametros_materia_regu($parametros);
        }
        
        $parametros['anio_academico_hash']  = $this->validate_param('anio_academico_hash', 'post', validador::TIPO_TEXTO);
        $parametros['periodo_hash']         = $this->validate_param('periodo_hash', 'post', validador::TIPO_TEXTO); 
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
}
?>