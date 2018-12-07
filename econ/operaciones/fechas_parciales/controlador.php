<?php
namespace econ\operaciones\fechas_parciales;

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
        if ($perfil == 'DOC')
        {
            $parametros = array('legajo'=> kernel::persona()->get_legajo_docente());
        }
        //die;
        $materias = catalogo::consultar('cursos', 'get_materias_cincuentenario', $parametros);
//        $operacion = kernel::ruteador()->get_id_operacion();
//        foreach($materias as $key => $materia)
//        {
//            $materias[$key]['LINK'] = kernel::vinculador()->crear($operacion, 'info_materia', array('parametro'=>1));
//        }
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
            //COMISION, COMISION_NOMBRE, ANIO_ACADEMICO, PERIODO_LECTIVO, ESCALA, TURNO, CARRERA

            if (count($comisiones) > 0)
            {
                $materias[$i]['CALIDAD'] = $this->get_tipo_escala_de_materia($anio_academico_hash, $periodo_hash, $materias[$i]['MATERIA']);
                // 'P', 'R' o 'PyR'

                $comisiones = $this->get_dias_de_clase_comision($comisiones);
                //DIAS_CLASE [DIA_SEMANA, HS_COMIENZO_CLASE, HS_FINALIZ_CLASE, DIA_NOMBRE]
                
                $comisiones = $this->get_fechas_no_validas($comisiones);

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
                
               // $materias[$i]['DIAS_NO_VALIDOS'] = $this->get_fechas_no_validas($materias[$i]['MATERIA']);
                $materias[$i]['FECHAS_OCUPADAS'] = $this->get_fechas_ya_asignadas($materias[$i]['MATERIA']);
               
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

//    function get_mensaje()
//    {
//        return $this->datos_filtro['mensaje'];
//    }
    
    function set_periodo($periodo)
    {
        $this->datos_filtro['periodo'] = $periodo;
    }

    function set_anio_academico($anio_academico)
    {
        $this->datos_filtro['anio_academico'] = $anio_academico;
    }

//    function set_mensaje($mensaje)
//    {
//        $this->datos_filtro['mensaje'] = $mensaje;
//    }

    /** Graba sólo la comisión */
    function accion__grabar_comision()
    {
        $parametros = $this->get_parametros_grabar_comision();
      
        $mensaje = '';
        if (kernel::request()->isPost()) 
        {
            try 
            {
                catalogo::consultar('cursos', 'set_porcentajes_instancias', $parametros);
                $comision_nombre = catalogo::consultar('cursos', 'get_nombre_comision', $parametros);
                $materia_nombre = catalogo::consultar('cursos', 'get_nombre_materia_de_comision', $parametros);
                $mensaje = 'Se actualizó correctamente los % de notas para el curso '.$comision_nombre.' de la materia '.$materia_nombre.'.';
                $this->set_anio_academico($parametros['anio_academico_hash']);
                $this->set_periodo($parametros['periodo_hash']);
                $this->set_mensaje($mensaje);
            }
            catch (\Exception $e) 
            {
                $mensaje = 'Error en la actualización.';
                $this->set_anio_academico($parametros['anio_academico_hash']);
                $this->set_periodo($parametros['periodo_hash']);
                $this->set_mensaje($e->getMessage().' - '.$mensaje);
            }
        }
        
    }
    
    function get_parametros_grabar_comision()
    {
        $parametros = array();
        $parametros['comision'] = $this->validate_param('comision', 'post', validador::TIPO_TEXTO);
        
        $parciales = 'porc_parciales_'.$parametros['comision'];
        $integrador = 'porc_integrador_'.$parametros['comision'];
        $trabajos = 'porc_trabajos_'.$parametros['comision'];
        
        $parametros['porc_parciales']       = $this->validate_param($parciales, 'post', validador::TIPO_TEXTO);
        $parametros['porc_integrador']      = $this->validate_param($integrador, 'post', validador::TIPO_TEXTO);
        $parametros['porc_trabajos']        = $this->validate_param($trabajos, 'post', validador::TIPO_TEXTO);
        $parametros['anio_academico_hash']  = $this->validate_param('anio_academico_hash', 'post', validador::TIPO_TEXTO);
        $parametros['periodo_hash']         = $this->validate_param('periodo_hash', 'post', validador::TIPO_TEXTO); 

        return $parametros;        
    }

    /** Graba todas las comisiones promocionables de la materia */
    function accion__grabar_materia()
    {
        $parametros = $this->get_parametros_grabar_materia();
        
        if (kernel::request()->isPost()) 
        {
            $comisiones = $this->get_comisiones($parametros['anio_academico_hash'], $parametros['periodo_hash'], $parametros['materia']);
            $result = 0;
            foreach($comisiones AS $comision)
            {
                $parametros['comision'] = $comision['COMISION'];
                $result = catalogo::consultar('cursos', 'set_porcentajes_instancias', $parametros);
            }
            if ($result)
            {
                $materia_nombre = catalogo::consultar('cursos', 'get_nombre_materia', $parametros);
                $mensaje = 'Se actualizó correctamente los % de notas para todos los cursos de la materia '.$materia_nombre.'.';
            }
            else
            {
                $mensaje = 'Error en la actualización.';
            }
            
            $this->set_anio_academico($parametros['anio_academico_hash']);
            $this->set_periodo($parametros['periodo_hash']);
            $this->set_mensaje($mensaje);
        }        
    }
    
    function get_parametros_grabar_materia()
    {
        $parametros = array();
        $parametros['materia'] = $this->validate_param('materia', 'post', validador::TIPO_TEXTO);
        
        $parciales = 'porc_parciales_'.$parametros['materia'];
        $integrador = 'porc_integrador_'.$parametros['materia'];
        $trabajos = 'porc_trabajos_'.$parametros['materia'];
        
        $parametros['porc_parciales']       = $this->validate_param($parciales, 'post', validador::TIPO_TEXTO);
        $parametros['porc_integrador']      = $this->validate_param($integrador, 'post', validador::TIPO_TEXTO);
        $parametros['porc_trabajos']        = $this->validate_param($trabajos, 'post', validador::TIPO_TEXTO);
        $parametros['anio_academico_hash']  = $this->validate_param('anio_academico_hash', 'post', validador::TIPO_TEXTO);
        $parametros['periodo_hash']         = $this->validate_param('periodo_hash', 'post', validador::TIPO_TEXTO); 

        return $parametros;        
    }

        
//    function get_materia($anio_academico_hash = null, $periodo_hash = null, $materia_hash = null)
//    {
//        $materia_actual = null;
//        
//        $materias = $this->get_materias_en_comision($anio_academico_hash, $periodo_hash);
//        foreach($materias as $materia){
//            if ($materia['__ID__'] == $materia_hash)
//            {
//                $materia_actual = $materia;
//            }
//        }
//        return $materia_actual;
//    }        
        
        
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
                        $this->form_builder = kernel::localizador()->instanciar('operaciones\fechas_parciales\filtro\builder_form_filtro');
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
}
?>