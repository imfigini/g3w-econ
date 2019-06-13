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
            if (!isset($datos[$mat]['DIAS_CLASE'][$dia_semana]['CLASE'][$clase]['CANT_INSCRIPTOS']))
            {
                $datos[$mat]['DIAS_CLASE'][$dia_semana]['CLASE'][$clase]['CANT_INSCRIPTOS'] = $dc['CANT_INSCRIPTOS'];
            }
            else
            {
                $datos[$mat]['DIAS_CLASE'][$dia_semana]['CLASE'][$clase]['CANT_INSCRIPTOS'] += $dc['CANT_INSCRIPTOS'];
            }
            if (!isset($datos[$mat]['DIAS_CLASE'][$dia_semana]['CLASE'][$clase]['CANT_LIBRES']))
            {
                $datos[$mat]['DIAS_CLASE'][$dia_semana]['CLASE'][$clase]['CANT_LIBRES'] = $dc['CANT_LIBRES'];
            }
            else
            {
                $datos[$mat]['DIAS_CLASE'][$dia_semana]['CLASE'][$clase]['CANT_LIBRES'] += $dc['CANT_LIBRES'];
            }
//VER::::::::
//            $datos[$mat]['DIAS_CLASE'][$dia_semana]['CLASE'][$clase]['COMISIONES'][$com]['URL_LIBRES'] = kernel::vinculador()->crear('asistencias', 'libres', array('ID' => $dias_clases[catalogo::id]));
//            $datos[$mat]['DIAS_CLASE'][$dia_semana]['CLASE'][$clase]['COMISIONES'][$com]['URL_PLANILLA'] = kernel::vinculador()->crear('asistencias', 'planilla', array('ID' => $dias_clases[catalogo::id], 'SUBCO'=>'', 'TIPO_CLASE'=>''));
        }
        
        return $datos;
    }
	

    
    function prepare()
    {
		$this->data = array();
		$this->add_var_js('mostrar_clases', kernel::traductor()->trans('mostrar_clases'));
		$this->add_var_js('ocultar_clases', kernel::traductor()->trans('ocultar_clases'));
		$this->add_var_js('ver_ultimas', kernel::traductor()->trans('ver_ultimas'));
		
		switch ($this->estado) {
			case 'crear_parcial':
				$this->set_template('parcial');
				break;
			default:
                                $this->data['datos'] = $this->get_lista_clases();
                            
                                $this->add_var_js('url_mostrar_clases', kernel::vinculador()->crear('asistencias', 'mostrar_clases'));
		}
		
		//$this->data['url_form_filtro'] = kernel::vinculador()->crear('asistencias', 'filtrar');
		//$this->add_var_js('url_autocomplete_materia', kernel::vinculador()->crear('asistencias', 'buscar_materia'));
		//$this->add_var_js('url_autocomplete_docente', kernel::vinculador()->crear('asistencias', 'buscar_docente'));
		$this->perfil_activo = kernel::persona()->get_id_perfil_activo();
    }
	
	
}
?>