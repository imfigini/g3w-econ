<?php
namespace econ\operaciones\agenda_examenes;

use kernel\interfaz\pagelet;
use kernel\kernel;
use kernel\util\u;
use siu\modelo\datos\catalogo;

class pagelet_reporte extends \siu\operaciones\agenda_examenes\pagelet_reporte 
{

	public function get_columnas(){
		$titulo = array();
		$titulo['MATERIA_NOMBRE_COMPLETO'] = array(ucfirst(kernel::traductor()->trans('agenda_examenes.materia')), array('width' => 150));
		$titulo['FECHA'] = ucfirst(kernel::traductor()->trans('agenda_examenes.dia'));
		$titulo['HORA_INI'] = ucfirst(kernel::traductor()->trans('agenda_examenes.inicio'));
		$titulo['HORA_FIN'] = ucfirst(kernel::traductor()->trans('agenda_examenes.fin'));
		$titulo['DOCENTE_ROL'] = ucfirst(kernel::traductor()->trans('agenda_examenes.rol'));
		$titulo['ANIO_ACADEMICO'] = ucfirst(kernel::traductor()->trans('agenda_examenes.anio_academico'));
		$titulo['TURNO_EXAMEN'] = ucfirst(kernel::traductor()->trans('agenda_examenes.turno'));
		//IRIS: Se modificó para que mestre el tipo de mesa y no el nombre de la mesa
		$titulo['TIPO_MESA'] = ucfirst(kernel::traductor()->trans('agenda_examenes.mesa'));
		$titulo['LLAMADO'] = ucfirst(kernel::traductor()->trans('agenda_examenes.llamado'));
		$titulo['AULA_NOMBRE'] = ucfirst(kernel::traductor()->trans('agenda_examenes.aula'));
		$titulo['EDIFICIO_NOMBRE'] = ucfirst(kernel::traductor()->trans('agenda_examenes.edificio'));
		$titulo['SEDE_NOMBRE_COMPLETO'] = ucfirst(kernel::traductor()->trans('agenda_examenes.sede'));

		return $titulo;
	}

}
?>