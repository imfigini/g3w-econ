<?php
namespace econ\modelo\transacciones;

use kernel\kernel;
use siu\modelo\datos\catalogo;
use siu\errores\error_guarani;
use siu\errores\error_guarani_procesar_renglones;

class carga_asistencias extends \siu\modelo\transacciones\carga_asistencias
{

    //---------------------------------------------
    //	LISTA CLASES
    //---------------------------------------------

    function get_materias_y_dias_clases()
    {
        $parametros = array('legajo' => kernel::persona()->get_legajo_docente());
        return catalogo::consultar('carga_asistencias', 'get_materias_y_dias_clases', $parametros);
        //return $this->agrupar_x_comisiones_mismos_dias($materias_dias);
    }

    function get_comisiones_en_clase($parametros)
    {
        return catalogo::consultar('carga_asistencias', 'get_comisiones_en_clase', $parametros);
    }

    function get_horarios_comision($comision)
    {
        return catalogo::consultar('carga_asistencias', 'get_horarios_comision', array('comision'=>$comision));
    }


    function get_clases_comisiones($comisiones_id, $cant)
    {
        $comisiones = explode ('-', $comisiones_id);
        $parametros['comision'] = $comisiones[0];
        $parametros['filas'] = $cant;
        $datos = catalogo::consultar('carga_asistencias', 'get_clases_comision', $parametros);
        $param['comisiones'] = $comisiones_id;
        foreach(array_keys($datos) as $id)
        {
            $param['fecha'] = $datos[$id]['FECHA_CLASE'];
            $param['hs_comienzo_clase'] = $datos[$id]['HS_COMIENZO_CLASE'];
            $param['filas'] = $parametros['filas'];
            $datos[$id]['URL_EDITAR'] = kernel::vinculador()->crear('asistencias', 'editar', $param);
            $datos[$id]['COMISIONES'] = $comisiones_id;
        }
//        kernel::log()->add_debug('get_clases_comisiones $datos: '.__FILE__.' - '.__LINE__, $datos);
        return $datos;
    }

    function info__clase_cabecera($comisiones_id, $fecha, $hs_comienzo_clase, $filas)
    {
        $comisiones = explode ('-', $comisiones_id);
        $parametros['fecha'] = $fecha;
        $parametros['hs_comienzo_clase'] = $hs_comienzo_clase;
        $parametros['comision'] = $comisiones[0];
        $datos = catalogo::consultar('carga_asistencias', 'get_datos_comision_enviada', $parametros);
        $nombre_comisiones = '';
        foreach ($comisiones as $comision)
        {
            $result = catalogo::consultar('carga_asistencias', 'get_nombre_comision', array('comision'=>$comision));
            $nombre_comisiones .= $result['NOMBRE'];
            $nombre_comisiones .=  ' ('.$comision.') - ';
        }
        $datos['COMISIONES_NOMBRE'] = substr($nombre_comisiones , 0, strlen($nombre_comisiones)-2);
        return $datos;
    }

    function info__clase_detalle($comisiones_id, $fecha, $hs_comienzo_clase, $filas)
    {
        $comisiones = explode ('-', $comisiones_id);
        $parametros['fecha'] = $fecha;
        $parametros['hs_comienzo_clase'] = $hs_comienzo_clase;
        $parametros['filas'] = $filas;
        $parametros['comisiones'] = $comisiones_id;
        $result = array();
        foreach($comisiones as $comision)
        {
            $parametros['comision'] = $comision;
//            kernel::log()->add_debug('info__clase_detalle $parametros: '.__FILE__.' - '.__LINE__, $parametros);
            $clase = catalogo::consultar('carga_asistencias', 'get_clase_comision', $parametros);
//            kernel::log()->add_debug('info__clase_detalle $clase: '.__FILE__.' - '.__LINE__, $clase);
            $r = catalogo::consultar('carga_asistencias', 'clase_detalle', array('clase'=>$clase['CLASE']));
//            kernel::log()->add_debug('info__clase_detalle $r: '.__FILE__.' - '.__LINE__, $r);
            $result = array_merge($result, $r);
        }
        kernel::log()->add_debug('$result: '.__FILE__.' - '.__LINE__, $result);
        return $result;
    }


    function generar_asistencias($comisiones_id, $fecha, $hs_comienzo_clase)
    {
        $comisiones = explode ('-', $comisiones_id);
        kernel::log()->add_debug('generar_asistencias $comisiones: '.__FILE__.' - '.__LINE__, $comisiones);
        foreach($comisiones as $comision)
        {
            $clase = catalogo::consultar('carga_asistencias', 'get_clase_comision', array('comision'=>$comision, 'fecha'=>$fecha, 'hs_comienzo_clase'=>$hs_comienzo_clase));
            kernel::log()->add_debug('generar_asistencias $clase: '.__FILE__.' - '.__LINE__, $clase);
            $tiene = catalogo::consultar('carga_asistencias', 'tiene_cargadas_asistencias', array('clase'=>$clase['CLASE']));
            kernel::log()->add_debug('generar_asistencias $tiene: '.__FILE__.' - '.__LINE__, $tiene);
            if (! $tiene) 
            {
                catalogo::consultar('carga_asistencias', 'recuperar_generar_asistencias', array('clase'=>$clase['CLASE']));
            }
        }
    }
    
    function grabar($comisiones_id, $fecha, $hs_comienzo_clase, $alumnos)
    {
        $error = new error_guarani_procesar_renglones('Error cargando notas');
        $hay_renglones_actualizados = false;
        
        $this->generar_asistencias($comisiones_id, $fecha, $hs_comienzo_clase);
        //die;
        $clases = $this->info__clase_detalle($comisiones_id, $fecha, $hs_comienzo_clase, 0);
        
        kernel::log()->add_debug('grabar $clases', $clases);
        kernel::log()->add_debug('grabar $alumnos', $alumnos);
        foreach ($alumnos as $id => $alumno)
        {
            try
            {
                $datos_clase = $this->get_datos_alumno_clase($clases, $id);
                $parametros = array();
                $parametros['carrera'] = $datos_clase['CARRERA'];
                $parametros['legajo'] = $datos_clase['LEGAJO'];
                $parametros['comision'] = $datos_clase['COMISION'];
                $parametros['clase'] = $datos_clase['CLASE'];
                $parametros['cant_inasist'] = $alumno['CANT_INASIST'];
                $parametros['justific'] = $alumno['JUSTIFIC'];

                $ok = catalogo::consultar('carga_asistencias', 'guardar', $parametros);
                if($ok[0] == 1) {
                    $hay_renglones_actualizados = true;
                }
                else {
                    $error->add_renglon($parametros['legajo'], util::mensaje($ok[1]), $parametros);
                }
            }
            catch (error_kernel $e)
            {
                $error->add_renglon($parametros['legajo'], $e->getMessage(), $parametros);
                //kernel::log()->add_error($e);
            }
        }
        if($error->hay_renglones()) {
            throw $error;
        }
    }

    function get_datos_alumno_clase($clases, $url_alumno)
    {
        foreach($clases as $clase)
        {
            if($clase['__ID__']==$url_alumno){
                return $clase;
            }
        }
        throw new error_guarani('Clase invalida');
    }

    function get_motivos_inasistencia()
    {
        return catalogo::consultar('carga_asistencias', 'get_motivos_inasistencia', null);
    }


    function get_planilla($seleccion)
    {
        //kernel::log()->add_debug('$seleccion', $seleccion);
        $comisiones_id = $seleccion['comisiones'];
        $comisiones = explode('-', $comisiones_id);
        $seleccion['tipo'] = 'T'; //tipo estado_asistencia = L - Libre / T - Todos
        $seleccion['tipo_clase'] = null;
        $datos_planilla = array();
        foreach($comisiones AS $comision)
        {
            $seleccion['comision'] = $comision;
            $r = catalogo::consultar('carga_asistencias', 'get_planilla', $seleccion);
            $datos_planilla = $this->merge_datos_planilla($datos_planilla, $r);
        }
//        kernel::log()->add_debug('$datos_planilla', $datos_planilla);
        return $datos_planilla;
    }

    private function merge_datos_planilla($datos_planilla, $nuevos_datos)
    {
        $key = $this->array_key_first($datos_planilla);
        if (!isset($key))
        {
            $datos_planilla = $nuevos_datos;
            $key = $this->array_key_first($datos_planilla);
            $datos_planilla[$key]['COMISION_NOMBRE'] .= ' ('.$datos_planilla[$key]['COMISION'].')';
        }
        else
        {
            $datos_planilla[$key]['COMISION'] = 'Todas';
            $datos_planilla[$key]['COMISION_NOMBRE'] .= ' | '.$nuevos_datos[$key]['COMISION_NOMBRE'].' ('.$nuevos_datos[$key]['COMISION'].')';
            if ($datos_planilla[$key]['DOCENTES'] <> $nuevos_datos[$key]['DOCENTES'])
            {
                $datos_planilla[$key]['DOCENTES'] .= ' | '.$nuevos_datos[$key]['DOCENTES'];
            }
            $cant = count($datos_planilla[$key]['ALUMNOS']);
            foreach($nuevos_datos[$key]['ALUMNOS'] AS $k => $unused)
            {
                $nuevos_datos[$key]['ALUMNOS'][$k]['NRO'] += $cant;
            }
            $datos_planilla[$key]['ALUMNOS'] = array_merge($datos_planilla[$key]['ALUMNOS'], $nuevos_datos[$key]['ALUMNOS']);
        }
        return $datos_planilla;

    }

    private function array_key_first(array $arr)
    {
        foreach($arr as $key => $unused)
        {
            return $key;
        }
        return NULL;
    }

    function get_resumen($seleccion)
    {
        //kernel::log()->add_debug('get_resumen $seleccion', $seleccion);
        $comisiones_id = $seleccion['comisiones'];
        $comisiones = explode('-', $comisiones_id);
        $datos_resumen = null;
        foreach($comisiones AS $comision)
        {
            $r = array();

            $docentes = catalogo::consultar('carga_asistencias', 'get_docentes_com', array('comision'=>$comision));
            $aulas = catalogo::consultar('carga_asistencias', 'get_asignac_com', array('comision'=>$comision));

            $r['DOCENTES'] = $docentes[0];
            $r['HORARIO_AULAS'] = $aulas[0];

            $datos_comision = catalogo::consultar('carga_asistencias', 'get_datos_comision', array('comision'=>$comision));
            kernel::log()->add_debug('get_resumen $datos_comision', $datos_comision);

            $r['COMISION'] = $comision;
            $r['COMISION_NOMBRE'] = $datos_comision[0]['COMISION_NOMBRE'];
            $r['MATERIA'] = $datos_comision[0]['MATERIA'];
            $r['MATERIA_NOMBRE'] = $datos_comision[0]['MATERIA_NOMBRE'];
            $r['ANIO_ACADEMICO'] = $datos_comision[0]['ANIO_ACADEMICO'];
            $r['PERIODO_LECTIVO'] = $datos_comision[0]['PERIODO_LECTIVO'];
            $r['TURNO'] = $datos_comision[0]['TURNO'];

            $i=$t=0;
            $hoy = date("d/m/y");
            foreach ($datos_comision as $dc)
            {
                $r['FECHAS'][$dc['FECHA']] = $dc['DIA_NOMBRE'].' '.date("d/m/y", strtotime($dc['FECHA']));
                if ($r['FECHAS'][$dc['FECHA']] <= $hoy) {
                    $i++;
                }
                $t++;
            }
            $r['CANT_CLASES'] = count($r['FECHAS']);
            $r['TOTAL_CLASES'] = $t;

            //kernel::log()->add_debug('get_resumen $resumen', $resumen);

            $alumnos = catalogo::consultar('carga_asistencias', 'get_alumnos_inscriptos_comision', array('comision'=>$comision));
            //kernel::log()->add_debug('get_resumen $alumnos', $alumnos);

            foreach($alumnos AS $alumno)
            {
                $parametros['legajo'] = $alumno['LEGAJO'];
                $parametros['comision'] = $comision;
                $inasistencias = catalogo::consultar('carga_asistencias', 'get_inasistencias_alumno', $parametros);
                //kernel::log()->add_debug('get_resumen $inasistencias', $inasistencias);
                $r['ALUMNOS'][$alumno['LEGAJO']] = $this->set_asistencias($inasistencias, $alumno, $r['CANT_CLASES']);
            }
            $datos_resumen = $this->merge_datos_resumen($datos_resumen, $r);
        }
        kernel::log()->add_debug('get_resumen $datos_resumen', $datos_resumen);
        return $datos_resumen;

    }


    function set_asistencias($inasistencias, $alumno, $cant_clases)
    {
        
        foreach($inasistencias AS $inasistencia)
        {
            $fecha_clase = $inasistencia['FECHA'];

            if ((int)$inasistencia['CANT_JUSTIFICADAS'] == 1)
            {
                $alumno['ASISTENCIAS'][$fecha_clase] = 'J';
            }
            else
            {
                if ((int)$inasistencia['CANT_INASISTENCIAS'] == 1)
                {
                    $alumno['ASISTENCIAS'][$fecha_clase] = 'A';
                }
                else
                {
                    if (!is_null($inasistencia['CANT_INASISTENCIAS']))
                    {
                        $alumno['ASISTENCIAS'][$fecha_clase] = 'P';
                    }
                    else
                    {
                        $alumno['ASISTENCIAS'][$fecha_clase] = '';
                    }
                }
            }
        }
        $alumno['PORC_REAL'] = ($cant_clases-$alumno['CANT_ACUMULADAS'])*100/$cant_clases;
        $alumno['PORC_REAL'] = number_format($alumno['PORC_REAL'], 2);
        $alumno['PORC_JUST'] = ($cant_clases-$alumno['CANT_ACUMULADAS']+$alumno['CANT_JUSTIFICADAS'])*100/$cant_clases;
        $alumno['PORC_JUST'] = number_format($alumno['PORC_JUST'], 2);
        return $alumno;
    }

    private function merge_datos_resumen($datos, $nuevos_datos)
    {
//        kernel::log()->add_debug('merge_datos_resumen $datos', $datos);
//        kernel::log()->add_debug('merge_datos_resumen $nuevos_datos', $nuevos_datos);
        if (!isset($datos))
        {
            $datos = $nuevos_datos;
            $datos['COMISION_NOMBRE'] .= ' ('.(string)$datos['COMISION'].')';
        }
        else
        {
            $datos['COMISION'] = 'Todas';
            $datos['COMISION_NOMBRE'] .= ' | '.$nuevos_datos['COMISION_NOMBRE'].' ('.$nuevos_datos['COMISION'].')';
            if ($datos['DOCENTES'] <> $nuevos_datos['DOCENTES'])
            {
                $datos['DOCENTES'] .= ' | '.$nuevos_datos['DOCENTES'];
            }
            $datos['ALUMNOS'] = array_merge($datos['ALUMNOS'], $nuevos_datos['ALUMNOS']);
        }
        return $datos;
    }

}


?>
