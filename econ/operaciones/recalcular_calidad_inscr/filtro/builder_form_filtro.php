<?php
namespace econ\operaciones\recalcular_calidad_inscr\filtro;

use kernel\interfaz\componentes\forms\form_elemento_config;
use kernel\kernel;
use kernel\util\validador;
use siu\extension_kernel\formularios\builder_formulario;
use siu\extension_kernel\formularios\fabrica_formularios;
use siu\extension_kernel\formularios\guarani_form;
use siu\modelo\datos\catalogo;
use siu\extension_kernel\formularios\guarani_form_elemento;

class builder_form_filtro extends builder_formulario
{
	function get_id_html() 
	{
		return 'formulario_filtro';
	}

	function get_action() 
	{
		return kernel::vinculador()->crear('recalcular_calidad_inscr', 'index');
	}

	protected function generar_definicion(guarani_form $form, fabrica_formularios $fabrica) 
	{
		$form->add_elemento($fabrica->elemento('anio_academico', array(
				form_elemento_config::label			=> ucfirst(kernel::traductor()->trans('actas.filtro_anio_academico')),
				form_elemento_config::filtro		=>  validador::TIPO_ALPHANUM,
				form_elemento_config::obligatorio	=> true,
				form_elemento_config::elemento		=> array('tipo' => 'select'),
				form_elemento_config::multi_options => self::get_anios_academicos(),
				form_elemento_config::validar_select => false,
				form_elemento_config::valor_default  =>   $this->anio_academico_hash,
				form_elemento_config::clase_css		 => 'filtros_comunes',
		)));
		
		$form->add_elemento($fabrica->elemento('periodo', array(
				form_elemento_config::label			=> ucfirst(kernel::traductor()->trans('actas.filtro_periodos')),
				form_elemento_config::filtro		=>  validador::TIPO_ALPHANUM,
				form_elemento_config::obligatorio	=> false,
				form_elemento_config::elemento		=> array('tipo' => 'select'),
				form_elemento_config::multi_options => self::get_periodos_lectivos(),
				form_elemento_config::validar_select => false,
				form_elemento_config::clase_css 	=> 'filtros_comunes',
		)));

		$form->add_elemento($fabrica->elemento('calidad', array(
			form_elemento_config::label			=> ucfirst(kernel::traductor()->trans(utf8_decode('calidad inscripción'))),
			form_elemento_config::filtro		=>  validador::TIPO_ALPHANUM,
			form_elemento_config::obligatorio	=> false,
			form_elemento_config::elemento		=> array('tipo' => 'select'),
			form_elemento_config::multi_options => self::get_calidades(),
			form_elemento_config::validar_select => false,
			form_elemento_config::clase_css 	=> 'filtros_comunes',
		)));
	
		$form->add_elemento($fabrica->elemento('materia', array(
			form_elemento_config::label			=> ucfirst(kernel::traductor()->trans(utf8_decode('materia'))),
			form_elemento_config::filtro		=>  validador::TIPO_ALPHANUM,
			form_elemento_config::obligatorio	=> false,
			form_elemento_config::elemento		=> array('tipo' => 'select'),
			form_elemento_config::multi_options => self::get_materias_cincuentenario(),
			form_elemento_config::validar_select => false,
			form_elemento_config::clase_css 	=> 'filtros_comunes',
		)));

		$form->add_accion($fabrica->accion_boton_submit('boton_buscar', ucfirst(kernel::traductor()->trans('actas.filtro_buscar'))));
	}

	/**
	 * Por defecto los filtros de guarani se basan en el layout grilla. Se debe configurar en este metodo para poder
	 * instanciar el mismo
	 */
	function get_configuracion_layout_grilla()
	{
		return array(
			array(
				'grupo' => 'filtros',
				'filas' => array(
					array(
						'anio_academico' => array('span' => 6),
						'periodo' => array('span' => 6),
					),
					array(
						'calidad' => array('span' => 6),
						'materia' => array('span' => 6),
					),
				)
			),
		);
	}
	
	function set_anio_academico($anio_academico_hash)
	{
		$this->anio_academico_hash = $anio_academico_hash;
	}
	
	function set_periodo($periodo_hash)
	{
		$this->periodo_hash = $periodo_hash;
	}

	function set_materia($materia)
	{
		$this->materia = $materia;
	}

	function set_calidad($calidad)
	{
		$this->calidad = $calidad;
	}
        
	static function get_anios_academicos()
	{
		$datos = catalogo::consultar('unidad_academica_econ', 'anios_academicos');
		//Esta operación sólo sirve a partir del año 2019, restirnjo los años anteriores
		$datos_2019 = Array();
		foreach($datos as $dato)
		{
			if ($dato['ANIO_ACADEMICO'] >= 2018) {
				$datos_2019[] = $dato;
			}
		}
		return guarani_form_elemento::armar_combo_opciones($datos_2019, '_ID_', 'ANIO_ACADEMICO', false, false, ucfirst(kernel::traductor()->trans('actas.filtro_seleccione')));
	}
	
	static function get_periodos_lectivos()
	{
		return array(""=>  kernel::traductor()->trans('recalcular_calidad_inscr.filtro_seleccione'));
	}

	static function get_calidades()
	{
		$datos = Array();
		$datos[] = Array('ID'=>'R', 'CALIDAD'=>'Regular');
		$datos[] = Array('ID'=>'P', 'CALIDAD'=>utf8_decode('Promoción'));
		return guarani_form_elemento::armar_combo_opciones($datos, 'ID', 'CALIDAD', false, false, kernel::traductor()->trans('recalcular_calidad_inscr.filtro_seleccione'));
	}

	static function get_materias_cincuentenario()
    {
		return array(""=>  kernel::traductor()->trans('recalcular_calidad_inscr.filtro_seleccione'));
	}
}
