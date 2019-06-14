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
        $dias_clases = array();
        if($this->perfil_activo == 'DOC') {	
            $dias_clases = $this->controlador->modelo()->listado_dias_clases_docente();
        }
        
        $datos	= array();
        foreach ($dias_clases as $dc) 
        {
            $mat = $dc['MATERIA'];
            $datos[$mat]['MATERIA'] = $mat;
            $datos[$mat]['NOMBRE'] = $dc['MATERIA_NOMBRE'];

            $dia_semana = $dc['DIA_SEMANA'];
            $clase = $dc['HS_COMIENZO_CLASE'];
            
            $datos[$mat]['DIAS_CLASE'][$dia_semana]['DIA'] = $dia_semana;
            $datos[$mat]['DIAS_CLASE'][$dia_semana]['DIA_NOMBRE'] = $dc['DIA_NOMBRE'];
            $datos[$mat]['DIAS_CLASE'][$dia_semana]['CLASE'][$clase]['HS_COMIENZO'] = $clase;
            $datos[$mat]['DIAS_CLASE'][$dia_semana]['CLASE'][$clase]['HS_FINALIZ'] = $dc['HS_FINALIZ_CLASE'];
            $datos[$mat]['DIAS_CLASE'][$dia_semana]['CLASE'][$clase]['ANIO_ACADEMICO'] = $dc['ANIO_ACADEMICO'];
            $datos[$mat]['DIAS_CLASE'][$dia_semana]['CLASE'][$clase]['PERIODO_LECTIVO'] = $dc['PERIODO_LECTIVO'];
            $datos[$mat]['DIAS_CLASE'][$dia_semana]['CLASE'][$clase]['ID'] = $dc[catalogo::id];
            if (!isset($datos[$mat]['DIAS_CLASE'][$dia_semana]['CLASE'][$clase]['CANT_INSCRIPTOS'])) {
                $datos[$mat]['DIAS_CLASE'][$dia_semana]['CLASE'][$clase]['CANT_INSCRIPTOS'] = $dc['CANT_INSCRIPTOS'];
            }
            else  {
                $datos[$mat]['DIAS_CLASE'][$dia_semana]['CLASE'][$clase]['CANT_INSCRIPTOS'] += $dc['CANT_INSCRIPTOS'];
            }
//VER::::::::
            $datos[$mat]['DIAS_CLASE'][$dia_semana]['CLASE'][$clase]['URL_PLANILLA'] = kernel::vinculador()->crear('asistencias', 'planilla', array('ID' => $dias_clases[catalogo::id], 'TIPO_CLASE'=>''));
        }
        return $datos;
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