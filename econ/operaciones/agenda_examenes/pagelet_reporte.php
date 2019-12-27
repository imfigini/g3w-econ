<?php


namespace econ\operaciones\agenda_examenes;


use kernel\kernel;

class pagelet_reporte extends \siu\operaciones\agenda_examenes\pagelet_reporte
{
    public function get_columnas(){
        $titulo = array();
        $titulo['MESA_EXAMEN'] = array(ucfirst(kernel::traductor()->trans('agenda_examenes.mesa')), array('width' => 150));
        $titulo['FECHA'] = ucfirst(kernel::traductor()->trans('agenda_examenes.dia'));
        $titulo['HORA_INI'] = ucfirst(kernel::traductor()->trans('agenda_examenes.inicio'));
        $titulo['DOCENTE_ROL'] = ucfirst(kernel::traductor()->trans('agenda_examenes.rol'));
        $titulo['ANIO_ACADEMICO'] = ucfirst(kernel::traductor()->trans('agenda_examenes.anio_academico'));
        $titulo['TURNO_EXAMEN'] = ucfirst(kernel::traductor()->trans('agenda_examenes.turno'));
        $titulo['LLAMADO'] = ucfirst(kernel::traductor()->trans('agenda_examenes.llamado'));

        return $titulo;
    }

}