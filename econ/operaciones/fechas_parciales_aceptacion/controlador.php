<?php
namespace econ\operaciones\fechas_parciales_aceptacion;

use kernel\kernel;
use siu\extension_kernel\controlador_g3w2;
use siu\modelo\datos\catalogo;
use kernel\util\validador;
//use siu\modelo\guarani_notificacion;


class controlador extends controlador_g3w2
{
    protected $datos_filtro = array('anio_academico'=>"", 'periodo'=>"", 'carrera'=>"", 'mix'=>"", 'mensaje'=>'', 'mensaje_error'=>'', 'comision'=>"");
    
    function modelo()
    {
    }

    function accion__index()
    {
        if (!empty($_GET['formulario_filtro'])) {
            $this->datos_filtro = $_GET['formulario_filtro'];
        }
    }
    
    function get_materias_cincuentenario($carrera, $mix)
    {
        $parametros['legajo'] = null;
        $parametros['carrera'] = null;
        $parametros['mix'] = null;
        
        if (isset($carrera))
        {
            $parametros['carrera'] = $carrera;
        }
        if (isset($mix))
        {
            $parametros['mix'] = $mix;
        }
        $materias = catalogo::consultar('cursos', 'get_materias_cincuentenario', $parametros);
        return $materias;
    }
    
    function get_materias_y_comisiones_cincuentenario($anio_academico_hash, $periodo_hash, $carrera, $mix)
    {
        $materias = $this->get_materias_cincuentenario($carrera, $mix); 
        //MATERIA, MATERIA_NOMBRE

        $datos = array();
        $cant = count($materias);
        for ($i=0; $i<$cant; $i++)
        {
            $materias[$i]['CICLO'] = catalogo::consultar('cursos', 'get_ciclo_de_materias', array('materia'=>$materias[$i]['MATERIA'])); 
            //F - P - FyP

            $materias[$i]['OBSERVACIONES'] = $this->get_observaciones_materia($anio_academico_hash, $periodo_hash, $materias[$i]['MATERIA']);
            
            $comisiones = $this->get_comisiones_de_materia_con_dias_de_clase($anio_academico_hash, $periodo_hash, $materias[$i]['MATERIA']);
            //COMISION, COMISION_NOMBRE, ANIO_ACADEMICO, PERIODO_LECTIVO, ESCALA, TURNO, CARRERA, OBSERVACIONES
                
            if (count($comisiones) > 0)
            {
                $materias[$i]['CALIDAD'] = $this->get_tipo_escala_de_materia($anio_academico_hash, $periodo_hash, $materias[$i]['MATERIA']);
                // 'P', 'R' o 'PyR'

                $comisiones = $this->get_dias_de_clase_comision($comisiones);
                //DIAS_CLASE [DIA_SEMANA, HS_COMIENZO_CLASE, HS_FINALIZ_CLASE, DIA_NOMBRE]
                
                $comisiones = $this->get_fechas_no_validas($comisiones);
                //DIAS_NO_VALIDOS
                
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

                    $parametros = array(
                                'anio_academico' => $anio_academico,
                                'periodo' => $periodo,
                                'materia' => $materia
                    );
                    return catalogo::consultar('cursos', 'get_comisiones_de_materia_con_dias_de_clase', $parametros);
                }
            }
        }
        return null;
    }
    
    function get_observaciones_materia($anio_academico_hash, $periodo_hash, $materia)
    {
        $periodo = null;
        $anio_academico = null;

        if (!empty($anio_academico_hash))
        {
            $anio_academico = $this->decodificar_anio_academico($anio_academico_hash);
            if (!empty($anio_academico) and !empty($periodo_hash))
            {
                $periodo = $this->decodificar_periodo($periodo_hash, $anio_academico);

                $parametros = array(
                            'anio_academico' => $anio_academico,
                            'periodo' => $periodo,
                            'materia' => $materia
                );
                return catalogo::consultar('cursos', 'get_observaciones_materia', $parametros);
            }
            return null;
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
            $comision['DIAS_NO_VALIDOS_JOSN'] = json_encode($arreglo);
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
                $comision['EVAL_PROMO1'] = $eval[0];
                $comision['EVAL_PROMO1']['READONLY'] = $this->is_eval_readonly($eval[0]);

                $parametros['evaluacion'] = 2;
                $eval = catalogo::consultar('cursos', 'get_evaluaciones_existentes', $parametros);
                $comision['EVAL_PROMO2'] = $eval[0];
                $comision['EVAL_PROMO2']['READONLY'] = $this->is_eval_readonly($eval[0]);

                $parametros['evaluacion'] = 7;
                $eval = catalogo::consultar('cursos', 'get_evaluaciones_existentes', $parametros);
                $comision['EVAL_RECUP'] = $eval[0];
                $comision['EVAL_RECUP']['READONLY'] = $this->is_eval_readonly($eval[0]);

                $parametros['evaluacion'] = 14;
                $eval = catalogo::consultar('cursos', 'get_evaluaciones_existentes', $parametros);
                $comision['EVAL_INTEG'] = $eval[0];
                $comision['EVAL_INTEG']['READONLY'] = $this->is_eval_readonly($eval[0]);
                
                $parametros['evaluacion'] = 4;
                $eval = catalogo::consultar('cursos', 'get_evaluaciones_existentes', $parametros);
                $comision['EVAL_PROMRECUP1'] = $eval[0];
                
                $parametros['evaluacion'] = 5;
                $eval = catalogo::consultar('cursos', 'get_evaluaciones_existentes', $parametros);
                $comision['EVAL_PROMRECUP2'] = $eval[0];
            }
            
            //REGULAR
            if ($escala == 'R  ' || $escala == 'PyR')
            {
                $parametros['evaluacion'] = 21;
                $eval = catalogo::consultar('cursos', 'get_evaluaciones_existentes', $parametros);
                $comision['EVAL_REGU1'] = $eval[0];
                $comision['EVAL_REGU1']['READONLY'] = $this->is_eval_readonly($eval[0]);

                $parametros['evaluacion'] = 4;
                $eval = catalogo::consultar('cursos', 'get_evaluaciones_existentes', $parametros);
                $comision['EVAL_RECUP1'] = $eval[0];
                $comision['EVAL_RECUP1']['READONLY'] = $this->is_eval_readonly($eval[0]);

                $parametros['evaluacion'] = 5;
                $eval = catalogo::consultar('cursos', 'get_evaluaciones_existentes', $parametros);
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
                        $this->form_builder = kernel::localizador()->instanciar('operaciones\fechas_parciales_aceptacion\filtro\builder_form_filtro');
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
    
    /** Graba sólo la comisión */
    function accion__grabar_comision()
    {
        $mensaje = array();
        $datos = $this->get_parametros_grabar_comision();

        $materia = catalogo::consultar('cursos', 'get_materia', array('comision'=>$datos['comision']));
        $ciclo = catalogo::consultar('cursos', 'get_ciclo_de_materias', array('materia'=>$materia));

        //Instancias PROMO
        if (isset($datos['fecha_hora_promo1']))
        {
            $datos['evaluacion'] = 1;
            $datos['fecha_hora'] = $datos['fecha_hora_promo1']; 
            $datos['estado'] = $datos['opcion_promo1'];
            $mensaje[] = catalogo::consultar('cursos', 'alta_evaluacion_parcial', $datos);
        }
        if (isset($datos['fecha_hora_promo2']))
        {
            $datos['evaluacion'] = 2;
            $datos['fecha_hora'] = $datos['fecha_hora_promo2'];
            $datos['estado'] = $datos['opcion_promo2'];
            $mensaje[] = catalogo::consultar('cursos', 'alta_evaluacion_parcial', $datos);
            if (strpos($ciclo, 'P') !== false)
            {
                $datos_recup = $datos;
                $datos_recup['evaluacion'] = 4;
                $datos_recup['escala_notas'] = 3;
                $mensaje[] = catalogo::consultar('cursos', 'alta_evaluacion_parcial', $datos_recup);
            }
        }
        if (isset($datos['fecha_hora_recup']))
        {
            $datos['evaluacion'] = 7;
            $datos['fecha_hora'] = $datos['fecha_hora_recup'];
            $datos['estado'] = $datos['opcion_recup'];
            $mensaje[] = catalogo::consultar('cursos', 'alta_evaluacion_parcial', $datos);
        }
        if (isset($datos['fecha_hora_integ']))
        {
            $datos['evaluacion'] = 14;
            $datos['fecha_hora'] = $datos['fecha_hora_integ'];
            $datos['estado'] = $datos['opcion_integ'];
            $mensaje[] = catalogo::consultar('cursos', 'alta_evaluacion_parcial', $datos);
            if (strpos($ciclo, 'P') !== false)
            {
                $datos_recup = $datos;
                $datos_recup['evaluacion'] = 5;
                $datos_recup['escala_notas'] = 3;
                $mensaje[] = catalogo::consultar('cursos', 'alta_evaluacion_parcial', $datos_recup);
            }
        }
        
        //Instancias REGULAR
        if (isset($datos['fecha_hora_regu1']) && $datos['opcion_regu1'] != 'P')
        {
            $datos['evaluacion'] = 21;
            $datos['fecha_hora'] = $datos['fecha_hora_regu1']; 
            $datos['estado'] = $datos['opcion_regu1'];
            $mensaje[] = catalogo::consultar('cursos', 'alta_evaluacion_parcial', $datos);
        }
        if (isset($datos['fecha_hora_recup1']))
        {
            $datos['evaluacion'] = 4;
            $datos['fecha_hora'] = $datos['fecha_hora_recup1'];
            $datos['estado'] = $datos['opcion_recup1'];
            $mensaje[] = catalogo::consultar('cursos', 'alta_evaluacion_parcial', $datos);
        }
        if (isset($datos['fecha_hora_recup2']))
        {
            $datos['evaluacion'] = 5;
            $datos['fecha_hora'] = $datos['fecha_hora_recup2'];
            $datos['estado'] = $datos['opcion_recup2'];
            $mensaje[] = catalogo::consultar('cursos', 'alta_evaluacion_parcial', $datos);
        }
        
        $this->set_anio_academico($datos['anio_academico_hash']);
        $this->set_periodo($datos['periodo_hash']);
        $this->set_carrera($datos['carrera']);
        $this->set_mix($datos['mix']);
        
        $this->set_comision($datos['comision']);
        
        $msg = $this->formatear_mensaje($mensaje);
        $this->set_mensaje($msg[0]);
        $this->set_mensaje_error($msg[1]);
    }

    private function formatear_mensaje($mensaje)
    {
        $msg = '';
        $msg_error = '';
        foreach($mensaje as $m)
        {
            ($m['success'] == 1) ? $msg .= $m['mensaje'] : $msg_error .= $m['mensaje'];
        }
        return array($msg, $msg_error);
    }
    
    function get_parametros_grabar_comision()
    {
        $parametros = array();
       
        $parametros['comision'] = $this->validate_param('comision', 'post', validador::TIPO_TEXTO);
        $escala = 'escala_'.$parametros['comision'];
        $parametros['tipo_escala'] = $this->validate_param($escala, 'post', validador::TIPO_TEXTO); 
        switch ($parametros['tipo_escala'])
        {
            case 'R  ': $parametros['escala_notas'] = 3; break;    
            case 'P  ': $parametros['escala_notas'] = 6; break;   
            case 'PyR': $parametros['escala_notas'] = 4; break;   
        }

        //Instancia PROMO
        $parametros = $this->get_parametros_fecha($parametros, 'promo1');
        $parametros = $this->get_parametros_fecha($parametros, 'promo2');
        $parametros = $this->get_parametros_fecha($parametros, 'recup');
        $parametros = $this->get_parametros_fecha($parametros, 'integ');
        
        //Instancia REGULAR
        $parametros = $this->get_parametros_fecha($parametros, 'regu1');
        $parametros = $this->get_parametros_fecha($parametros, 'recup1');
        $parametros = $this->get_parametros_fecha($parametros, 'recup2');

        $parametros['anio_academico_hash']  = $this->validate_param('anio_academico_hash', 'post', validador::TIPO_TEXTO);
        $parametros['periodo_hash']         = $this->validate_param('periodo_hash', 'post', validador::TIPO_TEXTO); 
        $parametros['carrera']              = $this->validate_param('carrera', 'post', validador::TIPO_TEXTO);
        $parametros['mix']                  = $this->validate_param('mix', 'post', validador::TIPO_TEXTO); 

        return $parametros;
        
    }

    function get_parametros_fecha($parametros, $instancia)
    {
        $comision = $parametros['comision'];

        $param_validar = 'aceptar_'.$instancia.'_'.$comision;
        $opcion_instancia = 'opcion_'.$instancia;
        $parametros[$opcion_instancia] = $this->validate_param($param_validar, 'post', validador::TIPO_TEXTO);
        $fecha_hora_instancia = 'fecha_hora_'.$instancia;
        
        switch ($parametros[$opcion_instancia])
        {
            case 'A':
                $param_validar = 'fecha_hora_'.$instancia.'_'.$comision;
                $parametros[$fecha_hora_instancia] = $this->validate_param($param_validar, 'post', validador::TIPO_TEXTO);
                break;
            case 'R':
                $param_validar = 'datepicker_'.$instancia.'_'.$comision;
                $fecha = $this->validate_param($param_validar, 'post', validador::TIPO_TEXTO);
                $hs_comienzo = catalogo::consultar('cursos', 'get_hora_comienzo_clase', array('comision'=>$comision, 'fecha'=>$fecha));
                $fecha = $this->format_fecha($fecha);
                $parametros[$fecha_hora_instancia] = $fecha.' '.$hs_comienzo;
                break;

            case 'N': 
                $param_validar = 'datepicker_'.$instancia.'_'.$comision;
                $fecha = $this->validate_param($param_validar, 'post', validador::TIPO_TEXTO);
                $hs_comienzo = catalogo::consultar('cursos', 'get_hora_comienzo_clase', array('comision'=>$comision, 'fecha'=>$fecha));
                $fecha = $this->format_fecha($fecha);
                $parametros[$fecha_hora_instancia] = $fecha.' '.$hs_comienzo;
                break;
            default: 
                $parametros[$fecha_hora_instancia] = null;
        }
        return $parametros;
    }
    
    private function format_fecha($fecha)
    {
        $exp = explode('/', $fecha);
        if (count($exp) > 1)
        {
            $dia = substr($fecha, 0, 2);
            $mes = substr($fecha, 3, 2);
            $anio = substr($fecha, 6, 4);
            $fecha_fromateada = $anio.'-'.$mes.'-'.$dia;
            return $fecha_fromateada;
        }
        return $fecha;
    }
}
?>