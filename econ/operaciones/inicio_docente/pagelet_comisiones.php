<?php
namespace econ\operaciones\inicio_docente;

use kernel\interfaz\pagelet;
use kernel\kernel;
use siu\guarani;
use siu\modelo\datos\catalogo;

class pagelet_comisiones extends \siu\operaciones\inicio_docente\pagelet_comisiones
{
	
    function prepare()
    {
		$evaluaciones = $this->get_lista_evaluaciones();
		$clases = $this->get_lista_clases();

		//Modificado el link para que lleve a la opertación de Asistencias
		foreach ($clases as $key => $clase) {
			$clases[$key]['LINK'] = kernel::vinculador()->crear('asistencias', 'accion__mostrar_clases');
		}
		
		$data = array_merge($evaluaciones, $clases);
		
		$timestamp = array();
		foreach ($data as $key => $fila) {
			$timestamp[$key] = $fila['TS'];
		}		
		
		//Cambiado el orden en que se muestran las clases y los cursos, desde hoy en adelante. 
		array_multisort($timestamp, SORT_ASC, $data);
		$this->data['items'] = $data;
	
		$this->add_var_js('ver_mas', kernel::traductor()->trans('ver_mas'));
		$this->add_var_js('ocultar', kernel::traductor()->trans('ocultar'));
    }
}
?>
