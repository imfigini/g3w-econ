<?php

namespace econ\operaciones\inicio_alumno;

use kernel\kernel;
use kernel\interfaz\pagelet;

class pagelet_lista_term_cond extends pagelet
{
    function get_nombre()
    {
        return 'lista_term_cond';
    }

    function prepare()
    {
        $this->data['is_periodo_integrador'] = $this->controlador->is_periodo_integrador();

        if ($this->data['is_periodo_integrador']) 
        {
            $this->data['acept_term_cond'] = $this->controlador->acepto_terminos_y_condiciones();
            $path = dirname(__FILE__);
            $this->data['term_y_cond'] = file_get_contents($path.'/lista_term_cond/terminosYcondiciones.html');
            $operacion = kernel::ruteador()->get_id_operacion();
            $this->add_var_js('url_grabar_acept_term_cond', kernel::vinculador()->crear($operacion, 'grabar_acept_term_cond'));
        }
    }
}
?>