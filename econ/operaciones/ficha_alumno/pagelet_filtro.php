<?php
namespace econ\operaciones\ficha_alumno;

use kernel\interfaz\pagelet;
use kernel\kernel;

class pagelet_filtro extends \siu\operaciones\ficha_alumno\pagelet_filtro
{

	public function prepare()
	{
		$this->add_var_js('url_autocomplete_filtro', kernel::vinculador()->crear('ficha_alumno', 'auto_alumno'));
		$this->add_mensaje_js('no_se_encontraron_alumnos', ucfirst(kernel::traductor()->trans('ficha_alumno.no_se_encontraron_alumnos')));
		$this->add_mensaje_js('esconder_menu', ucfirst(kernel::traductor()->trans('ficha_alumno.esconder_menu')));
		$this->add_mensaje_js('mostrar_menu', ucfirst(kernel::traductor()->trans('ficha_alumno.mostrar_menu')));
		
		// Obtenemos el formulario del builder
		$form = $this->get_builder_form()->get_formulario();
		// Lo inicializamos
		$form->inicializar();
		// Se agrega al pagelet
		$this->add_form($form);
		
		$this->data['datos_alumno'] = $this->controlador->get_datos_alumno();
		$this->data['es_perfil_docente'] = $this->controlador->is_perfil_docente();
		
		if ($this->data['es_perfil_docente']) 
		{
			$fechas_turno_examen_actual = $this->controlador->get_fechas_turno_examen_actual();
			$this->data['inicio_turno'] = $fechas_turno_examen_actual['FECHA_INICIO'];
			$this->data['fin_turno'] = $fechas_turno_examen_actual['FECHA_FIN'];

			$periodo_integrador_actual = $this->controlador->get_periodo_integrador_actual();
			$this->data['inicio_integrador'] = $periodo_integrador_actual['FECHA_INICIO'];
			$this->data['fin_integrador'] = $periodo_integrador_actual['FECHA_FIN'];
		}

	}
}
?>