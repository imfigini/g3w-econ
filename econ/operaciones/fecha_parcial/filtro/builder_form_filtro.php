<?php
namespace econ\operaciones\fecha_parcial\filtro;

use kernel\interfaz\componentes\forms\form_elemento_config;
use kernel\kernel;
use kernel\util\validador;
use \siu\extension_kernel\formularios\builder_formulario;
use \siu\extension_kernel\formularios\fabrica_formularios;
use \siu\extension_kernel\formularios\guarani_form;
use siu\modelo\datos\catalogo;
use siu\extension_kernel\formularios\guarani_form_elemento;
use siu\operaciones\fecha_parcial\controlador;

class builder_form_filtro extends builder_formulario
{

    protected $controlador;

    /**
     * @return controlador
     */
    function get_controlador(){
        if (! isset($this->controlador)) {
            $this->controlador = kernel::localizador()->instanciar('operaciones\fecha_parcial\controlador');
        }

        return $this->controlador;
    }

	function get_id_html() 
	{
		return 'formulario_filtro';
	}

	function get_action() 
	{
		return kernel::vinculador()->crear('fecha_parcial');
	}

	protected function generar_definicion(guarani_form $form, fabrica_formularios $fabrica) 
	{
		$form->add_elemento($fabrica->elemento('carrera', array(
				form_elemento_config::label			=> ucfirst(kernel::traductor()->trans('fecha_parcial.filtro_carrera')),
				form_elemento_config::filtro			=>  validador::TIPO_ALPHANUM,
				form_elemento_config::obligatorio	=> false,
				form_elemento_config::elemento		=> array('tipo' => 'select'),
				form_elemento_config::multi_options => self::get_carreras(),
				form_elemento_config::validar_select => false,
		)));

        $form->add_elemento($fabrica->elemento('plan', array(
            form_elemento_config::label			=> ucfirst(kernel::traductor()->trans('fecha_parcial.filtro_plan')),
            form_elemento_config::filtro			=>  validador::TIPO_ALPHANUM,
            form_elemento_config::obligatorio	=> false,
            form_elemento_config::elemento		=> array('tipo' => 'select'),
            form_elemento_config::multi_options => self::get_planes(),
            form_elemento_config::validar_select => false,
        )));

        $form->add_elemento($fabrica->elemento('anio_cursada', array(
            form_elemento_config::label			=> ucfirst(kernel::traductor()->trans('fecha_parcial.filtro_anio_cursada')),
            form_elemento_config::filtro			=>  validador::TIPO_ALPHANUM,
            form_elemento_config::obligatorio	=> false,
            form_elemento_config::elemento		=> array('tipo' => 'select'),
            form_elemento_config::multi_options => self::get_anios_cursada(),
            form_elemento_config::validar_select => false,
        )));

        $materia = $form->add_elemento($fabrica->elemento('materia_descr', array(
            form_elemento_config::label => ucfirst(kernel::traductor()->trans('fecha_parcial.filtro_materia')),
            form_elemento_config::filtro            => validador::TIPO_TEXTO,
            form_elemento_config::obligatorio       => false,
            form_elemento_config::elemento          => array('tipo' => 'text'),
            form_elemento_config::valor_default => '',
        )));
        $fabrica->elemento_decorar_boton_limpiar($materia, 'limpiar_materia');

        $form->add_elemento($fabrica->elemento('materia', array(
            form_elemento_config::filtro            => validador::TIPO_ALPHANUM,
            form_elemento_config::obligatorio       => false,
            form_elemento_config::elemento          => array('tipo' => 'hidden'),
            form_elemento_config::valor_default => '',
        )));

        $form->add_accion($fabrica->accion_boton_submit('boton_buscar', ucfirst(kernel::traductor()->trans('fecha_parcial.filtro_buscar'))));
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
                        'carrera' => array('span' => 12),
                    ),
                    array(
                        'plan' => array('span' => 12),
                    ),
                    array(
                        'anio_cursada' => array('span' => 12),
                    ),
                    array(
                        'materia_descr' => array('span' => 12),
                        'materia' => array('span' => 0)
                    ),
				)
			),
		);
	}
	
	function get_carreras()
	{
		$datos = catalogo::consultar('plan_estudios', 'carreras');
		return guarani_form_elemento::armar_combo_opciones($datos, '_ID_', 'NOMBRE', true, false, ucfirst(kernel::traductor()->trans('fecha_parcial.filtro_todas')));
	}

    function get_planes()
    {
        $formulario_filtro = kernel::request()->get('formulario_filtro');
        $carrera = "";
        if($formulario_filtro['carrera'] != "") $carrera = $this->get_controlador()->decodificar_carrera($formulario_filtro['carrera']);

        if($carrera){
            $datos = catalogo::consultar('plan_estudios', 'planes', array('carrera' => $carrera));
            return guarani_form_elemento::armar_combo_opciones($datos, '_ID_', 'PLAN', true, false, ucfirst(kernel::traductor()->trans('fecha_parcial.filtro_todos')));
        }
        else{
            return array('' => ucfirst(kernel::traductor()->trans('fecha_parcial.filtro_todos')));
        }

    }

    function get_anios_cursada()
    {
        return array('0'=>'--Todos--',
                    '1'=>1,
                    '2'=>2,
                    '3'=>3,
                    '4'=>4,
                    '5'=>5);
	}
}