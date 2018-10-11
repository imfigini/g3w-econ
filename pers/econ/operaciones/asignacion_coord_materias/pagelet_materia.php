<?php
namespace econ\operaciones\asignacion_coord_materias;

use kernel\interfaz\pagelet;
use kernel\kernel;

class pagelet_materia extends pagelet 
{
    public function get_nombre()
    {
        return 'materia';
    }

    function prepare() 
    {
        $link_form = kernel::vinculador()->crear('asignacion_coord_materias', 'grabar');
        $this->data['form_url'] = $link_form;        
    } 
        
}