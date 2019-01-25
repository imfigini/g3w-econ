<?php
namespace econ\operaciones\fechas_parciales_aceptacion;

use kernel\kernel;
use siu\extension_kernel\controlador_g3w2;
use siu\modelo\datos\catalogo;
use kernel\util\validador;
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
        $parametros = array('legajo' => null);
        $perfil = kernel::persona()->perfil()->get_id();
        if ($perfil == 'COORD')
        {
            $parametros = array('legajo'=> kernel::persona()->get_legajo_docente());
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

    function get_mensaje()
    {
        return $this->datos_filtro['mensaje'];
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
        $mensaje = 'En desarrollo.... Sólo acepta la fecha propuesta. Falta implementar la modificación de fecha.  ';
        
        $datos = $this->get_parametros_grabar_comision();

        if ($datos['opcion_promo1'] == 'A')
        {
            $datos['evaluacion'] = 1;
            $datos['fecha_hora'] = $datos['fecha_hora_promo1']; 
            $mensaje .= catalogo::consultar('cursos', 'alta_evaluacion_parcial', $datos);
        }
        if ($datos['opcion_promo2'] == 'A')
        {
            $datos['evaluacion'] = 2;
            $datos['fecha_hora'] = $datos['fecha_hora_promo2'];
            $mensaje .= catalogo::consultar('cursos', 'alta_evaluacion_parcial', $datos);
        }
        if ($datos['opcion_recup'] == 'A')
        {
            $datos['evaluacion'] = 7;
            $datos['fecha_hora'] = $datos['fecha_hora_recup'];
            $mensaje .= catalogo::consultar('cursos', 'alta_evaluacion_parcial', $datos);
        }
        if ($datos['opcion_integ'] == 'A')
        {
            $datos['evaluacion'] = 14;
            $datos['fecha_hora'] = $datos['fecha_hora_integ'];
            $mensaje .= catalogo::consultar('cursos', 'alta_evaluacion_parcial', $datos);
        }
        $this->set_anio_academico($datos['anio_academico_hash']);
        $this->set_periodo($datos['periodo_hash']);
        $this->set_mensaje($mensaje);
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

        $opcion = 'aceptar_promo1_'.$parametros['comision'];
        $parametros['opcion_promo1'] = $this->validate_param($opcion, 'post', validador::TIPO_TEXTO);
        $fecha_hora = 'fecha_hora_promo1_'.$parametros['comision'];
        $parametros['fecha_hora_promo1'] = $this->validate_param($fecha_hora, 'post', validador::TIPO_TEXTO);
        
        $opcion = 'aceptar_promo2_'.$parametros['comision'];
        $parametros['opcion_promo2'] = $this->validate_param($opcion, 'post', validador::TIPO_TEXTO);
        $fecha_hora = 'fecha_hora_promo2_'.$parametros['comision'];
        $parametros['fecha_hora_promo2'] = $this->validate_param($fecha_hora, 'post', validador::TIPO_TEXTO);
        
        $opcion = 'aceptar_recup_'.$parametros['comision'];
        $parametros['opcion_recup'] = $this->validate_param($opcion, 'post', validador::TIPO_TEXTO);
        $fecha_hora = 'fecha_hora_recup_'.$parametros['comision'];
        $parametros['fecha_hora_recup'] = $this->validate_param($fecha_hora, 'post', validador::TIPO_TEXTO);
        
        $opcion = 'aceptar_integ_'.$parametros['comision'];
        $parametros['opcion_integ'] = $this->validate_param($opcion, 'post', validador::TIPO_TEXTO);
        $fecha_hora = 'fecha_hora_integ_'.$parametros['comision'];
        $parametros['fecha_hora_integ'] = $this->validate_param($fecha_hora, 'post', validador::TIPO_TEXTO);
        
        $parametros['anio_academico_hash']  = $this->validate_param('anio_academico_hash', 'post', validador::TIPO_TEXTO);
        $parametros['periodo_hash']         = $this->validate_param('periodo_hash', 'post', validador::TIPO_TEXTO); 

        return $parametros;
//        $dias_clse_com = 'dias_clase_'.$parametros['comision'];
//        $dias_clase = $this->validate_param($dias_clse_com, 'post', validador::TIPO_TEXTO);
//        $parametros['dias_clase'] = (array) json_decode($dias_clase);
//        $observaciones = 'observaciones_'.$parametros['comision'];
//        $parametros['observaciones'] = $this->validate_param($observaciones, 'post', validador::TIPO_TEXTO);
//
//        //PROMO
//        if ($parametros['tipo_escala'] == 'P  ' || $parametros['tipo_escala'] == 'PyR' )
//        {
//            $parametros = $this->get_parametros_comision_promo($parametros);
//        }
//
//        //REGULAR
//        if ($parametros['tipo_escala'] == 'R  ' || $parametros['tipo_escala'] == 'PyR' )
//        {
//            $parametros = $this->get_parametros_comision_regu($parametros);
//        }
//
//        switch ($parametros['tipo_escala'])
//        {
//            case 'R  ': $parametros['escala'] = 3; break;    
//            case 'P  ': $parametros['escala'] = 6; break;   
//            case 'PyR': $parametros['escala'] = 4; break;   
//        }
        
    }
    
    
    /** Graba sólo la comisión */
//    function accion__grabar_comision()
//    {
//        $datos = $this->get_parametros_grabar_comision();
////        print_r("<br> datos: ");
////        print_r($datos);
//
//        $mensaje = '';
//        if (kernel::request()->isPost()) 
//        {
//            $parametros['comision'] = $datos['comision'];
//            $parametros['escala_notas'] = $datos['escala'];
//
//            //PROMO
//            if (!empty($datos['fecha_promo1']))
//            {
//                $parametros['evaluacion'] = 1;
//                $parametros['fecha_hora'] = $datos['fecha_promo1'];
//                $mensaje .= catalogo::consultar('cursos', 'alta_evaluacion_parcial', $parametros);
//            }
//            if (!empty($datos['fecha_promo2']))
//            {
//                $parametros['evaluacion'] = 2;
//                $parametros['fecha_hora'] = $datos['fecha_promo2'];
//                $mensaje .= catalogo::consultar('cursos', 'alta_evaluacion_parcial', $parametros);
//            }
//            if (!empty($datos['fecha_recup']))
//            {
//                $parametros['evaluacion'] = 7;
//                $parametros['fecha_hora'] = $datos['fecha_recup'];
//                $mensaje .= catalogo::consultar('cursos', 'alta_evaluacion_parcial', $parametros);
//            }
//            if (!empty($datos['fecha_integ']))
//            {
//                $parametros['evaluacion'] = 14;
//                $parametros['fecha_hora'] = $datos['fecha_integ'];
//                $mensaje .= catalogo::consultar('cursos', 'alta_evaluacion_parcial', $parametros);
//            }
//
//            //REGULAR
//            if (!empty($datos['fecha_regu1']))
//            {
//                $parametros['evaluacion'] = 21;
//                $parametros['fecha_hora'] = $datos['fecha_regu1'];
//                $mesnaje .= catalogo::consultar('cursos', 'alta_evaluacion_parcial', $parametros);
//            }
//
//            if (!empty($datos['fecha_recup1']))
//            {
//                $parametros['evaluacion'] = 4;
//                $parametros['fecha_hora'] = $datos['fecha_recup1'];
//                $mensaje .= catalogo::consultar('cursos', 'alta_evaluacion_parcial', $parametros);
//            }
//            if (!empty($datos['fecha_recup2']))
//            {
//                $parametros['evaluacion'] = 5;
//                $parametros['fecha_hora'] = $datos['fecha_recup2'];
//                $mensaje .= catalogo::consultar('cursos', 'alta_evaluacion_parcial', $parametros);
//            }
//
//            //Observaciones
//            if (!empty($datos['observaciones']))
//            {
//                $parametros['observaciones'] = $datos['observaciones'];
//                catalogo::consultar('cursos', 'set_evaluaciones_observaciones', $parametros);
//            }
//
//            //.' de la materia '.$materia_nombre.'.';
//
//            //print_r($mensaje);
//            $this->set_anio_academico($datos['anio_academico_hash']);
//            $this->set_periodo($datos['periodo_hash']);
//            $this->set_mensaje($mensaje);
//        }
//    }
//    
//    function get_parametros_grabar_comision()
//    {
//        $parametros = array();
//       
//        $parametros['comision'] = $this->validate_param('comision', 'post', validador::TIPO_TEXTO);
//        $tipo_escala = 'escala_'.$parametros['comision'];
//        $parametros['tipo_escala'] = $this->validate_param($tipo_escala, 'post', validador::TIPO_TEXTO);
//        $dias_clse_com = 'dias_clase_'.$parametros['comision'];
//        $dias_clase = $this->validate_param($dias_clse_com, 'post', validador::TIPO_TEXTO);
//        $parametros['dias_clase'] = (array) json_decode($dias_clase);
//        $observaciones = 'observaciones_'.$parametros['comision'];
//        $parametros['observaciones'] = $this->validate_param($observaciones, 'post', validador::TIPO_TEXTO);
//
//        //PROMO
//        if ($parametros['tipo_escala'] == 'P  ' || $parametros['tipo_escala'] == 'PyR' )
//        {
//            $parametros = $this->get_parametros_comision_promo($parametros);
//        }
//
//        //REGULAR
//        if ($parametros['tipo_escala'] == 'R  ' || $parametros['tipo_escala'] == 'PyR' )
//        {
//            $parametros = $this->get_parametros_comision_regu($parametros);
//        }
//
//        switch ($parametros['tipo_escala'])
//        {
//            case 'R  ': $parametros['escala'] = 3; break;    
//            case 'P  ': $parametros['escala'] = 6; break;   
//            case 'PyR': $parametros['escala'] = 4; break;   
//        }
//        
//        $parametros['anio_academico_hash']  = $this->validate_param('anio_academico_hash', 'post', validador::TIPO_TEXTO);
//        $parametros['periodo_hash']         = $this->validate_param('periodo_hash', 'post', validador::TIPO_TEXTO); 
//        
//        return $parametros;        
//    }
//
//    private function get_parametros_comision_promo($parametros)
//    {
//        $promo1 = 'datepicker_comision_promo1_'.$parametros['comision'];
//        $promo2 = 'datepicker_comision_promo2_'.$parametros['comision'];
//        $recup = 'datepicker_comision_promo_recup_'.$parametros['comision'];
//        $integ = 'datepicker_comision_promo_integ_'.$parametros['comision'];
//
//        $fecha_promo1 = $this->validate_param($promo1, 'post', validador::TIPO_TEXTO);
//        $parametros['fecha_promo1'] = $this->format_fecha_hora($fecha_promo1, $parametros['dias_clase']);
//        
//        $fecha_promo2 = $this->validate_param($promo2, 'post', validador::TIPO_TEXTO);
//        $parametros['fecha_promo2'] = $this->format_fecha_hora($fecha_promo2, $parametros['dias_clase']);
//
//        $fecha_recup = $this->validate_param($recup, 'post', validador::TIPO_TEXTO);
//        $parametros['fecha_recup'] = $this->format_fecha_hora($fecha_recup, $parametros['dias_clase']);
//        
//        $fecha_integ = $this->validate_param($integ, 'post', validador::TIPO_TEXTO);
//        $parametros['fecha_integ'] = $this->format_fecha_hora($fecha_integ, $parametros['dias_clase']);
//        
//        return $parametros;
//    }
//    
//    private function get_parametros_comision_regu($parametros)
//    {
//        $regu1 = 'datepicker_comision_regu1_'.$parametros['comision'];
//        $recup1 = 'datepicker_comision_regu_recup1_'.$parametros['comision'];
//        $recup2 = 'datepicker_comision_regu_recup2_'.$parametros['comision'];
//
//        $fecha_regu1 = $this->validate_param($regu1, 'post', validador::TIPO_TEXTO);
//        $parametros['fecha_regu1'] = $this->format_fecha_hora($fecha_regu1, $parametros['dias_clase']);
//        
//        $fecha_recup1 = $this->validate_param($recup1, 'post', validador::TIPO_TEXTO);
//        $parametros['fecha_recup1'] = $this->format_fecha_hora($fecha_recup1, $parametros['dias_clase']);
//                
//        $fecha_recup2 = $this->validate_param($recup2, 'post', validador::TIPO_TEXTO);
//        $parametros['fecha_recup2'] = $this->format_fecha_hora($fecha_recup2, $parametros['dias_clase']);
//        
//        return $parametros;
//    }
//    
//    private function format_fecha_hora($fecha, $dias_clase)
//    {
//        $exp = explode('/', $fecha);
//        if (count($exp) > 1)
//        {
//            $dia_semana = date("w",strtotime('mm/dd/yyyy',$fecha));
//            
//            foreach($dias_clase AS $d)
//            {
//                if ($d->{'DIA_SEMANA'} == $dia_semana)
//                {
//                    $hora = $d->{'HS_COMIENZO_CLASE'};
//                    $dia = substr($fecha, 0, 2);
//                    $mes = substr($fecha, 3, 2);
//                    $anio = substr($fecha, 6, 4);
//                    $fecha_fromateada = $anio.'-'.$mes.'-'.$dia;
//                    return $fecha_fromateada.' '.$hora;
//                }
//            }
//        }
//        return $fecha;
//    }
    
//   
//    private function format_fecha($fecha)
//    {
//        $exp = explode('/', $fecha);
//        if (count($exp) > 1)
//        {
//            $dia = substr($fecha, 0, 2);
//            $mes = substr($fecha, 3, 2);
//            $anio = substr($fecha, 6, 4);
//            $fecha_fromateada = $anio.'-'.$mes.'-'.$dia;
//            return $fecha_fromateada;
//        }
//        return $fecha;
//    }

}
?>