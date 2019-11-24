<?php
namespace econ\operaciones\notas_cursada;

use kernel\kernel;
use siu\modelo\datos\catalogo;

class pagelet_renglones extends \siu\operaciones\notas_cursada\pagelet_renglones
{
    function prepare()
    {
		parent::prepare();
		
		$cierre_parcial = $this->controlador->cierre_parcial();
		$cierre_parcial_modifica_notas = $this->controlador->cierre_parcial_modifica_notas();
		
		$this->data['cierre_parcial']  = $cierre_parcial;
		$this->add_var_js('cierre_parcial', $cierre_parcial);
		$this->data['cierre_parcial_modifica_notas']  = $cierre_parcial_modifica_notas;
		$this->add_var_js('cierre_parcial_modifica_notas', $cierre_parcial_modifica_notas);
		
		$encabezado = $this->get_encabezado();		
		$this->add_var_js('fecha_inicio', $encabezado['FECHA_INICIO']);
		$this->add_var_js('fecha_fin', date('d/m/Y'));
		$this->add_var_js('prom_abierta', $encabezado['PROM_ABIERTA']);
		
		$this->data['encabezado']  = $encabezado;
		$this->data['condiciones'] = $this->get_condiciones();
		$this->add_var_js('url_autocomplete', kernel::vinculador()->crear('notas_cursada', 'auto_alumno', $this->get_comision()));
		
		//Iris: se agreg? para Autocalcular nota
		$this->add_var_js('url_autocalcular', kernel::vinculador()->crear('notas_cursada', 'autocalcular', $this->get_comision()));

		$this->add_mensaje_js('no_se_encontraron_alumnos', kernel::traductor()->trans('no_se_encontraron_alumnos'));
		$this->add_mensaje_js('nota_invalida', kernel::traductor()->trans('nota_invalida'));
		$this->add_mensaje_js('nota_no_coincide_condicion_seleccionada', kernel::traductor()->trans('nota_no_coincide_condicion_seleccionada'));
		$this->add_mensaje_js('fecha_invalida', kernel::traductor()->trans('fecha_invalida_acta', array(
			'%1%' => $encabezado['FECHA_INICIO'],
			'%2%' => date('d/m/Y')
		)));
		$this->add_mensaje_js('asistencia_invalida', kernel::traductor()->trans('asistencia_invalida'));
		$vinculo_mensaje = kernel::vinculador()->crear('mensajes', 'enviar_mensaje', array(
			'comision' =>  $this->get_url_comision()
		));
		
		$this->add_var_js('guardado_exitoso', kernel::traductor()->trans('notas_cursada_guardado_exitoso', array(
			'%1%' => $vinculo_mensaje
		)));
		$this->add_var_js('guardado_error', kernel::traductor()->trans('notas_cursada_guardado_error'));
		$this->add_var_js('msj_navegacion', kernel::traductor()->trans('msj_navegacion'));
    }

}
?>
