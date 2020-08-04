<?php
namespace econ\operaciones\notas_cursada;

use kernel\interfaz\pagelet;
use kernel\kernel;
use siu\guarani;

class pagelet_cabecera extends \siu\operaciones\notas_cursada\pagelet_cabecera
{

    function prepare()
    {
		$this->data = array();
		$this->data = $this->controlador->get_encabezado();
		$this->data['IN_MIX'] = $this->controlador->pertenece_mix_cincuentenario_80($this->data['MATERIA']);
		$this->add_var_js('msg_valor', kernel::traductor()->trans('valor'));
		$this->add_var_js('msg_descripcion', kernel::traductor()->trans('descripcion'));
		$this->add_var_js('msg_resultado', kernel::traductor()->trans('resultado'));
		$this->add_var_js('esconder_escala_notas', kernel::traductor()->trans('esconder_escala_notas'));
		$this->add_var_js('url_escala', kernel::vinculador()->crear('notas_cursada', 'info_escala', $this->data['ESCALA_NOTAS']));
    }
}
?>
