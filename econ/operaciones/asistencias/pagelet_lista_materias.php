<?php
namespace econ\operaciones\asistencias;

use kernel\interfaz\pagelet;
use kernel\kernel;
use siu\modelo\datos\catalogo;

class pagelet_lista_materias extends \siu\operaciones\asistencias\pagelet_lista_materias
{

    function get_lista_clases()
    {
        if (!isset($this->perfil_activo)) {
                $this->perfil_activo = kernel::persona()->get_id_perfil_activo();
        }
        $clases = array();
        if($this->perfil_activo == 'DOC') 
        {	
//            kernel::log()->add_debug('get_lista_clases: GET: '.__FILE__.' - '.__LINE__, $_GET);
//            kernel::log()->add_debug('get_lista_clases: POST: '.__FILE__.' - '.__LINE__, $_POST);
            $materias_dias = $this->controlador->modelo()->get_materias_y_dias_clases();
            $resultado = array();
            $comisiones_vistas = array();
            foreach($materias_dias as $dato)
            {
                $materia = $dato['MATERIA'];
                $comisiones = $this->controlador->modelo()->get_comisiones_en_clase($dato);
                if (!in_array($comisiones[0], $comisiones_vistas) )
                {
                    $clase['COMISIONES'] = $comisiones;
                    $clase['DIAS_CLASE'] = $this->controlador->modelo()->get_horarios_comision($comisiones[0]['COMISION']);
                    $clase['CANT_INSCRIPTOS'] = 0;
                    $clase['ID'] = '';
                    foreach ($comisiones AS $comision)
                    {
                        $clase['CANT_INSCRIPTOS'] += $comision['CANT_INSCRIPTOS'];
                        $clase['ID'] = $clase['ID'].'-'.$comision['COMISION'];
                    }
                    $clase['ID'] = substr($clase['ID'], 1, strlen($clase['ID']));
                    $parametros = array('comisiones'=>$clase['ID']);
//                    kernel::log()->add_debug('get_lista_clases: $clase: '.__FILE__.' - '.__LINE__, $clase);
//                    kernel::log()->add_debug('get_lista_clases: $parametros: '.__FILE__.' - '.__LINE__, $parametros);
                    $clase['URL_PLANILLA'] = kernel::vinculador()->crear('asistencias', 'planilla', $parametros);
                    $clase['URL_RESUMEN'] = kernel::vinculador()->crear('asistencias', 'resumen', $parametros);
                    $resultado[$materia]['MATERIA'] = $materia;
                    $resultado[$materia]['MATERIA_NOMBRE'] = $dato['MATERIA_NOMBRE'];
                    $resultado[$materia]['ANIO_ACADEMICO'] = $dato['ANIO_ACADEMICO'];
                    $resultado[$materia]['PERIODO_LECTIVO'] = $dato['PERIODO_LECTIVO'];
                    $resultado[$materia]['CLASES'][] = $clase;
                }
                $comisiones_vistas = array_merge($comisiones_vistas, $comisiones);
            }
        }
        kernel::log()->add_debug('get_lista_clases: '.__FILE__.' - '.__LINE__, $resultado);
        return $resultado;
    }
	
    
    function prepare()
    {
        $this->data = array();
        $this->add_var_js('mostrar_clases', kernel::traductor()->trans('mostrar_clases'));
        $this->add_var_js('ocultar_clases', kernel::traductor()->trans('ocultar_clases'));
        $this->add_var_js('ver_ultimas', kernel::traductor()->trans('ver_ultimas'));

        $this->data['datos'] = $this->get_lista_clases();
        $this->add_var_js('url_mostrar_clases', kernel::vinculador()->crear('asistencias', 'mostrar_clases'));

        $this->perfil_activo = kernel::persona()->get_id_perfil_activo();
    }

}
?>